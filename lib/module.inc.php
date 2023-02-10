<?php

/**
 * module class
 * Application modules related class
 *
 * Copyright (C) 2010 Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

use SLiMS\GroupMenu;
use SLiMS\GroupMenuOrder;
use SLiMS\Plugins;

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

class module extends simbio
{
    private $modules_dir = 'modules';
    private $module_table = 'mst_module';
    public $module_list = array();
    public $appended_first;
    public $appended_last;


    public function __construct()
    {
        $this->appended_first = '<li><a class="menu home#replaced#" href="index.php"><span>' . __('Home') . '</a></li><li><a class="menu opac" href="../index.php" title="' . __('View OPAC in New Window') . '" target="_blank"><span>' . __('OPAC') . '</span></a></li>';
        $this->appended_last = '<li><a class="menu logout" href="logout.php"><span>' . __('Logout') . '</span></a></li>';
    }

    /**
     * Method to set modules directory
     *
     * @param string $str_modules_dir
     * @return  void
     */
    public function setModulesDir($str_modules_dir)
    {
        $this->modules_dir = $str_modules_dir;
    }


    /**
     * Method to generate a list of module menu
     *
     * @param object $obj_db
     * @return  string
     */
    public function generateModuleMenu($obj_db)
    {
        // create the HTML Hyperlinks
        $_menu = '<ul id="menuList">';
        $_menu .= !isset($_GET['mod']) ? str_replace('#replaced#', ' menuCurrent', $this->appended_first) : str_replace('#replaced#', '', $this->appended_first);
        // sort modules
        if ($this->module_list = $this->getModuleMainMenu($obj_db)) {
            foreach ($this->module_list as $_module) {
                $_formated_module_name = ucwords(str_replace('_', ' ', $_module['name']));
                $_mod_dir = $_module['path'];
                $_menu .= '<li><a class="menu ' . $_module['name'] . ((isset($_GET['mod']) && $_GET['mod'] == $_module['path']) ? ' menuCurrent' : '') . '" title="' . $_module['desc'] . '" href="index.php?mod=' . $_mod_dir . '"><span>' . __($_formated_module_name) . '</span></a></li>';
            }
        }
        $_menu .= $this->appended_last;
        $_menu .= '</ul>';

        return $_menu;
    }

    /**
     * Method to get a list of module menu
     *
     * @param object $obj_db
     * @param boolean $also_get_childs
     * @return  array
     */
    public function getModuleMainMenu($obj_db, $also_get_childs = false)
    {
        $_menu = array();
        $module_list = array();
        // get module data from database
        $_mods_q = $obj_db->query('SELECT * FROM ' . $this->module_table);
        while ($_mods_d = $_mods_q->fetch_assoc()) {
            $module_list[] = array('name' => $_mods_d['module_name'], 'path' => $_mods_d['module_path'], 'desc' => $_mods_d['module_desc']);
        }

        // Get module from plugin
        Plugins::run('module_main_menu_init', [&$module_list]);
        
        // sort modules
        if ($module_list) {
            foreach ($module_list as $_id => $_module) {
                $_mod_dir = $_module['path'];
                $_path_exists = file_exists($this->modules_dir . $_mod_dir) || (isset($_module['plugin_module_path']) && file_exists($_module['plugin_module_path']));
                if (isset($_SESSION['priv'][$_module['path']]['r']) && $_SESSION['priv'][$_module['path']]['r'] && $_path_exists) {
                    $_menu[$_id] = $_module;
                    if ($also_get_childs) {
                        $_menu[$_id]['childs'] = $this->getSubMenuItems($_module['name']);
                    }
                }
            }
        }

        return $_menu;
    }


    /**
     * Method to generate a list of module submenu
     *
     * @param string $str_module
     * @return  string
     */
    public function generateSubMenu($str_module = '')
    {
        global $dbs;
        $_submenu = '';
        $_submenu_current = 'curModuleLink';
        $i = 0;
        $menus = $this->getSubMenuItems($str_module);
        // iterate menu array
        foreach ($menus as $header => $menu) {
            $_submenu .= '<div class="subMenuHeader subMenuHeader-' . $header . '">' . strtoupper($header) . '</div>';

            foreach ($menu as $item) {
                if ($i > 0) $_submenu_current = '';
                $_submenu .= '<a class="subMenuItem ' . $_submenu_current . '" '
                    . ' href="' . $item[1] . '"'
                    . ' title="' . (isset($item[2]) ? $item[2] : $item[0]) . '" href="#"><span>' . $item[0] . '</span></a>';
                $i++;
            }
        }
        $_submenu .= '&nbsp;';
        return $_submenu;
    }

    /**
     * Method to get a list of module submenu
     *
     * @param string $str_module
     * @return  array
     */
    public function getSubMenuItems($str_module = '')
    {
        global $dbs;
        $_submenu_file = $this->modules_dir . $str_module . DIRECTORY_SEPARATOR . 'submenu.php';

        // get menus from plugins
        $plugin_menus = \SLiMS\Plugins::getInstance()->getMenus($str_module);

        if (file_exists($_submenu_file) || (isset($_SESSION['priv'][$str_module]['submenu']) && file_exists($_submenu_file = $_SESSION['priv'][$str_module]['submenu']))) {
            include $_submenu_file;
        } else {
            include 'default/submenu.php';
            foreach ($this->get_shortcuts_menu($dbs) as $key => $value) {
                $link = explode('|', $value);
                // Exception for shortcut menu based on registered plugin
                if (preg_match('/plugin_container/', $link[1])) {
                    $menu[$link[0]] = array(__($link[0]), $link[1]);
                    continue;
                }
                $menu[$link[0]] = array(__($link[0]), MWB . $link[1]);
            }
        }

        $menus = [];
        foreach ($this->reorderMenus($menu, $plugin_menus) as $header => $items) {
            foreach ($items as $item) {
                $menus[$header] = $menus[$header] ?? [];
                if ($_SESSION['uid'] > 1 && !empty($str_module) && !in_array(md5($item[1]), $_SESSION['priv'][$str_module]['menus'] ?? [])) continue;
                $menus[$header][] = $item;
            }
        }
        $menus = array_filter($menus, fn ($m) => count($m) > 0);
        return $menus;
    }

    /**
     * Method to order default menu and plugin menu
     * 
     * @param array $default 
     * @param array $plugin 
     * @return array 
     */
    function reorderMenus($default, $plugin)
    {
        $groups = [];
        $orders = GroupMenuOrder::getInstance()->getOrder();
        $group_menu = GroupMenu::getInstance()->getGroup();

        // collect header from default menu
        $header = null;
        foreach ($default as $menu) {
            if (count($menu) === 2 && strtolower($menu[0]) === 'header') {
                // before continue to new header
                // check to plugin group
                if (!is_null($header) && isset($group_menu[$header])) {
                    foreach ($group_menu[$header] as $hash) {
                        $groups[$header][] = $plugin[$hash];
                    }
                    unset($group_menu[$header]);
                }

                // reset header
                $header = null;
                // iterate orders
                if (count($orders) > 0) {
                    // get menu before
                    foreach ($orders as $key => $value) {
                        if (count($plugin) < 1) break;
                        $group_menu_items = array_map(fn ($i) => $plugin[$i] ?? [], $group_menu[$key]);
                        if (count($group_menu_items) > 0 && strtolower($menu[1]) === $value['group'] && $value['position'] === 'before')
                            $groups[strtolower($key)] = $group_menu_items;
                    }

                    // main menu
                    $groups[strtolower($menu[1])] = [];

                    // get menu after
                    foreach ($orders as $key => $value) {
                        if (count($plugin) < 1) break;
                        $group_menu_items = array_map(fn ($i) => $plugin[$i] ?? [], $group_menu[$key]);
                        if (count($group_menu_items) > 0 && strtolower($menu[1]) === $value['group'] && $value['position'] === 'after')
                            $groups[strtolower($key)] = $group_menu_items;
                    }
                } else {
                    $groups[strtolower($menu[1])] = [];
                }
                $header = strtolower($menu[1]);
                continue;
            }
            if (!is_null($header)) {
                // Check if the registered plugin menu label is the same as the default
                $override_menu = array_values(array_filter($plugin, function($itemPlugin) use($menu) {
                    if ($itemPlugin[0] === $menu[0] && isset($itemPlugin[3])) return true;
                }))[0]??$menu;

                // if match then remove matching plugin from plugin list
                if (isset($override_menu[3])) unset($plugin[md5(realpath($override_menu[3]??''))]);

                // Register menu into group
                $groups[strtolower($header)][] = $override_menu;
            }
        }

        foreach ($group_menu as $header => $menus) {
            $tmp_menu = [];
            foreach ($menus as $hash) {
                if (isset($plugin[$hash])) $tmp_menu[] = $plugin[$hash];
            }

            if(count($tmp_menu) > 0) $groups[$header] = $tmp_menu;
        }

        // ungrouped plugin group to "plugins" group
        $ungrouped = array_filter(array_keys($plugin), fn ($p) => !in_array($p, GroupMenu::getInstance()->getPluginInGroup()));
        $ungrouped = ['plugins' => array_map(fn ($i) => $plugin[$i], $ungrouped)];

        // merge group
        if (count($plugin) > 0)
            $groups = array_merge($groups, $ungrouped);

        return $groups;
    }

    /**
     * Method to get a first submenu of module
     * 
     * @param string $str_module 
     * @return mixed 
     */
    public function getFirstMenu($str_module = '')
    {
        $menus = $this->getSubMenuItems($str_module);
        $key = array_keys($menus)[0] ?? false;
        if ($key) return $menus[$key][0] ?? null;
        return null;
    }

    /**
     * Method to get a list of shortcut submenu
     *
     * @param object $obj_db
     * @return  array
     */
    function get_shortcuts_menu()
    {
        global $dbs;
        $shortcuts = array();
        $shortcuts_q = $dbs->query('SELECT * FROM setting WHERE setting_name LIKE \'shortcuts_' . $dbs->escape_string($_SESSION['uid']) . '\'');
        $shortcuts_d = $shortcuts_q->fetch_assoc();
        if ($shortcuts_q->num_rows > 0) {
            $shortcuts = unserialize($shortcuts_d['setting_value']);
        }
        return $shortcuts;
    }

    public function unprivileged()
    {
        global $sysconf;
        $alertType = 'alert-warning';
        $alertTitle = __('Warning');
        $alertMessage = __('You don\'t have access to interact with this module. Call system administrator to give you right to access it.');
        include SB . 'template/alert.php';
    }
}
