<?php
/**
 * @create date 2023-08-22 14:50:12
 * @modify date 2023-10-28 13:08:44
 * @desc Database connection manager
 */
namespace SLiMS;

use mysqli;
use PDO;

class Connection {
    private ?object $conn = null;
    private string $name;
    private string $driver;
    private string $dsnPrefix = 'mysql';
    private array $detail;

    public function __construct(string $name, array $detail, string $driver)
    {
        $this->name = $driver . '_' . $name;
        $this->driver = $driver;
        $this->detail = $detail;
    }

    private function connect()
    {
        if ($this->driver === 'pdo' && $this->conn === null) {       
            $this->conn = new PDO(...$this->buildConnectionArgument());
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, ENVIRONMENT == 'development' ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT);
        } else if ($this->driver === 'mysqli' && $this->conn === null) {
            $this->conn = new mysqli(...$this->buildConnectionArgument());
        }

        if ($this->dsnPrefix == 'mysql') {
            $this->conn->query('SET NAMES utf8');
            $this->conn->query('SET CHARACTER SET utf8');
        }
    }

    public function buildConnectionArgument()
    {
        extract($this->detail);

        if ($this->driver === 'pdo') {
            $this->dsnPrefix = $options['driver']??'mysql';
            $dsn = [];
            if (isset($options['dsn'])) {
                $this->dsnPrefix = explode(':', $options['dsn'])[0]??'mysql';
                $dsn[] = str_replace(['{host}','{port}','{dbname}'], [$host, $port, ($database??$name)], $options['dsn']);
            } else {
                $dsn[] = $this->dsnPrefix . ':host=' . $host . ';port=' . $port . ';dbname=' . ($database??$name);
            }
            $dsn[] = $username;
            $dsn[] = $password;
            return $dsn;
        } 

        return [$host, $username, $password, ($database??$name), $port];
    }

    public function getName()
    {
        return $this?->name??null;
    }

    public function getDsnPrefix()
    {
        return $this->dsnPrefix;
    }

    public function getDetail(string $key = '', $default = null)
    {
        if (empty($key)) return $this->detail;

        $result = null;
        foreach (explode('.', trim($key, '.')) as $key) {
            if (isset($this->detail[$key])) {
                $result = $this->detail[$key];
                continue;
            }

            if (is_array($result) && isset($result[$key])) $result = $result[$key];
            else $result = $default;
        }
        return $result;
    }

    public function getConn()
    {
        $this->connect();
        return $this?->conn??null;
    }
    
    public function __sleep()
    {
        return array('detail');
    }
    
    public function __wakeup()
    {
        $this->connect();
    }

    public function __toString()
    {
        return $this->name;
    }
}