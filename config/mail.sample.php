<?php
return [
    /**
     * SLiMS by default use PHPMailer as core library
     * to send email
     */

    /**
     * Debugging status
     * 
     * please read the documentation in:
     * - https://phpmailer.github.io/PHPMailer/classes/PHPMailer-PHPMailer-PHPMailer.html#property_SMTPDebug
     */
    'debug' => '_debug_',

    /**
     * An encryption to use on the SMTP connection.
     * 
     * for more information read:
     * - https://phpmailer.github.io/PHPMailer/classes/PHPMailer-PHPMailer-PHPMailer.html#property_SMTPSecure
     */
    'SMTPSecure' => '_SMTPSecure_',

    /**
     * Enable process
     */
    'enable' => true,

    /**
     * SMTP server address
     * 
     * example : ssl://smtp.gmail.com:465
     */
    'server' => '_server_',

    /**
     * SMTP Port.
     * 
     * The value of this option is depend
     * on which SMTP Encryption you use
     */
    'server_port' => '_serverport_',

    /**
     * Enable SMTP authentication
     */
    'auth_enable' => true,

    /**
     * User credential
     */
    'auth_username' => '_authusername_', // example : foo@example.org
    'auth_password' => '_authpassword_',

    /**
     * E-Mail information
     */
    'from' => '_from_', // example foo@example.org
    'from_name' => '_fromname_', // put a nice name here
    'reply_to' => '_from_', // similar with 'from' option
    'reply_to_name' => '_fromname_', // similar with 'from' option
];