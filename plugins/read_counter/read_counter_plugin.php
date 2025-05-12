<?php
/**
 * Plugin Name: Read Counter
 * Plugin URI: https://github.com/idoalit/read_counter
 * Description: Logging the books that have been read
 * Version: 0.0.2
 * Author: Waris Agung Widodo
 * Author URI: https://github.com/idoalit
 */


// registering our plugin into bibliography module
$this->registerMenu('bibliography', __('Read Counter'), __DIR__ . '/index.php');
