<?php
/**
 * Copyright (C) 2012 Arie Nugraha (dicarve@yahoo.com)
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

/* Z3950 Web Services section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

if (!isset ($errors)) {
    $errors = false;
}

// start the session
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

if (isset($_GET['z3950_SRU_source'])) {
    $zserver = trim(urldecode($_GET['z3950_SRU_source']));
} else {
    $zserver = 'http://z3950.loc.gov:7090/voyager?';
}

/* RECORD OPERATION */
if (isset($_POST['saveZ']) AND isset($_SESSION['z3950result'])) {
  require MDLBS.'bibliography/biblio_utils.inc.php';

  $gmd_cache = array();
  $publ_cache = array();
  $place_cache = array();
  $lang_cache = array();
  $author_cache = array();
  $subject_cache = array();
  $input_date = date('Y-m-d H:i:s');
  // create dbop object
  $sql_op = new simbio_dbop($dbs);
  $r = 0;

  foreach ($_POST['zrecord'] as $id) {
      // get record detail
      $record = $_SESSION['z3950result'][$id];
      // insert record to database
      if ($record) {
          // create dbop object
          $sql_op = new simbio_dbop($dbs);
          // escape all string value
          foreach ($record as $field => $content) { if (is_string($content)) { $biblio[$field] = $dbs->escape_string(trim($content)); } }
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
          // $biblio['last_update'] = $biblio['modified_date'];
          $biblio['last_update'] = date('Y-m-d H:i:s');

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

          // fot debugging purpose
          // var_dump($biblio);
          // die();

          // insert biblio data
          $sql_op->insert('biblio', $biblio);
          echo '<p>'.$sql_op->error.'</p><p>&nbsp;</p>';
          $biblio_id = $sql_op->insert_id;
          if ($biblio_id < 1) {
              continue;
          }
          // insert authors
          if ($authors) {
              $author_id = 0;
              foreach ($authors as $author) {
                  $author_id = getAuthorID($author['name'], strtolower(substr($author['author_type'], 0, 1)), $author_cache);
                  @$dbs->query("INSERT IGNORE INTO biblio_author (biblio_id, author_id, level) VALUES ($biblio_id, $author_id, ".$author['level'].")");
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
          if ($biblio_id) {
              // write to logs
              utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' insert bibliographic data from P2P service (server:'.$p2pserver.') with ('.$biblio['title'].') and biblio_id ('.$biblio_id.')');
              $r++;
          }
      }
  }

  // destroy result Z3950 session
  unset($_SESSION['z3950result']);
  utility::jsAlert($r.' records inserted to database.');
  echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
  exit();
}
/* RECORD OPERATION END */

/* SEARCH OPERATION */
if (isset($_GET['keywords']) AND $can_read) {
  require LIB.'modsxmlslims.inc.php';
  $_SESSION['z3950result'] = array();
  if ($_GET['index'] != 0) {
    $index = trim($_GET['index']).' any ';
    $keywords = urlencode($index.'"'.trim($_GET['keywords'].'"'));
  } else {
    $keywords = urlencode('"'.trim($_GET['keywords']).'"');
  }

  $query = '';
  if ($keywords) {
    $sru_server = $zserver.'?version=1.1&operation=searchRetrieve&query='.$keywords.'&startRecord=1&maximumRecords=20&recordSchema=mods';
    // parse SRU Server XML
    $sru_xml = new SimpleXMLElement($sru_server, LIBXML_NSCLEAN, true);
    // below is for debugging purpose
    // echo '<pre>'; var_dump($sru_xml); echo '</pre>'; exit();
    $zs_xml = $sru_xml->children('http://www.loc.gov/zing/srw/');
    $hits = $zs_xml->numberOfRecords;

    if ($hits > 0) {
      echo '<div class="infoBox">Found '.$hits.' records from Z3950 SRU Server.</div>';
      echo '<form method="post" class="notAJAX" action="'.MWB.'bibliography/z3950sru.php" target="blindSubmit">';
      echo '<table align="center" id="dataList" cellpadding="5" cellspacing="0">';
      echo '<tr>';
      echo '<td colspan="3"><input type="submit" name="saveZ" value="Save Z3950 Records to Database" /></td>';
      echo '</tr>';
      $row = 1;
      foreach ($zs_xml->records->record as $rec) {
        // echo '<pre>'; var_dump($rec->recordData->children()); echo '</pre>';
        $mods = modsXMLslims($rec->recordData->children()->mods);
        // save it to session vars for retrieving later
        $_SESSION['z3950result'][$row] = $mods;
        // authors
        $authors = array(); foreach ($mods['authors'] as $auth) { $authors[] = $auth['name']; }

        $row_class = ($row%2 == 0)?'alterCell':'alterCell2';
        echo '<tr>';
        echo '<td width="1%" class="'.$row_class.'"><input type="checkbox" name="zrecord['.$row.']" value="'.$row.'" /></td>';
        echo '<td width="80%" class="'.$row_class.'"><strong>'.$mods['title'].'</strong><div><i>'.implode(' - ', $authors).'</i></div></td>';
        if (isset ($mods['isbn_issn'])) {
            echo '<td width="19%" class="'.$row_class.'">'.$mods['isbn_issn'].'</td>';
        } else {
            echo '<td width="19%" class="'.$row_class.'">&nbsp;</td>';
        }
        echo '</tr>';
        $row++;
      }
      echo '</table>';
      echo '</form>';
    } else if ($errors) {
      echo '<div class="errorBox"><ul>';
      foreach ($errors as $errmsg) {
          echo '<li>'.$errmsg.'</li>';
      }
      echo '</ul></div>';
    } else {
      echo '<div class="errorBox">No Results Found!</div>';
    }
  } else {
    echo '<div class="errorBox">No Keywords Supplied!</div>';
  }
  exit();
}
/* SEARCH OPERATION END */

/* search form */
?>
<fieldset class="menuBox">
<div class="menuBoxInner biblioIcon">
	<div class="per_title">
	    <h2><?php echo __('Z3950 Search/Retrieve via URL (SRU)'); ?></h2>
    </div>
    <div class="sub_section">
    <form name="search" id="search" action="<?php echo MWB; ?>bibliography/z3950sru.php" loadcontainer="searchResult" method="get" style="display: inline;"><?php echo __('Search'); ?> :
    <input type="text" name="keywords" id="keywords" size="30" />
    <select name="index"><option value="0"><?php echo __('All fields'); ?></option><option value="bath.isbn"><?php echo __('ISBN/ISSN'); ?></option><option value="dc.title"><?php echo __('Title/Series Title'); ?></option><option value="bath.name"><?php echo __('Authors'); ?></option></select>
    <?php echo __('SRU Server'); ?>: <select name="z3950_SRU_source" style="width: 20%;"><?php foreach ($sysconf['z3950_SRU_source'] as $serverid => $z3950_source) { echo '<option value="'.$z3950_source['uri'].'">'.$z3950_source['name'].'</option>';  } ?></select>
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
    </form>
    </div>
    <div class="infoBox"><?php echo __('* Please make sure you have a working Internet connection.'); ?></div>
</div>
</fieldset>
<div id="searchResult">&nbsp;</div>
