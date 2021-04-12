<?php
/**
 * Plugin Name: Label & Barcode
 * Plugin URI: https://github.com/idoalit/label_barcode
 * Description: Label Call Number including barcode item
 * Version: 0.0.1
 * Author: Waris Agung Widodo
 * Author URI: https://github.com/idoalit
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering menus
$plugin->registerMenu('bibliography', 'Label & Barcode', __DIR__ . '/index.php');
