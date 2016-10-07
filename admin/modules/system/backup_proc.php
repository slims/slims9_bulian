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

// if backup process is invoked
if (isset($_POST['start']) && isset($_POST['tkn']) && $_POST['tkn'] === $_SESSION['token']) {
	sleep(2);
    $output = '';
    // turn on implicit flush
    ob_implicit_flush();
    // checking if the binary can be executed
    exec($sysconf['mysqldump'], $outputs, $status);
    if ($status == BINARY_NOT_FOUND) {
        $output = 'The PATH for mysqldump program is NOT RIGHT! Please check your configuration file again for mysqldump path vars';
    } else {
        // checking are the backup directory is exists and writable
        if (file_exists($sysconf['backup_dir']) AND is_writable($sysconf['backup_dir'])) {
            // time string to append to filename
            $time2append = (date('Ymd_His'));
            // execute the backup process
            exec($sysconf['mysqldump'].' -B '.DB_NAME.' --no-create-db --quick --user='.DB_USERNAME.' --password='.DB_PASSWORD.' --host='.DB_HOST.' > '.$sysconf['backup_dir'].DS.'backup_'.$time2append.'.sql', $outputs, $status);
			if ($status == COMMAND_SUCCESS || $status == 1) {
                $data['user_id'] = $_SESSION['uid'];
                $data['backup_time'] = date('Y-m-d H:i"s');
                $data['backup_file'] = $dbs->escape_string($sysconf['backup_dir'].'backup_'.$time2append.'.sql');
                $output = 'Backup SUCCESSFUL, backup files saved to '.$sysconf['backup_dir'].'!';

                if (!preg_match('@^WIN.*@i', PHP_OS)) {
                    // get current directory path
                    $curr_dir = getcwd();
                    // change current PHP working dir
                    @chdir($sysconf['backup_dir']);
                    // compress the backup using tar gz
                    exec('tar cvzf backup_'.$time2append.'.sql.tar.gz backup_'.$time2append.'.sql', $outputs, $status);
                    if ($status == COMMAND_SUCCESS) {
                        // delete the original file
                        @unlink($data['backup_file']);
                        $output .= "File is compressed using tar gz archive format";
                        $data['backup_file'] = $dbs->escape_string($sysconf['backup_dir'].'backup_'.$time2append.'.sql.tar.gz');
                    }
                    // return to previous PHP working dir
                    @chdir($curr_dir);
                }

                // input log to database
                $sql_op = new simbio_dbop($dbs);
                $sql_op->insert('backup_log', $data);
            } else if ($status == COMMAND_FAILED) {
                $output = 'Backup FAILED! Wrong user or password to connect to database server!';
            }
        } else {
            $output = "Backup FAILED! The Backup directory is not exists or not writeable";
            $output .= "Contact System Administrator for the right path of backup directory";
        }
    }

    // remove token
    unset($_SESSION['token']);
    echo '<script type="text/javascript">top.alert(\''.$output.'\');</script>';
    echo '<script type="text/javascript">top.$(\'#mainContent\').simbioAJAX(\''.MWB.'system/backup.php\');</script>';
    exit();
}
?>
