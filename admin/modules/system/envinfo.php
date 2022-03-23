<?php
/**
 *
 * Copyright (C) 2015 Arie Nugraha (dicarve@yahoo.com)
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

/* Global application configuration */

// key to authenticate
if (!defined('INDEX_AUTH')) {
  define('INDEX_AUTH', '1');    
}

// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB.'admin/default/session.inc.php';
}
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');

// require SB.'admin/default/session_check.inc.php';
// require SIMBIO.'simbio_FILE/simbio_directory.inc.php';
// require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
// require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
// require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

$environment = array(
  array('title' => __('SLiMS Environment Mode'), 'desc' => ucfirst(ENVIRONMENT)),
  array('title' => __('SLiMS Version'), 'desc' => SENAYAN_VERSION_TAG),
  array('title' => __('Operating System'), 'desc' => php_uname('a')),
  array('title' => __('OS Architecture'), 'desc' => php_uname('m').' '.(8 * PHP_INT_SIZE).' bit'),
  array('title' => __('Web Server'), 'desc' => $_SERVER['SERVER_SOFTWARE']),  
  array('title' => __('PHP version'), 'desc' => phpversion()),  
  array('title' => __('MySQL Database version'), 'desc' => $dbs->server_info),  
  array('title' => __('MySQL Client version'), 'desc' => $dbs->client_info),  
  array('title' => __('Browser/User Agent'), 'desc' => $_SERVER['HTTP_USER_AGENT']),  
  array('title' => __('Hostname'), 'desc' => $_SERVER['SERVER_NAME']),  
  array('title' => __('jQuery version'), 'desc' => '<span id="jqueryver"></span><script>$(\'#jqueryver\').html($.fn.jquery)</script>'),  
  array('title' => __('HTML5 Support?'), 'desc' => '<span id="isHTML5"></span><script>var supportHTML5 = !!document.createElement(\'canvas\').getContext;if(supportHTML5){$(\'#isHTML5\').html(\'Support\');}</script>')
);
?>
<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?php echo __('System Environment'); ?></h2>
    </div>
    <div class="infoBox">
      <?php echo __('Information on SLiMS System Environment. Use this to support troubleshotting problem.'); ?>
    </div>
  </div>
</div>
<table class="table">
<?php foreach($environment as $env) : ?>
  <tr>
    <td><strong><?php echo $env['title'] ?></strong></td>
    <td>: <?php echo $env['desc'] ?></td>
  </tr>
<?php endforeach ?>
</table>