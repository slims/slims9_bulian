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

class DB
{
    private static $instance = null;
    private static $instance_mysqli = null;

    private function __construct($driver = 'pdo')
    {
        try {

            if ($driver === 'mysqli') {
                self::$instance_mysqli = new \mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
            } else {
                self::$instance = new PDO("mysql:host=".DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME, DB_USERNAME, DB_PASSWORD);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                self::$instance->query('SET NAMES utf8');
                self::$instance->query('SET CHARACTER SET utf8');
            }

        } catch(PDOException $error) {
            echo $error->getMessage();
        } catch (Exception $error) {
            echo $error->getMessage();
        }
    }

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
}