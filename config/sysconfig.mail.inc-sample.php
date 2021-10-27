<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-10-27 07:09:09
 * @modify date 2021-10-27 07:09:09
 * @desc [description]
 */

 // be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

// Main configuration
$sysconf['mail']['debug'] = '_debug_'; // Show log? 0 : Disable, 2 : Enable
$sysconf['mail']['SMTPSecure'] = '_SMTPSecure_'; // ssl or tls
$sysconf['mail']['enable'] = true;
$sysconf['mail']['server'] = '_server_'; // SMTP server : ssl://smtp.gmail.com:465
$sysconf['mail']['server_port'] = '_serverport_'; // the SMTP port
$sysconf['mail']['auth_enable'] = true; // enable SMTP authentication
$sysconf['mail']['auth_username'] = '_authusername_'; // SMTP account username
$sysconf['mail']['auth_password'] = '_authpassword_'; // SMTP account password
$sysconf['mail']['from'] = '_from_'; // Email sender address
$sysconf['mail']['from_name'] = '_fromname_'; // Email sender name
$sysconf['mail']['reply_to'] = &$sysconf['mail']['from'];
$sysconf['mail']['reply_to_name'] = &$sysconf['mail']['from_name'];