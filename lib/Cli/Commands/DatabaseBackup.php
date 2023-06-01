<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-14 20:43:05
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

use SLiMS\DB;
use Ifsnop\Mysqldump as IMysqldump;
use Symfony\Component\Console\Helper\ProgressBar;

class DatabaseBackup extends \SLiMS\Cli\Command
{
    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'db:backup {--F|filename?} {--P|path?}';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'backup database';

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        $backup = DB::backup([    
            'compress' => IMysqldump\Mysqldump::NONE,
            'no-data' => false,
            'add-drop-table' => true,
            'single-transaction' => true,
            'lock-tables' => true,
            'add-locks' => false,
            'extended-insert' => false,
            'disable-keys' => true,
            'skip-triggers' => false,
            'add-drop-trigger' => true,
            'routines' => true,
            'databases' => false,
            'add-drop-database' => false,
            'hex-blob' => true,
            'no-create-info' => false,
            'where' => '',
            /**
             * an option for definer state in trigger query. 
             * For some case, user had bad experience 
             * when they move their SLiMS database to other database 
             * machine without same privileged user as trigger definer.
             */
            'skip-definer' => true
        ]);

        $progress = new ProgressBar($this->output);
        $progress->setBarCharacter('<info>+</info>');
        $progress->setFormat("%message% %current%/%max%");

        // Starting backup
        $path = ($this->option('path')??SB . 'files' . DS . 'backup' . DS);
        $filename = ($this->option('filename')??'backup-database-' . date('YmdHis') . '.sql');
        $backup->start($path . $filename, $progress);
        $this->success("\n" . sprintf(__('Backup database has been finished as %s'), $filename));

        return 1;
    }
}