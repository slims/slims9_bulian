<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-11 07:25:22
 * @modify date 2022-10-11 13:11:26
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\Currency;

// key to authenticate
define('INDEX_AUTH', '1');

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
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

if (!function_exists('addOrUpdateSetting')) {
    function addOrUpdateSetting($name, $value) {
        global $dbs;
        $sql_op = new simbio_dbop($dbs);
        $data['setting_value'] = $dbs->escape_string(serialize($value));

        $query = $dbs->query("SELECT setting_value FROM setting WHERE setting_name = '{$name}'");
        if ($query->num_rows > 0) {
            // update
            $sql_op->update('setting', $data, "setting_name='{$name}'");
        } else {
            // insert
            $data['setting_name'] = $name;
            $sql_op->insert('setting', $data);
        }
    }
}

if (isset($_POST['saveData']))
{
    // set setting
    $setting = [
        'enable' => $_POST['enable'],
        'region' => $_POST['region'],
        'detail' => [
            'attribute' => $_POST['attribute'],
            // 'textAttribute' => $_POST['textAttribute']
        ]
    ];
    
    // resetter
    if (config('custom_currency_locale.region') !== $_POST['region']) unset($setting['detail']);

    addOrUpdateSetting('custom_currency_locale', $setting);
    toastr(__('Successfully save currency configuration'))->success();
    echo '<script>top.$("#mainContent").simbioAJAX("' . $_SERVER['PHP_SELF'] . '")</script>';
    exit;
}


// create currency instance
$currency = new Currency;
?>

<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?= __('Currency Configuration'); ?></h2>
    </div>
    <div class="<?= $currency->isSupport() ? 'info' : 'error' ?>Box">
      <?php
        if (!$currency->isSupport())
        {
            echo '<b>' . __('Extension Intl must be enable first.') . "</b>";
            exit;
        }
      ?>
    </div>
  </div>
</div>
<?php
// get currency formatter to override default value of formatter
$currencyFormatter = $currency->getFormatter();

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

/* Set field */
$form->submit_button_attr = 'name="saveData" value="'.__('Save Settings').'" class="btn btn-default"';

// Enable or not
$form->addSelectList('enable', __('Enable system currency?'), [[1, __('Yes')],[0, __('No')]], config('custom_currency_locale.enable')??1 ,'class="form-control col-3"');

// set Locale
$form->addSelectList('region', __('Region'), $currency->getIsoCode(), config('custom_currency_locale.region', config('default_lang')) ,'class="select select2 form-control col-3"', __('By default region value same as default language'));

// set how many decimal character will show
$defaultDecimal = config('custom_currency_locale.detail.attribute.MAX_FRACTION_DIGITS', $currencyFormatter->getAttribute(NumberFormatter::MAX_FRACTION_DIGITS));
$form->addTextField('text', 'attribute[MAX_FRACTION_DIGITS]', __('Number of decimal position'), $defaultDecimal, 'style="width: 20%;" class="form-control"');

/*----- Text attribute -----*/
// default prefix
// $positivePrefix = config('custom_currency_locale.detail.textAttribute.POSITIVE_PREFIX', $currencyFormatter->getTextAttribute(NumberFormatter::POSITIVE_PREFIX));
// $sample = __('Example') . ' : ' . currency(100)->get();
// $form->addAnything(__('Positive Prefix'), <<<HTML
//     <input type="text" class="form-control w-25" name="textAttribute[POSITIVE_PREFIX]" value="{$positivePrefix}"/>
//     <strong>{$sample}</strong>
// HTML);

// $negativePrefix = config('custom_currency_locale.detail.textAttribute.NEGATIVE_PREFIX', $currencyFormatter->getTextAttribute(NumberFormatter::NEGATIVE_PREFIX));
// $sample = __('Example') . ' : ' . currency(-100)->get();
// $form->addAnything(__('Negative Prefix'), <<<HTML
//     <input type="text" class="form-control w-25" name="textAttribute[NEGATIVE_PREFIX]" value="{$negativePrefix}"/>
//     <strong>{$sample}</strong>
// HTML);

// print out the object
echo $form->printOut();