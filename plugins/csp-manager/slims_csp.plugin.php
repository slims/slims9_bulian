<?php
/**
 * Plugin Name: SLiMS CSP
 * Plugin URI: https://github.com/drajathasan/slims-csp-manager
 * Description: atur pengaturan CSP SLiMS
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: https://t.me/drajathasan
 */


// registering menus or hook
$this->registerMenu("system", 'CSP', __DIR__ . '/index.php');