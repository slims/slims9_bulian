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

    public function __construct() {
        $this->appended_first = '<li><a class="menu home#replaced#" href="index.php"><span>' . __('Home') . '</a></li><li><a class="menu opac" href="../index.php" title="' . __('View OPAC in New Window') . '" target="_blank"><span>' . __('OPAC') . '</span></a></li>';
        $this->appended_last = '<li><a class="menu logout" href="logout.php"><span>' . __('LOGOUT') . '</span></a></li>';
    }

    /**
     * Method to set modules directory
     *
     * @param   string  $str_modules_dir
     * @return  void
     */
    public function setModulesDir($str_modules_dir)
    {
      $this->modules_dir = $str_modules_dir;
    }


    /**
     * Method to generate a list of module menu
     *
     * @param   object  $obj_db
     * @return  string
     */
    public function generateModuleMenu($obj_db)
    {
      // create the HTML Hyperlinks
      $_menu = '<ul id="menuList">';
      $_menu .= !isset($_GET['mod'])?str_replace('#replaced#', ' menuCurrent',$this->appended_first):str_replace('#replaced#', '',$this->appended_first);
      // sort modules
      if ($this->module_list = $this->getModuleMainMenu($obj_db)) {
          foreach ($this->module_list as $_module) {
              $_formated_module_name = ucwords(str_replace('_', ' ', $_module['name']));
              $_mod_dir = $_module['path'];
              $_menu .= '<li><a class="menu '.$_module['name'].( (isset($_GET['mod']) && $_GET['mod']==$_module['path'])?' menuCurrent':'' ).'" title="'.$_module['desc'].'" href="index.php?mod='.$_mod_dir.'"><span>'.__($_formated_module_name).'</span></a></li>';
          }
      }
      $_menu .= $this->appended_last;
      $_menu .= '</ul>';
      
      return $_menu;
    }
    
    /**
     * Method to get a list of module menu
     *
     * @param   object  $obj_db
     * @param   boolean $also_get_childs
     * @return  array
     */    
    public function getModuleMainMenu($obj_db, $also_get_childs = false)
    {
      $_menu = array();
      $module_list = array();
      // get module data from database
      $_mods_q = $obj_db->query('SELECT * FROM '.$this->module_table);
      while ($_mods_d = $_mods_q->fetch_assoc()) {
          $module_list[] = array('name' => $_mods_d['module_name'], 'path' => $_mods_d['module_path'], 'desc' => $_mods_d['module_desc']);
      }
      
      // sort modules
      if ($module_list) {
          foreach ($module_list as $_id => $_module) {
              $_mod_dir = $_module['path'];
              if (isset($_SESSION['priv'][$_module['path']]['r']) && $_SESSION['priv'][$_module['path']]['r'] && file_exists($this->modules_dir.$_mod_dir)) {
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
     * @param   string  $str_module
     * @return  string
     */
    public function generateSubMenu($str_module = '')
    {
      global $dbs;
      $_submenu = '';
      $menu = $this->getSubMenuItems($str_module);
      // iterate menu array
      foreach ($menu as $_list) {
        if ($_list[0] == 'Header') {
          $_submenu .= '<div class="subMenuHeader">'.$_list[1].'</div>';
        } else {
          $_submenu .= '<a class="subMenuItem" '
              .' href="'.$_list[1].'"'
              .' title="'.( isset($_list[2])?$_list[2]:$_list[0] ).'" href="#"><span>'.$_list[0].'</span></a>';
        }
      }
      $_submenu .= '&nbsp;';
      return $_submenu;
    }
    
    /**
     * Method to get a list of module submenu
     *
     * @param   string  $str_module
     * @return  array
     */
    public function getSubMenuItems($str_module = '')
    {
      global $dbs;
      $_submenu_file = $this->modules_dir.$str_module.DIRECTORY_SEPARATOR.'submenu.php';
      if (file_exists($_submenu_file)) {
        include $_submenu_file;
      } else {
        include 'default/submenu.php';
      }
      return $menu;
    }
}
