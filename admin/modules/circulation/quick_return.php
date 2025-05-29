<?php
/**
 * Displaying quick return page.
 * 
 * @author Original code by Ari Nugraha (dicarve@gmail.com).
 * @package SLiMS
 * @subpackage Circulation
 * @since 2007
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
 *
 */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}
// key to get full database access
define('DB_ACCESS', 'fa');

require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');

// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

// load settings from database
utility::loadSettings($dbs);

// privileges checking
$can_read = utility::havePrivilege('circulation', 'r');
$can_write = utility::havePrivilege('circulation', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

// check if quick return is enabled
if (!$sysconf['quick_return']) {
    die('<div class="errorBox">'.__('Quick Return is disabled').'</div');
}
if($sysconf['barcode_reader']) {
ob_start();
require SB.'admin/'.$sysconf['admin_template']['dir'].'/barcodescannermodal.tpl.php';
$barcode = ob_get_clean();
echo $barcode;
?>
  <script type="text/javascript">
//   eddy xxx
    $('#barcodeReader').click(function(e){
      e.preventDefault();
      var url = $(this).attr('href');
      $('#iframeBarcodeReader').attr('src', url);
      $('#barcodeModal').modal('show');
    });

    $(document.body).bind('keyup', this, function(e){
      // F8
      if(e.keyCode == 119) {
        $('#barcodeReader').click();
      }
    });
    parent.$(".modal-backdrop").remove();
  </script>
  <?php } ?>
<div class="menuBox">
<div class="menuBoxInner quickReturnIcon">
    <div class="per_title">
	    <h2><?php echo __('Quick Return'); ?></h2>
    </div>
    <div class="infoBox">
        <?php echo __('Insert an item ID to return collection with keyboard or barcode reader'); ?>
    </div>
    <div class="sub_section">
      <form action="<?php echo MWB; ?>circulation/ajax_action.php" target="circAction" method="post" class="form-inline notAJAX">
        <?php echo __('Item ID'); ?>
        <input type="text" name="quickReturnID" id="quickReturnID" size="30" class="form-control col-3" />
        <input type="submit" value="<?php echo __('Return'); ?>" id="quickReturnProcess" class="s-btn btn btn-default" />
        <?php if($sysconf['barcode_reader']) : ?>
        <a class="s-btn btn btn-default notAJAX" id="barcodeReader" href="<?php echo MWB.'circulation/barcode_reader.php?mode=quickreturn' ?>">Open Barcode Reader - Experimental (F8)</a>
        <?php endif ?>
    </form>
      <iframe name="circAction" id="circAction" style="display: inline; width: 5px; height: 5px; visibility: hidden;"></iframe>
    </div>
</div>
</div>
<div id="circulationLayer">&nbsp;</div>
