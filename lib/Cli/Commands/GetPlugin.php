<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-23 05:57:58
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

    // plugin property
    protected $plugins = null;

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        // ini_set('memory_limit', '-1');
        switch ($this->option('type')) {
            case 'active':
                $activePlugins = array_values(array_map(function($item){
                    $data = (array)$item;
                    foreach (['options','uid','updated_at','deleted_at'] as $item) unset($data[$item]);
                    return $data;
                }, Plugins::getInstance()->getActive()));

                if (!$activePlugins) {
                    $this->info('No active plugins');
                    return self::INVALID;
                }

                $tableAttribute = [
                    array_keys($activePlugins[0]),
                    $activePlugins
                ];
                $this->info('Table of active plugins');
                break;
            
            case 'all':
            case 'inactive':
            case 'available':
            default:
                $this->getAllPlugin();
                $tableAttribute = [
                    array_keys($this->plugins[0]),
                    array_map(fn($item) => array_values($item), $this->plugins)
                ];
                $this->info('Table of all plugins (included active and inacitve)');
                break;
        }

        // Create table
        $this->table(...$tableAttribute);
        return 1;
    }

    public function getAllPlugin(string $path = SB . 'plugins' . DS)
    {
        // open location
        $dir = array_diff(scandir($path), ['.','..']);

        foreach ($dir as $item) {
            if (is_file($filePath = $path . $item) && preg_match('/.plugin/i', $item)) 
            {
                $this->plugins[] = ['id' => md5($filePath), 'path' => $filePath, 'created_at' => date('Y-m-d H:i:s', filectime($filePath))];
            }
            else if (is_dir($path . $item . DS))
            {
                $this->getAllPlugin($path . $item . DS);
            }
        }
    }
} 