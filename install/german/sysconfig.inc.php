<?php
/**
 * SENAYAN application global file configuration
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com), Hendro Wicaksono (hendrowicaksono@yahoo.com), Wardiyono (wynerst@gmail.com)
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
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die();
}

// be sure that magic quote is off
@ini_set('magic_quotes_gpc', false);
@ini_set('magic_quotes_runtime', false);
@ini_set('magic_quotes_sybase', false);
// force disabling magic quotes
if (get_magic_quotes_gpc()) {
    function stripslashes_deep($value)
    {
        $value = is_array($value)?array_map('stripslashes_deep', $value):stripslashes($value);
        return $value;
    }

    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}
// turn off all error messages for security reason
@ini_set('display_errors', true);
// check if safe mode is on
if ((bool) ini_get('safe_mode')) {
    define('SENAYAN_IN_SAFE_MODE', 1);
}

// senayan version
define('SENAYAN_VERSION', 'Senayan3-stable11');

// senayan session cookie name
define('SENAYAN_SESSION_COOKIES_NAME', 'SenayanAdmin');

// senayan base dir
define('SENAYAN_BASE_DIR', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

// absolute path for simbio platform
define('SIMBIO_BASE_DIR', SENAYAN_BASE_DIR.'simbio2'.DIRECTORY_SEPARATOR);

// senayan library base dir
define('LIB_DIR', SENAYAN_BASE_DIR.'lib'.DIRECTORY_SEPARATOR);

// document, member and barcode images base dir
define('IMAGES_DIR', 'images');
define('IMAGES_BASE_DIR', SENAYAN_BASE_DIR.IMAGES_DIR.DIRECTORY_SEPARATOR);

// library automation module base dir
define('MODULES_DIR', 'modules');
define('MODULES_BASE_DIR', SENAYAN_BASE_DIR.'admin'.DIRECTORY_SEPARATOR.MODULES_DIR.DIRECTORY_SEPARATOR);

// files upload dir
define('FILES_DIR', 'files');
define('FILES_UPLOAD_DIR', SENAYAN_BASE_DIR.FILES_DIR.DIRECTORY_SEPARATOR);

// repository dir
define('REPO_DIR', 'repository');
define('REPO_BASE_DIR', SENAYAN_BASE_DIR.REPO_DIR.DIRECTORY_SEPARATOR);

// file attachment dir
define('ATT_DIR', 'att');
define('FILE_ATT_DIR', FILES_UPLOAD_DIR.ATT_DIR);

// printed report dir
define('REPORT_DIR', 'reports');
define('REPORT_FILE_BASE_DIR', FILES_UPLOAD_DIR.REPORT_DIR.DIRECTORY_SEPARATOR);

// languages base dir
define('LANGUAGES_BASE_DIR', LIB_DIR.'lang'.DIRECTORY_SEPARATOR);

// senayan web doc root dir
$temp_senayan_web_root_dir = preg_replace('@admin.*@i', '', dirname($_SERVER['PHP_SELF']));
define('SENAYAN_WEB_ROOT_DIR', $temp_senayan_web_root_dir.(preg_match('@\/$@i', $temp_senayan_web_root_dir)?'':'/'));

// javascript library web root dir
define('JS_WEB_ROOT_DIR', SENAYAN_WEB_ROOT_DIR.'js/');

// library automation module web root dir
define('MODULES_WEB_ROOT_DIR', SENAYAN_WEB_ROOT_DIR.'admin/'.MODULES_DIR.'/');

// item status rules
define('NO_LOAN_TRANSACTION', 1);
define('SKIP_STOCK_TAKE', 2);

// command execution status
define('BINARY_NOT_FOUND', 127);
define('BINARY_FOUND', 1);
define('COMMAND_SUCCESS', 0);
define('COMMAND_FAILED', 2);

// simbio main class inclusion
require SIMBIO_BASE_DIR.'simbio.inc.php';
// simbio security class
require SIMBIO_BASE_DIR.'simbio_UTILS'.DIRECTORY_SEPARATOR.'simbio_security.inc.php';
// we must include utility library first
require LIB_DIR.'utility.inc.php';

// check if we are in mobile browser mode
if (utility::isMobileBrowser()) {
    define('LIGHTWEIGHT_MODE', 1);
}

/* AJAX SECURITY */
$sysconf['ajaxsec_user'] = 'ajax';
$sysconf['ajaxsec_passwd'] = 'secure';
$sysconf['ajaxsec_ip_enabled'] = 0;
$sysconf['ajaxsec_ip_allowed'] = '';

/* Custom base URL */
$sysconf['baseurl'] = '';

/* DATABASE CONNECTION config */
// database constant
// change below setting according to your database configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'senayandb');
define('DB_USERNAME', 'senayanuser');
define('DB_PASSWORD', 'password_senayanuser');
// we prefer to use mysqli extensions if its available
if (extension_loaded('mysqli')) {
    /* MYSQLI */
    $dbs = @new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
    if (mysqli_connect_error()) {
        die('<div style="border: 1px dotted #FF0000; color: #FF0000; padding: 5px;">Datenbankverbindung fehlgeschlagen. Bitte prüfen Sie ihre Konfiguration</div>');
    }
} else {
    /* MYSQL */
    // require the simbio mysql class
    include SIMBIO_BASE_DIR.'simbio_DB/mysql/simbio_mysql.inc.php';
    // make a new connection object that will be used by all applications
    $dbs = @new simbio_mysql(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
}

/* session login timeout in second */
$sysconf['session_timeout'] = 7200;

/* default application language */
$sysconf['default_lang'] = 'de_DE';

/* Force UTF-8 for MySQL connection and HTTP header */
header('Content-type: text/html; charset=UTF-8');
$dbs->query('SET NAMES \'utf8\'');

/* GUI Template config */
$sysconf['template']['dir'] = 'template';
$sysconf['template']['theme'] = 'default';
$sysconf['template']['css'] = $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/style.css';
#require $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/tinfo.inc.php';

/* ADMIN SECTION GUI Template config */
$sysconf['admin_template']['dir'] = 'admin_template';
$sysconf['admin_template']['theme'] = 'default';
$sysconf['admin_template']['css'] = $sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/style.css';

/* OPAC */
$sysconf['opac_result_num'] = 10;

/* Promote selected title(s) to homepage setting */
$sysconf['enable_promote_titles'] = false;
$sysconf['promote_first_emphasized'] = true;

/* Dynamic Content */
$sysconf['content']['allowable_tags'] = '<p><a><cite><code><em><strong><cite><blockquote><fieldset><legend>'
    .'<h3><hr><br><table><tr><td><th><thead><tbody><tfoot><div><span><img><object><param>';

/* XML */
$sysconf['enable_xml_detail'] = true;
$sysconf['enable_xml_result'] = true;

/* DATABASE BACKUP config */
// specify the full path of mysqldump binary
$sysconf['mysqldump'] = '/usr/bin/mysqldump';
// backup location (make sure it is accessible and rewritable to webserver!)
$sysconf['temp_dir'] = '/tmp';
$sysconf['backup_dir'] = FILES_UPLOAD_DIR.'backup'.DIRECTORY_SEPARATOR;

/* FILE DOWNLOAD */
$sysconf['allow_file_download'] = true;

/* BARCODE config */
// encoding
/*
EAN     8 or 13 EAN-Code
UPC     12-digit EAN
ISBN    isbn numbers (still EAN-13)
39      code 39
128     code 128
128C    code 128 (compact form for digits)
128B    code 128, full printable ascii
I25     interleaved 2 of 5
128RAW  Raw code 128
CBR     Codabars
MSI     MSI
PLS     Plesseys
93      code 93
*/
$sysconf['barcode_encoding'] = '128B';

/* QUICK RETURN */
$sysconf['quick_return'] = true;

/* LOAN LIMIT OVERRIDE */
$sysconf['loan_limit_override'] = false;

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
    '.ogg', '.mp3');

/* FILE ATTACHMENT MIMETYPES */
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

/* PRICE CURRENCIES SETTING */
$sysconf['currencies'] = array( array('0', 'NONE'), 'USD', 'Euro', 'DM', 'Pfund', 'Yen', 'Rupien');

/* RESERVE PERIODE (In Days) */
$sysconf['reserve_expire_periode'] = 7;

/* CONTENT */
$sysconf['library_name'] = 'Beispielbibliothek';
$sysconf['library_subname'] = 'Open Source Bibliotheksmanagementsystem';
$sysconf['page_footer'] = ' <strong>SENAYAN Library Automation</strong> - Pusat Informasi dan Humas Depdiknas RI - Released Under GNU GPL License';

/* HTTPS Setting */
$sysconf['https_enable'] = false;
$sysconf['https_port'] = 443;

/* Date Format Setting for OPAC */
$sysconf['date_format'] = 'd-m-Y'; /* Produce 31-12-2009 */
// $sysconf['date_format'] = 'd-M-Y'; /* Produce 31-Dec-2009 */

// load global settings from database. Uncomment below lines if you dont want to load it
utility::loadSettings($dbs);
// check for user language selection if we are not in admin areas
if (stripos($_SERVER['PHP_SELF'], '/admin') === false) {
    if (isset($_GET['select_lang'])) {
        $select_lang = trim($_GET['select_lang']);
        // delete previous language cookie
        if (isset($_COOKIE['select_lang'])) {
            @setcookie('select_lang', $select_lang, time()-14400, SENAYAN_WEB_ROOT_DIR);
        }
        // create language cookie
        @setcookie('select_lang', $select_lang, time()+14400, SENAYAN_WEB_ROOT_DIR);
        $sysconf['default_lang'] = $select_lang;
    } else if (isset($_COOKIE['select_lang'])) {
        $sysconf['default_lang'] = trim($_COOKIE['select_lang']);
    }
}
// Apply language settings
require LANGUAGES_BASE_DIR.'localisation.php';

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

// template info config
if (!file_exists($sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/tinfo.inc.php')) {
    $sysconf['template']['base'] = 'php'; /* html OR php */
} else {
    require $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/tinfo.inc.php';
}

#require $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/tinfo.inc.php';
// redirect to mobile template on mobile mode
if (defined('LIGHTWEIGHT_MODE') OR isset($_COOKIE['LIGHTWEIGHT_MODE'])) {
    $sysconf['template']['theme'] = 'lightweight';
    $sysconf['template']['css'] = $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/style.css';
    $sysconf['enable_xml_detail'] = false;
    $sysconf['enable_xml_result'] = false;
}
?>
