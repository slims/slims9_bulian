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
    <fieldset>
    <div class="per_title">
        <h2><?php echo __('Library Visitor Report'); ?></h2>
    </div>
    <div class="infoBox">
    <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
    <div id="filterForm">
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Year'); ?></div>
            <div class="divRowContent">
            <?php
            $current_year = date('Y');
            $year_options = array();
            for ($y = $current_year; $y > 1999; $y--) {
                $year_options[] = array($y, $y);
            }
            echo simbio_form_element::selectList('year', $year_options, $current_year);
            ?>
            </div>
        </div>
    </div>
    <div style="padding-top: 10px; clear: both;">
    <input type="submit" name="applyFilter" value="<?php echo __('Apply Filter'); ?>" />
    <input type="hidden" name="reportView" value="true" />
    </div>
    </form>
    </div>
    </fieldset>
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
    $output = '<table align="center" class="border" style="width: 100%;" cellpadding="3" cellspacing="0">';

    // header
    $output .= '<tr>';
    $output .= '<td class="dataListHeaderPrinted">'.__('Member Type').'</td>';
    foreach ($months as $month_num => $month) {
        $total_month[$month_num] = 0;
        $output .= '<td class="dataListHeaderPrinted">'.$month.'</td>';
    }
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
    $r = 1;
    // count library member visitor each month
    foreach ($member_types as $id => $member_type) {
        $row_class = ($r%2 == 0)?'alterCellPrinted':'alterCellPrinted2';
        $output .= '<tr>';
        $output .= '<td class="'.$row_class.'">'.$member_type.'</td>'."\n";
        foreach ($months as $month_num => $month) {
            $sql_str = "SELECT COUNT(visitor_id) FROM visitor_count AS vc
                INNER JOIN (member AS m LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id) ON m.member_id=vc.member_id
                WHERE m.member_type_id=$id AND vc.checkin_date LIKE '$selected_year-$month_num-%'";
            $visitor_q = $dbs->query($sql_str);
            $visitor_d = $visitor_q->fetch_row();
            if ($visitor_d[0] > 0) {
                $output .= '<td class="'.$row_class.'"><strong style="font-size: 1.5em;">'.$visitor_d[0].'</strong></td>';
            } else {
                $output .= '<td class="'.$row_class.'"><span style="color: #ff0000;">'.$visitor_d[0].'</span></td>';
            }
            $total_month[$month_num] += $visitor_d[0];
        }
        $output .= '</tr>';
        $r++;
    }

    // non member visitor count
    $row_class = ($r%2 == 0)?'alterCellPrinted':'alterCellPrinted2';
    $output .= '<tr>';
    $output .= '<td class="'.$row_class.'">'.__('NON-Member Visitor').'</td>'."\n";
    foreach ($months as $month_num => $month) {
        $sql_str = "SELECT COUNT(visitor_id) FROM visitor_count AS vc
            WHERE (vc.member_id IS NULL OR vc.member_id='') AND vc.checkin_date LIKE '$selected_year-$month_num-%'";
        $visitor_q = $dbs->query($sql_str);
        $visitor_d = $visitor_q->fetch_row();
        if ($visitor_d[0] > 0) {
            $output .= '<td class="'.$row_class.'"><strong style="font-size: 1.5em;">'.$visitor_d[0].'</strong></td>';
        } else {
            $output .= '<td class="'.$row_class.'"><span style="color: #ff0000;">'.$visitor_d[0].'</span></td>';
        }
        $total_month[$month_num] += $visitor_d[0];
    }
    $output .= '</tr>';

    // total for each month
    $output .= '<tr>';
    $output .= '<td class="dataListHeaderPrinted">'.__('Total visit/month').'</td>';
    foreach ($months as $month_num => $month) {
        $output .= '<td class="dataListHeaderPrinted">'.$total_month[$month_num].'</td>';
    }
    $output .= '</tr>';

    $output .= '</table>';

    // print out
    echo '<div class="printPageInfo">'. str_replace('{selectedYear}', $selected_year,__('Visitor Count Report for year <strong>{selectedYear}</strong>')).' <a class="printReport" onclick="window.print()" href="#">'.__('Print Current Page').'</a></div>'."\n";
    echo $output;

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
