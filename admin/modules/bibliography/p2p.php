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
use SLiMS\Url;
use SLiMS\Http\Client;

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
  $cover = Client::download($url, [
    'headers' => [
      'User-Agent' => $_SERVER['HTTP_USER_AGENT']
    ]
  ]);

  $savingCover = $cover->to($path);

  if (!empty($savingCover->getError())) {
    return $savingCover->getError();
  } else {
    return true;
  }
}

function cleanUrl($url)
{
  $Url = Url::parse($url);
  
  return $Url->getScheme() . '://' . // http or https
         // localhost, ip, or domain       
         $Url->getDomain() .
         // http standart port (80 & 443) or non http standart port
         (!is_null($Url->getPort()) ? ':' . $Url->getPort() : '') .
         // path
         (substr($Url->getPath(), -1) == '/' ? $Url->getPath() . '' : $Url->getPath() . '/');
}

function remoteFileExists($url) 
{
  $existation = Client::get($url);
  return $existation->getStatusCode() == 200 ? $url : null;
}    

// get servers
$server_q = $dbs->query('SELECT name, uri FROM mst_servers WHERE server_type = 1 ORDER BY name ASC');
while ($server = $server_q->fetch_assoc()) {
  if (Url::isValid($server['uri'])) $sysconf['p2pserver'][] = array('uri' => $server['uri'], 'name' => $server['name']);
}

/* RECORD OPERATION */
if (isset($_POST['saveResults']) && isset($_POST['p2precord'])) {
  require MDLBS . 'bibliography/biblio_utils.inc.php';

  $p2pserver = cleanUrl($_SESSION['p2pserver']);
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

      $biblio['input_date'] = date('Y-m-d H:i:s');
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

      // download image 

      if (isset($biblio['image']) && remoteFileExists($p2pserver . 'images/docs/' . $biblio['image'])) {
        $url_image  = $p2pserver . 'images/docs/' . $biblio['image']; 
        $image_path = IMGBS . 'docs' . DS . $biblio['image'];
        downloadFile($url_image, $image_path);
      }else{
        $biblio['image'] = NULL; 
      }

      // for debugging purpose
      // var_dump($biblio);
      // die();

      // insert biblio data
      $sql_op->insert('biblio', $biblio);
      echo '<p>' . $sql_op->error . '</p><p>&nbsp;</p>';
      $biblio_id = $sql_op->insert_id;
      if ($biblio_id < 1) {
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography',sprintf(__('failed %s download file ( %s ) from  ( %s )') . ' : ' . $sql_op->error,$_SESSION['realname'],$fdata['file_title'],$stream_file), 'Download');  
        continue;
      }
      // insert authors
      if ($authors) {
        $author_id = 0;
        foreach ($authors as $author) {
          $author_id = getAuthorID(trim($author['name']), strtolower(substr($author['author_type'], 0, 1)), $author_cache);
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
          $subject_id = getSubjectID(trim($subject['term']), $subject_type, $subject_cache);
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

        	  // write to logs
        	  utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography',sprintf(__('%s download file ( %s ) from  ( %s )'),$_SESSION['realname'],$fdata['file_title'],$stream_file), 'Download');  

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
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography',sprintf(__('%s insert bibliographic data from P2P service (server : %s) with title (%s) and biblio_id (%s)'),$_SESSION['realname'],$p2pserver,$biblio['title'],$biblio_id), 'P2P', 'Add');  
        $r++;
      }
      unset($biblio);
    }
  }
  exit();
}

/* RECORD OPERATION END */
?>

    <div class="menuBox">
        <div class="menuBoxInner biblioIcon">
            <div class="per_title">
                <h2><?php echo __('P2P Service'); ?></h2>
            </div>
            <div class="sub_section">
                <form name="search" action="<?php echo MWB; ?>bibliography/p2p.php" id="search" method="get"
                      class="form-inline">
                    <span class="mr-2"><?php echo __('Search'); ?></span>
                    <input type="text" name="keywords" id="keywords" class="form-control col-md-3"/>
                    <span class="mx-2"><?php echo __('Fields'); ?> :</span>
                    <select name="fields" style="width: 20%;" class="form-control">
                        <option value=""><?php echo __('ALL'); ?></option>
                        <option value="title"><?php echo __('Title'); ?></option>
                        <option value="isbn"><?php echo __('ISBN'); ?></option>
                        <option value="author"><?php echo __('Author'); ?></option>
                    </select>
                    <span class="mx-2"><?php echo __('Server'); ?>:</span>
                    <select name="p2pserver" style="width: 20%;"
                            class="form-control"><?php foreach ($sysconf['p2pserver'] as $serverid => $p2pserver) {
                            echo '<option value="' . $serverid . '" '.trim(isset($_GET['p2pserver']) && $_GET['p2pserver'] == $serverid ? 'selected' : '').'>' . $p2pserver['name'] . '</option>';
                        } ?></select>
                    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>"
                           class="s-btn btn btn-default"/>
                </form>
            </div>
            <div class="infoBox"><?php echo __('* Please make sure you have a working Internet connection.'); ?></div>
        </div>
    </div>

<?php
/* SEARCH OPERATION */
if (isset($_GET['keywords']) && $can_read && isset($_GET['p2pserver'])) {
  $max_fetch = 20;
  # get server information
  $serverid = (integer)$_GET['p2pserver'];
  $p2pserver = cleanUrl($sysconf['p2pserver'][$serverid]['uri']);
  $p2pserver_name = $sysconf['p2pserver'][$serverid]['name'];

  $_SESSION['p2pserver'] = $p2pserver;
  # get keywords
  $keywords = urlencode($_GET['keywords']);
  # $p2pquery = $p2pserver.'index.php?resultXML=true&keywords='.$_GET['keywords'];

  $page = isset($_GET['page'])?$_GET['page']:1;

  if($_GET['fields']!=''){
    $keywords = $_GET['fields'].'='.$keywords;
    $url = $p2pserver . "index.php?resultXML=true&".$keywords."&search=Search&page=".$page;
    $data = modsXMLsenayan($url, 'uri');
  }
  else{
    $url = $p2pserver . "index.php?resultXML=true&search=Search&page=".$page."&keywords=" . $keywords;
    $data = modsXMLsenayan($url, 'uri');
  }

  # debugging tools
  debug($url, $data);

  if (isset($data['records'])) {

    echo '<div class="infoBox">' .sprintf(__('Found %s records from <strong>%s</strong> Server'),$data['result_num'],$p2pserver_name).'</div>';
    echo '<div class="p-3" style="padding: 1rem 0px 0px 1rem !important;"><span id="pagingBox"></span></div>';
    $table = new simbio_table();
    $table->table_attr = 'align="center" class="s-table table" cellpadding="5" cellspacing="0"';
    echo  '<div class="p-3">
            <input value="'.__('Check All').'" class="check-all button btn btn-default" type="button"> 
            <input value="'.__('Uncheck All').'" class="uncheck-all button btn btn-default" type="button">
            <input type="submit" name="saveZ" class="s-btn btn btn-success save" value="' . __('Save P2P Records to Database') . '" /></div>';
    // table header
    $table->setHeader(array(__('Select'),__('Title'),__('Digital Files'),__('Detail')));
    $table->table_header_attr = 'class="dataListHeader alterCell font-weight-bold"';
    $table->setCellAttr(0, 0, '');

    $row = 1;
    foreach ($data['records'] as $record) {
      $cb = '<input class="p2precord" type="checkbox" name="p2precord['.$record['id'].']" value="'.$record['id'].'">';
      if ($row > $max_fetch) {
        break;
      }
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
          $digital_str .= '<li class="d-flex"><span class="pr-2"><input class="p2pdigital" data-biblio="'.$record['id'].'" name="p2pdigital[' . $record['id'] . '][]" value="' . $digital['id'] . '" type="checkbox"></span><span>' . $file_name . '</span></li>';
        }
        $digital_str .= '<ul>';
      } else {
        // try get from detail xml
        // construct full XML URI
        $detail_uri = $p2pserver . "/index.php?p=show_detail&inXML=true&id=" . $record['id'];
        // parse XML
        $result = modsXMLsenayan($detail_uri, 'uri');
        if (isset($result['records']) && isset($result['records'][0])) {
            $detail = $result['records'][0];
            if ( isset($detail['digitals'])) {
                $digital_str .= '<ul style="padding: 0; margin: 0; list-style: none">';
                foreach ($detail['digitals'] as $digital) {
                    $file_name = str_replace('/', '', $digital['path']);
                    $digital_str .= '<li class="d-flex"><span class="pr-2"><input class="p2pdigital" data-biblio="'.$record['id'].'" name="p2pdigital[' . $record['id'] . '][]" value="' . $digital['id'] . '" type="checkbox"></span><span>' . $file_name . '</span></li>';
                }
                $digital_str .= '<ul>';
            } else {
                $digital_str .= '-';
            }
        } else {
            $digital_str .= '-';
        }
      }

      $image_path = isset($record['image'])?$p2pserver . 'images/docs/' . $record['image']:'../images/default/image.png';

      $image_uri = remoteFileExists($image_path)??'../images/default/image.png';

      $server = urlencode($p2pserver);

      $detail = '<a class="s-btn btn btn-default btn-sm notAJAX openPopUp" href="modules/bibliography/pop_p2p.php?uri='.$server.'&biblioID='.$record['id'].'" title="detail">'.__('Detail').'</a>';
      $title_content = '<div class="media">
                    <img class="mr-3 rounded" src="'.$image_uri.'" alt="'.$image_uri.'" loading="lazy" style="height:70px;">
                    <div class="media-body">
                      <div class="title">'.stripslashes($record['title']).'</div><div class="authors">'.$authors.'</div>
                    </div>
                  </div>';
      
      $table->appendTableRow(array($cb,$title_content,$digital_str,$detail));
      // set cell attribute
      $row_class = ($row%2 == 0)?'alterCell':'alterCell2';
      $table->setCellAttr($row, 0, 'class="'.$row_class.'" valign="top" style="width: 5px;"');
      $table->setCellAttr($row, 1, 'class="'.$row_class.'" valign="top" style="width: auto;"');
      $table->setCellAttr($row, 2, 'class="'.$row_class.'" valign="top" style="width: auto;"');
      $table->setCellAttr($row, 2, 'class="'.$row_class.'" valign="top" style="width: auto;"');
      $row++;
    }
    echo $table->printTable();  
    $page = new simbio_paging();
    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $page->paging($data['result_num'],10)??'').'\');'."\n";
    echo '</script>';
    ?>
    <script>
        $('.save').on('click', function (e) {
        let p2precord = {}, p2pdigital = {};
        let uri = '<?php echo $_SERVER['PHP_SELF']; ?>';
        $(".p2precord:checked").each(function() {
           p2precord[$(this).val()] = $(this).val();
        });
        $(".p2pdigital:checked").each(function() {
            if (p2precord[$(this).data('biblio')] !== undefined) {
                if (p2pdigital[$(this).data('biblio')] === undefined) p2pdigital[$(this).data('biblio')] = {};
                p2pdigital[$(this).data('biblio')][$(this).val()] = $(this).val();
            }
        });

        if (Object.keys(p2precord).length > 0) {
            $.ajax({
                url: uri,
                type: 'post',
                data: {saveResults: true, p2precord, p2pdigital }
            })
                .done(function (msg) {
                    //console.log(p2precord);
                    parent.toastr.success(Object.keys(p2precord).length+" records inserted into the database", "P2P Service");
                    parent.jQuery('#mainContent').simbioAJAX(uri)
                })
        } else {
            alert('<?= __('No data selected!') ?>');
        }

        });
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
  } else {
    echo '<div class="errorBox">' . 
    sprintf(__('Sorry, no result found from %s OR maybe XML result and detail disabled.'), $p2pserver) . ',<button type="button" onclick="$(\'#detailError\').show()" class="' . (!isDev() ? 'notAJAX btn btn-link text-white' : 'd-none') . ' ">' . __('read error detail') . '.</button>' .
    '<div id="detailError" class="mt-2" style="display: none;background-color: #18171B; padding: 15px; color: #56DB3A; font: 12px Menlo, Monaco, Consolas, monospace">' . ((string)$data) . '</div>' .
    '</div>';
    exit();
  }
}

