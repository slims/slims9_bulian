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

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_FILE/simbio_directory.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

?>
<fieldset class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?php echo __('System Environment'); ?></h2>
    </div>
    <div class="infoBox">
      <?php echo __('Information on SLiMS System Environment. Use this to support troubleshotting problem.'); ?>
    </div>
  </div>
</fieldset>

<form id="mainForm" class="form-horizontal envinfo">

  <div class="form-group">
    <label for="os" class="col-sm-2 control-label"><?php echo __('Operating System') ?></label>
    <div class="col-sm-10">
      <p class="form-control-static" id="os"><?php echo php_uname('a') ?></p>
    </div>
  </div>
  <div class="form-group">
    <label for="os" class="col-sm-2 control-label"><?php echo __('OS Architecture') ?></label>
    <div class="col-sm-10">
      <p class="form-control-static" id="os"><?php echo php_uname('m').' '.(8 * PHP_INT_SIZE).' bit'; ?></p>
    </div>
  </div>
  <div class="form-group">
    <label for="webserver" class="col-sm-2 control-label"><?php echo __('Web Server') ?></label>
    <div class="col-sm-10">
      <p class="form-control-static" id="webserver"><?php echo $_SERVER['SERVER_SOFTWARE'] ?></p>
    </div>
  </div>
  <div class="form-group">
    <label for="phpversion" class="col-sm-2 control-label"><?php echo __('PHP version') ?></label>
    <div class="col-sm-10">
      <p class="form-control-static" id="phpversion"><?php echo phpversion() ?></p>
    </div>
  </div>
  <div class="form-group">
    <label for="mysqlserver" class="col-sm-2 control-label"><?php echo __('MySQL Database version') ?></label>
    <div class="col-sm-10">
      <p class="form-control-static" id="mysqlserver"><?php echo $dbs->server_info ?></p>
    </div>
  </div>
  <div class="form-group">
    <label for="mysqlclient" class="col-sm-2 control-label"><?php echo __('MySQL Client version') ?></label>
    <div class="col-sm-10">
      <p class="form-control-static" id="mysqlclient"><?php echo $dbs->client_info ?></p>
    </div>
  </div>
  <div class="form-group">
    <label for="mysqlclient" class="col-sm-2 control-label"><?php echo __('Browser/User Agent') ?></label>
    <div class="col-sm-10">
      <p class="form-control-static" id="mysqlclient"><?php echo $_SERVER['HTTP_USER_AGENT'] ?></p>
    </div>
  </div>
  <div class="form-group">
    <label for="hostname" class="col-sm-2 control-label"><?php echo __('Hostname') ?></label>
    <div class="col-sm-10">
      <p class="form-control-static" id="hostname"><?php echo $_SERVER['SERVER_NAME'] ?></p>
    </div>
  </div>
  <div class="form-group">
    <label for="jqueryinfo" class="col-sm-2 control-label"><?php echo __('jQuery version') ?></label>
    <div class="col-sm-10">
      <p class="form-control-static" id="jqueryinfo"><span id="jqueryver"></span><script type="text/javascript">$('#jqueryver').html($.fn.jquery)</script></p>
    </div>
  </div>
  <div class="form-group">
    <label for="html5" class="col-sm-2 control-label"><?php echo __('HTML5 Support?') ?></label>
    <div class="col-sm-10">
      <p class="form-control-static" id="html5"><span id="isHTML5"></span><script type="text/javascript">
      var supportHTML5 = !!document.createElement('canvas').getContext;
      if (supportHTML5) {
        $('#isHTML5').html('Support');
      }
      </script></p>
    </div>
  </div>
  
</form>

