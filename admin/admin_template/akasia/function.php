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
  global $dbs, $sysconf;
  $modules_dir    = 'modules';
  $module_table   = 'mst_module';
  $module_list    = array();
  $_menu          = '';
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
  //fake, just for translation po... xieyh :(
  $translations          = array(
    'bibliography'   => __('Bibliography'),
    'master_file'    => __('Master File'),
  );
  $appended_first  = '<li><input type="radio" name="s-menu" id="home" role="button"><label for="home" class="menu home"><i class="nav-icon '.$icon['home'].'"></i> <span class="s-menu-title">'.__('Shortcut').'</span></label><input type="radio" name="s-menu" class="s-menu-close" id="home-close" role="button"><label for="home-close" class="menu home s-current s-menu-hide"><i class="nav-icon '.$icon['home'].'"></i> <span class="s-menu-title">'.__('Shortcut').'</span></label>';
  
  $_mods_q = $dbs->query('SELECT * FROM '.$module_table);
  
  if ($_mods_q) {
      while ($_mods_d = $_mods_q->fetch_assoc()) {
          $module_list[] = array('name' => $_mods_d['module_name'], 'path' => $_mods_d['module_path'], 'desc' => $_mods_d['module_desc']);
      }
  }

  $_menu  .= '<ul class="nav">';
  $_menu  .= $appended_first;
  $_menu  .= sub_menu('default', array()); 
  $_menu  .= '</li>'."\n";
  $_menu  .= '<li><a class="menu dashboard" href="'.AWB.'index.php"><i class="nav-icon fa fa-dashboard"></i> <span class="s-menu-title">'.__('Dashboard').'</span></a></li>';
  $_menu  .= '<li><a class="menu opac" href="'.SWB.'index.php" target="_blank"><i class="nav-icon '.$icon['opac'].'"></i> <span class="s-menu-title">' . __('OPAC') . '</span></a></li>';
  
  $priv_session = $_SESSION['priv'] ?? [];

  if ($module_list) {
    foreach ($module_list as $_module) {
      $_formated_module_name = ucwords(str_replace('_', ' ', $_module['name']));
      $_mod_dir = $_module['path'];
      
      $has_read_permission = isset($priv_session[$_mod_dir]['r']) && $priv_session[$_mod_dir]['r'];

      if ($has_read_permission && file_exists($modules_dir.DS.$_mod_dir)) {
        $_icon = $icon[$_module['name']] ?? 'fa fa-bars';
        $_menu .= '<li><input type="radio" name="s-menu" id="'.$_module['name'].'" role="button"><label for="'.$_module['name'].'" class="menu '.$_module['name'].'" title="'.$_module['desc'].'"><i class="nav-icon '.$_icon.'"></i> <span class="s-menu-title">'.__($_formated_module_name).'</span></label><input type="radio" name="s-menu" class="s-menu-close" id="'.$_module['name'].'-close" role="button"><label for="'.$_module['name'].'-close" class="menu '.$_module['name'].' s-current s-menu-hide"><i class="nav-icon '.$_icon.'"></i> <span class="s-menu-title">'.__($_formated_module_name).'</span></label>';
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
    $modules_dir  = 'modules';
    $_submenu     = '<div id="sidepan"><ul class="nav">'; 
    $module_path = $_module['path'] ?? $str_module;
    $_submenu_file  = $modules_dir.DS.$module_path.DS.'submenu.php';
    $menu = [];
    $plugin_menus = \SLiMS\Plugins::getInstance()->getMenus($str_module);

    if (file_exists($_submenu_file)) {
        include $_submenu_file;
  
        $menu = array_merge($menu, $plugin_menus);
        $uid = $_SESSION['uid'] ?? 0;
        $priv_session = $_SESSION['priv'] ?? [];

        if ($uid > 1) {
            $tmp_menu = [];
            $allowed_menus = $priv_session[$str_module]['menus'] ?? [];

            if (isset($menu) && count($menu) > 0) {
                foreach ($menu as $item) {
                  $is_header = ($item[0] ?? '') === 'Header';
                  $menu_path_md5 = md5($item[1] ?? '');

                  if (in_array($menu_path_md5, $allowed_menus) || $is_header) {
                      $tmp_menu[] = $item;
                  }
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
    
    if (is_array($menu)) {
      // iterate menu array
      foreach ($menu as $i=>$_list) {
        $label = $_list[0] ?? '';
        $href = $_list[1] ?? '#';
        $title = $_list[2] ?? $label;

        if ($label === 'Header') {
          $_submenu .= '<li class="s-submenu-header">'.$href.'</li>'."\n";
        } else {
          $css_class = strtolower(str_replace(' ', '-', $label));
          $_submenu .= '<li><a class="menu s-current-child submenu-'.$i.' '.$css_class.'" href="'.$href.'" title="'.$title.'"><i class="nav-icon fa fa-bars"></i> '.$label.'</a></li>'."\n";
        }
      }
    }
    
    $_submenu .= '</ul></div>';
    return $_submenu;
}

function get_shortcuts_menu()
{
    global $dbs;
    $shortcuts = array();
    $uid = $_SESSION['uid'] ?? 0;

    $setting_name_param = 'shortcuts_'.$dbs->escape_string((string)$uid);
    $shortcuts_q = $dbs->query('SELECT * FROM setting WHERE setting_name LIKE \''.$setting_name_param.'\'');
    
    if ($shortcuts_q) {
      $shortcuts_d = $shortcuts_q->fetch_assoc();
      if ($shortcuts_q->num_rows > 0) {
        $setting_value = $shortcuts_d['setting_value'] ?? null;
        if ($setting_value !== null) {
            $unserialized_data = unserialize($setting_value);
            if (is_array($unserialized_data)) {
                $shortcuts = $unserialized_data;
            }
        }
      }
    }
    return $shortcuts;
}