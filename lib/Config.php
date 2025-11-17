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
use Symfony\Component\Finder\Finder;
use utility;

/**
 * Configuration Manager for SLiMS
 * 
 * Handles loading, accessing, and managing configuration from files and database.
 * Implements singleton pattern to ensure single configuration instance.
 */
class Config
{
    private static ?self $instance = null;
    
    /**
     * Configuration storage array
     * @var array<string, mixed>
     */
    private array $configs = [];

    /**
     * Private constructor to enforce singleton pattern
     * Loads default configuration from config directory
     */
    private function __construct()
    {
        $this->loadDefaultConfigs();
    }

    /**
     * Get singleton instance of Config class
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load default configuration files from config directory
     * 
     * @return void
     */
    private function loadDefaultConfigs(): void
    {
        $configPath = __DIR__ . '/../config';
        $ignoredFiles = ['*.*.php', '*_*.php', 'index.php', 'env.php'];
        $this->load($configPath, $ignoredFiles);
    }

    /**
     * Load configuration files from specified directory
     *
     * @param string $directory Directory path containing config files
     * @param array<string> $ignore File patterns to ignore
     * @return void
     */
    public function load(string $directory, array $ignore = []): void
    {
        $finder = new Finder();
        $finder->files()->in($directory)->name('*.php')->notName($ignore);
        
        foreach ($finder as $file) {
            $configName = basename($file->getFilename(), '.php');
            $this->configs[$configName] = require $file->getPathname();
        }

        // Load config from database (this will override file-based config)
        $this->loadFromDatabase();
    }

    /**
     * Load application preferences from database
     * Database settings override file-based configurations
     * 
     * @return void
     */
    public function loadFromDatabase(): void
    {
        if (self::getFile('database') === null) {
            return;
        }

        try {
            $query = DB::getInstance()->query('SELECT setting_name, setting_value FROM setting');
            
            while ($data = $query->fetch(PDO::FETCH_OBJ)) {
                $value = utility::unserialize($data->setting_value);
                $settingName = $data->setting_name;

                if (is_array($value)) {
                    $this->mergeArrayConfig($settingName, $value);
                } else {
                    $this->configs[$settingName] = $this->sanitizeConfigValue($value);
                }
            }
        } catch (\Throwable $e) {
            // Silently fail if database is not available or table doesn't exist
            // This allows the system to work with file-based config only
        }
    }

    /**
     * Merge array configuration values
     * 
     * @param string $settingName Configuration key
     * @param array $value Array of configuration values
     * @return void
     */
    private function mergeArrayConfig(string $settingName, array $value): void
    {
        foreach ($value as $key => $configValue) {
            $this->configs[$settingName][$key] = $configValue;
        }
    }

    /**
     * Sanitize configuration value
     * Removes slashes from string values while preserving other types (bool, int, etc.)
     * 
     * @param mixed $value Raw configuration value
     * @return mixed Sanitized value
     */
    private function sanitizeConfigValue($value)
    {
        // Only apply stripslashes to actual strings to preserve boolean false, null, 0, etc.
        if (is_string($value)) {
            return stripslashes($value);
        }
        
        // Return other types as-is (bool, int, float, array, object, null)
        return $value;
    }

    /**
     * Get configuration value using dot notation
     * 
     * Examples:
     *   - get('database.host') returns $configs['database']['host']
     *   - get('app.name', 'SLiMS') returns value or 'SLiMS' if not found
     *
     * @param string $key Configuration key with dot notation support
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value or default
     */
    public function get(string $key, $default = null)
    {
        // Use a unique sentinel value to detect if key doesn't exist
        $sentinel = new \stdClass();
        $value = $this->getNestedConfig($key, $sentinel);
        
        // If sentinel returned, key doesn't exist - fallback to global $sysconf
        if ($value === $sentinel) {
            $value = $this->getGlobal($key, $sentinel);
            
            // If still sentinel, key doesn't exist anywhere - return default
            if ($value === $sentinel) {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Get nested configuration using dot notation
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @return mixed Configuration value
     */
    private function getNestedConfig(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $this->configs;

        foreach ($keys as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * Recursively find and modify multidimensional configuration
     *
     * @param array &$config Configuration array (passed by reference)
     * @param string $keyToModify Key to find and modify
     * @param mixed $newValue New value to set
     * @param string $mode Modification mode: 'append' or 'replace'
     * @return void
     */
    private function findAndModify(array &$config, string $keyToModify, $newValue, string $mode = 'append'): void
    {
        foreach ($config as $key => &$value) {
            if ($key === $keyToModify) {
                $this->modifyConfigValue($value, $newValue, $mode);
            } elseif (is_array($value)) {
                $this->findAndModify($value, $keyToModify, $newValue, $mode);
            }
        }
    }

    /**
     * Modify configuration value based on mode
     * 
     * @param mixed &$currentValue Current configuration value
     * @param mixed $newValue New value to apply
     * @param string $mode Modification mode
     * @return void
     */
    private function modifyConfigValue(&$currentValue, $newValue, string $mode): void
    {
        if ($mode === 'append' && is_array($currentValue)) {
            $valuesToAppend = is_array($newValue) ? $newValue : [$newValue];
            $currentValue = array_merge($currentValue, $valuesToAppend);
        } elseif ($mode === 'replace') {
            $currentValue = $newValue;
        }
    }

    /**
     * Change configuration value without overriding the original
     * Supports dot notation for nested configuration
     *
     * @param string $key Configuration key (supports dot notation)
     * @param mixed $newValue New value to set
     * @param string $mode Modification mode: 'replace' or 'append'
     * @return bool True if successful, false if key doesn't exist
     * @throws \Exception If trying to merge into non-array config
     */
    public function change(string $key, $newValue, string $mode = 'replace'): bool
    {
        if ($this->get($key) === null) {
            return false;
        }

        $keys = explode('.', trim($key, '.'));
        $configName = $keys[0];
        $isMultidimensional = count($keys) > 1;

        if (!isset($this->configs[$configName])) {
            return false;
        }

        $config = $this->configs[$configName];

        if ($isMultidimensional) {
            $keyToModify = end($keys);
            $this->findAndModify($config, $keyToModify, $newValue, $mode);
        } else {
            $config = $this->mergeOrReplaceConfig($config, $newValue, $configName, $mode);
        }

        $this->configs[$configName] = $config;
        return true;
    }

    /**
     * Merge or replace configuration value
     * 
     * @param mixed $currentConfig Current configuration value
     * @param mixed $newValue New value
     * @param string $configName Configuration name for error messages
     * @param string $mode Modification mode
     * @return mixed Modified configuration
     * @throws \Exception If config is not an array and mode is append
     */
    private function mergeOrReplaceConfig($currentConfig, $newValue, string $configName, string $mode)
    {
        if ($mode === 'replace') {
            return $newValue;
        }

        if (!is_array($currentConfig)) {
            throw new \Exception("Config '{$configName}' value is not an array, cannot append.");
        }

        $valuesToMerge = is_array($newValue) ? $newValue : [$newValue];
        return array_merge($currentConfig, $valuesToMerge);
    }

    /**
     * Append value to existing configuration array
     * Shorthand for change() with 'append' mode
     *
     * @param string $key Configuration key
     * @param mixed $newValue Value to append
     * @return bool True if successful, false otherwise
     */
    public function append(string $key, $newValue): bool
    {
        return $this->change($key, $newValue, 'append');
    }

    /**
     * Replace current configuration value
     * Shorthand for change() with 'replace' mode
     *
     * @param string $key Configuration key
     * @param mixed $newValue New value to set
     * @return bool True if successful, false otherwise
     */
    public function replace(string $key, $newValue): bool
    {
        return $this->change($key, $newValue, 'replace');
    }

    /**
     * Get configuration from global $sysconf variable
     * Provides backward compatibility with legacy configuration system
     *
     * @param string $key Configuration key with dot notation support
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value or default
     */
    public function getGlobal(string $key, $default = null)
    {
        global $sysconf;
        
        if (!is_array($sysconf)) {
            return $default;
        }

        $keys = explode('.', $key);
        $config = $sysconf;

        foreach ($keys as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * Get configuration file content as plain text
     * 
     * @param string $filename Configuration filename without .php extension
     * @return string|null File contents or null if file doesn't exist
     */
    public static function getFile(string $filename): ?string
    {
        $path = SB . 'config/' . $filename . '.php';
        return file_exists($path) ? file_get_contents($path) : null;
    }

    /**
     * Check if configuration file exists
     * 
     * @param string $name Configuration name
     * @return bool True if file exists, false otherwise
     */
    public static function isExists(string $name): bool
    {
        $filename = basename($name) . '.php';
        $path = SB . 'config' . DS . $filename;
        return file_exists($path);
    }

    /**
     * Create configuration files from sample if they don't exist
     * 
     * @param string|array $nameOrNames Single name or array of config names
     * @return void
     */
    public static function createFromSampleIfNotExists($nameOrNames): void
    {
        $names = is_array($nameOrNames) ? $nameOrNames : [$nameOrNames];

        foreach ($names as $name) {
            if (!self::isExists($name)) {
                self::createFromSample($name);
            }
        }
    }


    /**
     * Create configuration file in <slims-root>/config/
     *
     * @param string $filename Configuration filename without .php extension
     * @param string|callable $content File content or callable that generates content
     * @return void
     */
    public static function create(string $filename, $content = ''): void
    {
        if (is_callable($content)) {
            $content = $content($filename);
        }
        
        $path = SB . 'config/' . basename($filename) . '.php';
        file_put_contents($path, $content);
    }

    /**
     * Create configuration file from sample template
     * 
     * @param string $configName Configuration name
     * @return void
     */
    public static function createFromSample(string $configName): void
    {
        $configName = basename($configName);
        $configBasePath = SB . 'config' . DS;
        $samplePath = $configBasePath . $configName . '.sample.php';
        $targetPath = $configBasePath . $configName . '.php';

        if (!self::isExists($configName . '.sample')) {
            return;
        }

        copy($samplePath, $targetPath);
    }

    /**
     * Create or update SLiMS configuration in database
     * 
     * @param string $name Setting name
     * @param mixed $value Setting value (will be serialized)
     * @return bool True if successful, false otherwise
     */
    public static function createOrUpdate(string $name, $value): bool
    {
        require_once SIMBIO . 'simbio_DB/simbio_dbop.inc.php';
        
        $db = DB::getInstance('mysqli');
        $sqlOp = new \simbio_dbop($db);
        
        $escapedName = $db->escape_string($name);
        $data = ['setting_value' => $db->escape_string(serialize($value))];

        $query = $db->query("SELECT setting_value FROM setting WHERE setting_name = '{$escapedName}'");
        
        if ($query->num_rows > 0) {
            // Update existing setting
            $status = $sqlOp->update('setting', $data, "setting_name='{$escapedName}'");
        } else {
            // Insert new setting
            $data['setting_name'] = $escapedName;
            $status = $sqlOp->insert('setting', $data);
        }

        return (bool) $status;
    }
}