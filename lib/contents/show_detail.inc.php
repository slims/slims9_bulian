<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com), Hendro Wicaksono (hendrowicaksono@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

/* Showing record detail in HTML and XML */

if (isset($_GET['inXML']) AND !empty($_GET['inXML'])) {
    if (!$sysconf['enable_xml_detail']) {
        die('XML Detail is disabled');
    }
    // filter the ID
    $detail_id = intval($_GET['id']);
    // include detail library and template
    include LIB.'detail.inc.php';
    // create detail object
    $detail = new detail($dbs, $detail_id, 'mods');
    $output = $detail->showDetail();
    // send http header
    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
    echo $detail->getPrefix();
    echo $output;
    echo $detail->getSuffix();
    exit();
} else if (isset($_GET['JSONLD']) AND !empty($_GET['JSONLD'])) {
    if (!$sysconf['jsonld_detail']) {
      die('JSON-LD detail is disabled');
    }
    // filter the ID
    $detail_id = intval($_GET['id']);
    // include detail library and template
    include LIB.'detail.inc.php';
    // create detail object
    $detail = new detail($dbs, $detail_id, 'json-ld');
    $output = $detail->showDetail();
    // send http header
    header('Content-Type: application/ld+json');
    echo $output;
    exit();
} else {
    // filter the ID
    $detail_id = intval($_GET['id']);
    // include detail library and template
    include LIB.'detail.inc.php';
    if ($sysconf['comment']['enable']) {
		include LIB.'comment.inc.php';
    }
	if (isset($_POST['comment']) && $_POST['comment']<>"" && ISSET($_SESSION['mid'])) {
		require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
		$data['comment'] = trim(strip_tags($_POST['comment']));
		$data['biblio_id'] = $detail_id;
		$data['member_id'] = $_SESSION['mid'];

		$data['input_date'] = date('Y-m-d H:i:s');
    $data['last_update'] = date('Y-m-d H:i:s');

		/* INSERT RECORD MODE */
		// insert the data
		$sql_op = new simbio_dbop($dbs);
		$insert = $sql_op->insert('comment', $data);
		if ($insert) {
		  utility::jsAlert(__('Thank you for your comment.'));
		} else { utility::jsAlert(__('FAILED to strore you comment. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
	}

  if (isset($_GET['keywords'])) {
    $keywords = trim($_GET['keywords']);
    $keywords_array = explode(' ', $keywords);
    $searched_words_js_array = '[';
    foreach($keywords_array as $word) {
      $word = str_replace(array('"', ',', "'", '-'), '', $word);
      $searched_words_js_array .= "'$word',";
    }
    $searched_words_js_array = substr_replace($searched_words_js_array, '', -1);
    $searched_words_js_array .= ']';
  }

  // create detail object
  $detail = new detail($dbs, $detail_id);
  $detail->setTemplate($sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/detail_template.php');

  // set the content for info box
  $info = '<strong>'.strtoupper(__('Record Detail')).'</strong><hr />';
  if (!defined('LIGHTWEIGHT_MODE')) {
      $info .= '<a href="javascript: history.back();" class="btn btn-default" title="Back to previous page">'.__('Back To Previous').'</a>';
  }
  if (isset($sysconf['enable_xml_detail']) && $sysconf['enable_xml_detail'] && !defined('LIGHTWEIGHT_MODE')) {
    $info .= '<a href="index.php?p=show_detail&inXML=true&id='.$detail_id.'" class="xmlDetailLink s-xml-detail" title="'.__('Show detail in XML format').'" target="_blank">'.__('XML Detail').'</a>';
    $info .= '<a href="index.php?p=cite&id='.$detail_id.'" class="openPopUp citationLink" title="'.str_replace('{title}', substr($detail->record_title, 0, 50) , __('Citation for: {title}')).'" target="_blank">'.__('Cite this').'</a>';
  }

  // output the record detail
  echo $detail->showDetail();
  $page_title = $detail->record_title.' | '.$sysconf['library_name'];
  $metadata   = $detail->metadata;
  $image_src  = $detail->image_src;
  $notes      = $detail->notes;

  // get keywords
  $subject    = '';
  if(isset($detail->subjects[0]) && count($detail->subjects[0]) > 0) {
    foreach($detail->subjects as $_subject) {
      $subject .= strtolower($_subject['topic']).',';
    }
    $subject = substr($subject,0,-1);
  }
  echo '<br />'."\n";
}
