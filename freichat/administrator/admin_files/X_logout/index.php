<?php

if (!defined('FREI_ADMIN')) {
    die("no direct access");
}
$_SESSION['phplogin'] = false;
unset($_SESSION['phplogin']);
echo "logged out successfully!";
?>