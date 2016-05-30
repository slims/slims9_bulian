<?php

/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Create by Waris Agung Widodo (ido.alit@gmail.com)
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

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-masterfile');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('master_file', 'r');
$can_write = utility::havePrivilege('master_file', 'w');

// GET ID FROM URL
$itemID = (integer)isset($_GET['itemID'])?$_GET['itemID']:0;
$vocID = (integer)isset($_GET['vocID'])?$_GET['vocID']:0;

// start buffer
ob_start();
// save proses

$page_title = 'Vocabulary Control';

// utility function to check subject/topic
function checkSubject($str_subject, $str_subject_type = 't')
{
    global $dbs;
    $_q = $dbs->query('SELECT topic_id FROM mst_topic WHERE topic=\''.$str_subject.'\' AND topic_type=\''.$str_subject_type.'\'');
    if ($_q->num_rows > 0) {
        $_d = $_q->fetch_row();
        // return the subject/topic ID
        return $_d[0];
    }
    return false;
}

if (isset($_POST['relatedterm']) AND (isset($_POST['topicID']) OR isset($_POST['search_str']))) {
  
  $relatedterm = trim($dbs->escape_string(strip_tags($_POST['relatedterm'])));
  $search_str = trim($dbs->escape_string(strip_tags($_POST['search_str'])));

  # create new sql op object
  $sql_op = new simbio_dbop($dbs);

  # curent item_id/topicID
  $itemID = (integer)isset($_GET['itemID'])?$_GET['itemID']:0;
  $vocID = (integer)isset($_GET['vocID'])?$_GET['vocID']:0;

  # alert sucsess add
  $alert_add  = '<script type="text/javascript">';
  $alert_add .= 'alert(\''.__('Vocabulary added!').'\');';
  $alert_add .= 'parent.setIframeContent(\'itemIframe\', \''.MWB.'master_file/iframe_vocabolary_control.php?itemID='.$itemID.'\');';
  $alert_add .= '</script>';

  $data['topic_id'] = $itemID;
  $data['vocabolary_id'] = '';
  $data['rt_id'] = $relatedterm;

  if (!empty($_POST['topicID'])) { # a.
    $data['related_topic_id'] = $_POST['topicID'];
  } else if ($search_str AND empty($_POST['topicID'])) {
    // check subject
    $subject_id = checkSubject($search_str);
    if ($subject_id !== false) {
        $data['related_topic_id'] = $subject_id;
    } else {
        // adding new topic
        $topic_data['topic'] = $search_str;
        $topic_data['classification'] = $_POST['topicClass'];
        $topic_data['topic_type'] = 't';
        $topic_data['input_date'] = date('Y-m-d');
        $topic_data['last_update'] = date('Y-m-d');
        // insert new topic to topic master table
        $sql_op->insert('mst_topic', $topic_data);
        // put last inserted ID
        $data['related_topic_id'] = $sql_op->insert_id;
    }
  }

  // data secondary vocabulary
  $_data['topic_id'] = $data['related_topic_id'];
  $_data['vocabolary_id'] = '';
  $_data['related_topic_id'] = $itemID;

  $_data['rt_id'] = false;

  if ($relatedterm === 'U') {
    $_data['rt_id'] = 'UF';
  }
  if($relatedterm === 'UF'){
    $_data['rt_id'] = 'U';
  }
  if ($relatedterm === 'RT') {
    $_data['rt_id'] = 'RT';
  }
  if($relatedterm === 'BT'){
    $_data['rt_id'] = 'NT';
  }
  if($relatedterm === 'NT'){
    $_data['rt_id'] = 'BT';
  }

  // update mode
  if (isset($_POST['saveData'])) {
    $update = $sql_op->update('mst_voc_ctrl', $data, 'vocabolary_id='.$vocID);
    if ($update) {
      $alert_update  = '<script type="text/javascript">';
      $alert_update .= 'alert(\''.__('Vocabulary update!').'\');';
      $alert_update .= 'parent.setIframeContent(\'itemIframe\', \''.MWB.'master_file/iframe_vocabolary_control.php?itemID='.$itemID.'\');';
      $alert_update .= 'top.jQuery.colorbox.close();';
      $alert_update .= '</script>';

      echo $alert_update;
    } else {
      utility::jsAlert(__('Subject FAILED to Add. Please Contact System Administrator')."\n".$sql_op->error);
    }
    
  } else {

    // checking if already added
    $check_vc = $dbs->query('SELECT count(topic_id) FROM mst_voc_ctrl WHERE topic_id='.$data['topic_id'].' AND related_topic_id='.$data['related_topic_id']);
    $check_dc = $check_vc->fetch_row();
    if ($check_dc[0] > 0) {
      // already add
      utility::jsAlert(__('Subject ALREADY Added in Relation!'));
    } else {
      // insert primary vocabolary
      if ($sql_op->insert('mst_voc_ctrl', $data)) {

        // insert secondary vocabolary
        if ($_data['rt_id']) {

          // insert related topic into vocabolary control
          $insert = $sql_op->insert('mst_voc_ctrl', $_data);
          if ($insert) {
            echo $alert_add;
          }else{
            utility::jsAlert(__('Subject FAILED to Add. Please Contact System Administrator')."\n".$sql_op->error);
          }

        }else{
          echo $alert_add;
        }

      } else {
        utility::jsAlert(__('Subject FAILED to Add. Please Contact System Administrator')."\n".$sql_op->error);
      }
    }

  }
}
if (isset($_GET['editTopic'])) {
  if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
    }
  // record form
  $itemID = (integer)isset($_GET['itemID'])?$_GET['itemID']:0;
  $vocID = (integer)isset($_GET['vocID'])?$_GET['vocID']:0;
  $rec_q = $dbs->query('SELECT * FROM mst_voc_ctrl WHERE vocabolary_id='.$vocID.' AND topic_id='.$itemID);
  $rec_d = $rec_q->fetch_assoc();

  $topic_q = $dbs->query('SELECT topic, classification FROM mst_topic WHERE topic_id='.$rec_d['related_topic_id']);
  $topic_d = $topic_q->fetch_row();


// edit mode
  ?>
<div class="popUpForm container">
  <div class="page-header"><h2><?php echo __('Edit Mode'); ?></h2></div>
  <form name="mainForm" class="form-horizontal" role="form" action="pop_vocabolary_control.php?itemID=<?php echo $itemID; ?>&vocID=<?php echo $vocID; ?>" method="post">
    <div class="form-group">
      <label for="ref" class="col-xs-2 control-label"><?php echo __('Related Term');?></label>
      <div class="col-xs-10">
        <select id="relatedterm" name="relatedterm">
        <?php 
        $ref_q = $dbs->query('SELECT * FROM mst_relation_term');
        while ($ref_d = $ref_q->fetch_row()) {
            $selected = ($ref_d[1] === $rec_d['rt_id'])?'selected':'';
            echo '<option '.$selected.' value="'.$ref_d[1].'">'.__($ref_d[2]).'</option>';
        }
        ?>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label for="subname" class="col-xs-2 control-label"><?php echo __('Vocabulary');?></label>
      <div class="col-xs-10">
      <?php
      $ajax_exp = "ajaxFillSelect('../../AJAX_lookup_handler.php', 'mst_topic', 'topic_id:topic:topic_type', 'topicID', $('#search_str').val())";
      ?>
      <input type="text" value="<?php echo $topic_d[0];?>" name="search_str" id="search_str" class="form-control" placeholder="Vocabulary" onkeyup="<?php echo $ajax_exp; ?>" />
      <select name="topicID" id="topicID" size="5" class="form-control"><option value="0"><?php echo __('Type to search for existing topics or to add a new one'); ?></option></select>
      </div>
    </div>
    <div class="form-group">
    <label for="subname" class="col-xs-2 control-label"><?php echo __('Classification'); ?></label>
    <div class="col-xs-10">
      <input type="text" name="topicClass" class="form-control" value="<?php echo $topic_d[1]; ?>">
    </div>
  </div>
    <div class="form-group">
      <div class="col-xs-offset-2 col-xs-10">
        <button type="submit" name="saveData" class="btn btn-success"><?php echo __('Update');?></button>
        <button type="button" onclick="top.jQuery.colorbox.close()" class="btn btn-warning"><?php echo __('Cancel');?></button>
      </div>
    </div>
  </form>
</div>

  <?php 

  }else{ 

  // new related topic
  ?>

<div class="popUpForm container">
<div class="page-header"><h2><?php echo __('Add Vocabulary Control'); ?></h2></div>
<form name="mainForm" class="form-horizontal" role="form" action="pop_vocabolary_control.php?itemID=<?php echo $itemID; ?>" method="post">
  <div class="form-group">
    <label for="ref" class="col-xs-2 control-label"><?php echo __('Related Term');?></label>
    <div class="col-xs-10">
      <select id="relatedterm" name="relatedterm">
      <?php 
      $ref_q = $dbs->query('SELECT * FROM mst_relation_term');
      while ($ref_d = $ref_q->fetch_row()) {
          echo '<option value="'.$ref_d[1].'">'.__($ref_d[2]).'</option>';
      }
      ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label for="subname" class="col-xs-2 control-label"><?php echo __('Vocabulary');?></label>
    <div class="col-xs-10">
    <?php
    $ajax_exp = "ajaxFillSelect('../../AJAX_lookup_handler.php', 'mst_topic', 'topic_id:topic:topic_type', 'topicID', $('#search_str').val())";
    ?>
    <input type="text" name="search_str" id="search_str" class="form-control" placeholder="<?php echo __('Enter Vocabulary');?>" onkeyup="<?php echo $ajax_exp; ?>" />
    <select name="topicID" id="topicID" size="5" class="form-control"><option value="0"><?php echo __('Type to search for existing topics or to add a new one'); ?></option></select>
    </div>
  </div>
  <div class="form-group">
    <label for="subname" class="col-xs-2 control-label"><?php echo __('Classification'); ?></label>
    <div class="col-xs-10">
      <input type="text" name="topicClass" class="form-control">
    </div>
  </div>
  <div class="form-group">
    <div class="col-xs-offset-2 col-xs-10">
      <button type="submit" class="btn btn-primary"><?php echo __('Add Vocabulary');?></button>
    </div>
  </div>
</form>
</div>

<?php
}
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';