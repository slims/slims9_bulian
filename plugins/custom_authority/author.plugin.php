<?php
/**
 * Plugin Name: Custom Authority Manager
 * Plugin URI: https://github.com/heroesoebekti/
 * Description: Adds a custom authority level.
 * Version: 0.0.1
 * Author: Heru Subekti
 * Author URI: #
 */

if (!defined('INDEX_AUTH')) {
    die('Direct access not allowed!');
}

$plugin = \SLiMS\Plugins::getInstance();
$plugin->registerMenu('master_file', __('Custom Authority'), __DIR__ . '/index.php');