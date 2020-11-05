<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 01/10/20 02.07
 * @File name           : DB.php
 */

namespace SLiMS;


use PDO;
use PDOException;

class DB
{
    private static $instance = null;

    private function __construct()
    {
        try {
            self::$instance = new PDO("mysql:host=".DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME, DB_USERNAME, DB_PASSWORD);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            self::$instance->query('SET NAMES utf8');
            self::$instance->query('SET CHARACTER SET utf8');

        } catch(PDOException $error) {
            echo $error->getMessage();
        }
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) new DB();
        return self::$instance;
    }
}