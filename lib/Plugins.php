<?php

/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 05/11/20 13.53
 * @File name           : Plugins.php
 */

namespace SLiMS;


use SLiMS\SearchEngine\Engine;
use SLiMS\Session\Factory;
use stdClass;

class Plugins
{
    const DATABASE_VERSION = 'db_version';

    /**
     * Constanta's List of Hooks
     */
    const ADMIN_SESSION_AFTER_START = 'admin_session_after_start';
    const CONTENT_BEFORE_LOAD = 'before_content_load';
    const CONTENT_AFTER_LOAD = 'after_content_load';
    const BIBLIOGRAPHY_INIT = 'bibliography_init';
    const BIBLIOGRAPHY_BEFORE_UPDATE = 'bibliography_before_update';
    const BIBLIOGRAPHY_AFTER_UPDATE = 'bibliography_after_update';
    const BIBLIOGRAPHY_BEFORE_SAVE = 'bibliography_before_save';
    const BIBLIOGRAPHY_AFTER_SAVE = 'bibliography_after_save';
    const BIBLIOGRAPHY_BEFORE_DELETE = 'bibliography_before_delete';
    const BIBLIOGRAPHY_AFTER_DELETE = 'bibliography_after_delete';
    const BIBLIOGRAPHY_CUSTOM_FIELD_DATA = 'advance_custom_field_data';
    const BIBLIOGRAPHY_CUSTOM_FIELD_FORM = 'advance_custom_field_form';
    const CIRCULATION_AFTER_SUCCESSFUL_TRANSACTION = 'circulation_after_successful_transaction';
    const MEMBERSHIP_INIT = 'membership_init';
    const MEMBERSHIP_BEFORE_UPDATE = 'membership_before_update';
    const MEMBERSHIP_AFTER_UPDATE = 'membership_after_update';
    const MEMBERSHIP_BEFORE_SAVE = 'membership_before_save';
    const MEMBERSHIP_AFTER_SAVE = 'membership_after_save';
    const OVERDUE_NOTICE_INIT = 'overduenotice_init';

    private static $instance;
    /**
     * Store plugins location, so plugins can stored in multiple location
     * @var array
     */
    protected array $locations = [];

    /**
     * Plugin scanned will store here
     * @var array
     */
    protected array $plugins = [];
    protected array $active_plugins = [];
    protected array $hooks = [];
    protected array $menus = [];
    private int $deep = 2;
    private string $hook_handler = '';
    private string $current_location = '';
    private ?string $group_name = null;

    /**
     * Plugins constructor.
     * @param null $location
     */
    public function __construct($location = null)
    {
        $this->addLocation($location);
    }

    public static function getInstance(): Plugins
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

    private function getPluginInfo($path): stdClass
    {
        $file_open = fopen($path, 'r');
        $raw_data = fread($file_open, 8192);
        fclose($file_open);

        // store plugin info as object
        $plugin = new stdClass;

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
                        // get migration info
                        $this->plugins[$plugin->id] = $plugin;
                        $this->plugins[$plugin->id]->migration = $this->getMigrationInfo($plugin);
                    }
                } elseif (is_dir($path) && (substr($file, 0, 1) != '.')) {
                    // get plugins from sub folder location
                    // deep level directory that will be scanned
                    if ($this->isDeep($location)) $this->getPluginsInfo($path);
                }
            }
            closedir($dir);
        }
    }

    private function getMigrationInfo($plugin): stdClass
    {
        $migration = new stdClass;
        $migration->is_exist = false;

        $migration_directory = dirname($plugin->path) . DIRECTORY_SEPARATOR . 'migration';
        if (is_dir($migration_directory)) {
            $migration->is_exist = true;
            $migration->{self::DATABASE_VERSION} = $this->getDBVersion($plugin->id);
        }
        return $migration;
    }

    private function getOptions($id): void
    {
        if (isset($this->active_plugins[$id])) {
            try {
                $this->plugins[$id]->options = json_decode($this->active_plugins[$id]->options??'');
            } catch (\Exception $exception) {
                $this->plugins[$id]->options = new stdClass;
            }
        } else {
            $stmt = DB::getInstance()->prepare('SELECT options FROM plugins WHERE id = :id');
            $stmt->execute(['id' => $id]);
            if ($stmt->rowCount() < 1) $this->plugins[$id]->options = new stdClass;
            $plugin = $stmt->fetch(\PDO::FETCH_OBJ);
            try {
                $this->plugins[$id]->options = json_decode($plugin->options ?? '{}');
            } catch (\Exception $exception) {
                $this->plugins[$id]->options = new stdClass;
            }
        }
    }

    private function getDBVersion($id): int
    {
        $this->getOptions($id);
        return $this->plugins[$id]->options->{self::DATABASE_VERSION} ?? 0;
    }

    function isDeep($location): bool
    {
        $sub_dir = str_replace($this->current_location, '', $location);
        $arr_sub_dir = explode(DIRECTORY_SEPARATOR, $sub_dir);
        return count($arr_sub_dir) <= $this->deep;
    }

    /**
     * @return array
     */
    public function getPlugins(): array
    {
        foreach ($this->locations as $location) {
            $this->current_location = $location;
            $this->getPluginsInfo($location);
        }
        return $this->plugins;
    }

    /**
     * Get active plugins from database
     */
    public function getActive(): array
    {
        $query = DB::getInstance()->query("SELECT * FROM plugins WHERE deleted_at IS NULL");
        while ($data = $query->fetchObject()) $this->active_plugins[$data->id] = $data;
        return $this->active_plugins;
    }

    public function isActive($id): bool
    {
        $query = DB::getInstance()->prepare("SELECT * FROM plugins WHERE ID = :id");
        $query->bindValue(':id', $id);
        $query->execute();
        return $query->rowCount() > 0;
    }

    public function loadPlugins()
    {
        foreach ($this->getActive() as $item) {
            if (file_exists($item->path)) require_once $item->path;
        }
    }

    public function register($hook, $callback)
    {
        $this->hooks[$hook][] = [$callback, $this->hook_handler];
    }

    public function registerHook($hook, $callback)
    {
        $this->register($hook, $callback);
    }

    public function registerMenu($module_name, $label, $path, $description = null)
    {
        $hash = md5(realpath($path));
        if ($module_name === 'opac') {
            $name = strtolower(implode('_', explode(' ', $label)));
            $this->menus[$module_name][$name] = [$label, SWB . 'index.php?p=' . $name, $description, realpath($path)];
        } else {
            $this->menus[$module_name][$hash] = [$label, AWB . 'plugin_container.php?mod=' . $module_name . '&id=' . $hash, $description, realpath($path)];
        }

        $group_instance = GroupMenu::getInstance()->bind($hash);
        if (!is_null($this->group_name)) $group_instance->group($this->group_name);

        return $group_instance;
    }

    public function registerSearchEngine($class_name)
    {
        Engine::init()->set($class_name);
    }

    public function registerSessionDriver($class_name)
    {
        Factory::getInstance()->registerDriver($class_name);
    }

    public static function group($group_name, $callback): GroupMenuOrder
    {
        self::getInstance()->setGroupName($group_name);
        $callback();
        self::getInstance()->setGroupName(null);
        return GroupMenuOrder::getInstance()->bind($group_name);
    }

    public static function menu($module_name, $label, $path, $description = null)
    {
        return self::getInstance()->registerMenu($module_name, $label, $path, $description = null);
    }

    public static function hook($hook, $callback)
    {
        self::getInstance()->registerHook($hook, $callback);
    }

    public static function run($hook, $params = []) {
        self::getInstance()->execute($hook, $params);
    }

    public function setGroupName($group_name)
    {
        $this->group_name = $group_name;
    }

    public function execute($hook, $params = [])
    {
        foreach ($this->hooks[$hook] ?? [] as $hook) {
            list($callback, $handler) = $hook;
            if (is_callable($callback)) call_user_func_array($callback, array_values($params));
            if (!empty($handler) && is_string($callback) && method_exists(($handlerInstance = new $handler), $callback)) 
            {call_user_func_array([$handlerInstance, $callback], array_values($params));}
        }
    }

    public static function use($handler_class)
    {
        self::getInstance()->hook_handler = $handler_class;
        return self::getInstance();
    }

    public function for($hooks)
    {
        if (empty($this->hook_handler)) return;
        if (is_callable($hooks)) call_user_func_array($hooks, [$this]);
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
