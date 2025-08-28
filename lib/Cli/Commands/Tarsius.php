<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-14 11:29:29
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

class Tarsius extends \SLiMS\Cli\Command
{
    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'tarsius';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'Create tarsius file in main SLiMS root';

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
       if (!file_exists(SB . 'tarsius')) copy(LIB . 'Cli/tarsius.console', SB . 'tarsius');
    }
} 