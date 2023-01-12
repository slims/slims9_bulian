<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-12 22:58:51
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

class DetailPlugin extends \SLiMS\Cli\Command
{
    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'plugin:detail {pluginNameOrUniqueId}';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'Get detail of a plugin by name or id';

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        $this->argument('pluginNameOrUniqueId');
    }
} 