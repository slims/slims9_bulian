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

// privileges checking
$can_write = utility::havePrivilege('bibliography', 'w');
if (!$can_write) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// page title
$page_title = 'Authority List';
// check for biblioID in url
$biblioID = 0;
if (isset($_GET['biblioID']) AND $_GET['biblioID']) {
    $biblioID = (integer)$_GET['biblioID'];
}
// utility function to check author name
function checkAuthor($str_author_name, $str_author_type = 'p')
{
  global $dbs;
  $_q = $dbs->query('SELECT author_id FROM mst_author WHERE author_name=\''.$str_author_name.'\' AND authority_type=\''.$str_author_type.'\'');
  if ($_q->num_rows > 0) {
    $_d = $_q->fetch_row();
    // return the author ID
    return $_d[0];
  }
  return false;
}

// start the output buffer
ob_start();
/* main content */
// biblio author save proccess
if (isset($_POST['save']) AND (isset($_POST['authorID']) OR trim($_POST['search_str']))) {
  $author_name = trim($dbs->escape_string(strip_tags($_POST['search_str'])));
  // create new sql op object
  $sql_op = new simbio_dbop($dbs);
  // check if biblioID POST var exists
  if (isset($_POST['biblioID']) AND !empty($_POST['biblioID'])) {
      $data['biblio_id'] = intval($_POST['biblioID']);
      // check if the author select list is empty or not
      if (isset($_POST['authorID']) AND !empty($_POST['authorID'])) {
          $data['author_id'] = $_POST['authorID'];
      } else if ($author_name AND empty($_POST['authorID'])) {
          // check author
          $author_id = checkAuthor($author_name, $_POST['type']);
          if ($author_id !== false) {
              $data['author_id'] = $author_id;
          } else {
              // adding new author
              $author_data['author_name'] = $author_name;
              $author_data['authority_type'] = $_POST['type'];
              $author_data['input_date'] = date('Y-m-d');
              $author_data['last_update'] = date('Y-m-d');
              // insert new author to author master table
              @$sql_op->insert('mst_author', $author_data);
              $data['author_id'] = $sql_op->insert_id;
          }
      }
      $data['level'] = intval($_POST['level']);

      if ($sql_op->insert('biblio_author', $data)) {
          utility::jsToastr('Author', __('Author succesfully updated!'), 'success');
          echo '<script type="text/javascript">';
          echo 'parent.setIframeContent(\'authorIframe\', \''.MWB.'bibliography/iframe_author.php?biblioID='.$data['biblio_id'].'\');';
          echo '</script>';
      } else {
          utility::jsToastr('Author', __('Author FAILED to Add. Please Contact System Administrator')."\n".$sql_op->error, 'error');
      }
  } else {
      if (isset($_POST['authorID']) AND !empty($_POST['authorID'])) {
          // add to current session
          $_SESSION['biblioAuthor'][$_POST['authorID']] = array($_POST['authorID'], intval($_POST['level']));
      } else if ($author_name AND empty($_POST['authorID'])) {
          // check author
          $author_id = checkAuthor($author_name);
          if ($author_id !== false) {
              $last_id = $author_id;
          } else {
              // adding new author
              $data['author_name'] = $author_name;
              $data['authority_type'] = $_POST['type'];
              $data['input_date'] = date('Y-m-d');
              $data['last_update'] = date('Y-m-d');
              // insert new author to author master table
              $sql_op->insert('mst_author', $data);
              $last_id = $sql_op->insert_id;
          }
          $_SESSION['biblioAuthor'][$last_id] = array($last_id, intval($_POST['level']));
      }

      utility::jsToastr('Author', __('Author added!'), 'success');
      echo '<script type="text/javascript">';
      echo 'parent.setIframeContent(\'authorIframe\', \''.MWB.'bibliography/iframe_author.php\');';
      echo '</script>';
  }
}

?>

<div class="popUpForm">
    <strong><?php echo __('Add Author'); ?> </strong>
    <hr />
    <form name="mainForm" action="pop_author.php?biblioID=<?php echo $biblioID; ?>" method="post">
        <div class="s-margin__bottom-1 form-inline">
            <?php
            $ajax_exp = "ajaxFillSelect('../../AJAX_lookup_handler.php', 'mst_author', 'author_id:author_name:author_year:authority_type', 'authorID', $('#search_str').val())";?>
            <input type="text" name="search_str" placeholder="<?php echo __('Author Name') ?>" class="form-control" id="search_str" style="width: 30%;" oninput="<?php echo $ajax_exp; ?>" />
            <select name="type" class="col form-control"><?php
            foreach ($sysconf['authority_type'] as $type_id => $type) {
                echo '<option value="'.$type_id.'">'.$type.'</option>';
            }
            ?></select>
            <select name="level" class="col form-control"><?php
            foreach ($sysconf['authority_level'] as $level_id => $level) {
                echo '<option value="'.$level_id.'">'.$level.'</option>';
            }
            ?></select>
        </div>

        <div class="popUpSubForm">
            <select name="authorID" id="authorID" size="5" class="s-margin__bottom-1 form-control"><option value="0"><?php echo __('Type to search for existing authors or to add a new one'); ?></option></select>
            <?php if ($biblioID) { echo '<input type="hidden" name="biblioID" value="'.$biblioID.'" />'; } ?>
            <input type="submit" name="save" value="<?php echo __('Insert To Bibliography'); ?>" class="popUpSubmit btn btn-primary" />
        </div>
    </form>
</div>

<?php
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
