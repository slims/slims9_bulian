<?php
/**
 * Plugin Name: Read Counter
 * Plugin URI: https://github.com/idoalit/read_counter
 * Description: Logging the books that have been read
 * Version: 0.0.2
 * Author: Waris Agung Widodo
 * Author URI: https://github.com/idoalit
 */

// get instance of plugin object
$plugin = \SLiMS\Plugins::getInstance();

// registering our plugin into bibliography module
$plugin->registerMenu('bibliography', __('Read Counter'), __DIR__ . '/index.php');
