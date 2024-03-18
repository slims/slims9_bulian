<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2024-03-16 09:30:52
 * @modify date 2024-03-16 09:30:52
 */

use SLiMS\Config;
use SLiMS\Json;
use SLiMS\Http\Client;
use SLiMS\Url;

define('INDEX_AUTH', '1');
require __DIR__ . '/../../../sysconfig.inc.php';

require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';

if (isset($_POST['saveData'])) {
    $data = array_merge([
        'url' => (string)Url::getSlimsBaseUri(),
        'library_name' => config('library_name'),
        'library_subname' => config('library_subname'),
    ], $_POST['registration_info']);

    dd($data);
    Config::createOrUpdate('registration_info', $data);
    sleep(3);

    // store to SLiMS Analytic
    try {
        Client::withHeaders([
            'Content-Type' => 'application/json'
        ])->withBody(
            (string)Json::stringify($data)
        )->post('https://12.12.12.2/?p=api/v1/register');
    } catch (\Throwable $th) {
        //throw $th;
    }

    redirect()->simbioAJAX(Url::getSelf());
}

?>
<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?php echo __('Register Your Site'); ?></h2>
    </div>
    <div class="p-4">
        <p><?= __('We\'d love to stay in touch and provide you with important things for your SLiMS site! By registering:') ?></p>
        <ul>
            <li><?= __('You are contributing to our SLiMS statistics of the worldwide community, which help us improve SLiMS and our community sites.') ?></li>
            <li><?= __('If you wish, your site can be included in the list of registered SLiMS sites in your country.') ?></li>
        </ul>
    </div>
  </div>
</div>
<?php
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="saveData" value="'.__('Save Settings').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

$form->addAnything(__('Library Name'), config('library_name'));
$form->addAnything(__('Library Subname'), config('library_subname'));
$form->addSelectList('registration_info[list_mysite]', __('Site listing'), [
    ['justregis', __('Do not list my site')],
    ['onlysitename', __('Only display my site name')],
    ['showall', __('Display my site name with the link')]
], config('registration_info.list_mysite'), 'class="form-control col-3"', __('You can choose to have your site listed publicly in the list of registeted sites, with or without a link to your site.'));
$form->addTextField('text', 'registration_info[npp]', __('NPP (Optional)'), config('registration_info.npp'), 'class="form-control"', __('A spesific identification code of some library in Indonesia under coordinated by Indnesia National Library.'));
$form->addAnything('Privacy data processing agreement', '
<input type="checkbox" class="noAutoFocus" name="registration_info[aggrement]" id="aggrement" />
<label for="aggrement">' . __('I agree to the <a href="https://slims.web.id/privacy">Privacy notice and data processing agreement</a>') . '</label>');

echo $form->printOut();