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
    <fieldset style="margin-bottom: 3px;">
    <div class="per_title">
    	<h2><?php echo __('Loans by Classification'); ?></h2>
	  </div>
    <div class="infoBox">
    <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
    <div id="filterForm">
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Classification'); ?>:</div>
            <div class="divRowContent">
            <?php
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
            echo simbio_form_element::selectList('class', $class_options);
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Collection Type'); ?></div>
            <div class="divRowContent">
            <?php
            $coll_type_q = $dbs->query('SELECT coll_type_id, coll_type_name FROM mst_coll_type');
            $coll_type_options = array();
            $coll_type_options[] = array('0', __('ALL'));
            while ($coll_type_d = $coll_type_q->fetch_row()) {
                $coll_type_options[] = array($coll_type_d[0], $coll_type_d[1]);
            }
            echo simbio_form_element::selectList('collType', $coll_type_options);
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Year'); ?></div>
            <div class="divRowContent">
            <?php
            $current_year = date('Y');
            $year_options = array();
            for ($y = $current_year; $y > 1999; $y--) {
                $year_options[] = array($y, $y);
            }
            echo simbio_form_element::selectList('year', $year_options, $current_year-1);
            ?>
            </div>
        </div>
        <div class="divRow">
          <div class="divRowLabel"><?php echo __('Membership Type'); ?></div>
          <div class="divRowContent">
            <select name="membershipType">
              <?php 
              foreach ($membershipTypes as $key => $membershipType) {
                echo '<option value="'.$key.'">'.$membershipType['member_type_name'].'</option>';
              }
              ?>
            </select>
          </div>
        </div>
    </div>
    <div style="padding-top: 10px; clear: both;">
    <input type="button" name="moreFilter" value="<?php echo __('Show More Filter Options'); ?>" />
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
    $output = '<table align="center" class="border" style="width: 100%;" cellpadding="3" cellspacing="0">';

    // header
    $output .= '<tr>';
    $output .= '<td class="dataListHeaderPrinted">'.__('Classification').'</td>';
	$xlsrows = array($xls_rc => array(__('Classification'),__('Jan'),__('Feb'),__('Mar'),__('Apr'),__('May'),__('Jun'),__('Jul'),__('Aug'),__('Sep'),__('Oct'),__('Nov'),__('Dec')));
	$xls_rc++;
    foreach ($months as $month) {
        $output .= '<td class="dataListHeaderPrinted">'.$month.'</td>';
    }
    $output .= '</tr>';

    // class
    $class_num = isset($_GET['class'])?trim($_GET['class']):'0';
    // year
    $selected_year = date('Y')-1;
    if (isset($_GET['year']) AND !empty($_GET['year'])) {
        $selected_year = (integer)$_GET['year'];
    }

    if (isset($_GET['membershipType']) AND !empty($_GET['membershipType'])) {
        $membershipType = (integer)$_GET['membershipType'];
    }

    // collection type
    $coll_type = null;
    if (isset($_GET['collType'])) {
        $coll_type = (integer)$_GET['collType'];
        $coll_type_q = $dbs->query('SELECT coll_type_name FROM mst_coll_type
            WHERE coll_type_id='.$coll_type);
        $coll_type_d = $coll_type_q->fetch_row();
        $coll_type_name = $coll_type_d[0];
    }

    $row_class = ($class_num%2 == 0)?'alterCellPrinted':'alterCellPrinted2';
    if ($class_num == 'NONDECIMAL') {
        $output .= '<tr><td class="'.$row_class.'"><strong style="font-size: 1.5em;">NON DECIMAL Classification</strong></td>';
		$xlsrows[$xls_rc][$xls_cc] = 'NON DECIMAL Classification';
		$xls_cc++;
        // count loan each month
        foreach ($months as $month_num => $month) {
            $loan_q = $dbs->query("SELECT COUNT(*) FROM biblio AS b
                LEFT JOIN item AS i ON b.biblio_id=i.biblio_id
                LEFT JOIN loan AS l ON l.item_code=i.item_code
                LEFT JOIN member AS m ON l.member_id=m.member_id
                WHERE classification REGEXP '^[^0-9]' AND l.loan_date LIKE '$selected_year-$month_num-%'".( !empty($coll_type)?" AND i.coll_type_id=$coll_type":'' ).( !empty($membershipType)?" AND m.member_type_id=$membershipType":'' ));
            $loan_d = $loan_q->fetch_row();
            if ($loan_d[0] > 0) {
                $output .= '<td class="'.$row_class.'"><strong style="font-size: 1.5em;">'.$loan_d[0].'</strong></td>';
            } else {
                $output .= '<td class="'.$row_class.'"><span style="color: #ff0000;">'.$loan_d[0].'</span></td>';
            }
			$xlsrows[$xls_rc][$xls_cc] = $loan_d[0];
			$xls_cc++;
        }

		$xls_rc++;
		$xls_cc =0;
        $output .= '</tr>';
    } else {
        $output .= '<tr><td class="'.$row_class.'"><strong style="font-size: 1.5em;">'.$class_num.'00</strong></td>';
		$xlsrows[$xls_rc][$xls_cc] = $class_num;
		$xls_cc++;

        // count loan each month
        foreach ($months as $month_num => $month) {
            $loan_q = $dbs->query("SELECT COUNT(*) FROM biblio AS b
                LEFT JOIN item AS i ON b.biblio_id=i.biblio_id
                LEFT JOIN loan AS l ON l.item_code=i.item_code
                LEFT JOIN member AS m ON l.member_id=m.member_id
                WHERE TRIM(classification) LIKE '$class_num%' AND l.loan_date LIKE '$selected_year-$month_num-%'".( !empty($coll_type)?" AND i.coll_type_id=$coll_type":'' ).( !empty($membershipType)?" AND m.member_type_id=$membershipType":'' ));
            $loan_d = $loan_q->fetch_row();
            if ($loan_d[0] > 0) {
                $output .= '<td class="'.$row_class.'"><strong style="font-size: 1.5em;">'.$loan_d[0].'</strong></td>';
            } else {
                $output .= '<td class="'.$row_class.'"><span style="color: #ff0000;">'.$loan_d[0].'</span></td>';
            }
			$xlsrows[$xls_rc][$xls_cc] = $loan_d[0];
			$xls_cc++;
        }

		$xls_rc++;
		$xls_cc =0;
        $output .= '</tr>';

        $class_num2 = 0;
        // 2nd subclasses
        while ($class_num2 < 10) {
            $row_class = ($row_class == 'alterCellPrinted')?'alterCellPrinted2':'alterCellPrinted';

            $output .= '<tr><td class="'.$row_class.'"><strong>&nbsp;&nbsp;&nbsp;'.$class_num.$class_num2.'0</strong></td>';
			$xlsrows[$xls_rc][$xls_cc] = '   '.$class_num;
			$xls_cc++;

            // count loan each month
            foreach ($months as $month_num => $month) {
                $loan_q = $dbs->query("SELECT COUNT(*) FROM biblio AS b
                    LEFT JOIN item AS i ON b.biblio_id=i.biblio_id
                    LEFT JOIN loan AS l ON l.item_code=i.item_code
                    LEFT JOIN member AS m ON l.member_id=m.member_id
                    WHERE TRIM(classification) LIKE '$class_num"."$class_num2%' AND l.loan_date LIKE '$selected_year-$month_num-%'".( !empty($coll_type)?" AND i.coll_type_id=$coll_type":'' ).( !empty($membershipType)?" AND m.member_type_id=$membershipType":'' ));
                $loan_d = $loan_q->fetch_row();
                if ($loan_d[0] > 0) {
                    $output .= '<td class="'.$row_class.'"><strong style="font-size: 1.5em;">'.$loan_d[0].'</strong></td>';
                } else {
                    $output .= '<td class="'.$row_class.'"><span style="color: #ff0000;">'.$loan_d[0].'</span></td>';
                }
				$xlsrows[$xls_rc][$xls_cc] = $loan_d[0];
				$xls_cc++;
	        }

			$xls_rc++;
			$xls_cc =0;
            $output .= '</tr>';
            $class_num2++;
        }
    }
    $output .= '</table>';

    // print out
    echo '<div class="printPageInfo">Loan Recap By Class <strong>'.$class_num.'</strong> for year <strong>'.$selected_year.'</strong>'.( isset($coll_type_name)?'<div>'.$coll_type_name.'</div>':'' ).' <a class="printReport" onclick="window.print()" href="#">'.__('Print Current Page').'</a>';
	echo '<a href="../xlsoutput.php" class="button">'.__('Export to spreadsheet format').'</a></div>'."\n";
    echo $output;

	unset($_SESSION['xlsquery']); 
	$_SESSION['xlsdata'] = $xlsrows;
	$_SESSION['tblout'] = "loan_by_class_list";
	// echo '<p><a href="../xlsoutput.php" class="button">'.__('Export to spreadsheet format').'</a></p>';
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
