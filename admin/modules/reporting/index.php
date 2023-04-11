<?php
/**
 * Collection general report
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com
 *
 * Copyright (C) 2008 Arie Nugraha (dicarve@yahoo.com)
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

/* Reporting section */


// key to authentication
define('INDEX_AUTH', '1');

if (!defined('SB')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB.'admin/default/session.inc.php';
}

// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';

// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

/* collection statistic */
$table = new simbio_table();
$table->table_attr = 'class="s-table table table-bordered mb-0"';

// total number of titles
$stat_query = $dbs->query('SELECT COUNT(biblio_id) FROM biblio');
$total_title_all = $stat_query->fetch_row()[0]??'?';
$collection_stat[__('Total Titles')] = $total_title_all.' '.__(' (including titles that still don\'t have items yet)');

// total number of titles
$stat_query = $dbs->query('SELECT DISTINCT biblio.biblio_id FROM biblio INNER JOIN item ON biblio.biblio_id = item.biblio_id');
$stat_data = $stat_query->num_rows;
$collection_stat[__('Total Titles with items')] = $stat_data.__(' (only titles that have items)');
$collection_stat[__('Total Titles without items')] = ($total_title_all - $stat_data).__(' (only titles that haven\'t items)');

// total number of items
$stat_query = $dbs->query('SELECT item.item_code FROM item,biblio WHERE item.biblio_id=biblio.biblio_id');
$stat_data = $stat_query->num_rows;
$collection_stat[__('Total Items/Copies')] = $stat_data;

// total number of checkout items
$stat_query = $dbs->query('SELECT COUNT(item_code) FROM loan_history
    WHERE is_lent=1 AND is_return=0');
$stat_data = $stat_query->fetch_row();
$collection_stat[__('Total Checkout Items')] = $stat_data[0];

// total number of items in library
$collection_stat[__('Total Items In Library')] = $collection_stat[__('Total Items/Copies')]-$collection_stat[__('Total Checkout Items')];

// total titles by GMD/medium
$stat_query = $dbs->query('SELECT gmd_name, COUNT(biblio_id) AS total_titles
    FROM `biblio` AS b
    INNER JOIN mst_gmd AS gmd ON b.gmd_id = gmd.gmd_id
    GROUP BY b.gmd_id HAVING total_titles>0 ORDER BY COUNT(biblio_id) DESC');

$stat_data = '<div class="chartLink"><a class="btn btn-success notAJAX openPopUp" href="'.MWB.'reporting/charts_report.php?chart=total_title_gmd" width="700" height="470" title="'.__('Total Titles By Medium/GMD').'">'.__('Show in chart/plot').'</a></div>';
while ($data = $stat_query->fetch_row()) {
    $stat_data .= $data[0].' (<strong>'.$data[1].'</strong>), ';
}
$stat_data = substr($stat_data,0,-1);
$collection_stat[__('Total Titles By Medium/GMD')] = $stat_data;

// total items by Collection Type
$stat_query = $dbs->query('SELECT coll_type_name, COUNT(item_id) AS total_items
    FROM `item` AS i
    INNER JOIN mst_coll_type AS ct ON i.coll_type_id = ct.coll_type_id
    GROUP BY i.coll_type_id
    HAVING total_items >0
    ORDER BY COUNT(item_id) DESC');

$stat_data = '<div class="chartLink"><a class="btn btn-success notAJAX openPopUp" href="'.MWB.'reporting/charts_report.php?chart=total_title_colltype" width="700" height="470" title="'.__('Total Items By Collection Type').'">'.__('Show in chart/plot').'</a></div>';
while ($data = $stat_query->fetch_row()) {
    $stat_data .= $data[0].' (<strong>'.$data[1].'</strong>), ';
}
$stat_data = substr($stat_data,0,-1);
$collection_stat[__('Total Items By Collection Type')] = $stat_data;

// popular titles
$stat_query = $dbs->query('SELECT max(title), max(biblio_id) AS total_loans FROM `loan_history` WHERE member_id IS NOT NULL AND biblio_id IS NOT NULL
    GROUP BY biblio_id ORDER BY COUNT(loan_id) DESC LIMIT 10');
$stat_data = '<ol>';
if(!empty($stat_query->num_rows)){
    while ($data = $stat_query->fetch_row()) {
        $stat_data .= '<li>'.$data[0].'</li>';
    }
}
$stat_data .= '</ol>';
$collection_stat[__('10 Most Popular Titles')] = $stat_data;

// table header
$table->setHeader(array(__('Collection Statistic Summary')));
$table->table_header_attr = 'class="dataListHeader"';
$table->setCellAttr(0, 0, 'colspan="2"');
// initial row count
$row = 1;
foreach ($collection_stat as $headings=>$stat_data) {
    $table->appendTableRow(array($headings, $stat_data));
    // set cell attribute
    $table->setCellAttr($row, 0, 'class="alterCell" valign="top" style="width: 300px;"');
    $table->setCellAttr($row, 1, 'class="alterCell" valign="top" style="width: auto;"');
    // add row count
    $row++;
}

// if we are in print mode
$page_title = __('Collection Statistic Report');
if (isset($_GET['print'])) {
    // load print template
    require_once SB.'admin/admin_template/printed.tpl.php';
    // write to file
    $file_write = @file_put_contents(REPBS.'biblio_stat_print_result.html', $html_str);
    if ($file_write) {
        // open result in new window
        echo '<script type="text/javascript">
        top.$.colorbox({
            href: "'.SWB.FLS.'/'.REP.'/biblio_stat_print_result.html", 
            height: 500,  
            width: 800,
            iframe : true,
            fastIframe: false,
            title: function(){return "'.$page_title.'";}
        })
        </script>';
    } else { utility::jsAlert(str_replace('{directory}', REPBS, __('ERROR! Collection Statistic Report failed to generate, possibly because {directory} directory is not writable'))); }
    exit();
}

?>
<div class="menuBox">
    <div class="menuBoxInner statisticIcon">
        <div class="per_title">
        <h2><?php echo __('Collection Statistic'); ?></h2>
    </div>
    <div class="infoBox">
        <form name="printForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="submitPrint" id="printForm" class="notAJAX" method="get">
            <input type="hidden" name="print" value="true" />
            <input type="submit" value="<?php echo __('Download Report'); ?>" class="s-btn btn btn-default" />
        </form>
    </div>
    <iframe name="submitPrint" style="display: none; visibility: hidden; width: 0; height: 0;"></iframe>
    </div>
</div>
<?php
echo $table->printTable();
/* collection statistic end */
