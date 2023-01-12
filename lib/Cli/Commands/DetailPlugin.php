<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-12 16:11:43
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

class DetailPlugin extends \SLiMS\Cli\Command
{
    protected string $signature = 'plugin:detail {pluginNameOrUniqueId}';
    protected string $description = 'Get detail of a plugin by name or id';

    public function handle()
    {
        $this->info('Hai');
    }
} 