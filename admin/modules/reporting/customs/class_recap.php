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

/* Recapitulation Report */

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

require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS.'reporting/report_dbgrid.inc.php';

$page_title = 'Recap Report';
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <div class="per_title">
        <h2><?php echo __('Custom Recapitulations'); ?></h2>
    </div>
    <div class="infoBox">
        <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
        <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView" class="form-inline">
            <?php echo __('Recap By'); ?>&nbsp;
            <?php
            $recapby_options[] = array('', __('Classification'));
            $recapby_options[] = array('gmd', __('GMD'));
            $recapby_options[] = array('collType', __('Collection Type'));
            $recapby_options[] = array('language', __('Language'));
            echo simbio_form_element::selectList('recapBy', $recapby_options,'','class="form-control"');
            ?>
            <input type="submit" name="applyFilter" value="<?php echo __('Apply Filter'); ?>" class="btn btn-primary" />
            <input type="hidden" name="reportView" value="true" />
        </form>
    </div>
    <script type="text/javascript">hideRows('filterForm', 1);</script>
    <!-- filter end -->
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
	$xls_rc = 0;
	$xls_cc = 0;
    $row_class = 'alterCellPrinted';
    $recapby = __('Classification');
    $output = '<table class="s-table table table-sm table-bordered mb-0">';
    // header
    $output .= '<tr>
        <th>'.$recapby.'</th>
        <th>'.__('Title').'</th>
        <th>'.__('Items').'</th></tr>';
	$xlsrows = array($xls_rc => array($recapby,__('Title'),__('Items')));
	$xls_rc++;
    if (isset($_GET['recapBy']) AND trim($_GET['recapBy']) != '') {
        switch ($_GET['recapBy']) {
            case 'gmd' :
            $recapby = __('GMD');
            /* GMD */
            $gmd_q = $dbs->query("SELECT DISTINCT gmd_id, gmd_name FROM mst_gmd");
            while ($gmd_d = $gmd_q->fetch_row()) {
                $row_class = ($row_class == 'alterCellPrinted')?'alterCellPrinted2':'alterCellPrinted';
                $output .= '<tr><td class="'.$row_class.'">'.$gmd_d[1].'</td>';
                // count by title
                $bytitle_q = $dbs->query("SELECT COUNT(biblio_id) FROM biblio WHERE gmd_id=".$gmd_d[0]);
                $bytitle_d = $bytitle_q->fetch_row();
                $output .= '<td class="'.$row_class.'">'.$bytitle_d[0].'</td>';
                // count by item
                $byitem_q = $dbs->query("SELECT COUNT(item_id) FROM item AS i INNER JOIN biblio AS b
                    ON i.biblio_id=b.biblio_id
                    WHERE b.gmd_id=".$gmd_d[0]);
                $byitem_d = $byitem_q->fetch_row();
                $output .= '<td class="'.$row_class.'">'.$byitem_d[0].'</td>';
				$xlsrows[$xls_rc] = array($gmd_d[1],$bytitle_d[0],$byitem_d[0]);
				$xls_rc++;
                $output .= '</tr>';
			}
            /* GMD END */
			break;
            case 'language' :
            $recapby = __('Language');
            /* LANGUAGE */
            $lang_q = $dbs->query("SELECT DISTINCT language_id, language_name FROM mst_language");
            while ($lang_d = $lang_q->fetch_row()) {
                $row_class = ($row_class == 'alterCellPrinted')?'alterCellPrinted2':'alterCellPrinted';
                $output .= '<tr><td class="'.$row_class.'"><strong style="font-size: 1.5em;">'.$lang_d[1].'</strong></td>';
                // count by title
                $bytitle_q = $dbs->query("SELECT COUNT(biblio_id) FROM biblio WHERE language_id='".$lang_d[0]."'");
                $bytitle_d = $bytitle_q->fetch_row();
                $output .= '<td class="'.$row_class.'"><strong style="font-size: 1.3em;">'.$bytitle_d[0].'</strong></td>';

                // count by item
                $byitem_q = $dbs->query("SELECT COUNT(item_id) FROM item AS i INNER JOIN biblio AS b
                    ON i.biblio_id=b.biblio_id
                    WHERE b.language_id='".$lang_d[0]."'");
                $byitem_d = $byitem_q->fetch_row();
                $output .= '<td class="'.$row_class.'"><strong style="font-size: 1.3em;">'.$byitem_d[0].'</strong></td>';

				$xlsrows[$xls_rc] = array($lang_d[1],$bytitle_d[0],$byitem_d[0]);
				$xls_rc++;
                $output .= '</tr>';
            }
            /* LANGUAGE END */
            break;
            case 'collType' :
            $recapby = __('Collection Type');
            /* COLLECTION TYPE */
            $ctype_q = $dbs->query("SELECT DISTINCT coll_type_id, coll_type_name FROM mst_coll_type");
            while ($ctype_d = $ctype_q->fetch_row()) {
                $output .= '<tr><td>'.$ctype_d[1].'</td>';
                // count by title
                $bytitle_q = $dbs->query("SELECT DISTINCT biblio_id FROM item AS i
                    WHERE i.coll_type_id=".$ctype_d[0]."");
				$bytitle_d[0] = $bytitle_q->num_rows;
                $output .= '<td>'.$bytitle_q->num_rows.'</td>';
				
                // count by item
                $byitem_q = $dbs->query("SELECT COUNT(item_id) FROM item AS i
                    WHERE i.coll_type_id=".$ctype_d[0]);
                $byitem_d = $byitem_q->fetch_row();
                $output .= '<td>'.$byitem_d[0].'</td>';

				$xlsrows[$xls_rc] = array($ctype_d[1],$bytitle_d[0],$byitem_d[0]);
				$xls_rc++;
                $output .= '</tr>';
            }
            /* COLLECTION TYPE END */
            break;
        }
    } else {
        // recap by classification
        /* DECIMAL CLASSES */
        $class_num = 0;
        while ($class_num < 10) {
            $class_num2 = 0;
            $output .= '<tr class="table-warning"><th>'.$class_num.'00</th>';

            // count by title
            $bytitle_q = $dbs->query("SELECT COUNT(biblio_id) FROM biblio WHERE TRIM(classification) LIKE '$class_num%'");
            $bytitle_d = $bytitle_q->fetch_row();
            $output .= '<th>'.$bytitle_d[0].'</th>';

            // count by item
            $byitem_q = $dbs->query("SELECT COUNT(item_id) FROM item AS i LEFT JOIN biblio AS b
                ON i.biblio_id=b.biblio_id
                WHERE TRIM(b.classification) LIKE '$class_num%'");
            $byitem_d = $byitem_q->fetch_row();
            $output .= '<th>'.$byitem_d[0].'</th>';

			$xlsrows[$xls_rc] = array($class_num.'00',$bytitle_d[0],$byitem_d[0]);
			$xls_rc++;
            $output .= '</tr>';

            // 2nd subclasses
            while ($class_num2 < 10) {

                $output .= '<tr><td>'.$class_num.$class_num2.'0</td>';
                // count by title
                $bytitle_q = $dbs->query("SELECT COUNT(biblio_id) FROM biblio WHERE TRIM(classification) LIKE '".$class_num.$class_num2."%'");
                $bytitle_d = $bytitle_q->fetch_row();
                $output .= '<td>'.$bytitle_d[0].'</td>';

                // count by item
                $byitem_q = $dbs->query("SELECT COUNT(item_id) FROM item AS i LEFT JOIN biblio AS b
                    ON i.biblio_id=b.biblio_id
                    WHERE TRIM(b.classification) LIKE '".$class_num.$class_num2."%'");
                $byitem_d = $byitem_q->fetch_row();
                $output .= '<td>'.$byitem_d[0].'</td>';

				$xlsrows[$xls_rc] = array('  '.$class_num.$class_num2.'0',$bytitle_d[0],$byitem_d[0]);
				$xls_rc++;
                $output .= '</tr>';
                $class_num2++;
            }

            $class_num++;
        }

        /* 2X NUMBER CLASSES */
        $output .= '<tr class="table-warning"><th>2X classes</th>';
        // count by title
        $bytitle_q = $dbs->query("SELECT COUNT(biblio_id) FROM biblio WHERE TRIM(classification) LIKE '2X%'");
        $bytitle_d = $bytitle_q->fetch_row();
        $output .= '<th>'.$bytitle_d[0].'</th>';

        // count by item
        $byitem_q = $dbs->query("SELECT COUNT(item_id) FROM item AS i INNER JOIN biblio AS b
            ON i.biblio_id=b.biblio_id
            WHERE TRIM(b.classification) LIKE '2X%'");
        $byitem_d = $byitem_q->fetch_row();
        $output .= '<th>'.$byitem_d[0].'</th>';

		$xlsrows[$xls_rc] = array('2X',$bytitle_d[0],$byitem_d[0]);
		$xls_rc++;
        $output .= '</tr>';
        /* 2X NUMBER CLASSES END */


        /* NON-DECIMAL NUMBER CLASSES */
        // get non-decimal class
        $_non_decimal_q = $dbs->query("SELECT DISTINCT classification FROM biblio WHERE classification REGEXP '^[^0-9]'");
        if ($_non_decimal_q->num_rows > 0) {
            while ($_non_decimal = $_non_decimal_q->fetch_row()) {
                $output .= '<tr><td>'.$_non_decimal[0].' classes</td>';
                // count by title
                $bytitle_q = $dbs->query("SELECT COUNT(biblio_id) FROM biblio WHERE classification LIKE '".$_non_decimal[0]."'");
                $bytitle_d = $bytitle_q->fetch_row();
                $output .= '<td>'.$bytitle_d[0].'</td>';

                // count by item
                $byitem_q = $dbs->query("SELECT COUNT(item_id) FROM item AS i INNER JOIN biblio AS b
                    ON i.biblio_id=b.biblio_id
                    WHERE classification LIKE '".$_non_decimal[0]."'");
                $byitem_d = $byitem_q->fetch_row();
                $output .= '<td>'.$byitem_d[0].'</td>';

				$xlsrows[$xls_rc] = array($_non_decimal[0],$bytitle_d[0],$byitem_d[0]);
				$xls_rc++;
                $output .= '</tr>';
            }
        }
        /* NON-DECIMAL NUMBER CLASSES END */
    }
    $output .= '</table>';

    // print out
    echo '<div class="mb-2">'.__('Title and Collection Recap by').' <strong>'.$recapby.'</strong>
    <a href="#" class="s-btn btn btn-default printReport" onclick="window.print()">'.__('Print Current Page').'</a>
    <a href="../xlsoutput.php" class="s-btn btn btn-default">'.__('Export to spreadsheet format').'</a></div>'."\n";
    echo $output;

	unset($_SESSION['xlsquery']); 
	$_SESSION['xlsdata'] = $xlsrows;
	$_SESSION['tblout'] = "recap_list";
	// echo '<p><a href="../xlsoutput.php" class="s-btn btn btn-default">'.__('Export to spreadsheet format').'</a></p>';
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
