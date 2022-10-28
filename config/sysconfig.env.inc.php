<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-10-27 17:29:18
 * @modify date 2021-10-27 17:29:18
 * @desc [description]
 */

// check
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

$Environment = 'production';
$ConditionEnvironment = '?';
$BasedIp = 0;
$RangeIp = [];

if ($BasedIp)
{
    // For load balancing or Reverse Proxy
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && in_array($_SERVER['HTTP_X_FORWARDED_FOR'], $RangeIp))
    {
        $Environment = $ConditionEnvironment;
    }
    else if (in_array($_SERVER['REMOTE_ADDR'], $RangeIp))
    {
        $Environment = $ConditionEnvironment;
    }   
}
