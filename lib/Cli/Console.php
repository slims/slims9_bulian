<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 11:55:24
 * @modify date 2023-01-12 23:23:56
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli;

use Symfony\Component\Console\Application;

class Console
{
    private static $instance = null;
    private $app = null;

    private function __construct()
    {
        $this->app = new Application('SLiMS Console', SENAYAN_VERSION_TAG);  
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new Console;
        return self::$instance;
    }

    public function loadDefaultCommands()
    {
        $commands = array_diff(scandir(__DIR__ . '/Commands/'), ['.','..']);

        foreach($commands as $command)
        {
            $class = '\SLiMS\Cli\Commands\\' . str_replace('.php', '', $command);
            if (class_exists($class)) $this->registerCommand(new $class);
        }
    }

    public function loadPluginCommands()
    {
        $commands = file_exists($path = SB . 'plugins/Commands/') ? array_diff(scandir($path), ['.','..']) : [];

        foreach($commands as $command)
        {
            $class = '\Commands\\' . str_replace('.php', '', $command);
            if (class_exists($class)) $this->registerCommand(new $class);
        }
    }

    public function registerCommand(Command $command)
    {
        self::getInstance()->app->add($command);
    }

    public function run()
    {
        $this->loadDefaultCommands();
        $this->loadPluginCommands();
        if (isCli()) $this->app->run();
    }
}