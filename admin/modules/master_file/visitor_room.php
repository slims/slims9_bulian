<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-25 11:41:11
 * @modify date 2023-01-26 14:54:00
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\Url;

define('INDEX_AUTH', '1');

require '../../../sysconfig.inc.php';

require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
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

if (isset($_POST['saveData']))
{
    $SQL = trim(
        // Query statement
        (isset($_POST['updateRecordID']) ? 'UPDATE' : 'INSERT INTO') . 

        // set prepared data
        ' `mst_visitor_room` SET `name` = ?, `unique_code` = ?' . 

        // with time or criteria
        (
            !isset($_POST['updateRecordID']) ? 
                ', created_at = current_timestamp()' 
                : 
                ' WHERE id = ' . ((int)$_POST['updateRecordID'])
        )
    );

    try {
        $state = \SLiMS\DB::getInstance()->prepare($SQL);
        $state->execute([$_POST['name'], $_POST['code']]);

        toastr(__('Success saved data'))->success();
    } catch (PDOException $e) {
        toastr($e->getMessage())->error();
    } catch (Exception $e) {
        toastr($e->getMessage())->error(); 
    }
    redirect()->simbioAJAX(Url::getSelf());
    exit;
}
?>
<div class="menuBox">
    <div class="menuBoxInner masterFileIcon">
    <div class="per_title">
        <h2><?php echo __('Visitor Room'); ?></h2>
    </div>
    <div class="sub_section">
        <div class="btn-group">
            <a href="<?php echo MWB; ?>master_file/visitor_room.php" class="btn btn-default"><?php echo __('Room List'); ?></a>
            <a href="<?php echo MWB; ?>master_file/visitor_room.php?action=add" class="btn btn-default"><?php echo __('Add New Room'); ?></a>
        </div>
        <form name="search" action="<?php echo MWB; ?>master_file/visitor_room.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
            <input type="text" name="keywords" class="form-control col-md-3" />
            <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
        </form>
    </div>
    </div>
</div>
<?php
if ((isset($_GET['action']) && !empty($_GET['action'])) || (isset($_POST['itemID']) && !empty($_POST['itemID'])))
{
    if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
    }
    /* RECORD FORM */
    $rec_q = \SLiMS\DB::getInstance()->prepare('SELECT * FROM mst_visitor_room WHERE id=?');
    $rec_q->execute([isset($_POST['itemID'])?$_POST['itemID']:0]);
    $rec_d = $rec_q->fetch(PDO::FETCH_ASSOC);

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-default"';

    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if ($rec_q->rowCount() > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        $form->record_id = $_POST['itemID'];
        // form record title
        $form->record_title = $rec_d['name'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // name
    $form->addTextField('text', 'name', __('Room Name').'*', $rec_d['name']??'', 'style="width: 60%;" class="form-control"');
    // unique code
    $form->addTextField('text', 'code', __('Room Code').'*', $rec_d['unique_code']??utility::createRandomString(5), 'style="width: 20%;" maxlength="3" class="form-control col-1"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit gmd data').' : <b>'.$rec_d['name'].'</b>  <br />'.__('Last Update').' '.$rec_d['updated_at'].'</div>'; //mfc
    }
    // print out the form object
    echo $form->printOut();
}
else
{
    // create datagrid
    $datagrid = new simbio_datagrid();

    $table = 'mst_visitor_room';

    $datagrid->setSQLColumn('id', 'name AS `' . __('Room Name') . '`', 'unique_code AS `' . __('Unique Code') . '`', 'created_at AS `' . __('Created At') . '`');
    $datagrid->setSQLorder('created_at DESC');

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $keywords = utility::filterData('keywords', 'get', true, true, true);
        $criteria_str = "name LIKE '%{$keywords}%' OR unique_code LIKE '%{$keywords}%'";
        $datagrid->setSQLCriteria($criteria_str);
    }

    function getLink($db, $data)
    {
        return '<a href="#" class="btn btn-link notAJAX copylink" data-code="'.$data[2].'" title="' . __('Copy this room link') . '"><i class="fa fa-clipboard"></i> ' . $data[2] . '</a>';
    }

    $datagrid->modifyColumnContent(2, 'callback{getLink}');

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];
    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table, 20, $can_read);

    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords'));
        echo '<div class="infoBox">' . $msg . ' : "' . htmlspecialchars($_GET['keywords']) . '"<div>' . __('Query took') . ' <b>' . $datagrid->query_time . '</b> ' . __('second(s) to complete') . '</div></div>';
    }
    echo $datagrid_result;
    ?>
    <script>
        $(document).ready(function() {
            $('.copylink').click(function(e){
                e.preventDefault()
                navigator.clipboard.writeText(`<?= Url::getSlimsBaseUri() ?>?p=visitor&room=${$(this).data('code')}`)
						.then(() => {
                            top.toastr.info('<?= __('Success copied visitor room link') ?>');
                        })
                        .catch(err => {
                            top.toastr.error(err);
                        })
            })
        });
    </script>
    <?php
}