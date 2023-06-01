<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 24/03/20 11.21
 * @File name           : autoload.php
 *
 * Original source code : https://stackoverflow.com/a/39774973
 */

$namespaces = [
    "SLiMS\\" => "/",
    "Slims\\Opac\\" => "../src/Slims/Opac",
    "Volnix\\CSRF\\" => "/csrf/src/",
    "Commands\\" => "../plugins/Commands/",
    "Psr\\SimpleCache\\" => "/psr/simple-cache/src/",
    "PhpOffice\\PhpSpreadsheet\\" => "/phpoffice/phpspreadsheet/src/PhpSpreadsheet/",
    "Ramsey\\Uuid\\" => "/uuid/src/",
    "Ramsey\\Collection\\" => "/collection/src/",
    "Brick\\Math\\" => "/math/src/",
    "Ifsnop\\Mysqldump\\" => "/mysqldump-php/src/Ifsnop/Mysqldump/",
    "PHPMailer\\PHPMailer\\" => "/PHPMailer/src/",
    "GuzzleHttp" => "/guzzlehttp/guzzle/src",
    "GuzzleHttp\\Psr7\\" => "/guzzlehttp/psr7/src",
    "GuzzleHttp\\Exception\\" => "/guzzle/src/",
    "GuzzleHttp\Promise" => "/guzzlehttp/promises/src",
    "Psr\\Http\\Message\\" => "/psr/http-message/src",
    "Psr\\Http\\Client\\" => "/psr/http-client/src",
    "Complex\\" => "/markbaker/comples/classes/src/",
    "Matrix\\" => "/markbaker/matrix/classes/src/",
    "MyCLabs\\Enum\\" => "/myclabs/php-enum/src/",
    "Symfony\\Polyfill\\Mbstring\\" => "/symfony/polyfill-mbstring/",
    "Symfony\\Component\\Translation\\" => "/symfony/translation/",
    "Symfony\\Component\VarDumper\\" => "/symfony/var-dumper/",
    "Symfony\Component\Console\\" => '/symfony/console/',
    "Symfony\Component\String\\" => '/symfony/string/',
    "Symfony\Contracts\Service\\" => '/symfony/service-contracts/',
    "Symfony\\Contracts\\Translation\\" => "/symfony/translation-contracts/",
    "ZipStream\\" => "/maennchen/zipstream-php/src/",
    "Carbon\\" => "/nesbot/carbon/src/Carbon/",
    "Minigalnano\\" => "/minigalnano/",
    "League\\Flysystem\\" => "/league/flysystem/src/",
    "League\\MimeTypeDetection\\" => "/league/mime-type-detection/src/",
    "phpseclib3\\" => "/phpseclib/phpseclib/phpseclib/",
    'ParagonIE\\ConstantTime\\' => '/paragonie/constant_time_encoding/src',
    'OTPHP\\' => '/spomky-labs/otphp/src',
    'DASPRiD\\Enum\\' => '/dasprid/enum/src',
    'BaconQrCode\\' => '/bacon/bacon-qr-code/src',
];

$class_alias = [];
if (file_exists($classAliasPath = __DIR__ . '/../config/class_alias.php')) $class_alias = require $classAliasPath;

foreach ($namespaces as $namespace => $classpaths) {
    if (!is_array($classpaths)) {
        $classpaths = array($classpaths);
    }
    spl_autoload_register(function ($classname) use ($namespace, $classpaths, $class_alias) {
        if (preg_match("#^" . preg_quote($namespace) . "#", $classname)) {
            $classname = str_replace($namespace, "", $classname);
            $filename = preg_replace("#\\\\#", "/", $classname) . ".php";
            foreach ($classpaths as $classpath) {
                $fullpath = __DIR__ . "/" . $classpath . "/$filename";
                if (file_exists($fullpath)) include_once $fullpath;
            }
        } elseif (isset($class_alias[$classname]) && class_exists($class_alias[$classname])) {
            class_alias($class_alias[$classname], $classname);
        }
    });
}

/*
 |--------------------------------------------------------------------------
 | Load library with self autoload
 |--------------------------------------------------------------------------
 */
// Ezyang
include "ezyang/htmlpurifier/library/HTMLPurifier.auto.php";
// Symfony
include "symfony/polyfill-mbstring/bootstrap.php";
// Nesbot legacy function
include "nesbot/carbon/legacy.func.php";
// Var-dumper
// Load the global dump() function
include 'symfony/var-dumper/Resources/functions/dump.php';