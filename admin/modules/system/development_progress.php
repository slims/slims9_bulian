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
    @unlink($sysconf['slims_github_api']['cache_dir'].'develop'.$sysconf['slims_github_api']['cache_format']);
}

// Check cache
if (!file_exists($sysconf['slims_github_api']['cache_dir'].'develop'.$sysconf['slims_github_api']['cache_format']))
{
    // Get Branch
    $curl->get($sysconf['slims_github_api']['url'].'branches/develop');
    $curl->setUserAgent($_SERVER['HTTP_USER_AGENT']);

    if ($curl->error) 
    {
        die('<div class="errorBox">Error with Http Code : '.$curl->error_code.'</div>');
    }

    // Develop
    $branch_develop = json_decode($curl->response, TRUE);
    // Get commit in develop branch 
    $curl->get($sysconf['slims_github_api']['url'].'commits?per_page='.$sysconf['slims_github_api']['per_page'].'&sha='.$branch_develop['commit']['sha']);
    $curl->setUserAgent($_SERVER['HTTP_USER_AGENT']);
    // Check if not error
    if ($curl->error)
    {
        die('<div class="errorBox">Error with Http Code : '.$curl->error_code.'</div>');
    }
    else
    {
        // Store into cache
        @file_put_contents($sysconf['slims_github_api']['cache_dir'].'develop'.$sysconf['slims_github_api']['cache_format'], $curl->response);
    }
}

// Get data
$getCommits = json_decode(file_get_contents($sysconf['slims_github_api']['cache_dir'].'develop'.$sysconf['slims_github_api']['cache_format']), TRUE);

?>
<!-- Start card -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?= __('SLiMS Development Progress at branch ') ?> <b>Develop</b></h5>
        <a href="<?=$_SERVER['PHP_SELF']?>?action=recheck" onclick="toastr.info('<?=__('Please wait')?>', 'info')" class="float-right btn btn-primary block w-full text-white">Re-check Progress</a>
    </div>
    <!-- Label -->
    <div class="card-body">
        <!-- Update informasi -->
        <?=__('Below is the SLiMS development progress for the latest features and code fixes in the next release by SDC (Senayan Developer Community).')?>
        <?php foreach($getCommits AS $key => $item): ?>
            <div class="row" style="cursor:pointer" onclick="window.open('<?=$item['html_url']?>', '_blank')">
                <div class="col-md-12 col-lg-12">
                    <p class="card-text text-lg">
                        <div>
                            <div class="author">
                                <a target="_blank" href="<?=$item['author']['html_url']?>">
                                    <img src="<?=$item['author']['avatar_url']?>" style="width: 20px; height: 20px;" class="rounded-circle">
                                    <label class="text-bold ml-2"><?=$item['author']['login']?></label>
                                </a>
                                 <?=__('at').' '. beautyDate($item['commit']['author']['date'])?>
                            </div>
                            <div class="body pl-5" style="border-left: 1px solid #727272;margin-left: 10px;">
                                <b><?=parseMarkDownBody($item['commit']['message'])?></b>
                            </div>
                        </div>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>