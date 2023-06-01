<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

use SLiMS\DB;
use Ifsnop\Mysqldump as IMysqldump;

// key to get full database access
define('DB_ACCESS', 'fa');

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';

// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');

require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

function color($message, $type = 'white')
{
    switch ($type) {
        case 'success':
            $color = 'green';
            break;

        case 'info':
            $color = 'blue';
            break;

        case 'error':
            $color = 'red';
            break;        

        default:
            $color = 'white';
            break;
    }

    return '<strong style="color: ' . $color . '; font-family: Sans">' . $message . '</strong>';
}

function outputWithFlush($message)
{
    if (isset($_POST['verbose']) && $_POST['verbose'] == 'yes')
    {
        echo str_replace('\n', ' ', $message) . '<br/>';
        ob_flush();
        flush();
    }
}

// if backup process is invoked
if (isset($_POST['start']) && isset($_POST['tkn']) && $_POST['tkn'] === $_SESSION['token']) {
    outputWithFlush(color(__('Starting...')));
    sleep(2);
    $output = '';
    $error = false;
    // turn on implicit flush
    ob_implicit_flush();    

    // checking are the backup directory is exists and writable
    if (file_exists($sysconf['backup_dir']) AND is_writable($sysconf['backup_dir'])) {
        // execute the backup process
        try {
            // set for unlimited time
            ini_set('max_execution_time', 0);

            // time string to append to filename
            $time2append = (date('Ymd_His'));
            $dump = DB::backup();
            $dump->start($sysconf['backup_dir'].DS.'backup_'.$time2append.'.sql');

            $data['user_id'] = $_SESSION['uid'];
            $data['backup_time'] = date('Y-m-d H:i"s');
            $data['backup_file'] = $dbs->escape_string($sysconf['backup_dir'].'backup_'.$time2append.'.sql');
            $output = sprintf(__('Backup SUCCESSFUL, backup files saved to %s !'),str_replace('\'', '/',$sysconf['backup_dir']));

            if (!preg_match('@^WIN.*@i', PHP_OS)) {
                // get current directory path
                $curr_dir = getcwd();
                // change current PHP working dir
                @chdir($sysconf['backup_dir']);
                // compress the backup using tar gz
                if(function_exists('exec')){
                    outputWithFlush(color(__('Compressing...')));
                    sleep(1);
                    exec('tar cvzf backup_'.$time2append.'.sql.tar.gz backup_'.$time2append.'.sql', $outputs, $status);
                    if ($status == COMMAND_SUCCESS) {
                        // delete the original file
                        @unlink($data['backup_file']);
                        $output .= __("File is compressed using tar gz archive format");
                        $data['backup_file'] = $dbs->escape_string($sysconf['backup_dir'].'backup_'.$time2append.'.sql.tar.gz');
                        outputWithFlush(color(__('Compressing Success'), 'success'));
                    }
                }
                // return to previous PHP working dir
                @chdir($curr_dir);
            }
            // input log to database
            $sql_op = new simbio_dbop($dbs);
            $sql_op->insert('backup_log', $data);
            outputWithFlush(color($output, 'success'));
        } catch (\Exception $e) {
            $error = true;
            $output = sprintf(__('Backup FAILED!,\n%s'),$e->getMessage());
            outputWithFlush(color($output, 'error'));
        }
    } else {
        $error = true;
        $output = __("Backup FAILED! The Backup directory is not exists or not writeable") . '<br> ';
        $output .= __("Contact System Administrator for the right path of backup directory");
        outputWithFlush(color($output, 'error'));
    }
    
    // remove token
    unset($_SESSION['token']);

    if ($_POST['verbose'] == 'no' && !isset($_POST['response']))
    {
        utility::jsToastr(__('Backup'), $dbs->escape_string(strip_tags($output)), ($error ? 'error' : 'success'));
    }

    if (isset($_POST['response']) && $_POST['response'] === 'json')
    {
        exit(\SLiMS\Json::stringify(['status' => !$error, 'message' => strip_tags($output)]));
    }

    echo '<script type="text/javascript">setTimeout(() => { top.$(\'#mainContent\').simbioAJAX(\''.MWB.'system/backup.php\'); }, ' . ($_POST['verbose'] == 'no' ? 0 : 5000) . ')</script>';
    exit();
}

