<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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

/* Stock Take */

// key to authenticate
define('INDEX_AUTH', '1');

// check if this file included directly
if (!defined('REPECT_INCLUDE')) {
  // main system configuration
  require '../../../sysconfig.inc.php';
  // start the session
  require SB.'admin/default/session.inc.php';
  require SB.'admin/default/session_check.inc.php';
  include SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
  include SIMBIO.'simbio_DB/simbio_dbop.inc.php';
}
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-stocktake');

// privileges checking
$can_read = utility::havePrivilege('stock_take', 'r');
$can_write = utility::havePrivilege('stock_take', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

ob_start();
// check if there is any active stock take proccess
$stk_query = $dbs->query("SELECT * FROM stock_take WHERE is_active=1");
if ($stk_query->num_rows < 1) {
    echo '<div class="errorBox">'.__('NO stock taking proccess initialized yet!').'</div>';
} else {
    // get stock take data
    $stk_data = $stk_query->fetch_assoc();
    // check if this file included directly
    if (!defined('REPECT_INCLUDE') AND !isset($_GET['print'])) {
?>
    <fieldset class="menuBox">
      <div class="menuBoxInner reportIcon">
        <div class="per_title">
          <h2><?php echo __('Current Stock Take Report'); ?></h2>
        </div>
        <div class="sub_section">
          <form name="printForm" action="<?php echo $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']; ?>" target="submitPrint" id="printForm" method="get" style="display: inline;">
          <input type="hidden" name="print" value="true" /><input type="submit" value="<?php echo __('Download Report'); ?>" class="btn btn-default" />
          </form>
          <iframe name="submitPrint" style="visibility: hidden; width: 0; height: 0;"></iframe>
        </div>
      </div>
    </fieldset>
<?php
    }
    $table = new simbio_table();
    $table->table_attr = 'align="center" id="dataList" cellpadding="3" cellspacing="0"';
    // make an array for report table row
    $report_row[__('Stock Take Name')] = $stk_data['stock_take_name'];
    $report_row[__('Total Item Stock Taked')] = $stk_data['total_item_stock_taked'];
    // stock take item lost
    $report_row[__('Total Item Lost')] = intval($stk_data['total_item_lost']);
    if ($report_row[__('Total Item Lost')] < 1) {
        $report_row[__('Total Item Lost')] = __('None');
    }
    // stock take item on loan
    if (empty($stk_data['total_item_loan'])) {
        $report_row[__('Total Item On Loan')] = __('None');
    } else {
        $report_row[__('Total Item On Loan')] = $stk_data['total_item_loan'];
    }
    // stock take total checked item
    $checked_count = $stk_data['total_item_stock_taked']-$stk_data['total_item_lost'];
    $checked_procent = floor(($checked_count/$stk_data['total_item_stock_taked'])*100);
    $progress_bar = '<div style="height: 15px; border: 1px solid #999; background-color: red;"><div style="height: 15px; width: '.$checked_procent.'%; background-color: #3161ff;">&nbsp;</div></div>';
    $report_row[__('Total Checked/Scanned Items')] = $checked_count.' ('.$checked_procent.'%) '.$progress_bar;
    // stock take participants data
    $report_row[__('Stock Take Participants')] = '<ul>';
        // get other stock take users
        $st_other_users_q = $dbs->query('SELECT DISTINCT checked_by, COUNT(item_id) AS num_count FROM stock_take_item GROUP BY checked_by ORDER BY `num_count` DESC');
        while ($st_other_users_d = $st_other_users_q->fetch_row()) {
            if ($st_other_users_d[0] != $stk_data['stock_take_users']) {
                $report_row[__('Stock Take Participants')] .= '<li>'.$st_other_users_d[0].' ('.$st_other_users_d[1].' '.__('items already checked').')</li>'; //mfc
            }
        }
        // destroy query object
        unset($st_other_users_q);
    $report_row[__('Stock Take Participants')] .= '</ul>';
    // stock take start date
    $report_row[__('Start Date')] = nl2br($stk_data['start_date']);
    // stock take end date
    $report_row[__('End Date')] = nl2br($stk_data['end_date']);

    // initial row count
    $row = 1;
    foreach ($report_row as $headings => $report_data) {
        $table->appendTableRow(array($headings, ':', $report_data));
        // set cell attribute
        $table->setCellAttr($row, 0, 'class="alterCell" style="width: 170px;"');
        $table->setCellAttr($row, 1, 'class="alterCell" style="width: 1%;"');
        $table->setCellAttr($row, 2, 'class="alterCell2" style="width: auto;"');
        $row++;
    }
    /* GENERAL REPORT */
    echo $table->printTable();

    /* DECIMAL CLASSES ITEM STATUS */
    $class_num = 0;
    $row_class = 'alterCell';
    $arr_status = array('m', 'e', 'l');
    $class_count_str = '<table align="center" class="border" style="width: 100%; margin-top: 5px;" cellpadding="3" cellspacing="0">';
    $class_count_str .= '<tr><td class="dataListHeader">'.__('Classification').'</td>
        <td class="dataListHeader">'.__('Lost Items').'</td>
        <td class="dataListHeader">'.__('Existing Items').'</td>
        <td class="dataListHeader">'.__('On Loan Items').'</td></tr>';
    while ($class_num < 10) {
        $row_class = ($class_num%2 == 0)?'alterCell':'alterCell2';
        $class_count_str .= '<tr><td class="'.$row_class.'"><strong>'.$class_num.'</strong>'.__(' classes').'</td>'; //mfc
        foreach ($arr_status as $status) {
            $cls_q = $dbs->query("SELECT COUNT(item_code) FROM stock_take_item WHERE TRIM(classification) LIKE '$class_num%' AND status='$status'");
            $cls_d = $cls_q->fetch_row();
            // append to string
            $class_count_str .= '<td class="'.$row_class.'">'.$cls_d[0].'</td>';
        }
        $class_count_str .= '</tr>';
        $class_num++;
    }

    /* NON DECIMAL NUMBER CLASSES ITEM STATUS */
    $cls_q = $dbs->query("SELECT DISTINCT classification FROM stock_take_item WHERE TRIM(classification) NOT REGEXP '^[0123456789]'");
    while ($cls_d = $cls_q->fetch_row()) {
        $row_class = ($row_class == 'alterCell')?'alterCell2':'alterCell';
        $class_count_str .= '<tr><td class="'.$row_class.'"><strong>'.$cls_d[0].'</strong> classes</td>';
        foreach ($arr_status as $status) {
            $cls_count_q = $dbs->query("SELECT COUNT(item_code) FROM stock_take_item WHERE TRIM(classification)='".$cls_d[0]."' AND status='$status'");
            $cls_count_d = $cls_count_q->fetch_row();
            // append to string
            $class_count_str .= '<td class="'.$row_class.'">'.$cls_count_d[0].'</td>';
        }
        $class_count_str .= '</tr>';
    }
    /* DECIMAL CLASSES AND NON DECIMAL NUMBER ITEM STATUS END */

    // table end
    $class_count_str .= '</table>';
    echo $class_count_str;

    /* COLLECTION TYPE ITEM STATUS */
    $coll_type_count_str = '<table align="center" class="border" style="width: 100%; margin-top: 5px;" cellpadding="3" cellspacing="0">';
    $coll_type_count_str .= '<tr><td class="dataListHeader">'.__('Collection Type').'</td>
        <td class="dataListHeader">'.__('Lost Items').'</td>
        <td class="dataListHeader">'.__('Existing Items').'</td>
        <td class="dataListHeader">'.__('On Loan Items').'</td></tr>';
    $ct_q = $dbs->query("SELECT DISTINCT coll_type_name FROM stock_take_item");
    $row = 1;
    while ($ct_d = $ct_q->fetch_row()) {
        $row_class = ($row%2 == 0)?'alterCell':'alterCell2';
        $coll_type_count_str .= '<tr><td class="'.$row_class.'"><strong>'.$ct_d[0].'</strong></td>';
        foreach ($arr_status as $status) {
            $ct_count_q = $dbs->query("SELECT COUNT(item_code) FROM stock_take_item WHERE coll_type_name='".$ct_d[0]."' AND status='$status'");
            $ct_count_d = $ct_count_q->fetch_row();
            // append to string
            $coll_type_count_str .= '<td class="'.$row_class.'">'.$ct_count_d[0].'</td>';
        }
        $coll_type_count_str .= '</tr>';
        $row++;
    }

    // table end
    $coll_type_count_str .= '</table>';
    echo $coll_type_count_str;
    /* COLLECTION TYPE ITEM STATUS END */

    // output report
    $report_content = ob_get_clean();

    // check if we are on printed mode
    if (isset($_GET['print'])) {
        // html strings
        $html_str = '<html><head><title>'.$sysconf['library_name'].' ' . __('Current Stock Take Report') . '</title>';
        $html_str .= '<style type="text/css">'."\n";
        $html_str .= 'body {padding: 0.2cm}'."\n";
        $html_str .= 'body * {color: black; font-size: 11pt;}'."\n";
        $html_str .= 'table {border: 1px solid #000000;}'."\n";
        $html_str .= '.dataListHeader {background-color: #000000; color: white; font-weight: bold;}'."\n";
        $html_str .= '.alterCell {border-bottom: 1px solid #666; background-color: #CCCCCC;}'."\n";
        $html_str .= '.alterCell2 {border-bottom: 1px solid #666; background-color: #FFFFFF;}'."\n";
        $html_str .= '</style>'."\n";
        $html_str .= '</head>';
        $html_str .= '<body>'."\n";
        $html_str .= '<h3>'.$sysconf['library_name'].' - ' . __('Current Stock Take Report') . '</h3>';
        $html_str .= '<hr size="1" />'."\n";
        $html_str .= $report_content;
        $html_str .= '<script type="text/javascript">self.print();</script>'."\n";
        $html_str .= '</body></html>'."\n";
        // write to file
        $file_write = @file_put_contents(REPBS.'stock_take_print_result.html', $html_str);
        if ($file_write) {
            // open result in new window
            echo '<script type="text/javascript">parent.openWin(\''.SWB.'/'.FLS.'/'.REP.'/stock_take_print_result.html\', \'popMemberReport\', 800, 500, true)</script>';
        } else { utility::jsAlert(str_replace('{directory}', REPBS, __('ERROR! Current Stock Take Report failed to generate, possibly because {directory} directory is not writable'))); }
        exit();
    } else {
        echo $report_content;
    }
}
