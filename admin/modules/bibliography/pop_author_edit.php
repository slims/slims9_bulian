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

/* Biblio Author */

// key to authenticate
if (!defined('INDEX_AUTH')) {
  define('INDEX_AUTH', '1');
}

// main system configuration
if (!defined('SB')) {
  require '../../../sysconfig.inc.php';
}

if (isset($_GET['authorID'])) {
  $_POST['itemID'] = $_GET['authorID'];
  $_POST['detail'] = true;
}

$_GET['inPopUp'] = true;

if(isset($_POST['updateData'])){
	$biblio_id = (integer)$_POST['biblio_id'];
	$author_id = (integer)$_POST['author_id'];
	$level = (integer)$_POST['level'];
	$query = sprintf("UPDATE biblio_author SET level=%d WHERE biblio_id=%d AND author_id=%d",$level,$biblio_id,$author_id);
	$update = $dbs->query($query);
    if($update){
		utility::jsAlert('Authority Level Data Successfully Updated');
		echo '<script type="text/javascript">top.setIframeContent(\'authorIframe\', \''.MWB.'bibliography/iframe_author.php?biblioID='.$biblio_id.'\');</script>';
    	echo '<script type="text/javascript">top.jQuery.colorbox.close();</script>';
    }else { 
    	utility::jsAlert( __('Authority Level Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$update->error.$query); 
		echo '<script type="text/javascript">top.setIframeContent(\'authorIframe\', \''.MWB.'bibliography/iframe_author.php?biblioID='.$_POST['biblioID'].'\');</script>';
    	echo '<script type="text/javascript">top.jQuery.colorbox.close();</script>';
    }
}

ob_start();

if(!isset($_GET['authority_level'])){
	require MDLBS.'master_file/author.php';
}
else{
	require SB.'admin/default/session.inc.php';
	require SB.'admin/default/session_check.inc.php';
	require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
	require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
	require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
	require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';

    /* RECORD FORM */
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    $query = 'SELECT b.biblio_id,ba.author_id,b.title,ma.author_name,ma.authority_type,ba.* FROM biblio b LEFT JOIN biblio_author ba ON ba.biblio_id=ba.biblio_id
    LEFT JOIN mst_author ma ON ma.author_id=ba.author_id WHERE b.biblio_id='.$_GET['biblio_id'].' AND ma.author_id='.$_GET['authorID'];
    $rec_q = $dbs->query($query);
    $rec_d = $rec_q->fetch_assoc();

	$form = new simbio_form_table_AJAX('authorLevelForm', $_SERVER['PHP_SELF'], 'post');
	$form->submit_button_attr = 'name="updateData" value="'.__('Update').'" class="btn btn-default"';

	// form table attributes
	$form->table_attr = 'id="dataList" class="s-table table"';
	$form->table_header_attr = 'class="alterCell font-weight-bold"';
	$form->table_content_attr = 'class="alterCell2"';
    
    /* Form Element(s) */
    $form->addHidden('biblio_id',$rec_d['biblio_id']);
    $form->addHidden('author_id',$rec_d['author_id']);
    // biblio title
    $form->addAnything(__('Title'),  $rec_d['title']);
    // author name
    $form->addAnything(__('Author Name'),  $rec_d['author_name']);
    // authority type
    $form->addAnything(__('Authority Type'),  $sysconf['authority_type'][$rec_d['authority_type']]);
    // authority_level
    foreach ($sysconf['authority_level'] as $authority_level_id => $authority_level) {
        $authority_level_options[] = array($authority_level_id, $authority_level);
    }
    $form->addSelectList('level', __('Authority Level'), $authority_level_options, (integer)$rec_d['level'],'class="form-control col-8"');
    // print out the form object
    echo $form->printOut();
}

$content = ob_get_clean();
// page title
$page_title = 'Biblio Author';

// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
