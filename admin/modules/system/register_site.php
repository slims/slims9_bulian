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
    if (trim(config('library_name')) === 'Senayan') {
      toastr(__('Please update your Library Name first!'))->error();
      redirect()->simbioAJAX(Url::getSelf());
    }

    if (!isset($_POST['registration_info']['aggrement']) && config('registration_info') === null) {
      toastr(__('Please check up the agreement'))->error();
      redirect()->simbioAJAX(Url::getSelf());
    }

    $data = array_merge([
        'url' => (string)Url::getSlimsBaseUri(),
        'library_name' => config('library_name'),
        'library_subname' => config('library_subname'),
    ], $_POST['registration_info']);

    $method = 'post';
    if (isset($_POST['is_registered'])) {
      $method = 'put';
      $lastData = config('registration_info');
      if ($lastData['library_name'] !== $data['library_name'] || $lastData['url'] !== $data['url']) {
        $data['last_data'] = $lastData;
        $method = 'post';
      }
    }

    Config::createOrUpdate('registration_info', $data);

    // store to SLiMS Analytic
    try {

        $response = Client::withHeaders([
            'Content-Type' => 'application/json',
            'User-Agent' => 'SLiMS-' . SENAYAN_VERSION_TAG
        ])->withBody(
            (string)Json::stringify($data)
        )->$method('https://analytics.slims.web.id/?p=api/v1/register');

        if (!empty($error = $response->getError())) {
          throw new Exception($error);
        }

        if ($response->getStatusCode() == 200) {
          $content = json_decode($response->getContent()??'', true);

          if (($content['status']??false) === false) throw new Exception($content['message']);

          toastr($content['message'])->success();
          redirect()->simbioAJAX(MWB . 'system/index.php');
        }
        
    } catch (Exception $e) {
        dd($e->getMessage());
        writeLog('staff', $_SESSION['uid'], 'system', $_SESSION['realname'] . ', register site : ' . $e->getMessage(), 'Logo', 'Delete');
        toastr(__('Failed to register your site. Open system log for more info'))->error();
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

if (config('registration_info') !== null) {
  $form->addHidden('is_registered', '1');
}

$form->addAnything(__('Library Name'), config('library_name'));
$form->addAnything(__('Library Subname'), config('library_subname'));
$form->addSelectList('registration_info[list_mysite]', __('Site listing'), [
    ['justregis', __('Do not list my site')],
    ['onlysitename', __('Only display my site name')],
    ['showall', __('Display my site name with the link')]
], config('registration_info.list_mysite'), 'class="form-control col-3"', __('You can choose to have your site listed publicly in the list of registeted sites, with or without a link to your site.'));
$form->addTextField('text', 'registration_info[npp]', __('NPP (Optional)'), config('registration_info.npp'), 'class="form-control"', __('A spesific identification code of some library in Indonesia under coordinated by Indnesia National Library.'));
if (config('registration_info') === null) {
  $form->addAnything('Privacy data processing agreement', '
  <input type="checkbox" class="noAutoFocus" name="registration_info[aggrement]" value="1" id="aggrement" />
  <label for="aggrement">' . __('I agree to the <a class="notAJAX" href="https://analytics.slims.web.id/?p=privacy">Privacy notice and data processing agreement</a>') . '</label>');
}

echo $form->printOut();