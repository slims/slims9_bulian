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

/* Due Date Warning Report */

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
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS.'reporting/report_dbgrid.inc.php';

$page_title = 'Overdued List Report';
$reportView = false;
$num_recs_show = 20;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <div class="per_title">
      <h2><?php echo __('Due Date Warning'); ?></h2>
    </div>
    <div class="infoBox">
        <?php echo __('Report Filter'); ?>
        <div><?php echo __('This report loan items which will due in 3 to 0 days'); ?></div>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
        <div id="filterForm">
            <div class="form-group divRow">
                <label><?php echo __('Member ID').'/'.__('Member Name'); ?></label>
                <?php
                echo simbio_form_element::textField('text', 'id_name', '', 'class="form-control col-4"');
                ?>
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
    <!-- filter end -->
    <div class="paging-area"><div class="pt-3 pr-3" id="pagingBox"></div></div>
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    // table spec
    $table_spec = 'member AS m
        LEFT JOIN loan AS l ON m.member_id=l.member_id';

    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->setSQLColumn('m.member_id AS \''.__('Member ID').'\'');
    $reportgrid->setSQLorder('MAX(l.due_date) DESC');
    $reportgrid->sql_group_by = 'm.member_id';

    $overdue_criteria = ' (l.is_lent=1 AND l.is_return=0 AND ( (TO_DAYS(due_date)-TO_DAYS(\''.date('Y-m-d').'\')) BETWEEN 0 AND 3)) ';
    // is there any search
    if (isset($_GET['id_name']) AND $_GET['id_name']) {
        $keyword = $dbs->escape_string(trim($_GET['id_name']));
        $words = explode(' ', $keyword);
        if (count($words) > 1) {
            $concat_sql = ' (';
            foreach ($words as $word) {
                $concat_sql .= " (m.member_id LIKE '%$word%' OR m.member_name LIKE '%$word%') AND";
            }
            // remove the last AND
            $concat_sql = substr_replace($concat_sql, '', -3);
            $concat_sql .= ') ';
            $overdue_criteria .= ' AND '.$concat_sql;
        } else {
            $overdue_criteria .= " AND m.member_id LIKE '%$keyword%' OR m.member_name LIKE '%$keyword%'";
        }
    }
    if (isset($_GET['recsEachPage'])) {
        $recsEachPage = (integer)$_GET['recsEachPage'];
        $num_recs_show = ($recsEachPage >= 5 && $recsEachPage <= 200)?$recsEachPage:$num_recs_show;
    }
    $reportgrid->setSQLCriteria($overdue_criteria);

    // set table and table header attributes
    $reportgrid->table_attr = 'class="s-table table table-sm table-bordered"';
    $reportgrid->table_header_attr = 'class="dataListHeaderPrinted"';
    $reportgrid->column_width = array('1' => '80%');

    // callback function to show overdued list
    function showOverduedList($obj_db, $array_data)
    {
        global $date_criteria;

        // member name
        $member_q = $obj_db->query('SELECT member_name, member_email, member_phone, member_mail_address FROM member WHERE member_id=\''.$array_data[0].'\'');
        $member_d = $member_q->fetch_row();
        $member_name = $member_d[0];
        $member_mail_address = $member_d[3];
        unset($member_q);

        $_title_q = $obj_db->query('SELECT l.item_code, b.title, l.loan_date,
            l.due_date, (TO_DAYS(due_date)-TO_DAYS(DATE(NOW()))) AS \'Overdue Days\'
            FROM loan AS l
                LEFT JOIN item AS i ON l.item_code=i.item_code
                LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
            WHERE (l.is_lent=1 AND l.is_return=0 AND ( (TO_DAYS(due_date)-TO_DAYS(\''.date('Y-m-d').'\')) BETWEEN 0 AND 3) AND l.member_id=\''.$array_data[0].'\')');
        $_buffer = '<div class="font-weight-bold">'.$member_name.' ('.$array_data[0].')';
        $_buffer .= '<div>'.$member_mail_address;
        if (!empty($member_d[1])) $_buffer .= '<div id="' . $array_data[0] . 'emailStatus"></div>' . __('E-mail').': <a href="mailto:'.$member_d[1].'">'.$member_d[1].'</a> - <a class="usingAJAX btn btn-sm btn-outline-primary" href="' . MWB . 'membership/duedate_mail.php' . '" postdata="memberID=' . $array_data[0] . '" loadcontainer="' . $array_data[0] . 'emailStatus"><i class="fa fa-paper-plane-o"></i>&nbsp;' . __('Send Notification e-mail') . '</a><br/>';
        $_buffer .= __('Phone Number').': '.$member_d[2].'</div></div>';
        $_buffer .= '<table width="100%" cellspacing="0">';
        while ($_title_d = $_title_q->fetch_assoc()) {
            $_buffer .= '<tr>';
            $_buffer .= '<td valign="top" width="10%">'.$_title_d['item_code'].'</td>';
            $_buffer .= '<td valign="top" width="40%">'.$_title_d['title'].'</td>';
            $_buffer .= '<td width="1%" style="white-space:nowrap">'.__('Loan Date').': '.$_title_d['loan_date'].'</td><td width="1%" style="white-space:nowrap">'.__('Due Date').': '.$_title_d['due_date'].'</td>';
            $_buffer .= '</tr>';
        }
        $_buffer .= '</table>';
        return $_buffer;
    }
    // modify column value
    $reportgrid->modifyColumnContent(0, 'callback{showOverduedList}');

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);
    ?>
    <script type="text/javascript" src="<?php echo JWB . 'jquery.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo JWB . 'updater.js'; ?>"></script>
    <script type="text/javascript">
        // registering event for send email button
        $(document).ready(function () {
            parent.$('#pagingBox').html('<?php echo str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set) ?>');
            $('a.usingAJAX').click(function (evt) {
                evt.preventDefault();
                var anchor = $(this);
                // get anchor href
                var url = anchor.attr('href');
                var postData = anchor.attr('postdata');
                var loadContainer = anchor.attr('loadcontainer');
                if (loadContainer) {
                    container = jQuery('#' + loadContainer);
                    container.html('<div class="alert alert-info"><?= __('Please wait') ?>....</div>');
                }
                // set ajax
                if (postData) {
                    container.simbioAJAX(url, {method: 'post', addData: postData});
                } else {
                    container.simbioAJAX(url, {addData: {ajaxload: 1}});
                }
            });
        })
    </script>

    <?php
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
