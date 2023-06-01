<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-26 23:24:02
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

use SLiMS\DB;

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
        $idOrName = $this->argument('pluginNameOrUniqueId');

        $query = DB::getInstance()->prepare("SELECT path FROM plugins WHERE deleted_at IS NULL AND id = ?");
        $query->execute([$idOrName]);

        if ($query->rowCount() === 0) exit($this->error('Plugin with id ' . $idOrName . ' not active or not found'));

        $data = $query->fetchObject();
        $info = $this->getPluginInfo($data->path);
        
        $this->info('Plugin info of ' . $info->name . "\n");
        $this->output(" Author : $info->author");
        $this->output(" Version : $info->version");
        $this->output(" Description : $info->description\n");
    }

    private function getPluginInfo($path): \stdClass
    {
        /**
         * Took from lib/Plugins.php
         */
        $file_open = fopen($path, 'r');
        $raw_data = fread($file_open, 8192);
        fclose($file_open);

        // store plugin info as object
        $plugin = new \stdClass;

        // parsing plugin data
        preg_match('|Plugin Name:(.*)$|mi', $raw_data, $plugin->name);
        preg_match('|Plugin URI:(.*)$|mi', $raw_data, $plugin->uri);
        preg_match('|Version:(.*)|i', $raw_data, $plugin->version);
        preg_match('|Description:(.*)$|mi', $raw_data, $plugin->description);
        preg_match('|Author:(.*)$|mi', $raw_data, $plugin->author);
        preg_match('|Author URI:(.*)$|mi', $raw_data, $plugin->author_uri);

        foreach (get_object_vars($plugin) as $key => $val) {
            $plugin->$key = isset($val[1]) && trim($val[1]) !== '' ? trim($val[1]) : null;
        }

        $plugin->id = md5($path);
        $plugin->path = $path;
        return $plugin;
    }
} 