<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-12 16:10:26
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

class GetPlugin extends \SLiMS\Cli\Command
{
    protected string $signature = 'plugin:list';
    protected string $description = 'Get available plugin';

    public function handle()
    {
        $this->info('Hai');
    }
} 