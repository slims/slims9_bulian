<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 05/11/20 13.53
 * @File name           : Plugins.php
 */

namespace SLiMS;


class Plugins
{
    private static $instance;
    /**
     * Store plugins location, so plugins can stored in multiple location
     * @var array
     */
    protected $locations = [];

    /**
     * Plugin scanned will store here
     * @var array
     */
    protected $plugins = [];
    protected $hooks = [];
    protected $menus = [];
    private $deep = 0;

    /**
     * Plugins constructor.
     * @param null $location
     */
    public function __construct($location = null)
    {
        $this->addLocation($location);
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new static;
        return self::$instance;
    }

    /**
     * @param array|string $location
     * @return Plugins
     */
    public function addLocation($location): Plugins
    {
        if (is_array($location)) {
            $this->locations = array_unique(array_merge($this->locations, $location));
        } elseif (!is_null($location)) {
            $this->locations[] = $location;
        } else {
            // setup default location
            $this->locations[] = realpath(__DIR__ . '/../plugins');
        }
        return $this;
    }

    private function getPluginInfo($path)
    {
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

    private function getPluginsInfo($location)
    {
        // open location
        if ($dir = opendir($location)) {
            while ($file = readdir($dir)) {
                $path = $location . DS . $file;
                // if location is file
                if (is_file($path)) {
                    // just get file with suffix plugin.php
                    if (strpos($path, 'plugin.php')) {
                        $plugin = $this->getPluginInfo($path);
                        $this->plugins[$plugin->id] = $plugin;
                    }
                } elseif (is_dir($path) && (substr($file, 0, 1) != '.')) {
                    $this->deep++;
                    // get plugins from sub folder location
                    // deep level directory that will be scanned
                    if ($this->deep < 3) $this->getPluginsInfo($path);
                }
            }
            closedir($dir);
        }
    }

    /**
     * @return array
     */
    public function getPlugins(): array
    {
        foreach ($this->locations as $location) {
            $this->getPluginsInfo($location);
        }
        return $this->plugins;
    }

    /**
     * Get active plugins from database
     */
    public function getActive()
    {
        $query = DB::getInstance()->query("SELECT * FROM plugins");
        $result = [];
        while ($data = $query->fetchObject()) $result[$data->id] = $data;
        return $result;
    }

    public function loadPlugins()
    {
        foreach ($this->getActive() as $item) {
            if (file_exists($item->path)) require_once $item->path;
        }
    }

    public function register($hook, $callback)
    {
        $this->hooks[$hook][] = $callback;
    }

    public function registerMenu($module_name, $label, $path, $description = null)
    {
        $hash = md5(realpath($path));
        $this->menus[$module_name][$hash] = [$label, AWB . 'plugin_container.php?mod=' . $module_name . '&id=' . $hash, $description, realpath($path)];
    }

    public function execute($hook, $params = [])
    {
        foreach ($this->hooks[$hook] ?? [] as $hook) {
            if (is_callable($hook)) call_user_func_array($hook, $params);
        }
    }

    /**
     * @param null $module
     * @return array
     */
    public function getMenus($module = null): array
    {
        if (is_null($module)) return $this->menus;
        return $this->menus[$module] ?? [];
    }

}