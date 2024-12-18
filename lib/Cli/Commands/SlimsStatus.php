<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-23 10:33:38
 * @modify date 2023-01-23 11:26:46
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

use SLiMS\DB;
use SLiMS\Plugins;

class SlimsStatus extends \SLiMS\Cli\Command
{
    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'status';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'Get SLiMS status info';

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        $dbs = DB::getInstance('mysqli');
        $columns = [
            [__('SLiMS Environment Mode'), ucfirst(ENVIRONMENT)],
            [__('SLiMS Version'), SENAYAN_VERSION_TAG],
            [__('Operating System'), php_uname('a')],
            [__('OS Architecture'), php_uname('m').' '.(8 * PHP_INT_SIZE).' bit'],
            [__('PHP version'), phpversion()],  
            [__('MySQL Database version'), $dbs->server_info],  
            [__('MySQL Client version'), $dbs->client_info]
        ];

        $this->newLine();
        $this->info(__('System Environment'));
        $this->output(__('Information on SLiMS System Environment. Use this to support troubleshotting problem.'));
        $this->newLine();
        foreach ($columns as $column) {
            $this->justify($column);
        }
        $this->newLine();
    }
}