<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Modified for Excel output (C) 2010 by Wardiyono (wynerst@gmail.com)
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

/* Loan Class Report */

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

$membershipTypes = membershipApi::getMembershipType($dbs);
$page_title = 'Loan Report by Class Report';
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
<!-- filter -->
<div class="per_title">
    <h2><?php echo __('Loans by Classification'); ?></h2>
</div>
<div class="infoBox">
    <?php echo __('Report Filter'); ?>
</div>
<div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
        <div id="filterForm">
            <div class="form-group divRow">
                <label><?php echo __('Classification'); ?></label>
                <?php
                $class_options[] = array(__('All'), __('All'));
                $class_options[] = array('0', __('0 Classes'));
                $class_options[] = array('1', __('1 Classes'));
                $class_options[] = array('2', __('2 Classes'));
                $class_options[] = array('2X', __('2X Classes (Islamic Related)'));
                $class_options[] = array('3', __('3 Classes'));
                $class_options[] = array('4', __('4 Classes'));
                $class_options[] = array('5', __('5 Classes'));
                $class_options[] = array('6', __('6 Classes'));
                $class_options[] = array('7', __('7 Classes'));
                $class_options[] = array('8', __('8 Classes'));
                $class_options[] = array('9', __('9 Classes'));
                $class_options[] = array('NONDECIMAL', __('NON Decimal Classes'));
                echo simbio_form_element::selectList('class', $class_options,'','class="form-control col-2"');
                ?>
            </div>
            <div class="form-group divRow">
                <label><?php echo __('Collection Type'); ?></label>
                <?php
                $coll_type_q = $dbs->query('SELECT coll_type_id, coll_type_name FROM mst_coll_type');
                $coll_type_options = array();
                $coll_type_options[] = array('0', __('All'));
                while ($coll_type_d = $coll_type_q->fetch_row()) {
                    $coll_type_options[] = array($coll_type_d[1], $coll_type_d[1]);
                }
                echo simbio_form_element::selectList('collType', $coll_type_options,'','class="form-control col-2"');
                ?>
            </div>
            <div class="form-group divRow">
                <label><?php echo __('Year'); ?></label>
                <?php
                $current_year = date('Y');
                $year_options[] = array('',__('All'));
                for ($y = $current_year; $y > 1999; $y--) {
                    $year_options[] = array($y, $y);
                }
                echo simbio_form_element::selectList('year', $year_options, __('All'),'class="form-control col-1"');
                ?>
            </div>
            <div class="form-group divRow">
                <label><?php echo __('Membership Type'); ?></label>
                <select name="membershipType" class="form-control col-1">
                <?php 
                foreach ($membershipTypes as $key => $membershipType) {
                    echo '<option value="'.$membershipType['member_type_name'].'">'.$membershipType['member_type_name'].'</option>';
                }
                ?>
                </select>
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
	$xls_rc = 0;
	$xls_cc = 0;
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
    $output .= '<tr>';
    $output .= '<th class="dataListHeaderPrinted">'.__('Classification').'</th>';
	$xlsrows = array($xls_rc => array(__('Classification'),__('Jan'),__('Feb'),__('Mar'),__('Apr'),__('May'),__('Jun'),__('Jul'),__('Aug'),__('Sep'),__('Oct'),__('Nov'),__('Dec')));
	$xls_rc++;
    foreach ($months as $month) {
        $output .= '<th class="dataListHeaderPrinted">'.$month.'</th>';
    }
    $output .= '</tr>';

    $criteria = '';
    // class
    $class_num = isset($_GET['class'])?$dbs->escape_string(trim($_GET['class'])) : __('All');
    // year
    $selected_year = '%';
    if (isset($_GET['year']) AND !empty($_GET['year']) AND $_GET['year']!='0') {
        $selected_year = (integer)$_GET['year'];
        $criteria .= ' AND loan_date LIKE \''.$selected_year.'%\'';
    }else{
        $criteria .= ' AND loan_date LIKE \'%%\'';
    }

    if (isset($_GET['membershipType']) AND !empty($_GET['membershipType']) AND $_GET['membershipType'] != __('All')) {
        $membershipType = $dbs->escape_string((string)$_GET['membershipType']);
        $criteria .= ' AND member_type_name LIKE \''.$membershipType.'\'';
    }else{
        $criteria .= ' AND member_type_name LIKE \'%%\'';
    }

    if (isset($_GET['collType']) AND !empty($_GET['collType']) AND $_GET['collType']!='0') {
        $collType = $dbs->escape_string((string)$_GET['collType']);
        $criteria .= ' AND collection_type_name LIKE \''.$collType.'\'';
    }else{
        $criteria .= ' AND collection_type_name LIKE \'%%\'';
    }

    $coll_query = "SUM(IF(loan_date LIKE '".$selected_year."-01%',1,0)), 
        SUM(IF(loan_date LIKE '".$selected_year."-02%',1,0)),
        SUM(IF(loan_date LIKE '".$selected_year."-03%',1,0)),
        SUM(IF(loan_date LIKE '".$selected_year."-04%',1,0)),
        SUM(IF(loan_date LIKE '".$selected_year."-05%',1,0)),
        SUM(IF(loan_date LIKE '".$selected_year."-06%',1,0)),
        SUM(IF(loan_date LIKE '".$selected_year."-07%',1,0)),
        SUM(IF(loan_date LIKE '".$selected_year."-08%',1,0)),
        SUM(IF(loan_date LIKE '".$selected_year."-09%',1,0)),
        SUM(IF(loan_date LIKE '".$selected_year."-10%',1,0)),
        SUM(IF(loan_date LIKE '".$selected_year."-11%',1,0)),
        SUM(IF(loan_date LIKE '".$selected_year."-12%',1,0))";

    if ($class_num == __('All')) {
        //main class
        for ($main_class=0; $main_class < 10; $main_class++) { 
        $query = "SELECT $coll_query FROM loan_history WHERE TRIM(classification) LIKE '".$main_class."%' ".$criteria;
        $q_main = $dbs->query($query);
        $q_d = $q_main->fetch_row();
        $output .= '<tr><td>'.$main_class.'00</td>';
        $data[$main_class.'00'] = $q_d;
        $xlsrows[$xls_rc][$xls_cc] = $main_class.'00';
        $xls_cc++;
            for ($i=0; $i < 12; $i++) { 
              $output .= '<td>'.(($q_d[$i]??'0')>0?'<strong>'.$q_d[$i].'</strong>':'0').'</td>';
              $xlsrows[$xls_rc][$xls_cc] = $q_d[$i]??'0';
              $xls_cc++;
            }
        $xls_rc++;
        $xls_cc =0;  
        }
    }

    elseif ($class_num == 'NONDECIMAL') {
        //main class
        $query = "SELECT $coll_query FROM loan_history WHERE classification REGEXP '^[^0-9]' ".$criteria;
        $q_main = $dbs->query($query);
        $q_d = $q_main->fetch_row();
        $output .= '<tr><td>'.$class_num.'</td>';
        $xlsrows[$xls_rc][$xls_cc] = $class_num;
        $xls_cc++;
        $data[$class_num] = $q_d;
        for ($i=0; $i < 12; $i++) { 
            $output .= '<td>'.(($q_d[$i]??'0')>0?'<strong>'.$q_d[$i].'</strong>':'0').'</td>';
            $xlsrows[$xls_rc][$xls_cc] = $q_d[$i]??'0';
            $xls_cc++;
        }
        $xls_rc++;
        $xls_cc =0;
    }
    else{
        //main classes
        $query = "SELECT $coll_query FROM loan_history WHERE TRIM(classification) LIKE '".$class_num."%' ".$criteria;
        $q_main = $dbs->query($query);
        $q_d = $q_main->fetch_row();
        $output .= '<tr><td><strong>'.$class_num.'00</strong></td>';
        for ($i=0; $i < 12; $i++) { 
            $output .= '<td>'.(($q_d[$i]??'0')>0?'<strong>'.$q_d[$i].'</strong>':'0').'</td>';
        }

        // 2nd subclasses
        for ($class_num2=0; $class_num2 < 10; $class_num2++) { 
            $query = "SELECT $coll_query FROM loan_history WHERE TRIM(classification) LIKE '".$class_num.$class_num2."%' ".$criteria;
            $q_main = $dbs->query($query);
            $q_d = $q_main->fetch_row();
            $output .= '<tr><td>'.$class_num.$class_num2.'0</td>';
            $data[$class_num.$class_num2.'0'] = $q_d;
            $xlsrows[$xls_rc][$xls_cc] = $class_num.$class_num2.'0';
            $xls_cc++;
                for ($i=0; $i < 12; $i++) { 
                  $output .= '<td>'.(($q_d[$i]??'0')>0?'<strong>'.$q_d[$i].'</strong>':'0').'</td>';
                  $xlsrows[$xls_rc][$xls_cc] = $q_d[$i]??'0';
                  $xls_cc++;
                }
                $xls_rc++;
                $xls_cc =0;      
            }
    }
    $output .= '</table>';

    unset($_SESSION['chart']);
    $chart['xAxis'] = $months;
    $chart['data'] = $data;
    $chart['title'] =  __('Loan Recap By Class'). ' <strong>'.$class_num.'</strong> '._('for year').' <strong>'.($selected_year!='%'?$selected_year:__('All')).'</strong>';
    $_SESSION['chart'] = $chart;
    // print out
    echo '<div class="mb-2">'.__('Loan Recap By Class'). ' <strong>'.$class_num.'</strong> '._('for year').' <strong>'.($selected_year!='%'?$selected_year:__('All')).'</strong>'.( isset($coll_type_name)?'<div>'.$coll_type_name.'</div>':'' ).' <a class="s-btn btn btn-default printReport" onclick="window.print()" href="#">'.__('Print Current Page').'</a>';
    echo '<a href="../xlsoutput.php" class="s-btn btn btn-default" target="_BLANK">'.__('Export to spreadsheet format').'</a>
    <a class="s-btn btn btn-info notAJAX openPopUp" href="'.MWB.'reporting/pop_chart.php" width="700" height="530" title="'.__('Loan Recap By Class'). '">'.__('Show in chart/plot').'</a></div>'."\n";
    echo $output;


	unset($_SESSION['xlsquery']); 
	$_SESSION['xlsdata'] = $xlsrows;
	$_SESSION['tblout'] = "loan_by_class_list";
	// echo '<p><a href="../xlsoutput.php" class="s-btn btn btn-default">'.__('Export to spreadsheet format').'</a></p>';
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/pop_iframe_tpl.php';
}
