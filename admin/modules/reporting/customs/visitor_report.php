<?php
/**
 *
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

use SLiMS\DB;

/* Visitor Report */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';

$page_title = 'Library Visitor Report';
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <div class="per_title">
      <h2><?php echo __('Library Visitor Report'); ?></h2>
    </div>
    <div class="infoBox">
      <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
      <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView" class="form-inline">
        <label class="mr-2"><?php echo __('Year'); ?></label>
        <?php
        $current_year = date('Y');
        $year_options = array();
        for ($y = $current_year; $y > 1999; $y--) {
            $year_options[] = array($y, $y);
        }
        echo simbio_form_element::selectList('year', $year_options, $current_year,'class="form-control col-2"');
        ?>
        <input type="submit" name="applyFilter" class="btn btn-primary" value="<?php echo __('Apply Filter'); ?>" />
        <input type="hidden" name="reportView" value="true" />
      </form>
    </div>
    <!-- filter end -->
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    // months array
    $months['01'] = __('Jan');
    $months['02'] = __('Feb');
    $months['03'] = __('Mar');
    $months['04'] = __('Apr');
    $months['05'] = __('May');
    $months['06'] = __('Jun');
    $months['07'] = __('Jul');
    $months['08'] = __('Aug');
    $months['09'] = __('Sep');
    $months['10'] = __('Oct');
    $months['11'] = __('Nov');
    $months['12'] = __('Dec');

    // table start
    $row_class = 'alterCellPrinted';
    $output = '<table class="s-table table table-sm table-bordered">';

    // header
    $output .= '<tr class="dataListHeaderPrinted">';
    $output .= '<td><a>'.__('Member Type').'</a></td>';
    foreach ($months as $month_num => $month) {
        $total_month[$month_num] = 0;
        $output .= '<td>'.$month.'</td>';
        $xAxis[$month_num] = $month;
    }
    $output .= '<td>TOTAL</td>';
    $output .= '</tr>';

    // year
    $selected_year = date('Y');
    if (isset($_GET['year']) AND !empty($_GET['year'])) {
        $selected_year = (integer)$_GET['year'];
    }

    // get member type data from databse
    $_q = $dbs->query("SELECT member_type_id, member_type_name FROM mst_member_type LIMIT 100");
    while ($_d = $_q->fetch_row()) {
        $member_types[$_d[0]] = $_d[1];
    }

    /**
     * @var $db \PDO
     */
    $db = DB::getInstance();
    $visitors = [];
    foreach ($months as $month_num => $month) {
        $visitor_q = $db->query("SELECT m.member_type_id, count(visitor_id) FROM visitor_count AS vc 
            LEFT JOIN member AS m ON vc.member_id=m.member_id
            WHERE (vc.member_id IS NOT NULL OR vc.member_id != '') AND checkin_date LIKE '$selected_year-$month_num%' GROUP BY member_type_id ORDER BY member_type_id");
        $visitor_d = $visitor_q->fetchAll();
        foreach ($member_types as $id => $member_type) {
            $visitors[$id][$month_num] = 0;
            foreach ($visitor_d as $visitor) {
                if ($visitor[0] == $id) {
                    $visitors[$id][$month_num] = $visitor[1];
                    $total_month[$month_num] += $visitor[1];
                    break;
                }
            }
        }
    }

    foreach ($member_types as $id => $member_type) {
        $output .= '<tr>';
        $output .= '<td>'.$member_type.'</td>'."\n";
        $count_row = 0;
        foreach ($visitors[$id] as $month => $value) {
            if ($value > 0) {
                $output .= '<td><strong>'.$value.'</strong></td>';
            } else {
                $output .= '<td>'.$value.'</td>';
            }
            $data[$member_type][$month] = $value;
            $count_row += $value;
        }
        $output .= '<td class="table-warning">'.$count_row.'</td>'."\n";
        $output .= '</tr>';
    }

    // non member visitor count
    $output .= '<tr>';
    $output .= '<td>'.__('NON-Member Visitor').'</td>'."\n";
    $data['non_member'] = $months;
    $count_row = 0;
    foreach ($months as $month_num => $month) {
        $sql_str = "SELECT COUNT(visitor_id) FROM visitor_count AS vc
            WHERE (vc.member_id IS NULL OR vc.member_id='') AND vc.checkin_date LIKE '$selected_year-$month_num-%'";
        $visitor_q = $dbs->query($sql_str);
        $visitor_d = $visitor_q->fetch_row();
        if ($visitor_d[0] > 0) {
            $data['non_member'][$month_num] = $visitor_d[0];
            $output .= '<td><strong>'.$visitor_d[0].'</strong></td>';
        } else {
            $data['non_member'][$month_num] = 0;
            $output .= '<td>'.$visitor_d[0].'</td>';
        }
        $count_row += $visitor_d[0];
        $total_month[$month_num] += $visitor_d[0];
    }
    $output .= '<td class="table-warning">'.$count_row.'</td>'."\n";
    $output .= '</tr>';



    // total for each month
    $output .= '<tr class="table-warning">';
    $output .= '<td>'.__('Total visit/month').'</td>';
    $count_row = 0;
    foreach ($months as $month_num => $month) {
        $output .= '<td>'.$total_month[$month_num].'</td>';
        $count_row += $total_month[$month_num];
    }
    $output .= '<td class="table-warning">'.$count_row.'</td>'."\n";
    $output .= '</tr>';

    $output .= '</table>';


    unset($_SESSION['chart']);
    $chart['xAxis'] = $xAxis;
    $chart['data'] = $data;
    $chart['title'] =  str_replace('{selectedYear}', $selected_year,__('Visitor Count Report for year <strong>{selectedYear}</strong>'));
    $_SESSION['chart'] = $chart;

    // print out

    echo '<div class="mb-2">'. str_replace('{selectedYear}', $selected_year,__('Visitor Count Report for year <strong>{selectedYear}</strong>'));
    echo '&nbsp;<div class="btn-group"><a class="s-btn btn btn-default printReport" onclick="window.print()" href="#">'.__('Print Current Page').'</a>
    <a class="s-btn btn btn-default notAJAX openPopUp" href="'.MWB.'reporting/pop_chart.php" width="700" height="530">'.__('Show in chart/plot').'</a></div></div>'."\n";
    echo $output;

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/pop_iframe_tpl.php';
}
