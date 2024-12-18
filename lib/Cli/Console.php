<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 11:55:24
 * @modify date 2023-01-13 22:15:33
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli;

use Symfony\Component\Console\Application;

final class Console
{
    /**
     * Default property
     *
     * @var [type]
     */
    private static $instance = null;
    private $app = null;

    private function __construct()
    {
        $this->app = new Application('SLiMS Console', SENAYAN_VERSION_TAG);  
    }

    /**
     * Get app instance
     *
     * @return Console
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new Console;
        return self::$instance;
    }

    /**
     * Default command is base tool
     * to manage SLiMS such as, plugin,
     * db etc.
     *
     * @return void
     */
    public function loadDefaultCommands()
    {
        $commands = array_diff(scandir(__DIR__ . '/Commands/'), ['.','..']);

        foreach($commands as $command)
        {
            $class = '\SLiMS\Cli\Commands\\' . str_replace('.php', '', $command);
            if (class_exists($class)) $this->registerCommand(new $class);
        }
    }

    /**
     * Load user command based on `CreateCommand`
     *
     * @return void
     */
    public function loadPluginCommands()
    {
        $commands = file_exists($path = SB . 'plugins/Commands/') ? array_diff(scandir($path), ['.','..']) : [];

        foreach($commands as $command)
        {
            $class = '\Commands\\' . str_replace('.php', '', $command);
            if (class_exists($class)) $this->registerCommand(new $class);
        }
    }

    /**
     * Register custom command
     *
     * @param Command $command
     * @return void
     */
    public function registerCommand(Command $command)
    {
        if (isCli()) $this->app->add($command);
    }

    /**
     * Running all console operation
     * if on cli interface
     *
     * @return void
     */
    public function run()
    {
        if (isCli()) 
        {
            $this->loadDefaultCommands();
            $this->loadPluginCommands();
            $this->app->run();
        }
    }
}