<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-13 14:42:45
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

class CreateCommand extends \SLiMS\Cli\Command
{
    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'make:command {commandname}';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'Create new command in plugins/Commands';

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        $commandName = $this->argument('commandname');
        $template = file_get_contents(__DIR__ . '/../Command.template');

        try {
            // is writeable
            if (!is_writable($path = SB . 'plugins/')) throw new \Exception("Directory {$path} is not writeable");

            // create command directory
            if (!file_exists($commandsPath = $path . 'Commands/')) mkdir($commandsPath, 0755);

            // Check exist or not
            if (file_exists($newfile = $commandsPath . $commandName . '.php')) throw new \Exception("Command {$newfile} is exists");

            // write command
            $write = file_put_contents($newfile, str_replace('<CommandName>', $commandName, $template));

            if (!$write) throw new \Exception("Cannot write {$newfile}");

            $this->success('Console command created successfully.');
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
} 