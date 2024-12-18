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


/* Biblio Author Adding Pop Windows */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// page title
$page_title = 'Biblio Relation';
// check for biblioID in url
$biblioID = 0;
if (isset($_GET['biblioID']) AND $_GET['biblioID']) {
    $biblioID = (integer)$_GET['biblioID'];
}

// start the output buffer
ob_start();
/* main content */
// biblio author save proccess
if (isset($_POST['save']) AND (isset($_POST['biblioID']) OR trim($_POST['search_str']))) {
  $author_name = trim($dbs->escape_string(strip_tags($_POST['search_str'])));
  // create new sql op object
  $sql_op = new simbio_dbop($dbs);
  // check if biblioID POST var exists
  if (isset($_POST['biblioID']) AND !empty($_POST['biblioID'])) {
      $data['rel_biblio_id'] = intval($_POST['relBiblioID']);
      $data['biblio_id'] = intval($_POST['biblioID']);

      if ($sql_op->insert('biblio_relation', $data)) {
          utility::jsToastr('Biblio Relation', __('Biblio relation succesfully updated!'), 'success');
          echo '<script type="text/javascript">';
          echo 'parent.setIframeContent(\'biblioIframe\', \''.MWB.'bibliography/iframe_biblio_rel.php?biblioID='.$data['biblio_id'].'\');';
          echo '</script>';
      } else {
          utility::jsToastr('Biblio Relation',__('Biblio relation FAILED to Add. Please Contact System Administrator')."\n".$sql_op->error, 'error');
      }
  } else {
      if (isset($_POST['relBiblioID']) AND !empty($_POST['relBiblioID'])) {
          // add to current session
          $_SESSION['biblioToBiblio'][$_POST['relBiblioID']] = array($_POST['relBiblioID']);
          utility::jsToastr('Biblio Relation', __('Biblio relation added!'), 'success');
          echo '<script type="text/javascript">';
          echo 'parent.setIframeContent(\'biblioIframe\', \''.MWB.'bibliography/iframe_biblio_rel.php\');';
          echo '</script>';
      }
  }
}

?>

<div class="popUpForm">
<form name="mainForm" action="pop_biblio_rel.php?biblioID=<?php echo $biblioID; ?>" method="post">
<strong><?php echo __('Add Biblio Relation'); ?> </strong>
<hr />
<div class="form-inline s-margin__bottom-1">
    <?php
    $ajax_exp = "ajaxFillSelect('../../AJAX_lookup_handler.php', 'biblio', 'biblio_id:title:edition:publish_year', 'relBiblioID', $('#search_str').val())";
    echo '<div class="col-1 p-0">'.__('Title').'</div>';
    ?>
    <input type="text" name="search_str" id="search_str" class="form-control col" placeholder="<?php echo __('Title'); ?>" oninput="<?php echo $ajax_exp; ?>" />
</div>
<div class="popUpSubForm">
    <select name="relBiblioID" id="relBiblioID" size="5" class="form-control s-margin__bottom-1">
        <option value="0"><?php echo __('Type to search for existing biblio data'); ?></option>
    </select>
    <?php if ($biblioID) { echo '<input type="hidden" name="biblioID" value="'.$biblioID.'" />'; } ?>
    <input type="submit" name="save" value="<?php echo __('Insert To Bibliography'); ?>" class="s-btn btn btn-primary popUpSubmit" />
</div>
</form>
</div>

<?php
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
