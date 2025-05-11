<?php

/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 05/11/20 13.53
 * @File name           : Plugins.php
 */

namespace SLiMS;


use SLiMS\DB;
use SLiMS\Cli\Console;
use SLiMS\SearchEngine\Engine;
use SLiMS\Session\Factory;
use Symfony\Component\Finder\Finder;
use stdClass;
use Exception;

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
    const BIBLIOGRAPHY_BEFORE_DATAGRID_OUTPUT = 'bibliography_before_datagrid_output';
    const CIRCULATION_AFTER_SUCCESSFUL_TRANSACTION = 'circulation_after_successful_transaction';
    const CIRCULATION_AFTER_START_TRANSACTION = 'circulation_after_start_transaction';
    const MEMBERSHIP_INIT = 'membership_init';
    const MEMBERSHIP_BEFORE_UPDATE = 'membership_before_update';
    const MEMBERSHIP_AFTER_UPDATE = 'membership_after_update';
    const MEMBERSHIP_BEFORE_SAVE = 'membership_before_save';
    const MEMBERSHIP_AFTER_SAVE = 'membership_after_save';
    const MEMBERSHIP_CUSTOM_FIELD_DATA = 'membership_custom_field_data';
    const MEMBERSHIP_CUSTOM_FIELD_FORM = 'membership_custom_field_form';
    const OVERDUE_NOTICE_INIT = 'overduenotice_init';
    const DUEDATE_NOTICE_INIT = 'duedate_init';
    const MODULE_MAIN_MENU_INIT = 'module_main_menu_init';
    const OAI2_INIT = 'oai2_init';
    const SYSTEM_BEFORE_CONFIGFORM_PRINTOUT = 'system_before_configform_printout';
    const SYSTEM_BEFORE_CONFIG_SAVE = 'system_before_config_save';
    const SYSTEM_AFTER_CONFIG_SAVE = 'system_after_config_save';

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
    private array $autoloadList = [];
    private string $hook_handler = '';
    private string $current_location = '';
    private ?string $group_name = null;
    private array $default_module = [];

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
     * Add .plugin.php | plugin file into list
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

    /**
     * Collection plugin information from .plugin.php
     * documentation at top of file.
     * 
     * @param string $path
     * @return stdClass
     */
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

    /**
     * Scan plugin directory to get plugin information.
     * by default SLiMS use <slims-root>/plugin to store
     * plugin. Please read 'addLocation' method method.
     * 
     * @param string $location
     * @return void
     */
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
                        $this->plugins[$plugin->id]->action = $this->getActionInfo($plugin);
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

    /**
     * Register migration directory in each plugin
     * if it exists. This method is use SLiMS\Migration\Migration
     * to migrate some query | schema | files etc from some plugin.
     * 
     * @param Plugins $plugin
     * @return stdClass
     */
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

    /**
     * Some times we need to move
     * a file into SLiMS core code such as
     * config etc without manual process. 
     * Maybe you ask "Why we need action? Is it migration 
     * feature is enough?" the answer is "seperate". Seperating 
     * database migration process and file migration process.
     * 
     * @param Plugins $plugin
     * @return stdClass
     */
    private function getActionInfo($plugin)
    {
        $action = new stdClass;
        $action->is_exist = false;

        $action_directory = dirname($plugin->path) . DIRECTORY_SEPARATOR . 'action';
        if (is_dir($action_directory)) {
            $action->directory = $action_directory;
            $action->is_exist = true;
        }
        return $action;
    }

    /**
     * Plugin option is an information about
     * plugin migration | database history
     * @param string $id
     */
    private function getOptions($id): void
    {
        if (isset($this->active_plugins[$id])) {
            try {
                $this->plugins[$id]->options = json_decode($this->active_plugins[$id]->options ?? '');
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

    /**
     * @param string $id
     */
    private function getDBVersion($id): int
    {
        $this->getOptions($id);
        return $this->plugins[$id]->options->{self::DATABASE_VERSION} ?? 0;
    }

    /**
     * Check if path location have sub-directory
     * 
     * @param string $location
     */
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

    /**
     * load .plugin.php file from plugin
     * @return void
     */
    public function loadPlugins()
    {
        foreach ($this->getActive() as $item) {
            if (file_exists($item->path)) require_once $item->path;
        }
    }

    /**
     * A method to listing hook into list
     * 
     * @param string $hook
     * @param closure $callback
     * @return void
     */
    public function register($hook, $callback, $sequence = 9999)
    {
        if (isset($this->hooks[$hook][$sequence])) {
            $sequence++;
            $this->register($hook, $callback, $sequence);
        } else {
            $this->hooks[$hook][$sequence] = [$callback, $this->hook_handler, $sequence];
        }
    }

    /**
     * A shortcut to register method
     * 
     * @param string $hook
     * @param closure $callback
     * @return void
     */
    public function registerHook($hook, $callback, $sequence = 9999)
    {
        $this->register($hook, $callback, $sequence);
    }

    /**
     * shortcut to 'registerHook'
     */
    public static function hook($hook, $callback, $sequence = 9999)
    {
        self::getInstance()->registerHook($hook, $callback, $sequence);
    }

    /**
     * A method to listing menu into SLiMS module
     * submenut.
     * 
     * @param string $module_name
     * @param string $label
     * @param string $path
     * @param string $description
     * @return void
     */
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

    /**
     * Shortcut for 'registerMenu'
     */
    public static function menu($module_name, $label, $path, $description = null)
    {
        return self::getInstance()->registerMenu($module_name, $label, $path, $description = null);
    }

    /**
     * Register SLiMS Module as Plugin
     *
     * @param string $module_name
     * @param string $path
     * @param string $description
     * @param string $callback_priv
     * @return void
     */
    public function registerModule($module_name, $path, $description = '', $callback_priv = '')
    {
        // Conver current path to md5 (prevent path transversal)
        $md5_path = md5($path);

        // Register module as hook
        Plugins::hook(Plugins::MODULE_MAIN_MENU_INIT, function (&$module_list) use ($module_name, $path, $md5_path, $description, $callback_priv) {
            // set module list
            $module_list[] = ['name' => $module_name, 'plugin_module_path' => $path, 'path' => $md5_path, 'desc' => $description];

            // set session 
            if (!isset($_SESSION['priv'][$md5_path])) {
                // Custom privelges
                if (is_callable($callback_priv)) {
                    $callback_priv();
                } else {
                    $_SESSION['priv'][$md5_path] = [
                        'r' => true,
                        'w' => true,
                        'submenu' => $path . 'submenu.php'
                    ];
                }
            }
        });

        // Make default group menu
        Plugins::group($module_name, function () use ($path, $md5_path) {
            // Scan all file inside module directory as menu
            foreach (array_diff(scandir($path), ['.', '..', 'submenu.php']) as $menu) {
                if (is_dir($menu) || strpos($menu, '.inc.php')) continue;

                // set label
                $label = trim($menu, '.php');
                $label = $label === 'index' ? __('Main List') : ucwords(str_replace('_', ' ', strtolower($menu)));

                // Register module menu
                Plugins::menu($md5_path, $label, $path . DS . $menu);
            }
        });
    }

    /**
     * A shortcut for registerModule
     *
     * @param string $module_name
     * @param string $path
     * @param string $description
     * @param string $callback_priv
     * @return void
     */
    public static function module($module_name, $path, $description = '', $callback_priv = '')
    {
        self::getInstance()->registerModule($module_name, $path, $description, $callback_priv);
    }

    /**
     * This method is relate to SLiMS\SearchEngine\Engine
     * 
     * @param string $class_name
     * @return void
     */
    public function registerSearchEngine($class_name)
    {
        Engine::init()->set($class_name);
    }

    /**
     * This method is relate to SLiMS\Session\Factory
     * 
     * @param string $class_name
     * @return void
     */
    public function registerSessionDriver($class_name)
    {
        Factory::getInstance()->registerDriver($class_name);
    }

    /**
     * This method is relate to SLiMS\Cli\Console
     * 
     * @param string $class_name
     * @return void
     */
    public function registerCommand($class_name)
    {
        Console::getInstance()->registerCommand($class_name);
    }

    /**
     * Seperate root composer ('slims-plugin') detector
     * and plugin base composer (vendor inside each plugin).
     * The autoload.php will be call at plugin_container.php
     */
    public function registerAutoload()
    {
        $directoryToAutoload = dirname(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file']);
        $match = file_exists($path = $directoryToAutoload . DS . 'vendor/autoload.php') || file_exists($path = $directoryToAutoload);
        if ($match) $this->autoloadList[$directoryToAutoload] = $path;
    }

    public function getAutoload($pluginPath)
    {
        $pluginDirectory = explode(DS, str_replace(SB . 'plugins' . DS, '', $pluginPath))[0] ?? '';
        if (isset($this->autoloadList[SB . 'plugins' . DS . $pluginDirectory])) include_once $this->autoloadList[SB . 'plugins' . DS . $pluginDirectory];
    }

    /**
     * Grouping some plugin into submenu.
     * 
     * @param string $group_name
     * @param closure $callback
     */
    public static function group($group_name, $callback): GroupMenuOrder
    {
        self::getInstance()->setGroupName($group_name);
        $callback();
        self::getInstance()->setGroupName(null);
        return GroupMenuOrder::getInstance()->bind($group_name);
    }

    public function setGroupName($group_name)
    {
        $this->group_name = $group_name;
    }

    /**
     * Running hook process.
     * 
     * @param string $hook
     * @param array $params
     * @return void
     */
    public static function run($hook, $params = [])
    {
        self::getInstance()->execute($hook, $params);
    }

    public function execute($hook, $params = [])
    {
        $hook = $this->hooks[$hook]??[];
        sort($hook);
        foreach ($hook ?? [] as $hook) {
            list($callback, $handler) = $hook;
            if (is_callable($callback)) call_user_func_array($callback, array_values($params));
            if (!empty($handler) && is_string($callback) && method_exists(($handlerInstance = new $handler), $callback)) {
                call_user_func_array([$handlerInstance, $callback], array_values($params));
            }
        }
        
    }

    /**
     * This method is part of hooking process.
     * If you have class to handle some hook, this
     * method to set up your class.
     * 
     * @param string $handler_class
     */
    public static function use($handler_class)
    {
        self::getInstance()->hook_handler = $handler_class;
        return self::getInstance();
    }

    /**
     * This method call closure to use hook
     * handler
     * 
     * @param closure $hooks
     */
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

    /**
     * Load php files inside (pages/) of a plugin
     * and register as module menu with 
     * hierarchical directory.
     * 
     * Description :
     * 
     * pages/ # first directory
     * --- bibliography # as module
     * ---- index.php # as file
     * 
     * or
     * --- membership # as module
     * ---- GROUP # as group name of plugin
     * ----- index.php # as file
     * 
     * or if you want to grouping and ordering menu
     * --- membership # as module
     * ---- GROUP_<after|before>_<EXISTING_GROUP> # as group name of plugin, e.g : GROUP_before_TOOLS
     * ----- index.php # as file
     * 
     * @param string $pathToPages
     * @return void
     */
    public static function registerPages(string $pathToPages = 'pages')
    {
        $trace = debug_backtrace(limit: 1)[0];
        $pathToPages = self::pathResolver($pathToPages, $trace['file']);

        // output as null a.k.a stop process
        if (!is_dir($pathToPages)) return;

        $finder = new Finder;
        $finder
            ->files()
            ->name('*.php')
            ->notName('*.inc.*')
            ->depth('< 3')->in($pathToPages);

        foreach ($finder as $item) {
            $paths = explode(DS, $item->getRelativePath());

            $module_name = $paths[0];
            $fileInfo = pathinfo($item->getFilename());


            $label = ucwords(str_replace('_', ' ', $fileInfo['filename']));
            $label = $module_name != 'opac' ? __($label) : $label;
            $registerMenu = self::getInstance()
                ->registerMenu(
                    $module_name,

                    // label
                    $label,

                    // absolute path
                    $item->getPathname()
                );

            // Grouping process
            if (isset($paths[1])) {
                preg_match('/_(after|before)_/', $paths[1], $match); // get order type
                $orderType = $match[1] ?? null;
                $splitName = preg_split('/_(after|before)_/', $paths[1]);
                $grouping = $registerMenu->group(str_replace('_', ' ', $splitName[0] ?? 'PLUGINS'));

                // set order type
                if ($orderType !== null) $grouping->{$orderType}(__($splitName[1]));
            }
        }
    }

    /**
     * Path resolver is use to get absolute path
     * of .plugin.php which call some method in
     * Plugins.php
     *
     * @param string $path
     * @param string $traceFile
     * @return string
     */
    private static function pathResolver(string $path, string $traceFile): string
    {
        return strpos($path, dirname($traceFile)) === false ? dirname($traceFile) . '/' . ltrim($path, '/') : $path;
    }

    /**
     * Magic 🪄 method to manage registing menu
     * per module via static method call.
     * 
     * e.g : 
     * 
     * Plugins::opac($webPath, $filePaath) = Plugins::menu('opac', ...etc)
     *
     * @param string $method
     * @param array $arguments
     * @return void|object
     */
    public static function __callStatic(string $method, array $arguments)
    {
        if (!method_exists(__CLASS__, $method)) {
            if (count($arguments) < 2) return;

            $module_name = implode('_', array_map(function ($item) {
                return strtolower($item);
            }, preg_split('/(?=[A-Z])/', lcfirst($method))));


            $trace = debug_backtrace(limit: 1)[0];
            $arguments[1] = self::pathResolver($arguments[1], $trace['file'] ?? '');
            return self::getInstance()->registerMenu($module_name, ...$arguments);
        }
    }
}
