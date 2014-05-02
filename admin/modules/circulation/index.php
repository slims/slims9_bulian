<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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

/* Circulation section */

// key to authenticate
define('INDEX_AUTH', '1');
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
do_checkIP('smc-circulation');

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';

// privileges checking
$can_read = utility::havePrivilege('circulation', 'r');
$can_write = utility::havePrivilege('circulation', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}
// check if there is transaction running
if (isset($_SESSION['memberID']) AND !empty($_SESSION['memberID'])) {
    define('DIRECT_INCLUDE', true);
    include MDLBS.'circulation/circulation_action.php';
} else {
?>
<fieldset class="menuBox">
  <div class="menuBoxInner circulationIcon">
    <div class="per_title">
	    <h2><?php echo __('Circulation'); ?></h2>
    </div>
    <div class="sub_section">
	    <div class="action_button">
		    <?php echo __('CIRCULATION - Insert a member ID to start transaction with keyboard or barcode reader'); ?>
	    </div>
      <form id="startCirc" action="<?php echo MWB; ?>circulation/circulation_action.php" method="post" style="display: inline;">
      <?php echo __('Member ID'); ?> :
      <?php
      // create AJAX drop down
      $ajaxDD = new simbio_fe_AJAX_select();
      $ajaxDD->element_name = 'memberID';
      $ajaxDD->element_css_class = 'ajaxInputField';
      $ajaxDD->handler_URL = MWB.'membership/member_AJAX_response.php';
      echo $ajaxDD->out();
      ?>
      <input type="submit" value="<?php echo __('Start Transaction'); ?>" name="start" id="start" class="button" />
      </form>
    </div>
  </div>
</fieldset>
<?php
    if (isset($_POST['finishID'])) {
      $msg = str_ireplace('{member_id}', $_POST['finishID'], __('Transaction with member {member_id} is completed'));
      echo '<div class="infoBox">'.$msg.'</div>';
    }
}
