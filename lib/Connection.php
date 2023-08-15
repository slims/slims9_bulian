<?php
namespace SLiMS;

use mysqli;
use PDO;

class Connection {
    private ?object $conn = null;
    private string $name;
    private string $driver;
    private array $detail;

    public function __construct(string $name, array $detail, string $driver)
    {
        $this->name = $driver . '_' . $name;
        $this->driver = $driver;
        $this->detail = $detail;
    }

    private function connect()
    {
        if ($this->driver === 'pdo') {       
            $this->conn = new PDO(...$this->buildConnectionArgument());
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, ENVIRONMENT == 'development' ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT);
        } else {
            $this->conn = new mysqli(...$this->buildConnectionArgument());
        }
        $this->conn->query('SET NAMES utf8');
        $this->conn->query('SET CHARACTER SET utf8');
    }

    private function buildConnectionArgument()
    {
        extract($this->detail);
        return $this->driver === 'pdo' ? 
                ['mysql:host=' . $host . ';port=' . $port . ';dbname=' . ($database??$name), $username, $password] 
                :
                [$host, $username, $password, ($database??$name), $port];
    }

    public function getName()
    {
        return $this?->name??null;
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
}