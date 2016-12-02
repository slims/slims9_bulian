<?php
/**
 * Membership general report
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';

// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

/* loan report */
$table = new simbio_table();
$table->table_attr = 'align="center" class="border" cellpadding="5" cellspacing="0"';

// total number of member
$report_q = $dbs->query('SELECT COUNT(member_id) FROM member');
$report_d = $report_q->fetch_row();
$loan_report[__('Total Registered Members')] = $report_d[0];

// total number of active member
$report_q = $dbs->query('SELECT COUNT(member_id) FROM member
    WHERE TO_DAYS(expire_date)>TO_DAYS(\''.date('Y-m-d').'\')');
$report_d = $report_q->fetch_row();
$loan_report[__('Total Active Member')] = $report_d[0];

// total number of active member by membership type
$report_q = $dbs->query('SELECT member_type_name, COUNT(member_id) FROM mst_member_type AS mt
    LEFT JOIN member AS m ON mt.member_type_id=m.member_type_id
    WHERE TO_DAYS(expire_date)>TO_DAYS(\''.date('Y-m-d').'\')
    GROUP BY m.member_type_id ORDER BY COUNT(member_id) DESC');
$report_d = '<div class="chartLink"><a class="notAJAX openPopUp" href="'.MWB.'reporting/charts_report.php?chart=total_member_by_type" width="700" height="470" title="'.__('Total Members By Membership Type').'">'.__('Show in chart/plot').'</a></div>';;
while ($data = $report_q->fetch_row()) {
    $report_d .= '<strong>'.$data[0].'</strong> : '.$data[1].', ';
}
$loan_report[__('Total Members By Membership Type')] = $report_d;

// total expired member
$report_q = $dbs->query('SELECT COUNT(member_id) FROM member
    WHERE TO_DAYS(\''.date('Y-m-d').'\')>TO_DAYS(expire_date)');
$report_d = $report_q->fetch_row();
$loan_report[__('Total Expired Member')] = $report_d[0];

// 10 most active member
$report_q = $dbs->query('SELECT m.member_name, m.member_id, COUNT(loan_id) FROM loan AS l
    INNER JOIN member AS m ON m.member_id=l.member_id
    WHERE TO_DAYS(expire_date)>TO_DAYS(\''.date('Y-m-d').'\')
    GROUP BY l.member_id ORDER BY COUNT(loan_id) DESC LIMIT 10');
$report_d = '<ul>';
while ($data = $report_q->fetch_row()) {
    $report_d .= '<li>'.$data[0].' ('.$data[1].')</li>';
}
$loan_report[__('10 most active members')] = $report_d;

// table header
$table->setHeader(array(__('Membership Data Summary')));
$table->table_header_attr = 'class="dataListHeader"';
$table->setCellAttr(0, 0, 'colspan="3"');
// initial row count
$row = 1;
foreach ($loan_report as $headings=>$report_d) {
    $table->appendTableRow(array($headings, ':', $report_d));
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
    $html_str .= '<html><head><title>'.$sysconf['library_name'].' '.__('Membership Report').'</title>';
    $html_str .= '<style type="text/css">'."\n";
    $html_str .= 'body {padding: 0.2cm}'."\n";
    $html_str .= 'body * {color: black; font-size: 11pt;}'."\n";
    $html_str .= 'table {border: 1px solid #000;}'."\n";
    $html_str .= '.dataListHeader {background-color: #000; color: white; font-weight: bold;}'."\n";
    $html_str .= '.alterCell {border-bottom: 1px solid #666; background-color: #CCCCCC;}'."\n";
    $html_str .= '.alterCell2 {border-bottom: 1px solid #666; background-color: #FFFFFF;}'."\n";
    $html_str .= '</style>'."\n";
    $html_str .= '</head>';
    $html_str .= '<body>'."\n";
    $html_str .= '<h3>'.$sysconf['library_name'].' - '.__('Membership Report').'</h3>';
    $html_str .= '<hr size="1" />'."\n";
    $html_str .= $table->printTable();
    $html_str .= '<script type="text/javascript">self.print();</script>'."\n";
    $html_str .= '</body></html>'."\n";
    // write to file
    $file_write = @file_put_contents(REPBS.'member_stat_print_result.html', $html_str);
    if ($file_write) {
        // open result in new window
        echo '<script type="text/javascript">top.$.colorbox({href: "'.SWB.FLS.'/'.REP.'/member_stat_print_result.html", width: 800, height: 500})</script>';
    } else { utility::jsAlert(str_replace('{directory}', REPBS, __('ERROR! Membership Report failed to generate, possibly because {directory} directory is not writable'))); }
    exit();
}

?>
<fieldset class="menuBox">
<div class="menuBoxInner statisticIcon">
	<div class="per_title">
	    <h2><?php echo __('Membership Report'); ?></h2>
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
/* loan report end */
