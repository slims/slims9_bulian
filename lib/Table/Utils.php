<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-30 21:48:33
 * @modify date 2023-01-23 12:22:40
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Table;

trait Utils
{
    private string $migrationFile = '';
    private string $verbose = '';
    
    public static function setMigrationFilePath(string $filePath)
    {
        self::getInstance()->migrationFile = $filePath;
    }
    
    public static function getMigrationFilePath()
    {
        return self::getInstance()->migrationFile;
    }

    public static function isDetailExists(string $hash)
    {
        $data = \SLiMS\DB::getInstance()->prepare('SELECT `id` FROM `migrations` WHERE `filehash` = :filehash');
        $data->execute(['filehash' => $hash]);

        return (bool) $data->rowCount();
    }

    public static function createMigrationDetail(string $filePath)
    {
        $fileDetail = explode('_', basename($filePath));

        return [
            'filepath' => $filePath,
            'filehash' => md5($filePath),
            'class' => isset($fileDetail[1]) ? str_replace('.php', '', $fileDetail[1]) : null,
            'version' => $fileDetail[0]??0,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    public static function getLastVersion()
    { 
        $version = \SLiMS\DB::getInstance()->query('SELECT `version` FROM `migrations` ORDER BY `version` DESC LIMIT 1');

        return $version->rowCount() ? intval($version->fetch(\PDO::FETCH_OBJ)->version) : 0;
    }

    public function debug()
    {
        echo $this->verbose . PHP_EOL;
    }

    public static function checkTable(string $tableName)
    {
        $tableName = \SLiMS\DB::getInstance('mysqli')->escape_string($tableName);
        $tablestate = \SLiMS\DB::getInstance('mysqli')->query('CHECK TABLE `' . $tableName . '`');

        return $tablestate->fetch_object();
    }

    public static function repairTable(string $tableName)
    {
        $tableName = \SLiMS\DB::getInstance('mysqli')->escape_string($tableName);
        $tablestate = \SLiMS\DB::getInstance('mysqli')->query('REPAIR TABLE `' . $tableName . '`');

        return $tablestate->fetch_object();
    }
}