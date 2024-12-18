<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-04-02 09:06:39
 * @modify date 2023-04-02 15:17:17
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\Config;

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
// main system configuration
require '../../../sysconfig.inc.php';
// start the session
require SB . 'admin/default/session.inc.php';
}
// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');

require SB . 'admin/default/session_check.inc.php';
require SIMBIO . 'simbio_FILE/simbio_directory.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_DB/simbio_dbop.inc.php';

if (isset($_POST['updateData'])) {
    foreach($_POST['database_backup'] as $option => $value) {
        if (is_array($value)) {
            foreach ($value as $suboption => $subvalue) {
                $_POST['database_backup'][$option][$suboption] = in_array($subvalue, [0,1]) ? (bool)$subvalue : $subvalue;
            }
        } else {
            $_POST['database_backup'][$option] = in_array($value, [0,1]) ? (bool)$value : $value;
        }
    }

    if (Config::createOrUpdate('database_backup', $_POST['database_backup'])) {
        toastr(__('Data has been saved'))->success();
    } else {
        toastr(__('Failed to save data'))->error();
    }
    exit;
}

ob_start();
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="updateData" value="' . __('Save Settings') . '" class="btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

$selectData = [
    [1, __('Enable')],
    [0, __('Disable')]
];
foreach (config('database_backup') as $option => $value) {
    if (is_array($value)) {
        $advopt = '<button type="button" id="showadv" class="btn btn-outline-primary">' . __('Show') . '</button>';
        $advopt .= '<div id="advopt" style="display : none">';
        foreach ($value as $option_in_value => $subvalue) {
            $advopt .= '<div>';
            $advopt .= '<label>' . ucwords(str_replace('-', ' ', $option_in_value)) . '</label>';

            $attributeName = 'database_backup['.$option.'][' . $option_in_value . ']';
            if (is_bool($subvalue)) {
                $advopt .= simbio_form_element::selectList($attributeName, $selectData, (int)$subvalue, 'class="form-control"');
            } else {
                $advopt .= simbio_form_element::textField('text', $attributeName, $subvalue, 'class="form-control"');
            }
        }
        $advopt .= '</div>';
        $form->addAnything('Advance Options', $advopt);
        continue;
    }

    if (is_bool($value)) {
        $form->addSelectList('database_backup['.$option.']', ucwords(str_replace('-', ' ', $option)), $selectData, (int)$value, 'class="form-control"');  
    }

    if (is_string($value)) {
        $form->addTextField('text', 'database_backup['.$option.']', ucwords(str_replace('-', ' ', $option)), $value, 'class="form-control"');
    }
}
// print out the form object
echo $form->printOut();
$confirm = __('Are you sure you want to make it automatically on first login?. It will take longer to complete if your SLiMS has a large collection.');
echo <<<HTML
<script>
    $('#showadv').click(function() {
        $('#advopt').slideDown()
        $(this).addClass('d-none')
    })
    $('select[name="database_backup[auto]"]').change(function(){
        if ($(this).val() == 1 && !confirm('{$confirm}')) {
            $(this).val(0)
            return
        }
    })
</script>
HTML;
$content = ob_get_clean();
require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/notemplate_page_tpl.php';