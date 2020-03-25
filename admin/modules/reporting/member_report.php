<?php
/**
 * Membership general report
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Modified by Waris Agung Widodo (ido.alit@gmail.com)
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
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
// start the session
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';

// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
  die('<div class="errorBox">' . __('You don\'t have enough privileges to access this area!') . '</div>');
}

$start = isset($_POST['start']) ? utility::filterData('start', 'post') : date('Y-m-d', strtotime("-1 year"));
$end = isset($_POST['end']) ? utility::filterData('end', 'post') : date('Y-m-d');
if (isset($_POST['doFilter'])) {
  echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\', {method: \'post\', addData: \'start=' . $start . '&end=' . $end . '\'});</script>';
  exit();
}

/* loan report */
$table = new simbio_table();
$table->table_attr = 'class="s-table table table-bordered mb-0"';

// total number of member
$report_q = $dbs->query('SELECT COUNT(member_id) FROM member');
$report_d = $report_q->fetch_row();
$loan_report[__('Total Registered Members')] = $report_d[0];

// total number of active member
$report_q = $dbs->query('SELECT COUNT(member_id) FROM member
    WHERE TO_DAYS(expire_date)>TO_DAYS(\'' . date('Y-m-d') . '\')');
$report_d = $report_q->fetch_row();
$loan_report[__('Total Active Member')] = $report_d[0];

// total number of active member by membership type
$report_q = $dbs->query('SELECT member_type_name, COUNT(member_id) FROM mst_member_type AS mt
    LEFT JOIN member AS m ON mt.member_type_id=m.member_type_id
    WHERE TO_DAYS(expire_date)>TO_DAYS(\'' . date('Y-m-d') . '\')
    GROUP BY m.member_type_id ORDER BY COUNT(member_id) DESC');

$report_d = '<div class="chartLink"><a class="btn btn-success notAJAX openPopUp" href="' . MWB . 'reporting/charts_report.php?chart=total_member_by_type" width="700" height="470" title="' . __('Total Members By Membership Type') . '">' . __('Show in chart/plot') . '</a></div>';;
$stat_data = '';
while ($data = $report_q->fetch_row()) {
  $stat_data .= $data[0] . ' (<strong>' . $data[1] . '</strong>), ';
}
$report_d = substr($stat_data, 0, -1);
$loan_report[__('Total Members By Membership Type')] = $report_d;

// total expired member
$report_q = $dbs->query('SELECT COUNT(member_id) FROM member
    WHERE TO_DAYS(\'' . date('Y-m-d') . '\')>TO_DAYS(expire_date)');
$report_d = $report_q->fetch_row();
$loan_report[__('Total Expired Member')] = $report_d[0];

// 10 most active member
$report_d = '';
$report_q = $dbs->query('SELECT m.member_name, m.member_id, COUNT(loan_id) FROM loan AS l
    INNER JOIN member AS m ON m.member_id=l.member_id
    WHERE TO_DAYS(expire_date)>TO_DAYS(\'' . date('Y-m-d') . '\')
    AND (loan_date BETWEEN \'' . $start . '\' AND \'' . $end . '\')
    GROUP BY l.member_id ORDER BY COUNT(loan_id) DESC LIMIT 10');
if ($report_q->num_rows > 0) {
  $_ = '__';
  if (!isset($_POST['print'])) {
    $report_d = <<<HTML
<form target="blindSubmit" method="post" class="form-inline chartLink" action="{$_SERVER['PHP_SELF']}">
  <div style="display: flex; justify-content: left; align-items: center; margin-bottom: 8px">
    <input value="{$start}" name="start" type="date" class="form-control" id="inlineFormInputName2" placeholder="From">
    <span style="padding: 0px 8px">To</span>
    <input value="{$end}" name="end" type="date" class="form-control" id="inlineFormInputName2" placeholder="From">
    <button name="doFilter" type="submit" class="btn btn-primary" style="margin: 0 0 0 8px">Submit</button>
  </div>
</form>
HTML;
  }

  $report_d .= '<ol class="p-2">';
  while ($data = $report_q->fetch_row()) {
    $report_d .= '<li>' . $data[0] . ' (' . $data[1] . ')</li>';
  }
  $report_d .= '</ol>';
} else {
    $report_d = '<i>'.__('There are no active members in this period').'</i>';
}
$loan_report[__('10 most active members')] = $report_d;

// table header
$table->setHeader(array(__('Membership Data Summary')));
$table->table_header_attr = 'class="dataListHeader"';
$table->setCellAttr(0, 0, 'colspan="2"');
// initial row count
$row = 1;
foreach ($loan_report as $headings => $report_d) {
  $table->appendTableRow(array($headings, $report_d));
  // set cell attribute
  $table->setCellAttr($row, 0, 'class="alterCell" valign="top" style="width: 350px;"');
  $table->setCellAttr($row, 1, 'class="alterCell2" valign="top" style="width: auto;"');
  // add row count
  $row++;
}

// if we are in print mode
$page_title = __('Membership Report');
if (isset($_POST['print'])) {
  // load print template
  require_once SB . 'admin/admin_template/printed.tpl.php';
  // write to file
  $file_write = @file_put_contents(REPBS . 'member_stat_print_result.html', $html_str);
  if ($file_write) {
    // open result in new window
    echo '<script type="text/javascript">
        top.$.colorbox({
            href: "' . SWB . FLS . '/' . REP . '/member_stat_print_result.html", 
            height: 500,  
            width: 800,
            iframe : true,
            fastIframe: false,
            title: function(){return "' . $page_title . '";}
        })
        </script>';
  } else {
    utility::jsAlert(str_replace('{directory}', REPBS, __('ERROR! Membership Report failed to generate, possibly because {directory} directory is not writable')));
  }
  exit();
}

?>
    <div class="menuBox">
        <div class="menuBoxInner statisticIcon">
            <div class="per_title">
                <h2><?php echo __('Membership Report'); ?></h2>
            </div>
            <div class="infoBox">
                <form name="printForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="submitPrint" id="printForm"
                      class="notAJAX" method="post" class="form-inline">
                    <input type="hidden" name="print" value="true"/>
                    <input type="hidden" name="start" value="<?= $start ;?>"/>
                    <input type="hidden" name="end" value="<?= $end ;?>"/>
                    <input type="submit" value="<?php echo __('Download Report'); ?>" class="s-btn btn btn-default"/>
                </form>
                <iframe name="submitPrint" style="display:none; visibility: hidden; width: 0; height: 0;"></iframe>
            </div>
        </div>
    </div>
<?php
echo $table->printTable();
/* loan report end */
