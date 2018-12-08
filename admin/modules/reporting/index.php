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
$table->table_attr = 'align="center" class="border" cellpadding="5" cellspacing="0"';

// total number of titles
$stat_query = $dbs->query('SELECT COUNT(biblio_id) FROM biblio');
$stat_data = $stat_query->fetch_row();
$collection_stat[__('Total Titles')] = $stat_data[0].' (including titles that still don\'t have items yet)';

// total number of titles
$stat_query = $dbs->query('SELECT DISTINCT biblio.biblio_id FROM biblio INNER JOIN item ON biblio.biblio_id = item.biblio_id');
$stat_data = $stat_query->num_rows;
$collection_stat[__('Total Titles with items')] = $stat_data.' (only titles that have items)';

// total number of items
$stat_query = $dbs->query('SELECT item.item_code FROM item,biblio WHERE item.biblio_id=biblio.biblio_id');
$stat_data = $stat_query->num_rows;
$collection_stat[__('Total Items/Copies')] = $stat_data;

// total number of checkout items
$stat_query = $dbs->query('SELECT COUNT(item_id) FROM item AS i
    LEFT JOIN loan AS l ON i.item_code=l.item_code
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
$stat_data = '<div class="chartLink"><a class="notAJAX openPopUp" href="'.MWB.'reporting/charts_report.php?chart=total_title_gmd" width="700" height="470" title="'.__('Total Titles By Medium/GMD').'">'.__('Show in chart/plot').'</a></div>';
while ($data = $stat_query->fetch_row()) {
    $stat_data .= '<strong>'.$data[0].'</strong> : '.$data[1];
    $stat_data .= ', ';
}
$collection_stat[__('Total Titles By Medium/GMD')] = $stat_data;

// total items by Collection Type
$stat_query = $dbs->query('SELECT coll_type_name, COUNT(item_id) AS total_items
    FROM `item` AS i
    INNER JOIN mst_coll_type AS ct ON i.coll_type_id = ct.coll_type_id
    GROUP BY i.coll_type_id
    HAVING total_items >0
    ORDER BY COUNT(item_id) DESC');
$stat_data = '<div class="chartLink"><a class="notAJAX openPopUp" href="'.MWB.'reporting/charts_report.php?chart=total_title_colltype" width="700" height="470" title="'.__('Total Items By Collection Type').'">'.__('Show in chart/plot').'</a></div>';
while ($data = $stat_query->fetch_row()) {
    $stat_data .= '<strong>'.$data[0].'</strong> : '.$data[1];
    $stat_data .= ', ';
}
$collection_stat[__('Total Items By Collection Type')] = $stat_data;

// popular titles
$stat_query = $dbs->query('SELECT b.title,b.biblio_id AS total_loans FROM `loan` AS l
    LEFT JOIN item AS i ON l.item_code=i.item_code
    LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
    GROUP BY b.biblio_id ORDER BY COUNT(l.loan_id) DESC LIMIT 10');
$stat_data = '<ul>';
while ($data = $stat_query->fetch_row()) {
    $stat_data .= '<li>'.$data[0].'</li>';
}
$stat_data .= '</ul>';
$collection_stat[__('10 Most Popular Titles')] = $stat_data;

// table header
$table->setHeader(array(__('Collection Statistic Summary')));
$table->table_header_attr = 'class="dataListHeader"';
$table->setCellAttr(0, 0, 'colspan="3"');
// initial row count
$row = 1;
foreach ($collection_stat as $headings=>$stat_data) {
    $table->appendTableRow(array($headings, ':', $stat_data));
    // set cell attribute
    $table->setCellAttr($row, 0, 'class="alterCell" valign="top" style="width: 170px;"');
    $table->setCellAttr($row, 1, 'class="alterCell" valign="top" style="width: 1%;"');
    $table->setCellAttr($row, 2, 'class="alterCell2" valign="top" style="width: auto;"');
    // add row count
    $row++;
}

// if we are in print mode
if (isset($_GET['print'])) {
    // html strings
    $html_str = '<!DOCTYPE html>';
    $html_str .= '<html><head><title>'.$sysconf['library_name'].' '.__('Collection Statistic Report').'</title>';
    $html_str .= '<style type="text/css">'."\n";
    $html_str .= 'body {padding: 0.2cm}'."\n";
    $html_str .= 'body * {color: black; font-size: 11pt;}'."\n";
    $html_str .= 'table {border: 1px solid #000000;}'."\n";
    $html_str .= '.dataListHeader {background-color: #000000; color: white; font-weight: bold;}'."\n";
    $html_str .= '.alterCell {border-bottom: 1px solid #666666; background-color: #CCCCCC;}'."\n";
    $html_str .= '.alterCell2 {border-bottom: 1px solid #666666; background-color: #FFFFFF;}'."\n";
    $html_str .= '</style>'."\n";
    $html_str .= '</head>';
    $html_str .= '<body>'."\n";
    $html_str .= '<h3>'.$sysconf['library_name'].' - '.__('Collection Statistic Report').'</h3>';
    $html_str .= '<hr size="1" />';
    $html_str .= $table->printTable();
    $html_str .= '<script type="text/javascript">self.print();</script>'."\n";
    $html_str .= '</body></html>';
    // write to file
    $file_write = @file_put_contents(REPBS.'biblio_stat_print_result.html', $html_str);
    if ($file_write) {
        // open result in new window
        echo '<script type="text/javascript">top.$.colorbox({href: "'.SWB.FLS.'/'.REP.'/biblio_stat_print_result.html", height: 800,  width: 500})</script>';
    } else { utility::jsAlert(str_replace('{directory}', REPBS, __('ERROR! Collection Statistic Report failed to generate, possibly because {directory} directory is not writable'))); }
    exit();
}

?>
<fieldset class="menuBox">
<div class="menuBoxInner statisticIcon">
	<div class="per_title">
	  <h2><?php echo __('Collection Statistic'); ?></h2>
  </div>
	<div class="infoBox">
    <form name="printForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="submitPrint" id="printForm" class="notAJAX" method="get" style="display: inline;">
    <input type="hidden" name="print" value="true" /><input type="submit" value="<?php echo __('Download Report'); ?>" class="button" />
    </form>
    <iframe name="submitPrint" style="visibility: hidden; width: 0; height: 0;"></iframe>
  </div>
</div>
</fieldset>
<?php
echo $table->printTable();
/* collection statistic end */
