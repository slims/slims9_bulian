<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-10 15:45:36
 * @modify date 2022-05-11 07:51:34
 * @license GPLv3
 * @desc [description]
 */

//  Biblio detail comment

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB.'admin/default/session.inc.php';
}

// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-masterfile');

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('master_file', 'r');
$can_write = utility::havePrivilege('master_file', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    foreach ($_POST['itemID'] as $id) {
        \SLiMS\DB::getInstance()->prepare('DELETE FROM comment WHERE comment_id = ?')->execute([$id]);
    }
    utility::jsToastr(__('Success'), __('All Data Successfully Deleted'), 'success');
    echo '<script>parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\')</script>';
    exit;
}

?>

<div class="menuBox">
    <div class="menuBoxInner masterFileIcon">
    <div class="per_title">
        <h2><?php echo __('Comment Management'); ?></h2>
    </div>
    <?php if (!is_null(config('3rd_party_comment'))): ?>
        <div class="infoBox">
            <?= __('3rd party comment management has been activated, all comment will handled by it . This page is not use more.');?>
        </div>
    <?php endif; ?>
    <div class="sub_section">
        <div class="btn-group">
            <a href="<?php echo MWB; ?>master_file/detail_comment.php" class="btn btn-default"><?php echo __('Comment List'); ?></a>
        </div>
        <form name="search" action="<?php echo MWB; ?>master_file/detail_comment.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
            <input type="text" name="keywords" class="form-control col-md-3" />
            <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
        </form>
    </div>
    </div>
</div>
<?php
// create datagrid
$datagrid = new simbio_datagrid();

$table = 'comment AS c
    INNER JOIN biblio AS b ON b.biblio_id = c.biblio_id
    INNER JOIN member AS m ON m.member_id = c.member_id';

$datagrid->setSQLColumn('c.comment_id', 'b.title', 'c.comment AS `' . __('Comment') . '`', 'm.member_name AS `' . __('Comment By') . '`', 'c.input_date AS `' . __('Comment At') . '`');
$datagrid->setSQLorder('c.input_date DESC');
$datagrid->invisible_fields = [0];

// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $keywords = utility::filterData('keywords', 'get', true, true, true);
    $criteria_str = "c.comment LIKE '%{$keywords}%' OR m.member_name LIKE '%{$keywords}%'";
    $datagrid->setSQLCriteria($criteria_str);
}

function addTitle($dbs, $data)
{
    return '<strong>' . __('Title') . '</strong> : <br>' . $data[1] . ' <hr/></br> <div style="line-height: 1.1;">'.$data[2].'</div>';
}

$datagrid->modifyColumnContent(2, 'callback{addTitle}');

// set table and table header attributes
$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// edit and checkbox property
$datagrid->edit_property = false;
$datagrid->chbox_property = array('itemID', __('Delete'));
// set delete proccess URL
$datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];
$datagrid->column_width = array(0 => '5%', 1 => '75%');
// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table, 20, $can_read);

if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords'));
    echo '<div class="infoBox">' . $msg . ' : "' . htmlspecialchars($_GET['keywords']) . '"<div>' . __('Query took') . ' <b>' . $datagrid->query_time . '</b> ' . __('second(s) to complete') . '</div></div>';
}
echo $datagrid_result;