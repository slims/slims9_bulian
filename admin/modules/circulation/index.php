<?php
/**
 * Circulation module default view.
 * 
 * @author Original code by Ari Nugraha (dicarve@gmail.com).
 * @package SLiMS
 * @subpackage Circulation
 * @since 2007
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
 *
 */

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
<div class="menuBox">
  <div class="menuBoxInner circulationIcon">
    <div class="per_title">
	    <h2><?php echo __('Circulation'); ?></h2>
    </div>
    <div class="infoBox">
        <?php echo __('CIRCULATION - Insert a member ID to start transaction with keyboard or barcode reader'); ?>
    </div>
    <div class="sub_section">
      <form id="startCirc" action="<?php echo MWB; ?>circulation/circulation_action.php" method="post" class="form-inline">
      <span class="mr-2"><?php echo __('Member ID'); ?></span>
      <?php
      // create AJAX drop down
      $ajaxDD = new simbio_fe_AJAX_select();
      $ajaxDD->element_name = 'memberID';
      $ajaxDD->element_css_class = 'form-control col-3 ajaxInputField';
      $ajaxDD->handler_URL = MWB.'membership/member_AJAX_response.php';
      echo $ajaxDD->out();
      ?>
      <input type="submit" value="<?php echo __('Start Transaction'); ?>" name="start" id="start" class="s-btn btn btn-default" />
      <?php if($sysconf['barcode_reader']) : ?>
      <a class="s-btn btn btn-default notAJAX" id="barcodeReader" href="<?php echo MWB.'circulation/barcode_reader.php?mode=membership' ?>"><?= __('Open Barcode Reader - Experimental (F8)')?></a>
      <?php endif ?>
      </form>
    </div>
  </div>
</div>
<?php 
  if($sysconf['barcode_reader']) {
    ob_start();
    require SB.'admin/'.$sysconf['admin_template']['dir'].'/barcodescannermodal.tpl.php';
    $barcode = ob_get_clean();
    echo $barcode;
  ?>
  <script type="text/javascript">
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
  <?php }
    if (isset($_POST['finishID'])) {
      $msg = str_ireplace('{member_id}', $_POST['finishID'], __('Transaction with member {member_id} is completed'));
      echo '<div class="infoBox">'.$msg.'</div>';
    }
}
