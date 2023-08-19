<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 01/10/20 02.07
 * @File name           : DB.php
 */

namespace SLiMS;


use Exception;
use mysqli;
use PDO;
use PDOException;
use Ifsnop\Mysqldump as IMysqldump;

class DB
{
    /**
     * PDO instance
     * @var null
     */
    private static $instance = [];

    /**
     * MySQLi Instance
     * @var null
     */
    private static $instance_mysqli = [];

    // Current connection name
    private static $connectionName = null;

    /**
     * @var SLiMS\Collection
     */
    private static $connectionCollection = null;

    /**
     * Database config
     */
    private static array $config = [];

    /**
     * Current database credential
     */

    private static array $credentials = [];

    /**
     * Backup const
     */
    const BACKUP_BASED_ON_DAY = 1;
    const BACKUP_BASED_ON_LAST_ITEM = 2;

    /**
     * Intial database instance
     *
     * @param string $driver
     */
    private function __construct($driver = 'pdo')
    {
        try {
            $this->setConnection($driver);
        } catch(PDOException $error) {
            echo $error->getMessage();
        } catch (Exception $error) {
            echo $error->getMessage();
        }
    }

    /**
     * An method to get database instance
     * based on database driver PDO | MySQLi
     *
     * @param string $driver
     * @return PDO|MySQLi
     */
    public static function getInstance($driver = 'pdo', $connectionName = '')
    {
        self::getConfig();

        // Create collection instance
        if (is_null(self::$connectionCollection)) self::$connectionCollection = new Collection(Connection::class);

        // set current connection name
        self::$connectionName = $connectionName;

        // get connection from collection
        $instance = self::$connectionCollection->get($driver . '_' . self::$connectionName)?->getConn();

        if (is_null($instance) === false) return $instance;
        
        // not exists? then create it.
        new DB($driver);
        
        return self::$connectionCollection->get($driver . '_' . self::$connectionName)?->getConn();
    }

    /**
     * Create MySQLDump instance 
     * with default profile provide by
     * this Object.
     *
     * @param array $settings
     * @return IMysqldump\Mysqldump
     */
    public static function backup()
    {
        $static = new static;
        return new IMysqldump\Mysqldump(...array_merge($static->getProfile(), [config('database_backup.options')]));
    }

    /**
     * Connection profiling
     *
     * @param string $name
     * @param string $driver
     * @return void
     */
    public static function connection(string $name, string $driver = 'pdo')
    {
        return self::getInstance($driver, $name);
    }

    /**
     * Register connection into collection
     *
     * @param string $driver
     * @return void
     */
    private function setConnection(string $driver = 'pdo')
    {
        self::$connectionCollection->add(new Connection(self::$connectionName??'database', $this->getProfile(), $driver));
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public static function hasBackup($by = self::BACKUP_BASED_ON_DAY)
    {
        $criteria = "substring(backup_time, 1,10) = '" . date('Y-m-d') . "'";

        if ($by === self::BACKUP_BASED_ON_LAST_ITEM) $criteria = 'backup_time >= (SELECT last_update FROM item ORDER BY last_update DESC LIMIT 1)';

        $state = self::getInstance()->query(<<<SQL
            SELECT backup_log_id FROM backup_log WHERE {$criteria}
        SQL);

        return (bool)$state->rowCount();
    }

    /**
     * Retrive database profile 
     * from database.php and 
     * return database parameter as splat
     *
     * @param string $driver
     * @return array
     */
    private function getProfile()
    {
        $connectionName = 
            empty(self::$connectionName) ? 
                // get default profile?
                self::$config['default_profile'] : self::$connectionName;

        // in Proxy?
        if ($config['proxy']??false) $connectionName = $this->setProxy();

        // in database.php?
        if (isset(self::$config['nodes'][$connectionName])) {
            $config = self::$config['nodes'][$connectionName];
        } else {
            self::getConfig($connectionName);
            $config = self::$config['database'];
        }

        self::$credentials[self::$connectionName] = $config;

        return $config;
    }

    /**
     * @return array
     */
    private static function getConfig(?string $path = null)
    {
        self::$config = require SB . 'config/' . basename($path??'database') . '.php';
        return self::$config;
    }

    public static function getCredential(string $name)
    {
        return self::$credentials[$name]??null;
    }

    /**
     * Load proxy validator
     * to manage database connection
     *
     * @return string
     */
    private function setProxy()
    {
        if (!file_exists($dbProxy = SB . 'config/database_proxy.php')) return [];
        include $dbProxy;
        return $defaultProfile;
    }
}