<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2020-11-01 09:39:18
 * @modify date 2020-11-01 12:53:42
 * @desc [description]
 */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

use Curl\Curl;

// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB.'admin/default/session.inc.php';
}

// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

// Curl
$curl = new Curl();

// Parse markdown format
function parseMarkDownBody($markDown)
{
    $markDown = str_replace(['Change log', 'Conflicts:'], '', $markDown);
    // markdown library
    require_once LIB.'parsedown/Parsedown.php';
    //Load Markdown File  
    $parsedown = new Parsedown();
    // set out
    return Parsedown::instance()->setBreaksEnabled(true)->text($markDown);
}

// Make date beauty
function beautyDate($date)
{
    $parse = date_parse($date);

    return date('d M Y', strtotime($parse['day'].'-'.$parse['month'].'-'.$parse['year']));
}

// dir Check
function dirCheck($path)
{
    if (!is_writable($path))
    {
        die('<div class="alert alert-danger" role="alert">Direktori '.$path.' tidak dapat ditulis!</div>');
    }
}

dirCheck($sysconf['slims_github_api']['cache_dir']);

// re-check progress
if (isset($_GET['action']) && $_GET['action'] == 'recheck')
{
    // remove cache
    @unlink($sysconf['slims_github_api']['cache_dir'].'releasesInfo'.$sysconf['slims_github_api']['cache_format']);
}

// Check cache
if (!file_exists($sysconf['slims_github_api']['cache_dir'].'releasesInfo'.$sysconf['slims_github_api']['cache_format']))
{
    // Get Branch
    $curl->get($sysconf['slims_github_api']['url'].'releases');
    $curl->setUserAgent($_SERVER['HTTP_USER_AGENT']);

    if ($curl->error) 
    {
        die('<div class="errorBox">Error with Http Code : '.$curl->error_code.'</div>');
    }

    @file_put_contents($sysconf['slims_github_api']['cache_dir'].'releasesInfo'.$sysconf['slims_github_api']['cache_format'], $curl->response);
}

// get json data
$getAllRelease = json_decode(file_get_contents($sysconf['slims_github_api']['cache_dir'].'releasesInfo'.$sysconf['slims_github_api']['cache_format']), TRUE);
?>
<!-- Start card -->
<div class="card">
  <div class="card-header">
    <h5 class="card-title">All <?=SENAYAN_VERSION?> release information.</h5>
  </div>
  <!-- Label -->
  <div class="card-body">
    <!-- Update informasi -->
    <div class="alert alert-info" role="alert">You are currently using version <?=SENAYAN_VERSION_TAG?></div>
    <?php foreach($getAllRelease AS $key => $realease): ?>
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <p class="card-text text-lg">
                    <span><?=__('Senayan Version')?> : <b><?=str_replace(' (Bulian)', '', SENAYAN_VERSION)?></b><span><br>
                    <span><?=__('Codename')?> : <b><?=str_replace(['SLiMS 9 ','(',')'], '', SENAYAN_VERSION)?></b><span><br>
                    <span><?=__('Version Number')?> : <b><?=$realease['tag_name']?></b></span><br>
                    <span><?=__('Release date')?> : <?=beautyDate($realease['published_at'])?></span><br>
                    <span><?=__('Changelog')?> : <b><?=parseMarkDownBody($realease['body'])?></b></span><br>
                    <span><?=__('More details')?> : <a target="_blank" href="<?=$realease['html_url']?>"><?=__('SLiMS GitHub page : ')?> <?=$realease['tag_name']?></a></span>
                </p>
            </div>
        </div>
        <hr style="height: 1px; background: gray">
    <?php endforeach; ?>
  </div>
</div>