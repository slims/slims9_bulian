<?php
# @Author: Waris Agung Widodo <idoalit>
# @Date:   2017-09-15T15:19:37+07:00
# @Email:  ido.alit@gmail.com
# @Filename: perpusnassru.php
# @Last modified by:   heru subekti (heroe.soebekti@gmail.com)
# @Last modified time: 2017-09-22T11:01:48+07:00

/* Perpustakaan Nasional SRU Web Services section */

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
// marc Library
require LIB . 'marc/XMLParser.inc.php';
require LIB . 'marc/Record.inc.php';
require LIB . 'marc/Field.inc.php';
require LIB . 'marc/ControlField.inc.php';
require LIB . 'marc/DataField.inc.php';
require LIB . 'marc/SubField.inc.php';

// config
$start_record = 1;
$max_record = 20;

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

if (!\Marc\XMLParser::isSupport()) {
  die('<div class="errorBox">'.__('Extension XML is not enabled').'</div>');
}

// get servers
$server_q = $dbs->query('SELECT name, uri FROM mst_servers WHERE server_type = 4 ORDER BY name ASC');
while ($server = $server_q->fetch_assoc()) {
  $sysconf['marc_SRU_source'][] = array('uri' => $server['uri'], 'name' => $server['name']);
}

if (isset($_GET['marc_SRU_source'])) {
    $inList = (bool)count(array_filter($sysconf['marc_SRU_source'], fn($sru) => trim(urldecode($_GET['marc_SRU_source'])) == $sru['uri']));
    $zserver = $inList ? trim(urldecode($_GET['marc_SRU_source'])) : '';
} else {
    $zserver = 'https://opac.perpusnas.go.id/sru.aspx';
}

function getAcronym($sentence)
{
  $words = explode(' ', $sentenc);
  $acronym = '';
  foreach ($words as $word) {
    $acronym .= trim($word)[0];
  }
  return strtoupper($acronym);
}

$gmd_cache = array();
$publ_cache = array();
$place_cache = array();
$lang_cache = array();
$author_cache = array();
$subject_cache = array();
$frequency_cache = array();

/* RECORD OPERATION */
if (isset($_POST['saveZ']) AND isset($_SESSION['marcresult'])) {
  require MDLBS.'bibliography/biblio_utils.inc.php';
  // create dbop object
  $sql_op = new simbio_dbop($dbs);
  $r = 0;

    foreach ($_POST['zrecord'] as $id) {
    // get record detail
    $record = unserialize($_SESSION['marcresult'][$id]);

    // collect data
    $a = $record->getField(245);
    if ($a) {
      // set title
      $title = addslashes($a->getSubfield('a') . ' ' . $a->getSubfield('b') . ' ' . $a->getSubfield('l'));
      $data['title'] = preg_replace("/(?<=)+(\/)$/m", "", trim($dbs->escape_string(strip_tags($title))));
      // set SOR
      $data['sor']   = $a->getSubfield('c')?trim($dbs->escape_string(strip_tags($a->getSubfield('c')))) : '';
      // set GMD
      $data['gmd_id'] = 1;
      if ($a->getSubfield('h')) {
        $data['gmd_id'] = utility::getID($dbs, 'mst_gmd', 'gmd_id', 'gmd_name', preg_replace("/^w+ /m", "", ucfirst($a->getSubfield('h'))), $gmd_cache);
      }
    }
    // set edition
    $b = $record->getField(250);
    if ($b) {
      $data['edition'] = ($b) ? $b->getSubfield('a') : '';
    }
    // Set isbn_issn
    $c = $record->getField('020');
    if ($c) {
      $data['isbn_issn'] = ($c) ? $c->getSubfield('a') : '';
    }
    // Set classification
    $d = $record->getField('082');
    if ($d) {
      $data['classification'] = ($d) ? $d->getSubfield('a') : '';
    }
    // Set inputer uid
    $data['uid'] = $_SESSION['uid'];

    // Set publishing
    $e = $record->getField(260) ? $record->getField(260)  : $record->getField(264);
    if (isset($e)) {
      $place = $e->getSubfield('a');
      $place = preg_replace("~[^a-zA-Z0-9\s]~", "", $place);
      if ($place) {
        $data['publish_place_id'] = utility::getID($dbs, 'mst_place', 'place_id', 'place_name', $place, $place_cache);
      }
      $publisher = $e->getSubfield('b');
      $publisher = preg_replace("~[^a-zA-Z0-9\s\.]~", "", $publisher);
      if ($publisher) {
        $data['publisher_id'] = utility::getID($dbs, 'mst_publisher', 'publisher_id', 'publisher_name', $publisher, $publ_cache);
      }
      $data['publish_year'] = ($e->getSubfield('c') && is_integer($e->getSubfield('c'))) ? $e->getSubfield('c') : '';
    }
    // set collation
    $f = $record->getField(300);
    if ($f) {
      $data['collation']  = ($f->getSubfield('a')) ? $f->getSubfield('a') : '';
      $data['collation'] .= ($f->getSubfield('b')) ? ' '.$f->getSubfield('b') : '';
      $data['collation'] .= ($f->getSubfield('c')) ? ' '.$f->getSubfield('c') : '';
    }
    // set series title
    $data['series_title'] = '';
    // set call_number
    $g = $record->getField('084');
    if ($g) {
      $data['call_number'] = ($g->getSubfield('a')) ? $g->getSubfield('a') : '';
    }
    // set language_id
    $h = $record->getField('041');
    if ($h) {
      $data['language_id'] = ($h->getSubfield('a')) ? $h->getSubfield('a') : '';
    }
    // set notes
    $notes = '';
    $i = $record->getField(520)?$record->getField(520):$record->getField(500);
    if ($i) {
      $notes .= ($i->getSubfield('a')) ? $i->getSubfield('a') : '';
    }
    $ii = $record->getField(505);
    if ($ii) {
      $notes .= '<br/>'.($ii->getSubfield('a')) ? $ii->getSubfield('a') : '';
    }    
    $data['notes'] = trim($dbs->escape_string(strip_tags($notes)));
    // set frequency
  	$j= $record->getField(310);
  	if ($j) {
  		$frequency_id = $j->getSubfield('a');
  		$data['frequency_id'] = utility::getID($dbs, 'mst_frequency', 'frequency_id', 'frequency', $frequency_id, $frequency_cache);
  	}
   // set cover
    $data['image'] = null;
    $k =  $record->getField('856');
    if ($k) {
      if($k->getSubfield('x')){
        $url_image = $k->getSubfield('x');
        $url_image = str_replace('http','https',str_replace(' ', '%20', $url_image));
        $data_image = pathinfo($url_image);
        $image_name = explode('?', $data_image['basename'])[0];
        $image_path = IMGBS . 'docs' . DS . $image_name;
        $arrContextOptions = array(
            "ssl" => array(
              "verify_peer" => false,
              "verify_peer_name" => false,
            ),
          );
        $img = file_put_contents($image_path, file_get_contents($url_image, false, stream_context_create($arrContextOptions)));
        if($img){
          $data['image'] = $image_name;    
        }
      }
    }

    $data['opac_hide'] = 0;
    $data['promoted'] = 0;
    $data['labels'] = '';
    $data['spec_detail_info'] = '';
    $data['input_date'] = date('Y-m-d H:i:s');
    $data['last_update'] = date('Y-m-d H:i:s');

    $insert = $sql_op->insert('biblio', $data);
    echo '<p>'.$sql_op->error.'</p><p>&nbsp;</p>';
    $biblio_id = $sql_op->insert_id;
    if ($biblio_id < 1) {
        continue;
    }
    // insert authors
    $authors = $record->getFields('100|700|110|710|111|711');
    if ($authors) {
        $author_id = 0;
        foreach ($authors as $author) {
            switch ($author->getTag()) {
              case '100':
                $level = 1;
                $type = 'p';
                break;
              case '700':
                $level = 2;
                $type = 'p';
                break;
              case '110':
                $level = 1;
                $type = 'o';
                break;
              case '710':
                $level = 2;
                $type = 'o';
                break;
              case '111':
                $level = 1;
                $type = 'c';
                break;
              case '711':
                $level = 2;
                $type = 'c';
                break;
              default:
                $level = 1;
                $type = 'p';
                break;
            }
            $author_id = getAuthorID($author->getSubfield('a'), strtolower(substr($type, 0, 1)), $author_cache);
            @$dbs->query("INSERT IGNORE INTO biblio_author (biblio_id, author_id, level) VALUES ($biblio_id, $author_id, ".$level.")");
        }
    }
    // insert subject/topical terms
    $subjects = $record->getFields('^6');
    if ($subjects) {
        foreach ($subjects as $subject) {
            switch ($subject->getTag()) {
              case '600':
                $subject_type = 'n';
                break;
              case '610':
                $subject_type = 'o';
                break;
              case '611':
                $subject_type = 'c';
                break;
              case '651':
                $subject_type = 'g';
                break;

              default:
                $subject_type = 't';
                break;
            }
            $subject_id = getSubjectID($subject->getSubfield('a'), $subject_type, $subject_cache);
            @$dbs->query("INSERT IGNORE INTO biblio_topic (biblio_id, topic_id, level) VALUES ($biblio_id, $subject_id, 1)");
        }
    }
    if ($biblio_id) {
        // create biblio_indexer class instance
        $indexer = new biblio_indexer($dbs);
        // update index
        $indexer->makeIndex($biblio_id);
        // write to logs
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography',sprintf(__('%s insert bibliographic data from MARC SRU service (server: %s) with title (%s) and biblio_id (%s)'),$_SESSION['realname'],$zserver,$data['title'],$biblio_id), 'MARC SRU', 'Add');         
        $r++;
    }
  }

  // destroy result Z3950 session
  unset($_SESSION['marcresult']);
}

// Search
if (isset($_GET['keywords'])) {

  if (empty($zserver)) die('<div class="errorBox">'. __('Current Marc SRU address is not register in database!') .'</div>');

  $source = trim($_GET['marc_SRU_source']);
  $index = trim($_GET['index']);
  $keywords = urlencode(trim($_GET['keywords']));

  if (!$keywords) {
    echo '<div class="errorBox">' . __('No Keywords Supplied!') . '</div>';
    exit();
  }

  $_SESSION['marcresult'] = array();

  if (preg_match('/pnri|perpusnas/', $_GET['marc_SRU_source'])) {
    switch ($index) {
      case 'bath.name':
        $index = 'author';
        break;

      default:
        $index = 'title';
        break;
    }
    $request = $zserver . '?operation=searchRetrieve&'.$index.'='.$keywords.'&startRecord=1&maximumRecords='.$max_record.'&maxitem='.$max_record;
  } else {
    if ($_GET['index'] != 0) {
      $index = trim($_GET['index']).' any ';
      $keywords = urlencode($index.'"'.trim($_GET['keywords'].'"'));
    } else {
      $keywords = urlencode('"'.trim($_GET['keywords']).'"');
    }
    $request = $zserver.'?version=1.1&operation=searchRetrieve&query='.$keywords.'&startRecord=1&maximumRecords='.$max_record.'&maxitem='.$max_record.'&recordSchema=marc';
  }

  $marc = new \Marc\XMLParser($request);

  if ($marc->isError()) {
    echo '<div class="errorBox">' . __($marc->getMessage()) . '</div>';
    die();
  }

  if ($marc->count() > 0) {
    echo '<div class="infoBox">' . str_replace('{hits}', $marc->count(),__('Found {hits} records from Marc SRU Server.')) . '</div>';
    $table = new simbio_table();
    $table->table_attr = 'align="center" class="s-table table" cellpadding="5" cellspacing="0"';
    echo  '<div class="p-3">
            <input value="'.__('Check All').'" class="check-all button btn btn-default" type="button"> 
            <input value="'.__('Uncheck All').'" class="uncheck-all button btn btn-default" type="button">
            <input type="submit" name="saveZ" class="s-btn btn btn-success save" value="' . __('Save Marc Records to Database') . '" /></div>';
    // table header
    $table->setHeader(array(__('Select'),__('Title'),__('ISBN/ISSN'),__('GMD')));
    $table->table_header_attr = 'class="dataListHeader alterCell font-weight-bold"';
    $table->setCellAttr(0, 0, '');

    for ($i=1; $i <= $marc->count(); $i++) {

      $cb = '<input type="checkbox" name="zrecord['.$i.']" value="'.$i.'">';

      $record = $marc->get($i);
      // store temporary
      $_SESSION['marcresult'][$i] = serialize($record);

      // title
      $rTitle = $record->getField(245);
      $title  = $rTitle->getSubfield('a');
      $title .= ' '.$rTitle->getSubfield('b');
      $title = preg_replace("/(?<=)+(\/)$/m", "", trim($title));
      // authors
      $authors = $rTitle->getSubfield('c');      
      // isbn_issn
      $rISBN = $record->getField('020');
      $isbn = $rISBN?$rISBN->getSubfield('a'):'-';

      $gmd =  ucwords(preg_replace("/[\[\]\/]/m", "", $rTitle->getSubfield('h')));

      $url_image = '../images/default/image.png';

      $k =  $record->getField('856');
      if ($k) {
        if($k->getSubfield('x')){
          $url_image = $k->getSubfield('x');
        }
      }

      $title_content = '<div class="media">
                    <img class="mr-3 rounded" src="'.$url_image.'" alt="cover image" style="height:70px;">
                    <div class="media-body">
                      <div class="title">'.stripslashes($title).'</div><div class="authors">'.$authors.'</div>
                    </div>
                  </div>';
      
      $table->appendTableRow(array($cb,$title_content,$isbn,$gmd));
      // set cell attribute
      $row_class = ($i%2 == 0)?'alterCell':'alterCell2';
      $table->setCellAttr($i, 0, 'class="'.$row_class.'" valign="top" style="width: 5px;"');
      $table->setCellAttr($i, 1, 'class="'.$row_class.'" valign="top" style="width: auto;"');
      $table->setCellAttr($i, 2, 'class="'.$row_class.'" valign="top" style="width: auto;"');
      $table->setCellAttr($i, 2, 'class="'.$row_class.'" valign="top" style="width: auto;"');
      
    }
    echo $table->printTable();  
  } else {
    echo '<div class="errorBox">' . __('No Results Found!') . '</div>';
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
            data: {saveZ: true,zrecord }
        })
          .done(function (msg) {
            console.log(zrecord);
            parent.toastr.success(Object.keys(zrecord).length+" records inserted into the database", "MARC SRU");
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

?>

<div class="menuBox">
<div class="menuBoxInner biblioIcon">
	<div class="per_title">
	    <h2><?php echo __('MARC Search/Retrieve via URL (SRU)'); ?></h2>
    </div>
    <div class="sub_section">
    <form name="search" id="search" action="<?php echo MWB; ?>bibliography/marcsru.php" loadcontainer="searchResult" method="get" class="form-inline"><?php echo __('Search'); ?>
    <input type="text" name="keywords" id="keywords" class="form-control col-md-3" />
    <select name="index" class="form-control ">
      <option value="0"><?php echo __('All fields'); ?></option>
      <option value="bath.isbn"><?php echo __('ISBN/ISSN'); ?></option>
      <option value="dc.title"><?php echo __('Title/Series Title'); ?></option>
      <option value="bath.name"><?php echo __('Authors'); ?></option>
    </select>
    <span class="mx-2"><?php echo __('SRU Server: '); ?></span>
    <select name="marc_SRU_source" class="form-control">
      <?php foreach ($sysconf['marc_SRU_source'] as $serverid => $sru_source) { echo '<option value="'.$sru_source['uri'].'">'.$sru_source['name'].'</option>';  } ?>
    </select>
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
    </form>
    </div>
    <div class="infoBox"><?php echo __('* Please make sure you have a working Internet connection.'); ?></div>
</div>
</div>
<div id="searchResult">&nbsp;</div>
