<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-12 15:58:58
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

class CreateCommand extends \SLiMS\Cli\Command
{
    protected string $signature = 'make:command {commandname}';
    protected string $description = 'Create new command';

    public function handle()
    {
        $this->info('Hai');
    }
} 