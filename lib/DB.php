<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 01/10/20 02.07
 * @File name           : DB.php
 */

namespace SLiMS;


use PDO;
use PDOException;
use PHPMailer\PHPMailer\Exception;
use Ifsnop\Mysqldump as IMysqldump;

class DB
{
    /**
     * PDO instance
     * @var null
     */
    private static $instance = null;

    /**
     * MySQLi Instance
     * @var null
     */
    private static $instance_mysqli = null;

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

            if ($driver === 'mysqli') {
                self::$instance_mysqli = new \mysqli(...$this->getProfile($driver));
            } else {
                self::$instance = new PDO(...$this->getProfile($driver));
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, ENVIRONMENT == 'development' ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT);
                self::$instance->query('SET NAMES utf8');
                self::$instance->query('SET CHARACTER SET utf8');
            }

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
    public static function getInstance($driver = 'pdo')
    {
        if ($driver === 'mysqli') {
            if (is_null(self::$instance_mysqli)) new DB('mysqli');
            return self::$instance_mysqli;
        } else {
            if (is_null(self::$instance)) new DB();
            return self::$instance;
        }
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
        return new IMysqldump\Mysqldump(...array_merge($static->getProfile('pdo'), [config('database_backup.options')]));
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
    private function getProfile($driver = 'pdo')
    {
        $config = $this->getConfig();
        $defaultProfile = $config['default_profile'];

        if ($config['proxy']) $defaultProfile = $this->setProxy();

        extract($config['nodes'][$defaultProfile]??[]);

        if (!isset($host)) throw new \Exception("Database " . $defaultProfile . " is not valid!");

        // Casting $port as integer
        $port = (int)$port;

        return $driver === 'pdo' ? 
                ['mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database, $username, $password] 
                :
                [$host, $username, $password, $database, $port];
    }

    /**
     * Get database credential
     *
     * @param string $nodeName
     * @return array
     */
    private function getNode(string $nodeName)
    {
        return $this->getConfig()['nodes'][$nodeName]??[];
    }

    /**
     * @return array
     */
    private function getConfig()
    {
        return require SB . 'config/database.php';
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