<?php
/**
* Custom Menu Layout
*
* Copyright (C) 2015 Eddy Subratha (eddy.subratha@gmail.com)
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
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


// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

include_once '../sysconfig.inc.php';

// Generate Menu
function main_menu()
{
  global $dbs;
  $modules_dir    = 'modules';
  $module_table   = 'mst_module';
  $module_list    = array();
  $_menu            = '';
  $icon           = array(
    'home'           => 'fa fa-home',
    'bibliography'   => 'fa fa-bookmark',
    'circulation'    => 'fa fa-clock-o',
    'membership'     => 'fa fa-user',
    'master_file'    => 'fa fa-pencil',
    'stock_take'     => 'fa fa-suitcase',
    'system'         => 'fa fa-keyboard-o',
    'reporting'      => 'fa fa-file-text-o',
    'serial_control' => 'fa fa-barcode',
    'logout'         => 'fa fa-close',
    'opac'           => 'fa fa-desktop'
    );
  
  $appended_first  = '<li><input type="radio" name="s-menu" id="home" role="button"><label for="home" class="menu home"><i class="nav-icon '.$icon['home'].'"></i> <span class="s-menu-title">'.__('Shortcut').'</span></label><input type="radio" name="s-menu" class="s-menu-close" id="home-close" role="button"><label for="home-close" class="menu home s-current s-menu-hide"><i class="nav-icon '.$icon['home'].'"></i> <span class="s-menu-title">'.__('Shortcut').'</span></label>';
  $_mods_q = $dbs->query('SELECT * FROM '.$module_table);
  while ($_mods_d = $_mods_q->fetch_assoc()) {
    $module_list[] = array('name' => $_mods_d['module_name'], 'path' => $_mods_d['module_path'], 'desc' => $_mods_d['module_desc']);
  }
  $_menu    .= '<ul class="nav">';
  $_menu    .= $appended_first;
  $_menu    .= sub_menu('default', $module_list); 
  $_menu    .= '</li>'."\n";
  $_menu    .= '<li><a class="menu dashboard" href="'.AWB.'index.php"><i class="nav-icon fa fa-dashboard"></i> <span class="s-menu-title">'.__('Dashboard').'</span></a></li>';
  $_menu    .= '<li><a class="menu opac" href="'.SWB.'index.php" target="_blank"><i class="nav-icon '.$icon['opac'].'"></i> <span class="s-menu-title">' . __('OPAC') . '</span></a></li>';
  if ($module_list) {
    foreach ($module_list as $_module) {
      $module_name = $_module['name'] ?? '';
      $module_path = $_module['path'] ?? '';
      $module_desc = $_module['desc'] ?? '';
      
      $_formated_module_name = ucwords(str_replace('_', ' ', $module_name));
      $_mod_dir = $module_path;
      
      if ($module_path && isset($_SESSION['priv'][$module_path]['r']) && $_SESSION['priv'][$module_path]['r'] && file_exists($modules_dir.DS.$_mod_dir)) {
        $current_icon = $icon[$module_name] ?? 'fa fa-bars';
        
        $_menu .= '<li><input type="radio" name="s-menu" id="'.$module_name.'" role="button"><label for="'.$module_name.'" class="menu '.$module_name.'" title="'.$module_desc.'"><i class="nav-icon '.$current_icon.'"></i> <span class="s-menu-title">'.__($_formated_module_name).'</span></label><input type="radio" name="s-menu" class="s-menu-close" id="'.$module_name.'-close" role="button"><label for="'.$module_name.'-close" class="menu '.$module_name.' s-current s-menu-hide"><i class="nav-icon '.$current_icon.'"></i> <span class="s-menu-title">'.__($_formated_module_name).'</span></label>';
        $_menu .= sub_menu($_mod_dir, $_module);
        $_menu .= '</li>';
      }
    }
  }
  $_menu .= '<li><a class="menu logout" href="logout.php"><i class="nav-icon '.$icon['logout'].'"></i> <span class="s-menu-title">' . __('Logout') . '</span></a></li>';
  $_menu .= '</ul>';
  echo $_menu;
}

function sub_menu($str_module = '', $_module = array())
{
    global $dbs;
    $modules_dir    = 'modules';
    $_submenu       = '<div id="sidepan"><ul class="nav">';
    
    $module_path = $_module['path'] ?? '';
    $_submenu_file  = $modules_dir.DS.$module_path.DS.'submenu.php';

    $plugin_menus = \SLiMS\Plugins::getInstance()->getMenus($str_module);
    $menu = [];
    
    if (file_exists($_submenu_file)) {
        include $_submenu_file;
        $menu = array_merge($menu ?? [], $plugin_menus);
        if (($_SESSION['uid'] ?? 0) > 1) {
            $tmp_menu = [];
            if (isset($menu) && count($menu) > 0) {
                $priv_menus = $_SESSION['priv'][$str_module]['menus'] ?? [];
                foreach ($menu as $item) {
                    // FIX: Pastikan $item[1] ada
                    if (isset($item[1]) && (in_array(md5($item[1]), $priv_menus) || $item[0] == 'Header')) $tmp_menu[] = $item;
                }
            }
            $menu = $tmp_menu;
        }

    } else {
        include 'default/submenu.php';
    $shortcuts = get_shortcuts_menu();
    foreach ($shortcuts as $shortcut) {
      $path = preg_replace('@^.+?\|/@i', '', $shortcut);
      $label = preg_replace('@\|.+$@i', '', $shortcut);
      $menu[] = array(__($label), MWB.$path, __($label));
    }
    }
    // iterate menu array
    foreach ($menu as $i=>$_list) {
      $href = $menu[$i][1] ?? '#';
      $label = $menu[$i][0] ?? '';
      $title = $menu[$i][2] ?? $label;
        
      if ($label == 'Header') {
        $_submenu .= '<li class="s-submenu-header">'.$href.'</li>'."\n";
      } else {
        $_submenu .= '<li><a class="menu s-current-child submenu-'.$i.' '.strtolower(str_replace(' ', '-', $label)).'" href="'.$href.'" title="'.$title.'"><i class="nav-icon fa fa-bars"></i> '.$label.'</a></li>'."\n";
      }
    }
    $_submenu .= '</ul></div>';
    return $_submenu;
}

function get_shortcuts_menu()
{
    global $dbs;
    $shortcuts = array();
    $shortcuts_q = $dbs->query('SELECT * FROM setting WHERE setting_name LIKE \'shortcuts_'.$dbs->escape_string($_SESSION['uid'] ?? '').'\'');
    $shortcuts_d = $shortcuts_q->fetch_assoc();
    if ($shortcuts_q->num_rows > 0) {
      if (isset($shortcuts_d['setting_value'])) {
          $unserialized_value = unserialize($shortcuts_d['setting_value']);
          if ($unserialized_value !== false) {
              $shortcuts = $unserialized_value;
          }
      }
    }
    return $shortcuts;
}