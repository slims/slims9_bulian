<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-12 22:59:31
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

use SLiMS\DB;
use SLiMS\Plugins;

class GetPlugin extends \SLiMS\Cli\Command
{
    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'plugin:list {--type=active}';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'Get available plugin';

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Hai');
    }
} 