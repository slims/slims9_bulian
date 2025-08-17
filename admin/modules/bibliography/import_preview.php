<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-02-04 15:23:54
 * @modify date 2023-07-09 16:10:45
 * @license GPLv3
 * @desc CSV viewer before imported to SLiMS
 */

use SLiMS\Url;
use SLiMS\Csv\Reader;
use SLiMS\Filesystems\Storage;

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB.'admin/default/session.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r') || utility::havePrivilege('membership', 'r');
$can_write = utility::havePrivilege('bibliography', 'w') || utility::havePrivilege('membership', 'w');

if (!$can_read) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// create upload object
$files_disk = Storage::files();
$csv_file = 'temp' . DS . $_SESSION['csv']['name'] . '.csv';

if (isset($_GET['cancel'])) {
    $action = $_SESSION['csv']['action']??MWB . 'bibliography/import.php';

    // remove csv file
    $files_disk->delete($csv_file);

    // clear csv session
    unset($_SESSION['csv']['name']);
    
    // redirect to previous content
    redirect()->simbioAJAX($action);
}
 

if ( $files_disk->isExists($csv_file) ) : 
?>

<div class="menuBox">
    <div class="menuBoxInner importIcon">
        <div class="per_title">
        <h2><?php echo __('Preview Import'); ?></h2>
        </div>
        <div class="infoBox">
            <?php echo __('Preview your data before import to SLiMS'); ?>
        </div>
    </div>
</div>
<div id="progress" class="d-none my-2 mx-2">
    <p class="w-100 block"><?= __('Importing data to SLiMS') ?></p>
    <div class="progress">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%"></div>
    </div>
    <div class="d-flex align-items-center justify-content-center my-3">
        <button id="triggerProgressIframe" iframe-state="open" translate-when-open="<?= __('Show import process') ?>" translate-when-close="<?= __('Hide import process') ?>" class="btn btn-outline-secondary"><?= __('Show import process') ?></button>
    </div>
</div>
<iframe name="importProgressFrame" id="importProgressFrame" class="w-100 d-none" style="height: 50vh"></iframe>
<div id="preview" class="my-3 mx-2">
    <form id="formPreview" action="<?= $_SESSION['csv']['action']??MWB . 'bibliography/import.php' ?>" method="post" target="importProgressFrame">
        <h4 class="my-3"><?= __('Imported data list preview') ?></h4>
        <p><?= __('Make sure all the data that appears matches the column you enter.') ?></p>
        <div class="d-flex flex-row">
            <label>Show per :</label>
            <select class="perpage form-control col-1">
                <?php 
                foreach([5,10,15,20,25,30,35,40,45,50,0] as $num)  {
                    $selected = trim(isset($_GET['perpage']) && $_GET['perpage'] == $num ? 'selected' : '');
                    echo '<option value="' . $num . '" ' . $selected . '>'.($num === 0 ? __('All') : $num).'</option>';
                }
                ?>
            </select>
        </div>
        <input type="hidden" name="process" value="yes"/>
        <div class="d-flex flex-row">
            <button type="submit" name="doImport" class="btn btn-primary mx-1"><?= __('Import Now') ?></button>
            <a href="<?= Url::getSelf() ?>?cancel=true" class="btn btn-secondary mx-1">Cancel</a>
        </div>
        <div class="overflow-auto">
        <?php
        // set PHP time limit
        set_time_limit(0);
        // set ob implicit flush
        ob_implicit_flush();

        $limit = $_GET['perpage']??5;

        try {
            // set column information based on csv import section
            switch ($_SESSION['csv']['section']) {
                case 'biblio':
                    $header = 'No.,title,gmd_name,edition,isbn_issn,publisher_name,publish_year,collation,series_title,call_number,language_name,place_name,classification,notes,image,sor,authors,topics,item_code';
                    break;

                case 'item':
                    $header = 'No.,item_code,call_number,coll_type_name,inventory_code,received_date,supplier_name,order_no,location_name,order_date,item_status_name,site,source,invoice,price,price_currency,invoice_date,input_date,last_update,title';
                    break;
                
                case 'membership':
                    $header = 'No.,member_id,member_name,gender,member_type_name,member_email,member_address,postal_code,inst_name,is_new,member_image,pin,member_phone,member_fax,member_since_date,register_date,expire_date,birth_date,member_notes';
                    if ($_SESSION['csv']['password'] == 1) $header .= ',mpasswd';
                    break;

                default:
                    $header = '';
                    break;
            }

            // show error box
            if (empty($header)) die('<div class="errorBox">' . __('Uknown CSV import section!') . '</div>');

            $table = new simbio_table();
            $table->table_attr = 'class="table table-bordered"';

            $file = $files_disk->readStream('temp' . DS . $_SESSION['csv']['name'] . '.csv');

            $reader = new Reader([
                'separator' => trim($_SESSION['csv']['format']['fieldSep']),
                'enclosed_with' => trim($_SESSION['csv']['format']['fieldEnc']),
                'record_separator' => [
                    'newline' => "\n",
                    'return' => "\t"
                ]
            ]);
            $reader->readFromStream($file)->setLimit($_GET['perpage']??5);

            // set header
            $table->appendTableRow(explode(',', $header));

            // iterate field data
            $reader->each(function(&$field, $row, $index, $column_value) use($table) {
                // set cell attribute
                if ($index != 12) $field[$index] = htmlspecialchars($field[$index]);

                $table->setCellAttr($row, $index+1, 'class="alterCell" valign="top" style="width: '.strlen($column_value).'px;"');

            });

            // append data to table row
            $fields = $reader->getFields();
            foreach($fields as $order => $field) $table->appendTableRow(array_merge([$order + 1], $field));

            // print out the table
            echo $table->printTable();
        } catch (Exception $e) {
            debug($e->getMessage());
        }
        ?>
        <div class="d-flex flex-row">
            <button type="submit" name="doImport" class="btn btn-primary mx-1"><?= __('Import Now') ?></button>
            <a href="<?= Url::getSelf() ?>?cancel=true" class="btn btn-secondary mx-1">Cancel</a>
        </div>
    <form>
    <script>
        $(document).ready(function(){
            $('#formPreview').submit(function() {
                $('#preview').fadeOut()
                $('#progress').fadeIn()
            });

            $('#triggerProgressIframe').click(function(){
                let state = $(this).attr('iframe-state')

                if (state === 'open') {
                    var translate = $(this).attr('translate-when-close')
                    $(this).removeClass('btn-outline-secondary')
                    $(this).addClass('btn-outline-primary')
                    $(this).attr('iframe-state', 'close')
                    $(this).text(translate)
                    $('#importProgressFrame').removeClass('d-none')
                    $('#importProgressFrame').addClass('d-block')
                } else {
                    var translate = $(this).attr('translate-when-open')
                    $(this).removeClass('btn-outline-primary')
                    $(this).addClass('btn-outline-secondary')
                    $(this).attr('iframe-state', 'open')
                    $(this).text(translate)
                    $('#importProgressFrame').removeClass('d-block')
                    $('#importProgressFrame').addClass('d-none')

                }

            });

            $('.perpage').change(function(){
                let number = $(this).val()

                if (number == 0 && !confirm('<?= __('Loading all the data in the preview section may take longer. Would you like to continue the process?') ?>'))
                {
                    $(this).val() = 5
                    return;
                }

                $('#mainContent').simbioAJAX(`<?= Url::getSelf() ?>?perpage=${number}`)
            })
        })
    </script>
</div>
<?php else :
$action = $_SESSION['csv']['action']??MWB . 'bibliography/import.php';
unset($_SESSION['csv']);
?>
<script>$('#mainContent').simbioAJAX(`<?php echo $action; ?>`);</script>
<?php
endif; 
?>
