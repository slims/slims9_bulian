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

use \SLiMS\Filesystems\Storage;

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
  $_image = '';

  $img = 'images/default/image.png';
  $imageDisk = Storage::images();
  // biblio author detail
  if ($sysconf['index']['type'] == 'default') {
      $_sql_biblio_q = sprintf('SELECT b.title, a.author_name, opac_hide, promoted, b.labels,b.image, b.source FROM biblio AS b
          LEFT JOIN biblio_author AS ba ON b.biblio_id=ba.biblio_id
          LEFT JOIN mst_author AS a ON ba.author_id=a.author_id
          WHERE b.biblio_id=%d', $array_data[0]);
      $_biblio_q = $obj_db->query($_sql_biblio_q);
      $_authors = '';
      $_p2p_server_type = '';
      $type_id = 0;
      $server_source = '';

      while ($_biblio_d = $_biblio_q->fetch_row()) {
          $_title = $_biblio_d[0];
          $_image = $_biblio_d[5];
          $_authors .= $_biblio_d[1].' - ';
          $_opac_hide = (integer)$_biblio_d[2];
          $_promoted = (integer)$_biblio_d[3];
          $_labels = $_biblio_d[4];
          if ($_biblio_d[6] !== null && strpos($_biblio_d[6], '.') !== false) {
            list($type_id, $server_source) = explode('.', trim($_biblio_d[6]));
            $_p2p_server_type = $sysconf['p2pserver_type'][$type_id]??'';
          }
      }

      if ($server_source) {
        $server_source = (int)$server_source;
        $server = $obj_db->query("SELECT `name` FROM `mst_servers` WHERE `server_id` = $server_source");
        $server_data = $server->fetch_object();
        if ($server_data) $server_source = $server_data->name;
        else $server_source = 'Uknown data ' . $server_source;
      }

      if ($_p2p_server_type === 'z3950 SRU server' && $server_source == 0) {
        $server_source = 'Library of Congress SRU Voyager';
      }

      $_authors = substr_replace($_authors, '', -3);
      if($_image!='' AND $imageDisk->isExists('docs/'.$_image)){
        $img = 'images/docs/'.urlencode($_image);  
      }
      $_output = '<div class="media">
                    <img class="mr-3 rounded" loading="lazy" src="../lib/minigalnano/createthumb.php?filename='.$img.'&width=50&height=65" alt="cover image">
                    <div class="media-body">
                      <div class="title">'.stripslashes($_title).'</div>
                      <div class="authors">'.$_authors.'</div>
                      <div title="' . ($_p2p_server_type ? str_replace('{type}', $_p2p_server_type . ' ' . $server_source, __('Copy from {type}')) : 'Manual') . '" class="' . ($_p2p_server_type ? 'd-block' : 'd-none') . '">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-download" viewBox="0 0 16 16">
                          <path d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383"/>
                          <path d="M7.646 15.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 14.293V5.5a.5.5 0 0 0-1 0v8.793l-2.146-2.147a.5.5 0 0 0-.708.708z"/>
                        </svg>
                        <span>' . trim(str_replace(['Server','server'], '', $_p2p_server_type)) . ' - ' . ($server_source??'') . '</span>
                      </div>
                    </div>
                  </div>';
  } else {
  	  $_q = $obj_db->query("SELECT opac_hide,promoted,source FROM biblio WHERE biblio_id=".$array_data[0]);
	    $_p2p_server_type = '';
      $type_id = 0;
      $server_source = '';

      while ($_biblio_d = $_q->fetch_row()) {
	      $_opac_hide = (integer)$_biblio_d[0];
	      $_promoted  = (integer)$_biblio_d[1];
        if ($_biblio_d[2] !== null && strpos($_biblio_d[2], '.') !== false) {
          list($type_id, $server_source) = explode('.', trim($_biblio_d[2]));
          $_p2p_server_type = $sysconf['p2pserver_type'][$type_id]??'';
        }
	    }

      if ($server_source) {
        $server_source = (int)$server_source;
        $server = $obj_db->query("SELECT `name` FROM `mst_servers` WHERE `server_id` = $server_source");
        $server_data = $server->fetch_object();
        if ($server_data) $server_source = $server_data->name;
        else $server_source = 'Uknown data ' . $server_source;
      }

      if ($_p2p_server_type === 'z3950 SRU server' && $server_source == 0) {
        $server_source = 'Library of Congress SRU Voyager';
      }

      if($array_data[3]!='' AND $imageDisk->isExists('docs/'.$array_data[3])){
        $img = 'images/docs/'.urlencode($array_data[3]);  
      }
      $_output = '<div class="media">
                    <img class="mr-3 rounded" loading="lazy" src="../lib/minigalnano/createthumb.php?filename='.$img.'&width=50&height=65" alt="cover image">
                    <div class="media-body">
                      <div class="title">'.stripslashes($array_data[1]).'</div>
                      <div class="authors">'.$array_data[4].'</div>
                      <div title="' . ($_p2p_server_type ? str_replace('{type}', $_p2p_server_type . ' ' . $server_source, __('Copy from {type}')) : 'Manual') . '" class="' . ($_p2p_server_type ? 'd-block' : 'd-none') . '">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-download" viewBox="0 0 16 16">
                          <path d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383"/>
                          <path d="M7.646 15.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 14.293V5.5a.5.5 0 0 0-1 0v8.793l-2.146-2.147a.5.5 0 0 0-.708.708z"/>
                        </svg>
                        <span>' . trim(str_replace(['Server','server'], '', $_p2p_server_type)) . ' - ' . ($server_source??'') . '</span>
                      </div>
                    </div>
                  </div>';
      $_labels = $array_data[2];
  }
  // check for opac hide flag
  if ($_opac_hide) {
      $_output .= '<div class="badge badge-dark" title="' . __('Hidden in OPAC') . '">'.__('Hidden in OPAC').'</div>&nbsp;';
  }
  // check for promoted flag
  if ($_promoted) {
      $_output .= '<div class="badge badge-info" title="' . __('Promoted To Homepage') . '">'.__('Promoted To Homepage').'</div>&nbsp;';
  }
  // labels
  // Edit by Eddy Subratha
  if ($_labels) {
      $arr_labels = @unserialize($_labels);
      if ($arr_labels !== false) {
	  foreach ($arr_labels as $label) {
	      if (!isset($label_cache[$label[0]]['name'])) {
	          $_label_q = $obj_db->query('SELECT label_name, label_desc, label_image FROM mst_label AS lb WHERE lb.label_name=\''.$label[0].'\'');
              $_label_d = $_label_q->fetch_row();
	          $label_cache[$_label_d[0]] = array('name' => $_label_d[0], 'desc' => $_label_d[1], 'image' => $_label_d[2]);
	      }
	    //   $_output .= ' <img src="'.SWB.'lib/minigalnano/createthumb.php?filename='.IMG.'/labels/'.urlencode($label_cache[$label[0]]['image']).'&amp;width=16&amp;" title="'.$label_cache[$label[0]]['desc'].'" />';
	      $_output .= '<div class="badge badge-light">'.$label_cache[$label[0]]['desc'].'</div>&nbsp;';
	  }
	}
  }
  return $_output;
}

function importProgress(int $percentage)
{
  if ($percentage > 100) return;
  if(!ob_get_level()) ob_start();
  echo <<<HTML
  <script>
    if (!parent.$('#preview').hasClass('d-none')) parent.$('#preview').addClass('d-none')
    if (parent.$('#progress').hasClass('d-none')) parent.$('#progress').removeClass('d-none')
    parent.$('.progress-bar').attr('style', 'width: {$percentage}%')
    parent.$('.progress-bar').html('{$percentage}%')
  </script>
  HTML;
  ob_end_flush();
  ob_flush();
  flush();
}

function isItemExists(string $itemCode)
{
  $itemCode = trim($itemCode, '\'');
  $state = \SLiMS\DB::getInstance()->prepare('select item_code from item where item_code = ?');
  $state->execute([$itemCode]);

  return (bool)$state->rowCount();
}
