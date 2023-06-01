<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-11 07:25:22
 * @modify date 2023-05-17 07:54:59
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\Captcha\Factory;
use SLiMS\Captcha\Providers\ReCaptcha;
use SLiMS\Config;

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

if (isset($_POST['saveData']))
{
    Factory::createConfigFromSample();    
    toastr(__('Data has been saved'))->success();
    exit;
}

?>

<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?= __('Captcha Configuration'); ?></h2>
    </div>
  </div>
</div>
<?php
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

/* Set field */
$form->submit_button_attr = 'name="saveData" value="'.__('Save Settings').'" class="btn btn-default"';

// // Enable or not
$form->addSelectList('default', __('Default captcha provider'), Factory::getInstance()->getProviderList(), config('captcha.default'),'class="form-control col-3"');

// sections
$sectionList = [['librarian','librarian'],['memberarea','memberarea']];
$defaultData = [];
foreach (config('captcha.sections')??[] as $section => $status) {
 if ($status['active']) $defaultData[] = $section;
}

$form->addCheckBox('section', __('Enable for section'), $sectionList, $defaultData, ' class="form-control"');

if (config('captcha.default', 'ReCaptcha') === 'ReCaptcha')
{
  $recaptcha = Factory::getInstance()->getConfig('providers.ReCaptcha');
  $pubkey = $recaptcha['publickey']??ReCaptcha::PUBKEY;
  $privkey = $recaptcha['privatekey']??ReCaptcha::PRIVKEY;
  $html = <<<HTML
  <div id="ReCaptcha" class="providers">
      <h5>ReCaptcha</h5>
      <label>Public Key</label>
      <input type="text" name="recaptcha[publickey]" value="{$pubkey}" class="form-control"/>
      <label>Private Key</label>
      <input type="text" name="recaptcha[privatekey]" value="{$privkey}" class="form-control"/>
  </div>
  HTML;

  $form->addAnything('Provider Option', $html);
}

// print out the object
echo $form->printOut();
?>
<script>
    $(document).ready(function(){
        $('#default').change(function(){
            $('.providers').addClass('d-none')
            $(`#${$(this).val()}`).removeClass('d-none')
        })
    })
</script>