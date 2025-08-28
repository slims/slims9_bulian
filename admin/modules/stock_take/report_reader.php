<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-04-15 11:21:16
 * @modify date 2023-04-15 11:38:32
 * @license GPLv3
 * @desc [description]
 */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

$reportPath = SB . 'files/reports/' . basename($_GET['file']);

$css = <<<HTML
<style>
@media print 
{
    #print {
        display: none !important;
    }
}
</style>
HTML;
ob_start();
if (file_exists($reportPath)) echo '<div class="p-3"><button id="print" class="btn btn-secondary mb-2 text-white" onclick="window.print()"><i class="text-white fa fa-print"></i>&nbsp;'.__('Print').'</button>'.file_get_contents($reportPath).'</div>';
else echo '<div class="alert alert-danger">Path ' . simbio_security::xssFree($reportPath) . ' not found!</div>';
$content = ob_get_clean();

include SB . 'admin/admin_template/printed_page_tpl.php';
exit;
