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
require MDLBS.'system/biblio_indexer.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// get servers
$server_q = $dbs->query('SELECT name, uri FROM mst_servers WHERE server_type = 3 ORDER BY name ASC');
while ($server = $server_q->fetch_assoc()) {
  $sysconf['z3950_SRU_source'][] = array('uri' => $server['uri'], 'name' => $server['name']);
}

if (isset($_GET['z3950_SRU_source'])) {
    $inList = (bool)count(array_filter($sysconf['z3950_SRU_source'], fn($sru) => trim(urldecode($_GET['z3950_SRU_source'])) == $sru['uri']));
    $zserver = $inList ? trim(urldecode($_GET['z3950_SRU_source'])) : '';
} else {
    $zserver = 'http://z3950.loc.gov:7090/voyager?';
}

/* RECORD OPERATION */
if (isset($_POST['zrecord']) && isset($_SESSION['z3950result'])) {

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
              // create biblio_indexer class instance
              $indexer = new biblio_indexer($dbs);
              // update index
              $indexer->makeIndex($biblio_id);
              // write to logs
              utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography',sprintf(__('%s insert bibliographic data from Z3950 service (server : %s) with title (%s) and biblio_id (%s)'),$_SESSION['realname'],$zserver,$biblio['title'],$biblio_id), 'Z3950 SRU', 'Add');  
              $r++;
          }
      }
  }

  unset($_SESSION['z3950result']);
  exit();
}
/* RECORD OPERATION END */

/* SEARCH OPERATION */
if (isset($_GET['keywords']) AND $can_read) {
  
  if (empty($zserver)) die('<div class="errorBox">'. __('Current z3950 SRU address is not register in database!') .'</div>');

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
      echo '<div class="infoBox">' . str_replace('{hits}', $hits,__('Found {hits} records from Z3950 SRU Server.')) . '</div>';
      $table = new simbio_table();
      $table->table_attr = 'align="center" class="s-table table" cellpadding="5" cellspacing="0"';
      echo  '<div class="p-3">
              <input value="'.__('Check All').'" class="check-all button btn btn-default" type="button"> 
              <input value="'.__('Uncheck All').'" class="uncheck-all button btn btn-default" type="button">
              <input type="submit" name="saveResult" class="s-btn btn btn-success save" value="' . __('Save Z3950 Records to Database') . '" /></div>';
      // table header
      $table->setHeader(array(__('Select'),__('Title'),__('ISBN/ISSN'),__('GMD'),__('Collation'),__('Publisher'),__('Publishing Year')));
      $table->table_header_attr = 'class="dataListHeader alterCell font-weight-bold"';
      $table->setCellAttr(0, 0, '');

      $row = 1;
      foreach ($zs_xml->records->record as $rec) {
        // echo '<pre>'; var_dump($rec->recordData->children()); echo '</pre>';
        $mods = modsXMLslims($rec->recordData->children()->mods);
        // save it to session vars for retrieving later
        $_SESSION['z3950result'][$row] = $mods;

        // authors
        $authors = array(); foreach ($mods['authors'] as $auth) { $authors[] = $auth['name']; }

        $row_class = ($row%2 == 0)?'alterCell':'alterCell2';

        $cb = '<input type="checkbox" name="zrecord['.$row.']" value="'.$row.'">';

        $title_content = '<div class="media">
                      <div class="media-body">
                        <div class="title">'.stripslashes($mods['title']).'</div><div class="authors">'.implode(' - ', $authors).'</div>
                      </div>
                    </div>';

        $table->appendTableRow(array($cb,
          $title_content,
          ($mods['isbn_issn']??'-'),
          ($mods['gmd']??'-'),
          ($mods['collation']??'-'),
          ($mods['publisher']??'-'),
          ($mods['publish_year']??'-'),
        ));
        // set cell attribute
        $row_class = ($row%2 == 0)?'alterCell':'alterCell2';
        $table->setCellAttr($row, 0, 'class="'.$row_class.'" valign="top" style="width: 5px;"');
        $table->setCellAttr($row, 1, 'class="'.$row_class.'" valign="top" style="width: auto;"');
        $table->setCellAttr($row, 2, 'class="'.$row_class.'" valign="top" style="width: auto;"');
        $table->setCellAttr($row, 2, 'class="'.$row_class.'" valign="top" style="width: auto;"');
        $table->setCellAttr($row, 2, 'class="'.$row_class.'" valign="top" style="width: auto;"');
        $table->setCellAttr($row, 2, 'class="'.$row_class.'" valign="top" style="width: auto;"');
        $table->setCellAttr($row, 2, 'class="'.$row_class.'" valign="top" style="width: auto;"');      
        $row++;
      }

      echo $table->printTable(); 

    } else if ($errors) {
      echo '<div class="errorBox"><ul>';
      foreach ($errors as $errmsg) {
          echo '<li>'.$errmsg.'</li>';
      }
      echo '</ul></div>';
    } else {
      echo '<div class="errorBox">' . __('No Results Found!') . '</div>';
    }
  } else {
    echo '<div class="errorBox">' . __('No Keywords Supplied!') . '</div>';
  }
  ?>
  <script>
    $('.save').on('click', function (e) {
    var zrecord = {};
    var uri = '<?php echo $_SERVER['PHP_SELF']; ?>';
    $("input[type=checkbox]:checked").each(function() {
       zrecord[$(this).val()] = $(this).val();
    });

    $.ajax({
            url: uri,
            type: 'post',
            data: {saveResults: true, zrecord }
        })
          .done(function (msg) {
            console.log(zrecord);
            parent.toastr.success(Object.keys(zrecord).length+" records inserted into the database", "Z3950 Service");
            parent.jQuery('#mainContent').simbioAJAX(uri)
        })
    })
    $(".uncheck-all").on('click',function (e){
        e.preventDefault()
        $('[type=checkbox]').prop('checked', false);
    });
    $(".check-all").on('click',function (e){
        e.preventDefault()
        $('[type=checkbox]').prop('checked', true);
    });
</script>
<?php
  exit();
}
/* SEARCH OPERATION END */

/* search form */
?>
<div class="menuBox">
    <div class="menuBoxInner biblioIcon">
        <div class="per_title">
            <h2><?php echo __('Z3950 Search/Retrieve via URL (SRU)'); ?></h2>
        </div>
        <div class="sub_section">
            <form name="search" id="search" action="<?php echo MWB; ?>bibliography/z3950sru.php"
                  loadcontainer="searchResult" method="get" class="form-inline">
                <span class="mr-2"><?php echo __('Search'); ?></span>
                <input type="text" name="keywords" id="keywords" class="form-control col-md-3"/>
                <select name="index" class="form-control">
                    <option value="0"><?php echo __('All fields'); ?></option>
                    <option value="bath.isbn"><?php echo __('ISBN/ISSN'); ?></option>
                    <option value="dc.title"><?php echo __('Title/Series Title'); ?></option>
                    <option value="bath.name"><?php echo __('Authors'); ?></option>
                </select>
                <span class="mx-2"><?php echo __('SRU Server'); ?>:</span>
                <select name="z3950_SRU_source"
                        class="form-control"><?php foreach ($sysconf['z3950_SRU_source'] as $serverid => $z3950_source) {
                        echo '<option value="' . $z3950_source['uri'] . '">' . $z3950_source['name'] . '</option>';
                    } ?></select>
                <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default"/>
            </form>
        </div>
        <div class="infoBox"><?php echo __('* Please make sure you have a working Internet connection.'); ?></div>
    </div>
</div>
<div id="searchResult">&nbsp;</div>
