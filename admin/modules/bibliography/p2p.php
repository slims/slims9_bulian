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
require LIB.'ip_based_access.inc.php';

do_checkIP('smc');
do_checkIP('smc-bibliography');
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require LIB.'modsxmlsenayan.inc.php';
require MDLBS.'system/biblio_indexer.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// get servers
$server_q = $dbs->query('SELECT name, uri FROM mst_servers WHERE server_type = 1 ORDER BY name ASC');
while ($server = $server_q->fetch_assoc()) {
  $sysconf['p2pserver'][] = array('uri' => $server['uri'], 'name' => $server['name']);
}

/* RECORD OPERATION */
if (isset($_POST['saveResults']) && isset($_POST['p2precord']) && isset($_POST['p2pserver_save'])) {
  require MDLBS.'bibliography/biblio_utils.inc.php';

  $p2pserver = trim($_POST['p2pserver_save']);
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
      $detail_uri = $p2pserver."/index.php?p=show_detail&inXML=true&id=".$id;
      // parse XML
      $data = modsXMLsenayan($detail_uri, 'uri');
      // get record detail
      $record = $data['records'][0];
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
              // create biblio_indexer class instance
              $indexer = new biblio_indexer($dbs);
              // update index
              $indexer->makeIndex($biblio_id);
              // write to logs
              utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' insert bibliographic data from P2P service (server:'.$p2pserver.') with ('.$biblio['title'].') and biblio_id ('.$biblio_id.')');
              $r++;
          }
      }
  }
  utility::jsAlert(str_replace('{recordCount}', $r, __('{recordCount} records inserted to database.')));
  echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
  exit();
}

/* RECORD OPERATION END */


/* SEARCH OPERATION */
if (isset($_GET['keywords']) && $can_read && isset($_GET['p2pserver']))  {
    $max_fetch = 20;
    # get server information
    $serverid = (integer)$_GET['p2pserver'];
    $p2pserver = $sysconf['p2pserver'][$serverid]['uri'];
    $p2pserver_name = $sysconf['p2pserver'][$serverid]['name'];
    # get keywords
    $keywords = urlencode($_GET['keywords']);
    # $p2pquery = $p2pserver.'index.php?resultXML=true&keywords='.$_GET['keywords'];
    $data = modsXMLsenayan($p2pserver."/index.php?resultXML=true&search=Search&keywords=".$keywords, 'uri');

    # debugging tools
    # echo $p2pserver."/index.php?resultXML=true&keywords=".$keywords;
    # echo '<br />';
    if ($data['records']) {
        echo '<div class="infoBox">Found '.$data['result_num'].' records from <strong>'.$p2pserver_name.'</strong> Server</div>';
        echo '<form method="post" class="notAJAX" action="'.MWB.'bibliography/p2p.php" target="blindSubmit">';
        echo '<table align="center" id="dataList" cellpadding="5" cellspacing="0">';
        echo '<tr><td colspan="3"><input type="submit" name="saveResults" value="Save P2P Records to Database" /></td></tr>';
        $row = 1;
        foreach($data['records'] as $record) {
            if ($row > $max_fetch) {
                break;
            }
            $row_class = ($row%2 == 0)?'alterCell':'alterCell2';
            echo '<tr>';
            echo '<td width="2%" class="'.$row_class.'"><input type="checkbox" name="p2precord[]" value="'.$record['id'].'" /></td>';
            echo '<td width="98%" class="'.$row_class.'"><strong>'.$record['title'].'</strong>';
            echo '<div><i>';
            // concat authors name
            $buffer_authors = '';
            foreach ($record['authors'] as $author) { $buffer_authors .= $author['name'].' - '; }
            echo substr_replace($buffer_authors, '', -3);
            echo '</i></div>';
            echo '</td>';
            echo '</tr>';
            $row++;
        }
        echo '</table>'."\n";
        echo '<input type="hidden" name="p2pserver_save" value="'.$p2pserver.'" />';
        echo '</form>';
    } else {
        echo '<div class="errorBox">'.sprintf(__('Sorry, no result found from %s OR maybe XML result and detail disabled.'), $p2pserver).'</div>';
    }
    exit();
}
/* SEARCH OPERATION END */

/* search form */
?>
<fieldset class="menuBox">
<div class="menuBoxInner biblioIcon">
    <div class="per_title">
	    <h2><?php echo __('P2P Service'); ?></h2>
    </div>
    <div class="sub_section">
      <form name="search" action="<?php echo MWB; ?>bibliography/p2p.php" loadcontainer="searchResult" id="search" method="get" style="display: inline;"><?php echo __('Search'); ?> :
      <input type="text" name="keywords" id="keywords" size="30" />
      <?php echo __('Server'); ?>: <select name="p2pserver" style="width: 20%;"><?php foreach ($sysconf['p2pserver'] as $serverid => $p2pserver) { echo '<option value="'.$serverid.'">'.$p2pserver['name'].'</option>';  } ?></select>
      <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
      </form>
    </div>
      <div class="infoBox"><?php echo __('* Please make sure you have a working Internet connection.'); ?></div>
</div>
</fieldset>
<div id="searchResult">&nbsp;</div>
