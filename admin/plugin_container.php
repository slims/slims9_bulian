<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 06/11/20 05.31
 * @File name           : plugin_container.php
 */

use \SLiMS\Plugins;

define('INDEX_AUTH', 1);
require_once __DIR__ . '/../sysconfig.inc.php';

if (!isset($_GET['mod']) && !isset($_GET['id'])) die('Plugin id not defined!');

$module = utility::filterData('mod', 'get', false, true, true);
$plugin_id = utility::filterData('id', 'get', false, true, true);

// check if plugin is enabled
$all_active_menu_plugin = Plugins::getInstance()->getMenus($module);
if (!isset($all_active_menu_plugin[$plugin_id])) die('Plugin not found / disabled!');
if (!$all_active_menu_plugin[$plugin_id][3]) die('File path is not found!');

// load autoload
Plugins::getInstance()->getAutoload($all_active_menu_plugin[$plugin_id][3]);

// load plugin
require_once $all_active_menu_plugin[$plugin_id][3];