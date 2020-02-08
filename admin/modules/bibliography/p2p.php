<?php
/**
 * Copyright (C) 2009 Arie Nugraha (dicarve@yahoo.com)
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

/* Peer-to-Peer Web Services section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// start the session
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB . 'ip_based_access.inc.php';

do_checkIP('smc');
do_checkIP('smc-bibliography');
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/simbio_dbop.inc.php';
require LIB . 'modsxmlsenayan.inc.php';
require MDLBS . 'system/biblio_indexer.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
  die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

function downloadFile($url, $path)
{
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => array(
      "key: your-api-key"
    ),
  ));

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    return $err;
  } else {
    file_put_contents($path, $response);
    return true;
  }
}

function cleanUrl($url) {
  $_url = parse_url(trim($url));
  $_path = preg_replace('/(\/index.php|\/)$/', '', trim($_url['path']));
  return $_url['scheme'].'://'.$_url['host'].$_path.'/';
}

// get servers
$server_q = $dbs->query('SELECT name, uri FROM mst_servers WHERE server_type = 1 ORDER BY name ASC');
while ($server = $server_q->fetch_assoc()) {
  $sysconf['p2pserver'][] = array('uri' => $server['uri'], 'name' => $server['name']);
}

/* RECORD OPERATION */
if (isset($_POST['saveResults']) && isset($_POST['p2precord']) && isset($_POST['p2pserver_save'])) {
  require MDLBS . 'bibliography/biblio_utils.inc.php';

  $p2pserver = cleanUrl($_POST['p2pserver_save']);
  $gmd_cache = array();
  $publ_cache = array();
  $place_cache = array();
  $lang_cache = array();
  $author_cache = array();
  $subject_cache = array();
  $input_date = date('Y-m-d H:i:s');
  // record counter
  $r = 0;

  foreach ($_POST['p2precord'] as $id) {
    // construct full XML URI
    $detail_uri = $p2pserver . "/index.php?p=show_detail&inXML=true&id=" . $id;
    // parse XML
    $data = modsXMLsenayan($detail_uri, 'uri');
    // get record detail
    $record = $data['records'][0];
    // insert record to database
    if ($record) {
      // create dbop object
      $sql_op = new simbio_dbop($dbs);
      // escape all string value
      foreach ($record as $field => $content) {
        if (is_string($content)) {
          $biblio[$field] = $dbs->escape_string(trim($content));
        }
      }
      // gmd
      $biblio['gmd_id'] = utility::getID($dbs, 'mst_gmd', 'gmd_id', 'gmd_name', $record['gmd'], $gmd_cache);
      unset($biblio['gmd']);
      // publisher
      $biblio['publisher_id'] = utility::getID($dbs, 'mst_publisher', 'publisher_id', 'publisher_name', $record['publisher'], $publ_cache);
      unset($biblio['publisher']);
      // publish place
      $biblio['publish_place_id'] = utility::getID($dbs, 'mst_place', 'place_id', 'place_name', $record['publish_place'], $place_cache);
      unset($biblio['publish_place']);
      // language
      $biblio['language_id'] = utility::getID($dbs, 'mst_language', 'language_id', 'language_name', $record['language']['name'], $lang_cache);
      unset($biblio['language']);
      // authors
      $authors = array();
      if (isset($record['authors'])) {
        $authors = $record['authors'];
        unset($biblio['authors']);
      }
      // subject
      $subjects = array();
      if (isset($record['subjects'])) {
        $subjects = $record['subjects'];
        unset($biblio['subjects']);
      }

      $biblio['input_date'] = $biblio['create_date'];
      $biblio['last_update'] = $biblio['modified_date'];

      // remove unneeded elements
      unset($biblio['manuscript']);
      unset($biblio['collection']);
      unset($biblio['resource_type']);
      unset($biblio['genre_authority']);
      unset($biblio['genre']);
      unset($biblio['issuance']);
      unset($biblio['location']);
      unset($biblio['id']);
      unset($biblio['create_date']);
      unset($biblio['modified_date']);
      unset($biblio['origin']);

      // download image
      if (isset($biblio['image']) && $biblio['image'] !== '') {
        $image_path = IMGBS . 'docs' . DS . $biblio['image'];
        $url_image = $p2pserver . 'images/docs/' . $biblio['image'];
        $arrContextOptions = array(
          "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
          ),
        );
        file_put_contents($image_path, file_get_contents($url_image, false, stream_context_create($arrContextOptions)));
      }

      // fot debugging purpose
      // var_dump($biblio);
      // die();

      // insert biblio data
      $sql_op->insert('biblio', $biblio);
      echo '<p>' . $sql_op->error . '</p><p>&nbsp;</p>';
      $biblio_id = $sql_op->insert_id;
      if ($biblio_id < 1) {
        continue;
      }
      // insert authors
      if ($authors) {
        $author_id = 0;
        foreach ($authors as $author) {
          $author_id = getAuthorID($author['name'], strtolower(substr($author['author_type'], 0, 1)), $author_cache);
          @$dbs->query("INSERT IGNORE INTO biblio_author (biblio_id, author_id, level) VALUES ($biblio_id, $author_id, " . $author['level'] . ")");
        }
      }
      // insert subject/topical terms
      if ($subjects) {
        foreach ($subjects as $subject) {
          if ($subject['term_type'] == 'Temporal') {
            $subject_type = 'tm';
          } else if ($subject['term_type'] == 'Genre') {
            $subject_type = 'gr';
          } else if ($subject['term_type'] == 'Occupation') {
            $subject_type = 'oc';
          } else {
            $subject_type = strtolower(substr($subject['term_type'], 0, 1));
          }
          $subject_id = getSubjectID($subject['term'], $subject_type, $subject_cache);
          @$dbs->query("INSERT IGNORE INTO biblio_topic (biblio_id, topic_id, level) VALUES ($biblio_id, $subject_id, 1)");
        }
      }

      // save digital files
      if (isset($_POST['p2pdigital'][$id])) {
        foreach ($record['digitals'] as $digital) {
          if (in_array($digital['id'], $_POST['p2pdigital'][$id])) {
            $file_name = str_replace('/', '', $digital['path']);
            $url_file = $p2pserver . '/index.php?p=fstream-pdf&fid=' . $digital['id'] . '&bid=' . $id . '&fname=' . $file_name;
            $stream_file = $p2pserver . '/index.php?p=fstream&fid=' . $digital['id'] . '&bid=' . $id . '&fname=' . $file_name;
            $target_path = REPOBS . str_replace('/', DS, $digital['path']);
            if ($result = downloadFile($url_file, $target_path) !== true) {
              echo '<p>' . $result . '</p>';
            } else {
              // save to files
              $fdata['uploader_id'] = $_SESSION['uid'];
              $fdata['file_title'] = $dbs->escape_string($digital['title']);
              $fdata['file_name'] = $dbs->escape_string($file_name);
              $fdata['file_url'] = $dbs->escape_string($stream_file);
              $fdata['mime_type'] = $digital['mimetype'];
              $fdata['file_desc'] = '';
              $fdata['input_date'] = date('Y-m-d H:i:s');
              $fdata['last_update'] = $fdata['input_date'];
              $sql_op->insert('files', $fdata);
              $saved_file_id = $sql_op->insert_id;

              // save to biblio_attach
              $ba['biblio_id'] = $biblio_id;
              $ba['file_id'] = $saved_file_id;
              $ba['access_type'] = 'public';
              $ba['access_limit'] = 'literal{NULL}';
              $sql_op->insert('biblio_attachment', $ba);

              utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' download file (' . $fdata['file_title'] . ') from (' . $stream_file . ')');
            }
          }
        }
      }

      if ($biblio_id) {
        // create biblio_indexer class instance
        $indexer = new biblio_indexer($dbs);
        // update index
        $indexer->makeIndex($biblio_id);
        // write to logs
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' insert bibliographic data from P2P service (server:' . $p2pserver . ') with (' . $biblio['title'] . ') and biblio_id (' . $biblio_id . ')');
        $r++;
      }
    }
  }
  utility::jsToastr('P2P', str_replace('{recordCount}', $r, __('{recordCount} records inserted to database.')), 'success');
  echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\');</script>';
  exit();
}

/* RECORD OPERATION END */


/* SEARCH OPERATION */
if (isset($_GET['keywords']) && $can_read && isset($_GET['p2pserver'])) {
  $max_fetch = 20;
  # get server information
  $serverid = (integer)$_GET['p2pserver'];
  $p2pserver = cleanUrl($sysconf['p2pserver'][$serverid]['uri']);
  $p2pserver_name = $sysconf['p2pserver'][$serverid]['name'];
  # get keywords
  $keywords = urlencode($_GET['keywords']);
  # $p2pquery = $p2pserver.'index.php?resultXML=true&keywords='.$_GET['keywords'];

  if($_GET['fields']!=''){
    $keywords = $_GET['fields'].'='.$keywords;
    $data = modsXMLsenayan($p2pserver . "/index.php?resultXML=true&".$keywords."&search=Search", 'uri');
  }
  else{
    $data = modsXMLsenayan($p2pserver . "/index.php?resultXML=true&search=Search&keywords=" . $keywords, 'uri');
  }

  # debugging tools
  # echo $p2pserver."/index.php?resultXML=true&keywords=".$keywords;
  # echo '<br />';
  if (isset($data['records'])) {
    echo '<div class="infoBox">Found ' . $data['result_num'] . ' records from <strong>' . $p2pserver_name . '</strong> Server</div>';
    echo '<form method="post" class="notAJAX" action="' . MWB . 'bibliography/p2p.php" target="blindSubmit">';
    echo '<table id="dataLists" class="s-table table">';
    echo '<tr><td colspan="3"><input type="submit" name="saveResults" class="s-btn btn btn-primary" value="Save P2P Records to Database" /></td></tr>';
    $header = <<<HTML
<tr>
  <th scope="col">#</th>
  <th scope="col">&nbsp;</th>
  <th scope="col">Title</th>
  <th scope="col">Digital Files</th>
    <th scope="col">Detail</th>
</tr>
HTML;
    echo $header;

    $row = 1;
    foreach ($data['records'] as $record) {
      if ($row > $max_fetch) {
        break;
      }

      $row_class = ($row % 2 == 0) ? 'alterCell' : 'alterCell2';

      $authors = '';
      foreach ($record['authors'] as $author) {
        $authors .= $author['name'] . ' - ';
      }
      $authors = substr_replace($authors, '', -3);

      $digital_str = '';
      if (isset($record['digitals'])) {
        $digital_str .= '<ul style="padding: 0; margin: 0; list-style: none">';
        foreach ($record['digitals'] as $digital) {
          $file_name = str_replace('/', '', $digital['path']);
          $digital_str .= '<li class="d-flex"><span class="pr-2"><input name="p2pdigital[' . $record['id'] . '][]" value="' . $digital['id'] . '" type="checkbox"></span><span>' . $file_name . '</span></li>';
        }
        $digital_str .= '<ul>';
      } else {
        $digital_str .= '-';
      }

      $image_uri = isset($record['image'])?$p2pserver . 'images/docs/' . $record['image']:'';
      $image_str = isset($record['image'])?$record['image']:'';
      $server = urlencode($p2pserver);

      $row_str = <<<HTML
<tr class="{$row_class}">
    <th scope="row">
        <input type="checkbox" name="p2precord[]" value="{$record['id']}" />
    </th>
    <td>
        <img width="80" src="{$image_uri}" alt="{$image_str}">
    </td>
    <td>
        <div>{$record['title']}</div>
        <div><i>{$authors}</i></div>
    </td>
    <td>{$digital_str}</td>
    <td><a class="s-btn btn btn-default btn-sm notAJAX openPopUp" href="modules/bibliography/pop_p2p.php?uri={$server}&biblioID={$record['id']}" title="detail">detail</a></td>
</tr>
HTML;
      echo $row_str;

      $row++;
    }
    echo '</table>' . "\n";
    echo '<input type="hidden" name="p2pserver_save" value="' . $p2pserver . '" />';
    echo '</form>';
  } else {
    echo '<div class="errorBox">' . sprintf(__('Sorry, no result found from %s OR maybe XML result and detail disabled.'), $p2pserver) . '</div>';
  }
  exit();
}
/* SEARCH OPERATION END */

/* search form */
?>
<div class="menuBox">
    <div class="menuBoxInner biblioIcon">
        <div class="per_title">
            <h2><?php echo __('P2P Service'); ?></h2>
        </div>
        <div class="sub_section">
            <form name="search" action="<?php echo MWB; ?>bibliography/p2p.php" loadcontainer="searchResult" id="search"
                  method="get" class="form-inline">
              <?php echo __('Search'); ?>
                <input type="text" name="keywords" id="keywords" class="form-control col-md-3"/>
              <?php echo __('Fields'); ?> :  
              <select name="fields" style="width: 20%;"  class="form-control">
                <option value=""><?php echo __('ALL'); ?></option>
                <option value="title"><?php echo __('Title'); ?></option>
                <option value="isbn"><?php echo __('ISBN'); ?></option>
                <option value="author"><?php echo __('Author'); ?></option>
              </select>
              <?php echo __('Server'); ?>: <select name="p2pserver" style="width: 20%;"
                                                   class="form-control"><?php foreach ($sysconf['p2pserver'] as $serverid => $p2pserver) {
                  echo '<option value="' . $serverid . '">' . $p2pserver['name'] . '</option>';
                } ?></select>
                <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default"/>
            </form>
        </div>
        <div class="infoBox"><?php echo __('* Please make sure you have a working Internet connection.'); ?></div>
    </div>
</div>
<div id="searchResult">&nbsp;</div>
