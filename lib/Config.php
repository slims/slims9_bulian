<?php
/**
 * @CreatedBy          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date               : 2020-11-28  20:19:45
 * @FileName           : Config.php
 * @Project            : slims9_bulian
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

namespace SLiMS;


use PDO;

class Config
{
    private static $instance = null;
    private $configs = [];

    public function __construct()
    {
        // load default config folder
        $this->load(__DIR__ . '/../config', ['env.php', 'env.sample.php']);
    }

    /**
     * Get instance of this class
     *
     * @return static|null
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new static();
        return self::$instance;
    }

    /**
     * Load configuration files
     *
     * @param $directory
     * @param array $ignore
     */
    function load($directory, $ignore = [])
    {
        $ignore = array_unique(array_merge(['..', '.', 'index.html', 'index.php'], $ignore));
        $scanned_directory = array_diff(scandir($directory), $ignore);
        foreach ($scanned_directory as $file) {
            if (strpos($file, '.php')) {
                $file_path = $directory . DIRECTORY_SEPARATOR . $file;
                $this->configs[basename($file_path, '.php')] = require $file_path;
            }
        }

        // load config from database
        // this will override config file
        $this->loadFromDatabase();
    }

    /**
     * Load app preferences from database
     */
    function loadFromDatabase()
    {
        if (self::getFile('database') === null) return;
        
        $query = DB::getInstance()->query('SELECT setting_name, setting_value FROM setting');
        while ($data = $query->fetch(PDO::FETCH_OBJ)) {
            $value = @unserialize($data->setting_value);
            if (is_array($value)) {
                foreach ($value as $id => $current_value) {
                    $this->configs[$data->setting_name][$id] = $current_value;
                }
            } else {
                $this->configs[$data->setting_name] = stripslashes($value??'');
            }
        }
    }

    /**
     * Get config with dot separator
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $default;
        foreach ($keys as $index => $_key) {
            if ($index < 1) {
                $config = $this->configs[$_key] ?? $default;
                continue;
            }
            if ($config === $default) break;
            if (isset($config[$_key])) {
                $config = $config[$_key];
            } else {
                $config = $default;
            }
        }

        // if result is null, try to get global $sysconf
        if (is_null($config)) $config = $this->getGlobal($key, $default);

        return $config;
    }

    /**
     * Get data with dot separator
     *
     * @param string $key
     * @param stirng $default
     * @return array|null
     */
    public function getGlobal($key, $default = null)
    {
        global $sysconf;
        $keys = explode('.', $key);
        $config = $default;
        foreach ($keys as $index => $_key) {
            if ($index < 1) {
                $config = $sysconf[$_key] ?? $default;
                continue;
            }
            if ($config === $default) break;
            if (isset($config[$_key])) {
                $config = $config[$_key];
            } else {
                $config = $default;
            }
        }
        return $config;
    }

    /**
     * Get config as plain text
     */
    public static function getFile(string $filename)
    {
        return file_exists($path = SB . 'config/' . $filename . '.php') ? file_get_contents($path) : null;
    }

    /**
     * Create some configuration file
     * into <slims-root>/config/
     *
     * @param string $filename
     * @param string $content
     * @return void
     */
    public static function create(string $filename, $content = '')
    {
        if (is_callable($content)) $content = $content($filename);
        file_put_contents(SB . 'config/' . basename($filename) . '.php', $content);
    }

    /**
     * Create or update SLiMS configuration
     * to database
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function createOrUpdate(string $name, $value)
    {
        require_once SIMBIO.'simbio_DB/simbio_dbop.inc.php';
        $sql_op = new \simbio_dbop($dbs = DB::getInstance('mysqli'));
        $name = $dbs->escape_string($name);
        $data['setting_value'] = $dbs->escape_string(serialize($value));

        $query = $dbs->query("SELECT setting_value FROM setting WHERE setting_name = '{$name}'");
        if ($query->num_rows > 0) {
            // update
            $status = $sql_op->update('setting', $data, "setting_name='{$name}'");
        } else {
            // insert
            $data['setting_name'] = $name;
            $status = $sql_op->insert('setting', $data);
        }

        return $status;
    }
}