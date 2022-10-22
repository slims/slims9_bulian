<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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

/* Dynamic content Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/template_parser/simbio_template_parser.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO . 'simbio_FILE/simbio_file_upload.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

// Image upload handler from CKEditor5
if (!empty($_FILES['upload']) AND $_FILES['upload']['size']) {
    header('Content-Type: application/json');
    try {
        // Check base directory
        if (!is_writable(IMGBS)) throw new Exception(IMGBS . ' is not writeable!');
        // Create content directory if not exists
        if (!is_dir(IMGBS . 'content') && !mkdir(IMGBS . 'content')) throw new Exception('Failed to create ' . IMGBS . 'content');

        ob_start();
        // create upload object
        $image_upload = new simbio_file_upload();
        $image_upload->setAllowableFormat($sysconf['allowed_images']);
        $image_upload->setMaxSize($sysconf['max_image_upload']*1024);
        $image_upload->setUploadDir(IMGBS.'content');
        // upload the file and change all space characters to underscore
        $img_upload_status = $image_upload->doUpload('upload', md5(date('this')));
        ob_end_clean();
        if ($img_upload_status == UPLOAD_SUCCESS) {
            echo json_encode(['uploaded' => true, 'url' => SWB . 'images/content/'.$image_upload->new_filename]);
        } else {
            throw new Exception($image_upload->error);
        }
    } catch (Exception $e) {
        echo json_encode(['uploaded' => false, 'error' => ['message' => 'Error : ' . $e->getMessage()]]);
    }
    exit;
}

/* RECORD OPERATION */
if (isset($_POST['saveData'])) {
    $contentTitle = trim(strip_tags($_POST['contentTitle']));
    $contentPath = trim(strip_tags($_POST['contentPath']));
    // check form validity
    if (empty($contentTitle) OR empty($contentPath)) {
        utility::jsToastr('Error', __('Title or Path can\'t be empty!'), 'error');
        exit();
    } else {
        $data['content_title'] = $dbs->escape_string(strip_tags(trim($contentTitle)));
        $data['content_path'] = strtolower($dbs->escape_string(strip_tags(trim($contentPath))));
        $data['is_news'] = '0';
        if ($_POST['isNews'] && $_POST['isNews'] == '1') {
            $data['is_news'] = '1';
        }
        
        if (!empty($_POST['publishDate']))
        {
            $data['publish_date'] = $dbs->escape_string($_POST['publishDate']);
        }

        $data['is_draft'] = $_POST['isDraft'] == '1' ? '1' : '0';
        $data['content_desc'] = $dbs->escape_string(trim($_POST['contentDesc']));
        $data['input_date'] = date('Y-m-d H:i:s');
        $data['last_update'] = date('Y-m-d H:i:s');

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            // filter update record ID
            $updateRecordID = (integer)$_POST['updateRecordID'];
            // update the data
            $update = $sql_op->update('content', $data, 'content_id='.$updateRecordID);
            if ($update) {
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['content_title'].' update content data ('.$data['content_title'].') with contentname ('.$data['contentname'].')', 'Content', 'Update');
                utility::jsToastr('Success', __('Content data updated'), 'success');
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(parent.$.ajaxHistory[0].url);</script>';
            } else { utility::jsToastr('Error', __('Content data FAILED to update!')."\nDEBUG : ".$sql_op->error, 'error'); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            if ($sql_op->insert('content', $data)) {
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' add new content ('.$data['content_title'].') with contentname ('.$data['contentname'].')');
                utility::jsToastr('Success', __('Content data saved'), 'success');
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else {  utility::jsToastr('Error', __('Content data FAILED to save!')."\n".$sql_op->error, 'error'); }
            exit();
        }
    }
    exit();
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!($can_read AND $can_write)) {
        die();
    }
    /* DATA DELETION PROCESS */
    $sql_op = new simbio_dbop($dbs);
    $failed_array = array();
    $error_num = 0;
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array((integer)$_POST['itemID']);
    }
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = (integer)$itemID;
        // get content data
        $content_q = $dbs->query('SELECT content_title FROM content WHERE content_id='.$itemID);
        $content_d = $content_q->fetch_row();
        if (!$sql_op->delete('content', "content_id='$itemID'")) {
            $error_num++;
        } else {
            // write log
            utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' DELETE content ('.$content_d[0].')','Content', 'Delete');
        }
    }

    // error alerting
    if ($error_num == 0) {
        utility::jsToastr('Delete', __('All Data Successfully Deleted'), 'success');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.($_POST['lastQueryStr']??$_SERVER['QUERY_STRING']).'\');</script>';
    } else {
        utility::jsToastr('Error', __('Some or All Data NOT deleted successfully!\nPlease contact system administrator'), 'errpr');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.($_POST['lastQueryStr']??$_SERVER['QUERY_STRING']).'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

/* search form */
?>
<div class="menuBox">
    <div class="menuBoxInner systemIcon">
        <div class="per_title">
            <h2><?php echo __('Content'); ?></h2>
        </div>
        <div class="sub_section">
            <div class="btn-group">
                <a href="<?php echo MWB; ?>system/content.php" class="btn btn-default"><?php echo __('Content List'); ?></a>
                <a href="<?php echo MWB; ?>system/content.php?action=detail" class="btn btn-default"><?php echo __('Add New Content'); ?></a>
            </div>
            <form name="search" action="<?php echo MWB; ?>system/content.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
                <input type="text" name="keywords" class="form-control col-md-3" />
                <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
            </form>
        </div>
    </div>
</div>
<?php
/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
    }
    /* RECORD FORM */
    // try query
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    $rec_q = $dbs->query('SELECT * FROM content WHERE content_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // texteditor instance
    ?>
    <form class="d-flex px-3" id="contentForm" method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" target="submitExec">
        <?php
        if ($rec_q->num_rows > 0)
        {
            ?>
            <input type="hidden" name="updateRecordID" value="<?= $rec_d['content_id']??0 ?>"/>
            <?php
        }
        ?>
        <div id="editor" class="col-8">
            <div id="titleContent" class="my-3">
                <label class="font-weight-bold"><?= __('Content Title') ?>*</label>
                <input id="setPath" type="text" name="contentTitle" value="<?= $rec_d['content_title']??'' ?>" class="form-control"/>
            </div>
            <div id="toolbarContainer"></div>
            <div id="outerContent" style="background-color: #e1e1e1; padding: 30px">
                <div id="contentDesc" class="rounded-lg px-5" style="background-color: white; min-height: 800px"><?= str_replace(['<script>','</script>'], '', $rec_d['content_desc']??'')??'' ?></div>
            </div>
        </div>
        <div id="detail" class="col-4">
            <fieldset>
                <div class="d-flex justify-content-between">
                    <label><?= __('Content Settings') ?></label>
                    <button type="submit" name="saveData" class="btn btn-primary"><?= $rec_q->num_rows == 0 ? __("Save") : __("Update") ?></button>
                </div>
                <hr>
                <label class="m-0 font-weight-bold"><?= __('Publish at') ?></label>
                <input type="date" name="publishDate" class="form-control" value="<?= $rec_d['publish_date']??''?>"/>
                <label class="m-0 font-weight-bold"><?= __('This is News') ?>*</label>
                <?= simbio_form_element::selectList('isNews', [[1, __('Yes')],[0,__('No')]], $rec_d['is_news']??'', 'class="form-control"') . '&nbsp;'; ?>
                <label class="m-0 font-weight-bold"><?= __('Path (Must be unique)') ?>*</label>
                <input type="text" name="contentPath" value="<?= $rec_d['content_path']??'' ?>" id="path" class="form-control"/>
                <small id="warningChar" class="text-danger d-none"><?= __('Max 20 character') ?></small>
                <label class="m-0 font-weight-bold"><?= __('Draft?') ?>*</label><br>
                <?= simbio_form_element::selectList('isDraft', [[0, __('No')],[1,__('Yes')]], $rec_d['is_draft']??'', 'class="form-control"') . '&nbsp;'; ?>
            </fieldset>
        </div>
    </form>
    <iframe name="submitExec" class="d-none"></iframe>
    <script type="text/javascript">
        $(document).ready(
          function() {
            // automatic set path based on inputed title
            $('#setPath').on('keyup', function(){
                let text = $(this).val().replace(/[^a-zA-Z]/g, '-');
                let path = $('#path');

                if (text.length <= 20)
                {
                    path.val(text.toLowerCase());
                }
            });

            // manual input for path
            $('#path').keyup(function(e){
                // Reset
                $(this).removeClass('border border-danger');
                $('#warningChar').removeClass('d-block');
                $(this).removeAttr('maxlength');

                try {
                    if ($(this).val().length > 20)
                    {
                        throw "Stop";
                    }

                    let filter = $(this).val().replace(/[^a-zA-Z]/g, '-');
                    $(this).val(filter.toLowerCase());

                } catch (error) {
                    $(this).addClass('border border-danger');
                    $('#warningChar').addClass('d-block');
                    $(this).attr('maxlength', '20');
                }
            })

            let editorInstance = '';

            DecoupledEditor
                .create(document.querySelector('#contentDesc'),{  
                    toolbar: {shouldNotGroupWhenFull: true},
                    ckfinder: {uploadUrl: '<?php echo $_SERVER['PHP_SELF'];?>'}

                })
                .then( editor => {
                    const toolbarContainer = document.querySelector('#toolbarContainer');
                    toolbarContainer.appendChild( editor.ui.view.toolbar.element );
                    editorInstance = editor
                })
                .catch( error => {
                    console.log(error);
                });

            // when form submited retrive content
            // and put into hidden textarea
            $('#contentForm').submit(function(){
                $(this).append('<textarea name="contentDesc" class="d-none">' + editorInstance.getData() + '</textarea>');
            })
        });
        </script>
    <?php
} else {
    /* USER LIST */
    // table spec
    $table_spec = 'content AS c';

    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $datagrid->setSQLColumn('c.content_id',
            'c.content_title AS \''.__('Content Title').'\'',
            'c.content_path AS \''.__('Path (Must be unique)').'\'',
            'c.last_update AS \''.__('Last Updated').'\'');
    } else {
        $datagrid->setSQLColumn('c.content_title AS \''.__('Content Title').'\'',
            'c.content_path AS \''.__('Path (Must be unique)').'\'',
            'c.last_update AS \''.__('Last Updated').'\'');
    }
    $datagrid->setSQLorder('c.last_update DESC');

    // is there any search
    $criteria = 'c.content_id IS NOT NULL ';
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = utility::filterData('keywords', 'get', true, true, true);
       $criteria .= " AND MATCH(content_title, content_desc) AGAINST('$keywords')";
    }
    $datagrid->setSQLCriteria($criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</div>';
    }

    echo $datagrid_result;
}
/* main content end */
