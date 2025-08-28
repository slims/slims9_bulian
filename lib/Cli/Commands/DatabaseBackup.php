<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-12-17 07:01:17
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
        $start = microtime(true);
        $backup = DB::backup();

        // Starting backup
        $path = ($this->option('path')??SB . 'files' . DS . 'backup' . DS);
        $filename = ($this->option('filename')??'backup-database-' . date('YmdHis') . '.sql');
        $backup->start($path . $filename);

        if (file_exists($path . $filename)) {
            $logging = DB::query('insert into `backup_log` set `user_id` = 1, `backup_time` = ?, `backup_file` = ?', [date('Y-m-d H:i:s'), $path . $filename]);
            
            if ($logging->isAffected()) {
                $this->info('🕐 Backup success in ' . date('i:s', (int)(microtime(true) - $start)));
                $this->success('✅ ' . sprintf(__('Backup database has been finished as %s'), $filename));
            } else {
                $this->error(__('Backup logging is not saved successfully') . ' : ' . $logging->getError());
                $this->success(sprintf(__('Backup database has been finished as %s'), $filename));
            }
        }

        return 1;
    }
}