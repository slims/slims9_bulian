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

/* Fines Report */

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
require SIMBIO.'simbio_UTILS/simbio_date.inc.php';

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

$page_title = 'Fines Report';
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <div class="per_title">
    	<h2><?php echo __('Fines Report'); ?></h2>
	  </div>
    <div class="infoBox">
        <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
    <div id="filterForm">
        <div class="form-group divRow">
            <label><?php echo __('Year'); ?></label>
            <?php
            $current_year = date('Y');
            $year_options = array();
            for ($y = $current_year; $y > 1999; $y--) {
                $year_options[] = array($y, $y);
            }
            echo simbio_form_element::selectList('year', $year_options, $current_year, 'class="form-control col-1"');
            ?>
        </div>
        <div class="form-group divRow">
            <label><?php echo __('Month'); ?></label>
            <?php
            $current_month = date('m');
            $month_options = array();
            foreach ($months as $idx => $month) {
                $month_options[] = array($idx, $month);
            }
            echo simbio_form_element::selectList('month', $month_options, $current_month,'class="form-control col-1"');
            ?>
        </div>
    </div>
    <input type="button" name="moreFilter" class="btn btn-default" value="<?php echo __('Show More Filter Options'); ?>" />
    <input type="submit" name="applyFilter" class="btn btn-primary" value="<?php echo __('Apply Filter'); ?>" />
    <input type="hidden" name="reportView" value="true" />
    </form>
    </div>
    <!-- filter end -->
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    $fines_data = array();
    // year
    $selected_year = date('Y');
    if (isset($_GET['year']) AND !empty($_GET['year'])) {
        $selected_year = (integer)$_GET['year'];
    }
    // month
    $selected_month = date('m');
    if (isset($_GET['month']) AND !empty($_GET['month'])) {
        $selected_month = $_GET['month'];
    }

    // query fines data to database
    // echo "SELECT SUBSTRING(`fines_date`, -2) AS `mdate`, SUM(debet) AS `dtotal`, `fines_id` FROM `fines` WHERE `fines_date` LIKE '$selected_year-$selected_month%' GROUP BY `fines_date`";
    $_fines_q = $dbs->query("SELECT SUBSTRING(`fines_date`, -2) AS `mdate`, SUM(debet) AS `dtotal` FROM `fines` WHERE `fines_date` LIKE '$selected_year-$selected_month%' GROUP BY `fines_date`");
    while ($_fines_d = $_fines_q->fetch_row()) {
        $date = (integer)preg_replace('@^0+@i', '',$_fines_d[0]);
        $fines_data[$date] = '<div class="data"><a href="'.AWB.'modules/reporting/customs/member_fines_list.php?reportView=true&singleDate=true&finesDate='.$selected_year.'-'.$selected_month.'-'.$date.'" class="notAJAX openPopUp" width="800" height="600" title="'.__('Member Fines List').'">'.currency($_fines_d[1]?:'0').'</a></div>';
    }

    // generate calendar
    $output = simbio_date::generateCalendar($selected_year, $selected_month, $fines_data);

    // print out
    echo '<div class="mb-2">'.__('Fines count report for').' <strong>'.$months[$selected_month].', '.$selected_year.'</strong> <a class="s-btn btn btn-default printReport" onclick="window.print()" href="#">'.__('Print Current Page').'</a></div>'."\n";
    echo $output;

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
}
