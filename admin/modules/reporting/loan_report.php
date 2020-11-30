<?php
/**
 * Library loan general report
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
$table->table_attr = 'class="s-table table table-bordered mb-0"';

// total number of loan transaction
$report_q = $dbs->query('SELECT COUNT(loan_id) FROM loan');
$report_d = $report_q->fetch_row();
$loan_report[__('Total Loan')] = $report_d[0]??0;

// total number of loan transaction by GMD/medium
$report_q = $dbs->query('SELECT gmd_name, COUNT(loan_id) FROM loan AS l
    INNER JOIN item AS i ON l.item_code=i.item_code
    INNER JOIN biblio AS b ON i.biblio_id=b.biblio_id
    INNER JOIN mst_gmd AS gmd ON b.gmd_id=gmd.gmd_id
    GROUP BY b.gmd_id ORDER BY COUNT(loan_id) DESC');

$report_d = '<div class="chartLink"><a class="btn btn-success notAJAX openPopUp" href="'.MWB.'reporting/charts_report.php?chart=total_loan_gmd" width="700" height="470" title="'.__('Total Loan By GMD/Medium').'">'.__('Show in chart/plot').'</a></div>';
$stat_d = '';
while ($data = $report_q->fetch_row()) {
    $stat_d .= $data[0] . ' (<strong>'.$data[1].'</strong>),';
}
$report_d .= substr($stat_d,0,-1);
$loan_report[__('Total Loan By GMD/Medium')] = $report_d;

// total number of loan transaction by Collection type
$report_q = $dbs->query('SELECT coll_type_name, COUNT(loan_id) FROM loan AS l
    INNER JOIN item AS i ON l.item_code=i.item_code
    INNER JOIN mst_coll_type AS ct ON i.coll_type_id=ct.coll_type_id
    GROUP BY i.coll_type_id ORDER BY COUNT(loan_id) DESC');

$report_d = '<div class="chartLink"><a class="btn btn-success notAJAX openPopUp" href="'.MWB.'reporting/charts_report.php?chart=total_loan_colltype" width="700" height="470" title="'.__('Total Loan By Collection Type').'">'.__('Show in chart/plot').'</a></div>';
$stat_d = '';
while ($data = $report_q->fetch_row()) {
    $stat_d .= $data[0].' (<strong>'.$data[1].'</strong>),';
}
$report_d .= substr($stat_d,0,-1);
$loan_report[__('Total Loan By Collection Type')] = $report_d;

// total number of loan transaction
$report_q = $dbs->query('SELECT COUNT(loan_id)
    FROM loan
    GROUP BY member_id, loan_date
    ORDER BY `COUNT(loan_id)` DESC');
$report_d = $report_q->num_rows;
$loan_report[__('Total Loan Transactions')] = $report_d;
$peak_transaction_data = $report_q->fetch_row();

// transaction average per day
$total_loan_days_query = $dbs->query('SELECT DISTINCT loan_date FROM loan');
$total_loan_days = $total_loan_days_query->num_rows;
$loan_report[__('Transaction Average (Per Day)')] = $total_loan_days>0?@ceil($loan_report[__('Total Loan Transactions')]/$total_loan_days):0;

// peak transaction
$loan_report[__('Total Peak Transaction')] = $peak_transaction_data[0]??0;

// total members having loans
$report_q = $dbs->query('SELECT DISTINCT member_id FROM loan');
$report_d = $report_q->num_rows;
$loan_report[__('Members Already Had Loans')] = $report_d??0;

// total members having loans
// get total member that already not expired
$total_members_query = $dbs->query('SELECT COUNT(member_id) FROM member
    WHERE TO_DAYS(expire_date)>TO_DAYS(\''.date('Y-m-d').'\')');
$total_members_data = $total_members_query->fetch_row();
$loan_report[__('Members Never Have Loans Yet')] = $total_members_data[0]-$loan_report[__('Members Already Had Loans')];

// total overdued loand
$report_q = $dbs->query('SELECT COUNT(loan_id) FROM loan WHERE
    is_lent=1 AND is_return=0 AND TO_DAYS(due_date)<TO_DAYS(\''.date('Y-m-d').'\')');
$report_d = $report_q->fetch_row();
$loan_report[__('Total Overdued Loans')] = $report_d[0]??0;

// table header
$table->setHeader(array(__('Loan Data Summary')));
$table->table_header_attr = 'class="dataListHeader"';
$table->setCellAttr(0, 0, 'colspan="3"');
// initial row count
$row = 1;
foreach ($loan_report as $headings=>$report_d) {
    $table->appendTableRow(array($headings, $report_d));
    // set cell attribute
    $table->setCellAttr($row, 0, 'class="alterCell" valign="top" style="width: 350px;"');
    $table->setCellAttr($row, 1, 'class="alterCell" valign="top" style="width: auto;"');
    // add row count
    $row++;
}

// if we are in print mode
$page_title = __('Loan Report');
if (isset($_GET['print'])) {
    // load print template
    require_once SB.'admin/admin_template/printed.tpl.php';
    // write to file
    $file_write = @file_put_contents(REPBS.'loan_stat_print_result.html', $html_str);
    if ($file_write) {
        // open result in new window
        echo '<script type="text/javascript">
        top.$.colorbox({
            href: "'.SWB.FLS.'/'.REP.'/loan_stat_print_result.html", 
            height: 500,  
            width: 800,
            iframe : true,
            fastIframe: false,
            title: function(){return "'.$page_title.'";}
        })
        </script>';
    } else { utility::jsAlert(str_replace('{directory}', REPBS, __('ERROR! Loan Report failed to generate, possibly because {directory} directory is not writable'))); }
    exit();
}

?>
<div class="menuBox">
<div class="menuBoxInner statisticIcon">
	<div class="per_title">
	    <h2><?php echo __('Loan Report'); ?></h2>
  </div>
	<div class="infoBox">
    <form name="printForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="submitPrint" id="printForm" method="get" class="notAJAX" class="form-inline">
    <input type="hidden" name="print" value="true" /><input type="submit" value="<?php echo __('Download Report'); ?>" class="s-btn btn btn-default" />
    </form>
    <iframe name="submitPrint" style="display: none; visibility: hidden; width: 0; height: 0;"></iframe>
  </div>
</div>
</div>
<?php
echo $table->printTable();
/* loan report end */
