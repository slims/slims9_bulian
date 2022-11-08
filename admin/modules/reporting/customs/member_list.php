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

/* Library Member List */

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

$page_title = 'Members Report';
$reportView = false;
$num_recs_show = 20;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <div class="per_title">
      <h2><?php echo __('Member List'); ?></h2>
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
                    while ($mtype_d = $mtype_q->fetch_row()) {
                        $mtype_options[] = array($mtype_d[0], $mtype_d[1]);
                    }
                    echo simbio_form_element::selectList('member_type', $mtype_options,'','class="form-control col-2"');
                    ?>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Member ID').'/'.__('Member Name'); ?></label>
                    <?php echo simbio_form_element::textField('text', 'id_name', '', 'class="form-control col-3"'); ?>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Sex'); ?></label>
                    <?php
                    $gender_chbox[0] = array('ALL', __('ALL'));
                    $gender_chbox[1] = array('1', __('Male'));
                    $gender_chbox[2] = array('0', __('Female'));
                    echo simbio_form_element::selectList('gender', $gender_chbox, 'ALL','class="form-control col-1"');
                    ?>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Address'); ?></label>
                    <?php echo simbio_form_element::textField('text', 'address', '', 'class="form-control col-3"'); ?>
                </div>
                <div class="form-group divRow">
                    <div class="divRowContent">
                        <div>
                            <label style="width: 195px;"><?php echo __('Register Date From'); ?></label>
                            <label><?php echo __('Register Date Until'); ?></label>
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
                    <input type="text" name="recsEachPage" class="form-control col-1" size="3" maxlength="3" value="<?php echo $num_recs_show; ?>" />
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
    $table_spec = 'member AS m
        LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id';

    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->table_attr = 'class="s-table table table-sm table-bordered"';
    $reportgrid->setSQLColumn('m.member_id AS \''.__('Member ID').'\'',
        'm.member_name AS \''.__('Member Name').'\'',
        'mt.member_type_name AS \''.__('Membership Type').'\'');
    $reportgrid->setSQLorder('member_name ASC');

    // is there any search
    $criteria = 'm.member_id IS NOT NULL ';
    if (isset($_GET['member_type']) AND !empty($_GET['member_type'])) {
        $mtype = intval($_GET['member_type']);
        $criteria .= ' AND m.member_type_id='.$mtype;
    }
    if (isset($_GET['id_name']) AND !empty($_GET['id_name'])) {
        $id_name = $dbs->escape_string($_GET['id_name']);
        $criteria .= ' AND (m.member_id LIKE \'%'.$id_name.'%\' OR m.member_name LIKE \'%'.$id_name.'%\')';
    }
    if (isset($_GET['gender']) AND $_GET['gender'] != 'ALL') {
        $gender = intval($_GET['gender']);
        $criteria .= ' AND m.gender='.$gender;
    }
    if (isset($_GET['address']) AND !empty($_GET['address'])) {
        $address = $dbs->escape_string(trim($_GET['address']));
        $criteria .= ' AND m.member_address LIKE \'%'.$address.'%\'';
    }
    // register date
    if (isset($_GET['startDate']) AND isset($_GET['untilDate'])) {
        $criteria .= ' AND (TO_DAYS(m.register_date) BETWEEN TO_DAYS(\''.$_GET['startDate'].'\') AND
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
	$xlsquery = 'SELECT m.member_id AS \''.__('Member ID').'\''.
        ', m.member_name AS \''.__('Member Name').'\''.
        ', mt.member_type_name AS \''.__('Membership Type').'\' FROM '.$table_spec.' WHERE '.$criteria;

	unset($_SESSION['xlsdata']);
	$_SESSION['xlsquery'] = $xlsquery;
	$_SESSION['tblout'] = "member_list";
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
