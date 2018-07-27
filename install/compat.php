<?php
function isPhpOk($expectedVersion)
{
    // Is this version of PHP greater than minimum version required?
    return version_compare(PHP_VERSION, $expectedVersion, '>=');
}

function isDatabaseDriverOk()
{
    return extension_loaded('mysql') or extension_loaded('mysqli');
}

function databaseDriverType()
{
    if (extension_loaded('mysql')) {
        $type = 'mysql';
    } else if (extension_loaded('mysqli')) {
        $type = 'mysqli';
    } else {
        $type = 'unknow';
    }

    return $type;
}

function isZlibOk()
{
    return extension_loaded('zlib');
}

function isCurlOk()
{
    return extension_loaded('curl');
}

function isMcryptOk()
{
    return extension_loaded('mcrypt');
}

function isGdOk()
{
    // Homeboy is not rockin GD at all
    if (! function_exists('gd_info')) {
        return false;
    }

    $gd_info = gd_info();
    $gd_version = preg_replace('/[^0-9\.]/', '', $gd_info['GD Version']);

    // If the GD version is at least 1.0
    return ($gd_version >= 1);
}

function isYazOk()
{
    return extension_loaded('yaz');
}

function isGettextOk()
{
    return extension_loaded('gettext');
}

function isMbStringOk()
{
    return extension_loaded('mbstring');
}

?>
