<?php
/**
 *
 * Copyright (C) 2010  Arie Nugraha (dicarve@yahoo.com)
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

/* Library Visitor List */

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

require SIMBIO.'simbio_GUI/template_parser/simbio_template_parser.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS.'reporting/report_dbgrid.inc.php';

$page_title = __('Visitors Report');
$reportView = false;
$num_recs_show = 20;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <div class="per_title">
    	<h2><?php echo __('Visitor List'); ?></h2>
    </div>
    <div class="infoBox">
        <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
        <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
            <div id="filterForm">
                <div class="form-group divRow">
                    <label><?php echo __('Membership Type'); ?></label>
                    <?php
                    $mtype_q = $dbs->query('SELECT member_type_id, member_type_name FROM mst_member_type');
                    $mtype_options = array();
                    $mtype_options[] = array('0', __('ALL'));
                    $mtype_options[] = array('-1', __('NON-Member visitor'));
                    while ($mtype_d = $mtype_q->fetch_row()) {
                        $mtype_options[] = array($mtype_d[0], $mtype_d[1]);
                    }
                    echo simbio_form_element::selectList('member_type', $mtype_options,'','class="form-control col-3"');
                    ?>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Visitor ID').'/'.__('Visitor Name'); ?></label>
                    <?php echo simbio_form_element::textField('text', 'id_name', '', 'class="form-control col-3"'); ?>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Institution'); ?></label>
                    <?php echo simbio_form_element::textField('text', 'institution', '', 'class="form-control col-3"'); ?>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Room'); ?></label>
                    <?php
                    $room_q = $dbs->query('SELECT unique_code, name FROM mst_visitor_room');
                    $room_options = [];
                    $room_options[] = ['0', __('ALL')];
                    while ($room_d = $room_q->fetch_row()) {
                        $room_options[] = [$room_d[0], $room_d[1]];
                    }
                    echo simbio_form_element::selectList('room_code', $room_options,'','class="form-control col-3"');
                    ?>
                </div>
                <div class="form-group divRow">
                    <div class="divRowContent">
                        <div>
                            <label style="width: 195px;"><?php echo __('Visit Date From'); ?></label>
                            <label><?php echo __('Visit Date Until'); ?></label>
                        </div>
                        <div id="range">
                            <input type="text" name="startDate" value="2000-01-01">
                            <span><?= __('to') ?></span>
                            <input type="text" name="untilDate" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Record each page'); ?></label>
                    <input type="text" name="recsEachPage" size="3" maxlength="3" class="form-control col-1" value="<?php echo $num_recs_show; ?>" /> 
                    <small class="text-muted"><?php echo __('Set between 20 and 200'); ?></small>
                </div>
            </div>
            <input type="button" name="moreFilter" class="btn btn-default" value="<?php echo __('Show More Filter Options'); ?>" />
            <input type="submit" name="applyFilter" class="btn btn-primary" value="<?php echo __('Apply Filter'); ?>" />
            <input type="hidden" name="reportView" value="true" />
        </form>
	</div>
    <script>
        $(document).ready(function(){
            const elem = document.getElementById('range');
            const dateRangePicker = new DateRangePicker(elem, {
                language: '<?= substr($sysconf['default_lang'], 0,2) ?>',
                format: 'yyyy-mm-dd',
            });
        })
    </script>
    <!-- filter end -->
    <div class="paging-area"><div class="pt-3 pr-3" id="pagingBox"></div></div>
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    // table spec
    $table_spec = 'visitor_count AS vc
        LEFT JOIN mst_visitor_room AS mr ON mr.unique_code = vc.room_code
        LEFT JOIN (member AS m LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id)
        ON vc.member_id=m.member_id';

    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->table_attr = 'class="s-table table table-sm table-bordered"';
    $reportgrid->setSQLColumn('IF(vc.member_id IS NOT NULL, vc.member_id, \'NON-MEMBER\')  AS \''.__('Member ID').'\'',
        'vc.member_name AS \''.__('Visitor Name').'\'',
        'IF(mt.member_type_name IS NOT NULL, mt.member_type_name, \'NON-MEMBER\') AS \''.__('Membership Type').'\'',
        'mr.name AS \'' . __('Room Name') . '\'',
        'vc.institution AS \''.__('Institution').'\'',
        'vc.checkin_date AS \''.__('Visit Date').'\'');
    $reportgrid->setSQLorder('vc.member_id ASC');

    // is there any search
    $criteria = 'vc.visitor_id IS NOT NULL ';
    if (isset($_GET['member_type']) AND !empty($_GET['member_type'])) {
        $mtype = $_GET['member_type'];
        if (intval($mtype) < 0) {
            $criteria .= ' AND (vc.member_id IS NULL OR vc.member_id=\'\')';
        } else if (intval($mtype) > 0) {
            $criteria .= ' AND mt.member_type_id='.$mtype;
        }
    }
    if (isset($_GET['id_name']) AND !empty($_GET['id_name'])) {
        $id_name = $dbs->escape_string($_GET['id_name']);
        $criteria .= ' AND (vc.member_id LIKE \'%'.$id_name.'%\' OR vc.member_name LIKE \'%'.$id_name.'%\')';
    }
    if (isset($_GET['institution']) AND !empty($_GET['institution'])) {
        $institution = $dbs->escape_string(trim($_GET['institution']));
        $criteria .= ' AND vc.institution LIKE \'%'.$institution.'%\'';
    }
    if (isset($_GET['room_code']) AND !empty($_GET['room_code'])) {
        $roomcode = $dbs->escape_string(trim($_GET['room_code']));
        $criteria .= ' AND vc.room_code = \''.$roomcode.'\'';
    }
    // register date
    if (isset($_GET['startDate']) AND isset($_GET['untilDate'])) {
        $criteria .= ' AND (TO_DAYS(vc.checkin_date) BETWEEN TO_DAYS(\''.$_GET['startDate'].'\') AND
            TO_DAYS(\''.$_GET['untilDate'].'\'))';
    }
    if (isset($_GET['recsEachPage'])) {
        $recsEachPage = (integer)$_GET['recsEachPage'];
        $num_recs_show = ($recsEachPage >= 20 && $recsEachPage <= 200)?$recsEachPage:$num_recs_show;
    }
    $reportgrid->setSQLCriteria($criteria);

    // show spreadsheet export button
    $reportgrid->show_spreadsheet_export = true;

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);

    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set).'\');'."\n";
    echo '</script>';
	$xlsquery = 'SELECT IF(vc.member_id IS NOT NULL, vc.member_id, \'NON-MEMBER\')  AS \''.__('Member ID').'\''.
        ', vc.member_name AS \''.__('Visitor Name').'\''.
        ', IF(mt.member_type_name IS NOT NULL, mt.member_type_name, \'NON-MEMBER\') AS \''.__('Membership Type').'\''.
        ', vc.institution AS \''.__('Institution').'\''.
        ', mr.name AS \''.__('Room Name').'\''.
        ', vc.checkin_date AS \''.__('Visit Date').'\''.
		' FROM '.$table_spec.' WHERE '.$criteria. ' ORDER BY vc.member_id ASC';

		unset($_SESSION['xlsdata']); 
		$_SESSION['xlsquery'] = $xlsquery;
		$_SESSION['tblout'] = "visitor_list";

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
