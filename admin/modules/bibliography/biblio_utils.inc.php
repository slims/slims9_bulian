<?php
/**
 * Copyright (C) 2015  Arie Nugraha (dicarve@yahoo.com)
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

/**
 * Utility function to get author ID
 **/
function getAuthorID($str_author_name, $str_author_type, &$arr_cache = false)
{
  global $dbs;
  $str_value = trim($str_author_name);
  $str_author_type = $dbs->escape_string($str_author_type);
  if ($arr_cache) {
      if (isset($arr_cache[$str_value])) {
          return $arr_cache[$str_value];
      }
  }

  $str_value = $dbs->escape_string($str_value);
  $_sql_id_q = sprintf('SELECT author_id FROM mst_author WHERE author_name=\'%s\'', $str_value);
  $id_q = $dbs->query($_sql_id_q);
  if ($id_q->num_rows > 0) {
      $id_d = $id_q->fetch_row();
      unset($id_q);
      // cache
      if ($arr_cache) { $arr_cache[$str_value] = $id_d[0]; }
      return $id_d[0];
  } else {
      $_curr_date = date('Y-m-d');
      // if not found then we insert it as new value
      $_sql_insert_author = sprintf('INSERT IGNORE INTO mst_author (author_name, authority_type, input_date, last_update)'
          .' VALUES (\'%s\', \'%s\', \'%s\', \'%s\')', $str_value, $str_author_type, $_curr_date, $_curr_date);
      $dbs->query($_sql_insert_author);
      if (!$dbs->error) {
          // cache
          if ($arr_cache) { $arr_cache[$str_value] = $dbs->insert_id; }
          return $dbs->insert_id;
      }
  }
}


/**
 * Utility function to get subject ID
 **/
function getSubjectID($str_subject, $str_subject_type, &$arr_cache = false, $str_class_number = '')
{
  global $dbs;
  $str_value = trim($str_subject);
  if ($arr_cache) {
    if (isset($arr_cache[$str_value])) {
      return $arr_cache[$str_value];
    }
  }

  $str_value = $dbs->escape_string($str_value);
  $_sql_id_q = sprintf('SELECT topic_id FROM mst_topic WHERE topic=\'%s\'', $str_value);
  $id_q = $dbs->query($_sql_id_q);
  if ($id_q->num_rows > 0) {
      $id_d = $id_q->fetch_row();
      unset($id_q);
      // cache
      if ($arr_cache) { $arr_cache[$str_value] = $id_d[0]; }
      return $id_d[0];
  } else {
      $_curr_date = date('Y-m-d');
      // if not found then we insert it as new value
      $_sql_insert_topic = sprintf('INSERT IGNORE INTO mst_topic (topic, topic_type, classification, input_date, last_update)'
          .' VALUES (\'%s\', \'%s\', \'%s\', \'%s\', \'%s\')', $str_value, $str_subject_type, $str_class_number, $_curr_date, $_curr_date);
      $dbs->query($_sql_insert_topic);
      if (!$dbs->error) {
          // cache
          if ($arr_cache) { $arr_cache[$str_value] = $dbs->insert_id; }
          return $dbs->insert_id;
      } else {
          echo $dbs->error;
      }
  }
}

/**
 * callback function to show title and authors in datagrid
 **/
function showTitleAuthors($obj_db, $array_data)
{
  global $sysconf;
  global $label_cache;
  $_opac_hide = false;
  $_promoted = false;
  $_labels = '';
  // biblio author detail
  if ($sysconf['index']['type'] == 'default') {
      $_sql_biblio_q = sprintf('SELECT b.title, a.author_name, opac_hide, promoted, b.labels FROM biblio AS b
          LEFT JOIN biblio_author AS ba ON b.biblio_id=ba.biblio_id
          LEFT JOIN mst_author AS a ON ba.author_id=a.author_id
          WHERE b.biblio_id=%d', $array_data[0]);
      $_biblio_q = $obj_db->query($_sql_biblio_q);
      $_authors = '';
      while ($_biblio_d = $_biblio_q->fetch_row()) {
          $_title = $_biblio_d[0];
          $_authors .= $_biblio_d[1].' - ';
          $_opac_hide = (integer)$_biblio_d[2];
          $_promoted = (integer)$_biblio_d[3];
          $_labels = $_biblio_d[4];
      }
      $_authors = substr_replace($_authors, '', -3);
      $_output = '<div style="float: left;"><span class="title">'.$_title.'</span><div class="authors">'.$_authors.'</div></div>';
  } else {
      $_output = '<div style="float: left;"><span class="title">'.$array_data[1].'</span><div class="authors">'.$array_data[3].'</div></div>';
      $_labels = $array_data[2];
  }
  // check for opac hide flag
  if ($_opac_hide) {
      $_output .= '<div style="float: right; width: 20px; height: 20px;" class="lockFlagIcon" title="' . __('Hidden in OPAC') . '">&nbsp;</div>';
  }
  // check for promoted flag
  if ($_promoted) {
      $_output .= '<div style="float: right; width: 20px; height: 20px;" class="homeFlagIcon" title="' . __('Promoted To Homepage') . '">&nbsp;</div>';
  }
  // labels
  if ($_labels) {
      $_output .= '<div class="labels">';
      $arr_labels = @unserialize($_labels);
      if ($arr_labels !== false) {
	  foreach ($arr_labels as $label) {
	      if (!isset($label_cache[$label[0]]['name'])) {
	          $_label_q = $obj_db->query('SELECT label_name, label_desc, label_image FROM mst_label AS lb
	              WHERE lb.label_name=\''.$label[0].'\'');
	          $_label_d = $_label_q->fetch_row();
	          $label_cache[$_label_d[0]] = array('name' => $_label_d[0], 'desc' => $_label_d[1], 'image' => $_label_d[2]);
	      }
	      $_output .= ' <img src="'.SWB.'lib/minigalnano/createthumb.php?filename=../../'.IMG.'/labels/'.urlencode($label_cache[$label[0]]['image']).'&amp;width=24&amp;height=24" title="'.$label_cache[$label[0]]['desc'].'" align="middle" class="labels" />';
	  }
	}
  $_output .= '</div>';
  }
  return $_output;
}
