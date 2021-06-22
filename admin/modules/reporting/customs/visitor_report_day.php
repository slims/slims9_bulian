<?php
/**
 *
 * Copyright (C) 2010 Arie Nugraha (dicarve@yahoo.com)
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

/* Visitor Report by Day */

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

$page_title = __('Visitor Report by Day');
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
<!-- filter -->
<div class="per_title">
  <h2><?php echo $page_title; ?></h2>
</div>
<div class="infoBox">
  <?php echo __('Report Filter'); ?>
</div>
<div class="sub_section">
<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView" class="form-inline">
          <label><?php echo __('Year'); ?></label>
          <?php
          $current_year = date('Y');
          $year_options = array();
          for ($y = $current_year; $y > 1999; $y--) {
              $year_options[] = array($y, $y);
          }
          echo simbio_form_element::selectList('year', $year_options, $current_year,'class="form-control col-1"');
          ?>
          <label><?php echo __('Month'); ?></label>
          <?php
          $current_month = date('m');
          $month_options = array();
          foreach ($months as $idx => $month) {
              $month_options[] = array($idx, $month);
          }
          echo simbio_form_element::selectList('month', $month_options, $current_month,'class="form-control col-1"');
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
    $visitor_data = array();
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

    // for each day in the month
    for($i = 1; $i <=  date('t',strtotime($selected_year.'-'.$selected_month)); $i++){
       $date = str_pad($i, 2, '0', STR_PAD_LEFT);
       $xAxis[$date] = $date;
       $data['visitor'][$date] = 0;
    }

    // query visitor data to database
    $_visitor_q = $dbs->query("SELECT MAX(SUBSTRING(`checkin_date`, 9, 2)) AS `mdate`, COUNT(visitor_id) AS `vtotal` FROM `visitor_count` WHERE `checkin_date` LIKE '$selected_year-$selected_month%' GROUP BY DATE(`checkin_date`)");
    while ($_visitor_d = $_visitor_q->fetch_row()) {
        $date = (integer)preg_replace('@^0+@i', '',$_visitor_d[0]);
        $visitor_data[$date] = '<div class="data"><a class="notAJAX openPopUp" width="800" height="600" title="'.__('Visitor Report by Day').'" href="'.AWB.'modules/reporting/customs/visitor_list.php?reportView=true&startDate='.$selected_year.'-'.$selected_month.'-'.$date.'&untilDate='.$selected_year.'-'.$selected_month.'-'.$date.'">'.($_visitor_d[1]?$_visitor_d[1]:'0').'</a></div>';
        $data['visitor'][$_visitor_d[0]] = $_visitor_d[1];
    }

    unset($_SESSION['chart']);
    $chart['xAxis'] = $xAxis;
    $chart['data'] = $data;
    $chart['title'] =  str_replace(array('{selectedYear}', '{selectedMonth}'), array($selected_year, $months[$selected_month]),__('Visitor Report for <strong>{selectedMonth}, {selectedYear}</strong>'));
    $_SESSION['chart'] = $chart;

    // generate calendar
    $output = simbio_date::generateCalendar($selected_year, $selected_month, $visitor_data);

    // print out
    echo '<div class="mb-2">'. str_replace(array('{selectedYear}', '{selectedMonth}'), array($selected_year, $months[$selected_month]),__('Visitor Report for <strong>{selectedMonth}, {selectedYear}</strong>'));
    echo '<div class="btn-group"><a class="s-btn btn btn-default printReport" onclick="window.print()" href="#">'.__('Print Current Page').'</a>
          <a class="s-btn btn btn-default notAJAX openPopUp" href="'.MWB.'reporting/pop_chart.php" width="700" height="530">'.__('Show in chart/plot').'</a></div></div>'."\n";
    echo $output;

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/pop_iframe_tpl.php';
}
