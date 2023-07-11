<?php
/**
 * SENAYAN application global configuration file
 *
 * Copyright (C) 2010  Arie Nugraha (dicarve@yahoo.com), Hendro Wicaksono (hendrowicaksono@yahoo.com), Wardiyono (wynerst@gmail.com)
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

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} else if (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

// Environment config
$envExists = file_exists($envFile = __DIR__ .  '/config/env.php');
if ($envExists) require $envFile;

/*
 * Set to development or production
 *
 * In production mode, the system error message will be disabled
 */
define('ENVIRONMENT', $env??'unvailable');

switch (ENVIRONMENT) {
  case 'development':
    @error_reporting(-1);
    @ini_set('display_errors', true);
    break;
  case 'production':
    @ini_set('display_errors', false);
    @error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
    break;
  default:
    if (file_exists(__DIR__ . '/config/database.php') && php_sapi_name() !== 'cli') {
      header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
      include __DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'serviceunavailable.php';
      exit(1); // EXIT_ERROR
    }
}

// use httpOnly for cookie
@ini_set( 'session.cookie_httponly', true );
// check if safe mode is on
if ((bool) ini_get('safe_mode')) {
    define('SENAYAN_IN_SAFE_MODE', 1);
}

// senayan version
define('SENAYAN_VERSION', 'SLiMS 9 (Bulian)');
define('SENAYAN_VERSION_TAG', 'v9.6.1');

// senayan session cookies name
define('COOKIES_NAME', 'SenayanAdmin');
define('MEMBER_COOKIES_NAME', 'SenayanMember');

// alias for DIRECTORY_SEPARATOR
define('DS', DIRECTORY_SEPARATOR);

// senayan base dir
define('SB', realpath(dirname(__FILE__)).DS);

// absolute path for simbio platform
define('SIMBIO', SB.'simbio2'.DS);

// senayan library base dir
define('LIB', SB.'lib'.DS);

// document, member and barcode images base dir
define('IMG', 'images');
define('IMGBS', SB.IMG.DS);

// library automation module base dir
define('MDL', 'modules');
define('MDLBS', SB.'admin'.DS.MDL.DS);

// files upload dir
define('FLS', 'files');
define('UPLOAD', SB.FLS.DS);

// repository dir
define('REPO', 'repository');
$repobs['enable'] = FALSE;
$repobs['path'] = '/your/alternative/of/repository/directory/';
if ($repobs['enable'] == TRUE) {
  define('REPOBS', $repobs['path']);
} else {
  define('REPOBS', SB.REPO.DS);
}

// file attachment dir
define('ATC', 'att');
define('FILE_ATC', UPLOAD.ATC);

// printed report dir
define('REP', 'reports');
define('REPBS', UPLOAD.REP.DS);

// languages base dir
define('LANG', LIB.'lang'.DS);

//help base dir
define('HELP', SB.'help'.DS);

// item status rules
define('NO_LOAN_TRANSACTION', 1);
define('SKIP_STOCK_TAKE', 2);

// command execution status
define('BINARY_NOT_FOUND', 127);
define('BINARY_FOUND', 1);
define('COMMAND_SUCCESS', 0);
define('COMMAND_FAILED', 2);

// require composer library
if (file_exists(SB . 'vendor/autoload.php')) require SB . 'vendor/autoload.php';
require LIB . 'autoload.php';
// simbio main class inclusion
require SIMBIO.'simbio.inc.php';
// simbio security class
require SIMBIO.'simbio_UTILS'.DS.'simbio_security.inc.php';
// we must include utility library first
require LIB.'utility.inc.php';
// include API
require LIB.'api.inc.php';
// include biblio class
require MDLBS.'bibliography/biblio.inc.php';

// check if we are in mobile browser mode
if (utility::isMobileBrowser()) { define('LIGHTWEIGHT_MODE', 1); }

// senayan web doc root dir
/* Custom base URL */
$sysconf['baseurl'] = \SLiMS\Config::getInstance()->get('url.base', '');
$_SERVER['PHP_SELF'] = strip_tags(str_replace(['"','\''], '', $_SERVER['PHP_SELF']));
$temp_senayan_web_root_dir = preg_replace('@admin.*@i', '', str_replace('\\', '/', dirname(@$_SERVER['PHP_SELF'])));
define('SWB', $sysconf['baseurl'].$temp_senayan_web_root_dir.(preg_match('@\/$@i', $temp_senayan_web_root_dir)?'':'/'));
$_SERVER['PHP_SELF'] = $sysconf['baseurl'] . $_SERVER['PHP_SELF'];

// admin section web root dir
define('AWB', SWB.'admin/');

// javascript library web root dir
define('JWB', SWB.'js/');

// library automation module web root dir
define('MWB', SWB.'admin/'.MDL.'/');

// repository web base dir
define('REPO_WBS', SWB.REPO.'/');

/* AJAX SECURITY */
$sysconf['ajaxsec_user'] = 'ajax';
$sysconf['ajaxsec_passwd'] = 'secure';
$sysconf['ajaxsec_ip_enabled'] = 0;
$sysconf['ajaxsec_ip_allowed'] = '';

/* session login timeout in second */
$sysconf['session_timeout'] = 7200;

/* default application language */
$sysconf['default_lang'] = 'en_US';
$sysconf['spellchecker_enabled'] = true;

/* HTTP header */
header('Content-type: text/html; charset=UTF-8');

/* GUI Template config */
$sysconf['template']['dir'] = 'template';
$sysconf['template']['theme'] = 'default';
$sysconf['template']['css'] = $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/style.css';

/* ADMIN SECTION GUI Template config */
$sysconf['admin_template']['dir'] = 'admin_template';
$sysconf['admin_template']['theme'] = 'default';
$sysconf['admin_template']['css'] = $sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/style.css';

/* OPAC */
$sysconf['opac_result_num'] = 10;

/* Biblio module */
$sysconf['biblio_result_num'] = 30;
$sysconf['batch_item_code_pattern'] = 'B00000';

/* Promote selected title(s) to homepage setting */
$sysconf['enable_promote_titles'] = false;
$sysconf['promote_first_emphasized'] = true;

/* Dynamic Content */
$sysconf['content']['allowable_tags'] = '<p><a><cite><code><em><strong><cite><blockquote><fieldset><legend>'
    .'<h3><hr><br><table><tr><td><th><thead><tbody><tfoot><div><span><img><object><param><ul><ol><li><i>';

/* allow logged in members to mark bibliography titles, show the title basket in the member details and send a mail to reserve these titles */
$sysconf['enable_mark'] = true;

/* XML */
$sysconf['enable_xml_detail'] = true;
$sysconf['enable_xml_result'] = true;

/* JSON LD */
$sysconf['jsonld_result'] = true;
$sysconf['jsonld_detail'] = true;

// backup location (make sure it is accessible and rewritable to webserver!)
$sysconf['temp_dir'] = '/tmp';
$sysconf['backup_dir'] = UPLOAD.'backup'.DS;

/* FILE DOWNLOAD */
$sysconf['allow_file_download'] = false;

/* WEBCAM feature */
$sysconf['webcam'] = 'html5'; //enabled this feature by changed to 'html5' or 'flex'. FALSE will be defined if none is configured here.

/* SCANNER feature */
$sysconf['scanner'] = false;

/* Search Book Cover */
$sysconf['book_cover'] = true;

/* Barcode Reader */
$sysconf['barcode_reader'] = false;

// Zend Barcode Engine
$sysconf['zend_barcode_engine'] = true;
// Zend Barcode Engine Encoding selection
// $barcodes_encoding['CODE25'] = array('code25', 'Code 2 or 5 Industrial (may result in barcode creation error)');
// $barcodes_encoding['CODE25I'] = array('code25interleaved', 'Code 2 or 5 Interleaved (may result in barcode creation error)');
$barcodes_encoding['code39'] = array('code39', 'Code 39');
$barcodes_encoding['code128'] = array('code128', 'Code 128');
// $barcodes_encoding['EAN2'] = array('ean2', 'Ean 2 (may result in barcode creation error)');
// $barcodes_encoding['EAN5'] = array('ean5', 'Ean 5 (may result in barcode creation error)');
// $barcodes_encoding['EAN8'] = array('ean8', 'Ean 8');
// $barcodes_encoding['EAN13'] = array('ean13', 'Ean 13 (may result in barcode creation error)');
// $barcodes_encoding['IDENTCODE'] = array('identcode', 'Identcode (may result in barcode creation error)');
// $barcodes_encoding['ITF14'] = array('itf14', 'ITF-14 (may result in barcode creation error)');
// $barcodes_encoding['LEITCODE'] = array('leitcode', 'Leitcode (may result in barcode creation error)');
// $barcodes_encoding['PLANET'] = array('planet', 'Planet (may result in barcode creation error)');
// $barcodes_encoding['POSTNET'] = array('postnet', 'Postnet (may result in barcode creation error)');
// $barcodes_encoding['ROYALMAIL'] = array('royalmail', 'Royalmail (may result in barcode creation error)');
// $barcodes_encoding['UPCA'] = array('upca', 'UPC-A (may result in barcode creation error)');
// $barcodes_encoding['UPCE'] = array('upce', 'UPC-E (may result in barcode creation error)');
$sysconf['barcode_encoding'] = $barcodes_encoding['code128'][0];

/* QUICK RETURN */
$sysconf['quick_return'] = true;

/* LOAN LIMIT OVERRIDE */
$sysconf['loan_limit_override'] = false;

/* LOAN DATE CHANGE IN CIRCULATION */
$sysconf['allow_loan_date_change'] = false;

/* CIRCULATION RELATED */
$sysconf['circulation_receipt'] = false;

/* NOTIFICATION RELATED */
$sysconf['transaction_finished_notification'] = false;
$sysconf['bibliography_update_notification'] = true;
$sysconf['bibliography_item_update_notification'] = true;
$sysconf['login_message'] = false;
$sysconf['logout_message'] = false;

/* FILE UPLOADS */
$sysconf['max_upload'] = intval(ini_get('upload_max_filesize'))*1024;
$post_max_size = intval(ini_get('post_max_size'))*1024;
if ($sysconf['max_upload'] > $post_max_size) {
    $sysconf['max_upload'] = $post_max_size-1024;
}
$sysconf['max_image_upload'] = 500;
// allowed image file to upload
$sysconf['allowed_images'] = array('.jpeg', '.jpg', '.gif', '.png', '.JPEG', '.JPG', '.GIF', '.PNG');
// allowed file attachment to upload
$sysconf['allowed_file_att'] = array('.pdf', '.rtf', '.txt',
    '.odt', '.odp', '.ods', '.doc', '.xls', '.ppt',
    '.avi', '.mpeg', '.mp4', '.flv', '.mvk',
    '.jpg', '.jpeg', '.png', '.gif',
    '.docx', '.pptx', '.xlsx',
    '.ogg', '.mp3', '.xml', '.mrc');
$sysconf['allowed_images_mimetype'] = array(
  'image/jpeg', 'image/png',
);

/* FILE ATTACHMENT MIMETYPES */
$sysconf['mimetype']['docx'] = 'application/msword';
$sysconf['mimetype']['js'] = 'application/javascript';
$sysconf['mimetype']['json'] = 'application/json';
$sysconf['mimetype']['doc'] = 'application/msword';
$sysconf['mimetype']['dot'] = 'application/msword';
$sysconf['mimetype']['ogg'] = 'application/ogg';
$sysconf['mimetype']['pdf'] = 'application/pdf';
$sysconf['mimetype']['rdf'] = 'application/rdf+xml';
$sysconf['mimetype']['rss'] = 'application/rss+xml';
$sysconf['mimetype']['rtf'] = 'application/rtf';
$sysconf['mimetype']['xls'] = 'application/vnd.ms-excel';
$sysconf['mimetype']['xlt'] = 'application/vnd.ms-excel';
$sysconf['mimetype']['chm'] = 'application/vnd.ms-htmlhelp';
$sysconf['mimetype']['ppt'] = 'application/vnd.ms-powerpoint';
$sysconf['mimetype']['pps'] = 'application/vnd.ms-powerpoint';
$sysconf['mimetype']['odc'] = 'application/vnd.oasis.opendocument.chart';
$sysconf['mimetype']['odf'] = 'application/vnd.oasis.opendocument.formula';
$sysconf['mimetype']['odg'] = 'application/vnd.oasis.opendocument.graphics';
$sysconf['mimetype']['odi'] = 'application/vnd.oasis.opendocument.image';
$sysconf['mimetype']['odp'] = 'application/vnd.oasis.opendocument.presentation';
$sysconf['mimetype']['ods'] = 'application/vnd.oasis.opendocument.spreadsheet';
$sysconf['mimetype']['odt'] = 'application/vnd.oasis.opendocument.text';
$sysconf['mimetype']['swf'] = 'application/x-shockwave-flash';
$sysconf['mimetype']['zip'] = 'application/zip';
$sysconf['mimetype']['mp3'] = 'audio/mpeg';
$sysconf['mimetype']['jpg'] = 'image/jpeg';
$sysconf['mimetype']['gif'] = 'image/gif';
$sysconf['mimetype']['png'] = 'image/png';
$sysconf['mimetype']['flv'] = 'video/x-flv';
$sysconf['mimetype']['mp4'] = 'video/mp4';
$sysconf['mimetype']['xml'] = 'text/xml';
$sysconf['mimetype']['mrc'] = 'text/marc';
$sysconf['mimetype']['txt'] = 'text/plain';

/* PRICE CURRENCIES SETTING */
$sysconf['currencies'] = array( array('0', 'NONE'), 'Rupiah', 'USD', 'Euro', 'DM', 'Pounds', 'Yen', 'Won', 'Yuan', 'SGD', 'Bath', 'Ruppee', 'Taka', 'AUD');

/* RESERVE PERIODE (In Days) */
$sysconf['reserve_expire_periode'] = 7;

// false = send reserve via email
// true  = reservation will saved directly into reserve table
$sysconf['reserve_direct_database'] = true;

// false = reserve all item, ignoring loan status
// true  = only item on loan can be reserved
$sysconf['reserve_on_loan_only'] = false;

/* CONTENT */
$sysconf['library_name'] = 'Senayan';
$sysconf['library_subname'] = 'Open Source Library Management System';
$sysconf['page_footer'] = ' Senayan Library Management System (SLiMS). Released Under GNU GPL License.<br>Made with love by SLiMS Developer Community';

/* HTTPS Setting */
$sysconf['https_enable'] = false;
$sysconf['https_port'] = 443;

/* Date Format Setting for OPAC */
$sysconf['date_format'] = 'Y-m-d'; /* Produce 2009-12-31 */
// $sysconf['date_format'] = 'd-M-Y'; /* Produce 31-Dec-2009 */

$sysconf['pdf']['viewer'] = 'pdfjs'; # 'pdfjs'

/**
 * UCS global settings
 */
$sysconf['ucs']['enable'] = false;
// auto delete same record on UCS?
$sysconf['ucs']['auto_delete'] = false;
// auto insert new record to UCS?
$sysconf['ucs']['auto_insert'] = false;
// UCS server address. NO TRAILING SLASH! for local testing on Windows machine don't use localhost, use 127.0.0.1 instead
$sysconf['ucs']['serveraddr'] = 'http://localhost/ucs';
// UCS server version
$sysconf['ucs']['serverversion'] = 2;
// node ID
$sysconf['ucs']['id'] = 'slims-node';
// default is s0beautifulday
$sysconf['ucs']['password'] = '2325f677e21c1613909c953eb03c57352259cc5d';
// node name
$sysconf['ucs']['name'] = 'SLiMS Library';

/**
 * Z39.50 copy cataloguing sources
 */
$sysconf['z3950_max_result'] = 50;
$sysconf['z3950_source'][1] = array('uri' => 'z3950.loc.gov:7090/voyager', 'name' => 'Library of Congress Voyager');
$sysconf['z3950_SRU_source'][1] = array('uri' => 'http://z3950.loc.gov:7090/voyager', 'name' => 'Library of Congress SRU Voyager');

/**
 * MARC copy cataloguing sources
 */
$sysconf['marc_SRU_source'][1] = array('uri' => 'https://opac.perpusnas.go.id/sru.aspx', 'name' => 'Perpustakaan Nasional RI');


/**
 * User and member login method
 */
$sysconf['auth']['user']['method'] = 'native'; // method can be 'native' or 'LDAP'
$sysconf['auth']['member']['method'] = 'native'; // for library member, method can be 'native' or 'LDAP'
/**
 * LDAP Specific setting for User
 */
if (($sysconf['auth']['user']['method'] === 'LDAP') OR ($sysconf['auth']['member']['method'] === 'LDAP')) {
  $sysconf['auth']['user']['ldap_server'] = '127.0.0.1'; // LDAP server
  $sysconf['auth']['user']['ldap_base_dn'] = 'ou=slims,dc=diknas,dc=go,dc=id'; // LDAP base DN
  $sysconf['auth']['user']['ldap_suffix'] = ''; // LDAP user suffix
  $sysconf['auth']['user']['ldap_bind_dn'] = 'uid=#loginUserName,'.$sysconf['auth']['user']['ldap_base_dn']; // Binding DN
  $sysconf['auth']['user']['ldap_port'] = null; // optional LDAP server connection port, use null or false for default
  $sysconf['auth']['user']['ldap_options'] = array(
      array(LDAP_OPT_PROTOCOL_VERSION, 3),
      array(LDAP_OPT_REFERRALS, 0)
      ); // optional LDAP server options
  $sysconf['auth']['user']['ldap_search_filter'] = '(|(uid=#loginUserName)(cn=#loginUserName*))'; // LDAP search filter, #loginUserName will be replaced by the real login name
  $sysconf['auth']['user']['userid_field'] = 'uid'; // LDAP field for username
  $sysconf['auth']['user']['fullname_field'] = 'cn'; // LDAP field for full name
  $sysconf['auth']['user']['mail_field'] = 'mail'; // LDAP field for e-mail
  /**
   * LDAP Specific setting for member
   * By default same as User
   */
  $sysconf['auth']['member']['ldap_server'] = &$sysconf['auth']['user']['ldap_server']; // LDAP server
  $sysconf['auth']['member']['ldap_base_dn'] = &$sysconf['auth']['user']['ldap_base_dn']; // LDAP base DN
  $sysconf['auth']['member']['ldap_suffix'] = &$sysconf['auth']['user']['ldap_suffix']; // LDAP user suffix
  $sysconf['auth']['member']['ldap_bind_dn'] = &$sysconf['auth']['user']['ldap_bind_dn']; // Binding DN
  $sysconf['auth']['member']['ldap_port'] = &$sysconf['auth']['user']['ldap_port']; // optional LDAP server connection port, use null or false for default
  $sysconf['auth']['member']['ldap_options'] = &$sysconf['auth']['user']['ldap_options']; // optional LDAP server options
  $sysconf['auth']['member']['ldap_search_filter'] = &$sysconf['auth']['user']['ldap_search_filter']; // LDAP search filter, #loginUserName will be replaced by the real login name
  $sysconf['auth']['member']['userid_field'] = &$sysconf['auth']['user']['userid_field']; // LDAP field for username
  $sysconf['auth']['member']['fullname_field'] = &$sysconf['auth']['user']['fullname_field']; // LDAP field for full name
  $sysconf['auth']['member']['mail_field'] = &$sysconf['auth']['user']['mail_field']; // LDAP field for e-mail
}

/**
 * BIBLIO INDEXING
 */
$sysconf['index']['type'] = 'index'; // value can be 'default', 'index' OR 'sphinx'
$sysconf['index']['sphinx_opts'] = array(
    'host' => '127.0.0.1',
    'port' => 9312,
    'index' => 'slims', // name of index in sphinx.conf
	'mode' => null, 'timeout' => 0, 'filter' => '@last_update desc',
	'filtervals' => array(), 'groupby' => null, 'groupsort' => null,
	'sortby' => null, 'sortexpr' => null, 'distinct' => 'biblio_id',
	'select' => null, 'limit' => 20,
    'max_limit' => 100000, // must be less or same with max_matches in sphinx.conf
	'ranker' => null);

$sysconf['index']['engine']['enable'] = FALSE;
$sysconf['index']['engine']['type'] = 'es'; // value can be 'solr' OR 'es' for ElasticSearch
$sysconf['index']['engine']['solr_opts'] = array(
    'host' => 'http://172.17.0.4',
    'port' => 8983,
    'collection' => 'slims' // name of collection in Solr
  );

$sysconf['index']['engine']['es_opts'] = array(
  'hosts' => ['localhost:9200'],
  'index' => 'slims' // name of index in ElasticSearch
);

/**
 * Maximum biblio mark for member
 */
$sysconf['max_biblio_mark'] = 20;

// Thumbnail Generator
$sysconf['tg']['relative_url'] = '../../';
$sysconf['tg']['docroot'] = ''; #usually use this in a virtual or alias based hosting
$sysconf['tg']['type'] = 'minigalnano'; # minigalnano

// IP based access limitation
$sysconf['ipaccess']['general'] = 'all'; // donot change this unless you know what you are doing
$sysconf['ipaccess']['opac'] = 'all'; // donot change this unless you know what you are doing
$sysconf['ipaccess']['opac-member'] = 'all'; // donot change this unless you know what you are doing
$sysconf['ipaccess']['smc'] = 'all';
$sysconf['ipaccess']['smc-bibliography'] = 'all';
$sysconf['ipaccess']['smc-circulation'] = 'all';
$sysconf['ipaccess']['smc-membership'] = 'all';
$sysconf['ipaccess']['smc-masterfile'] = 'all';
$sysconf['ipaccess']['smc-stocktake'] = 'all';
$sysconf['ipaccess']['smc-system'] = 'all';
$sysconf['ipaccess']['smc-reporting'] = 'all';
$sysconf['ipaccess']['smc-serialcontrol'] = 'all';

// OAI-PMH settings
$sysconf['OAI']['enable'] = true;
$sysconf['OAI']['identifierPrefix'] = 'oai:slims/';
$sysconf['OAI']['Identify']['baseURL'] = 'http://'.@$_SERVER['SERVER_NAME'].':'.@$_SERVER['SERVER_PORT'].SWB.'oai.php';
$sysconf['OAI']['Identify']['repositoryName'] = 'SLiMS Senayan Library Management System Repository';
$sysconf['OAI']['Identify']['adminEmail'] = 'admin@slims.web.id';
$sysconf['OAI']['Identify']['granularity'] = 'YYYY-MM-DDThh:mm:ssZ';
$sysconf['OAI']['Identify']['deletedRecord'] = 'transient';
$sysconf['OAI']['Identify']['metadataPolicy'] = '';
$sysconf['OAI']['ListRecords']['RecordPerSet'] = '100';
$sysconf['OAI']['MetadataFormats']['Dublin Core'] = array(
  'oai_prefix' => 'oai_dc',
  'schema_xsd' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
  'namespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/');

// Search clustering
$sysconf['enable_search_clustering'] = false;

// comment
$sysconf['comment']['enable'] =  true;

// social apps sharing
$sysconf['social_shares'] = true;

// social media for user and member
$sysconf['social']['fb'] = 'Facebook';
$sysconf['social']['tw'] = 'Twitter';
$sysconf['social']['li'] = 'LinkedIn';
$sysconf['social']['rd'] = 'Reddit';
$sysconf['social']['pn'] = 'Pinterest';
$sysconf['social']['gp'] = 'Google Plus+';
$sysconf['social']['yt'] = 'YouTube';
$sysconf['social']['bl'] = 'Blog';
$sysconf['social']['ym'] = 'Yahoo! Messenger';

/* CHATTING SYSTEM */
$sysconf['chat_system']['enabled']    	= false;
$sysconf['chat_system']['vendors']    	= 'phpwebscoketchat';
$sysconf['chat_system']['opac']       	= false;
$sysconf['chat_system']['librarian']  	= false;
$sysconf['chat_system']['server']  		= '127.0.0.1';
$sysconf['chat_system']['server_port']  = 9300;

/* NEWS */
$sysconf['news']['num_each_page'] = 10;

/* LIBRARY MAP COORDINATES */
$sysconf['location']['lat'] = -7.977000;
$sysconf['location']['long'] = 112.634025;

/* CHART */
$sysconf['chart']['mode'] = 'chartjs'; // plot or chartjs. default is plot
$sysconf['admin_home']['mode'] = 'dashboard'; // set as 'default' or 'dashboard' mode

// check if session is auto started and then destroy it
if ($is_auto = @ini_get('session.auto_start')) { define('SESSION_AUTO_STARTED', $is_auto); }
if (defined('SESSION_AUTO_STARTED')) { @session_destroy(); }

// Check database config
if (!file_exists(SB.'config'.DS.'database.php')) {
  // backward compatibility if upgrade process from `git pull`
  if (file_exists(SB.'config'.DS.'sysconfig.local.inc.php')) {
    \SLiMS\Config::create('database', function($filename){
      // get last database connection
      include SB.'config'.DS.'sysconfig.local.inc.php';
      $source = file_get_contents(SB.'config'.DS.'database.sample.php');
      $params = [['_DB_HOST_','_DB_NAME_','_DB_PORT_','_DB_USER_','_DB_PASSWORD_'],[DB_HOST,DB_NAME,DB_PORT,DB_USERNAME,DB_PASSWORD], $source];
      return str_replace(...$params);
    });
  } else {
    // Redirect to installer
    header('location: ' . SWB . 'install/index.php');
    exit;
  }
}

/* DATABASE RELATED */
$dbs = \SLiMS\DB::getInstance('mysqli');

/* Force UTF-8 for MySQL connection */
$dbs->query('SET NAMES \'utf8\'');

// load global settings from database. Uncomment below lines if you dont want to load it
utility::loadSettings($dbs);

// check for user language selection if we are not in admin areas
if (stripos($_SERVER['PHP_SELF'], '/admin') === false) {
    if (isset($_GET['select_lang'])) {
        $select_lang = trim(strip_tags($_GET['select_lang']));
        // delete previous language cookie
        if (isset($_COOKIE['select_lang'])) {
            #@setcookie('select_lang', $select_lang, time()-14400, SWB);
            #@setcookie('select_lang', $select_lang, time()-14400, SWB, "", FALSE, TRUE);

            @setcookie('select_lang', $select_lang, [
                'expires' => time()-14400,
                'path' => SWB,
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            

        }
        // create language cookie
        #@setcookie('select_lang', $select_lang, time()+14400, SWB);
        #@setcookie('select_lang', $select_lang, time()+14400, SWB, "", FALSE, TRUE);

        @setcookie('select_lang', $select_lang, [
            'expires' => time()+14400,
            'path' => SWB,
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $sysconf['default_lang'] = $select_lang;



        //reload page on change language
        header("location:index.php");
        
    } else if (isset($_COOKIE['select_lang'])) {
        $sysconf['default_lang'] = trim(strip_tags($_COOKIE['select_lang']));
    }
    // set back to en_US on XML
    if (isset($_GET['resultXML']) OR isset($_GET['inXML'])) {
        $sysconf['default_lang'] = 'en_US';
    }
}

// Apply language settings
require LANG.'localisation.php';

// template info config
if (!file_exists($sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/tinfo.inc.php')) {
  $sysconf['template']['base'] = 'php'; /* html OR php */
} else {
  require $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/tinfo.inc.php';
}

// admin template info config
if (file_exists($sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/tinfo.inc.php')) {
  require $sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/tinfo.inc.php';
}

/* Load balancing environment */
$sysconf['load_balanced_env'] = false;
$sysconf['load_balanced_source_ip'] = 'HTTP_X_FORWARDED_FOR';

// visitor limitation
$sysconf['enable_counter_by_ip'] = true;
$sysconf['allowed_counter_ip'] = ['127.0.0.1'];
$sysconf['enable_visitor_limitation']     = false; // "true" or "false"
$sysconf['time_visitor_limitation']       = 60; // in minute

/* maximum insert batch */
$sysconf['max_insert_batch'] = 100;

/* Random static file version for production mode */
$sysconf['static_file_version'] = 444981076;

// Http Option
$sysconf['http'] = [
  // SLiMS by default use Guzzle as Http client. You
  // can provide your config.
  'client' => [
    // verify ssl
    'verify' => true,
    // in seconds
    'timeout' => 60 
  ],
  'cache' => [
    'lifetime' => 300 // in seconds
  ]
];

// Database backup
$sysconf['database_backup'] = [
  // show reminder message at admin
  // dashboard
  'reminder' => true,

  // backup data automatically when
  // super admin first login in to SLiMS
  'auto' => false,

  // Backup options
  // SLiMS use Ifsnop\Mysqldump library.
  'options' => [
      'compress' => \Ifsnop\Mysqldump\Mysqldump::NONE,
      'no-data' => false,
      'add-drop-table' => true,
      'single-transaction' => true,
      'lock-tables' => true,
      'add-locks' => false,
      'extended-insert' => false,
      'disable-keys' => true,
      'skip-triggers' => false,
      'add-drop-trigger' => true,
      'routines' => true,
      'databases' => false,
      'add-drop-database' => false,
      'hex-blob' => true,
      'no-create-info' => false,
      'where' => '',
      /**
       * an option for definer state in trigger query. 
       * For some case, user had bad experience 
       * when they move their SLiMS database to other database 
       * machine without same privileged user as trigger definer.
       */
      'skip-definer' => true
  ]
];

// load global settings again for override tinfo setting
utility::loadSettings($dbs);

/**
 * Peer to peer server config
 */
$sysconf['p2pserver'][1] = array('uri' => \SLiMS\Url::getSlimsBaseUri(), 'name' => $sysconf['library_name']);

/* AUTHORITY TYPE */
$sysconf['authority_type']['p'] = __('Personal Name');
$sysconf['authority_type']['o'] = __('Organizational Body');
$sysconf['authority_type']['c'] = __('Conference');

/* SUBJECT/AUTHORITY TYPE */
$sysconf['subject_type']['t'] = __('Topic');
$sysconf['subject_type']['g'] = __('Geographic');
$sysconf['subject_type']['n'] = __('Name');
$sysconf['subject_type']['tm'] = __('Temporal');
$sysconf['subject_type']['gr'] = __('Genre');
$sysconf['subject_type']['oc'] = __('Occupation');

/* AUTHORITY LEVEL */
$sysconf['authority_level'][1] = __('Primary Author');
$sysconf['authority_level'][2] = __('Additional Author');
$sysconf['authority_level'][3] = __('Editor');
$sysconf['authority_level'][4] = __('Translator');
$sysconf['authority_level'][5] = __('Director');
$sysconf['authority_level'][6] = __('Producer');
$sysconf['authority_level'][7] = __('Composer');
$sysconf['authority_level'][8] = __('Illustrator');
$sysconf['authority_level'][9] = __('Creator');
$sysconf['authority_level'][10] = __('Contributor');

// system user type
$sysconf['system_user_type'][1] = __('Librarian');
$sysconf['system_user_type'][2] = __('Senior Librarian');
$sysconf['system_user_type'][3] = __('Library Staff');

// redirect to mobile template on mobile mode
if (defined('LIGHTWEIGHT_MODE') AND !isset($_COOKIE['FULLSITE_MODE']) AND isset($sysconf['template']['responsive']) && (bool)$sysconf['template']['responsive'] === false) {
  $sysconf['template']['theme'] = 'lightweight';
  $sysconf['template']['css'] = $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/style.css';
  $sysconf['enable_xml_detail'] = false;
  $sysconf['enable_xml_result'] = false;
}

/* new log system */
$sysconf['log']['biblio'] = TRUE;

// REST Api
$sysconf['api']['version'] = 1;

$sysconf['visitor_lang'] = 'hi-IN'; // Please visit this URL for voice mode - https://developer.mozilla.org/en-US/docs/Web/API/SpeechSynthesis/getVoices

$sysconf['always_user_login'] = true;

/* new advanced system log - still experimental */
$sysconf['log']['adv']['enabled'] = FALSE;
$sysconf['log']['adv']['handler'] = 'fs'; # 'fs' for filesystem, 'es' for elasticsearch
# for filesystem
$sysconf['log']['adv']['path'] = '/var/www/logs';
# for elasticsearch
$sysconf['log']['adv']['host'] = 'localhost:9200';
$sysconf['log']['adv']['index'] = 'slims_logs';

// load helper
require_once LIB . "helper.inc.php";

// set default timezone
// for a list of timezone, please see PHP Manual at "List of Supported Timezones" section
// https://www.php.net/manual/en/timezones.php
@date_default_timezone_set(config('timezone', 'Asia/Jakarta'));

// set real client ip address if SLiMS behind a reverse proxy
if ((bool)$sysconf['load_balanced_env']) ip()->setSourceRemoteIp($sysconf['load_balanced_source_ip']);

// load all Plugins
$sysconf['max_plugin_upload'] = 5000;
\SLiMS\Plugins::getInstance()->loadPlugins();

// Captcha factory
\SLiMS\Captcha\Factory::operate();

// Sanitize incoming data
$sanitizer = \SLiMS\Sanitizer::fromGlobal(config('custom_sanitizer_options', [
  'get' => $_GET,
  'server' => $_SERVER,
  'post' => $_POST
]));