<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-10-27 10:43:48
 * @modify date 2022-10-08 12:39:36
 * @desc [description]
 */

use SLiMS\Mail;

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

$configPath = SB.'config'.DS.'mail.php';
$samplePath = SB.'config'.DS.'mail.sample.php';
$isConfigExists = file_exists($configPath);
$isConfigWriteable = is_writable(SB.'config'.DS);

function setValue($key)
{
    global $isConfigExists;

    if ($isConfigExists)
    {
        return config('mail')[$key]??'';
    }

    return '';
}

function setPlaceholder($key, $ignore = 'auth_password')
{
    $placeholder = [
      'SMTPSecure' => 'ssl', // ssl or tls
      'enable' => true,
      'server' => 'ssl://smtp.gmail.com:465', // SMTP server
      'server_port' => 465, // the SMTP port
      'auth_enable' => true, // enable SMTP authentication
      'auth_username' => 'admin', // SMTP account username
      'auth_password' => 'admin', // SMTP account password
      'from' => 'admin@localhost.com',
      'from_name' => 'SLiMS Administrator',
      'reply_to' => 'admin@localhost.com',
      'reply_to_name' => 'SLiMS Administrator'
    ];

    if (isset($placeholder[$key]))
    {
        $label = ($key === $ignore) ? ' 123. ' . __('NB : Password is hidden for security reason') : ($placeholder[$key]??'');
        return __('Example') . ' : ' . $label;
    }

    return '';
}

if (isset($_POST['saveData']))
{
    if (!$isConfigWriteable)
    {
        toastr(__('Directory config is not writeable.'))->error();
        exit;
    }

    if (isset($_POST['edit']) && empty(trim($_POST['authpassword'])))
    {
      $_POST['authpassword'] = config('mail')['auth_password']??''; // if user change configuration without password, use available password
    }

    // get sample
    $Config = file_get_contents($samplePath);
    $mailConfig = $isConfigExists ? config('mail') : require $samplePath;

    foreach ($mailConfig as $key => $value) 
    {
        $key = str_replace('_', '', $key);
        $customConfig = ($_POST[$key]) ?? '?';
        $Config = str_replace('_' . $key . '_', $customConfig, $Config);
    }

    // write mail configuration
    file_put_contents($configPath, $Config);

    // alert
    toastr(__('Settings inserted.'))->success(__('E-Mail Configuration'));

    // redirect
    echo '<script>setTimeout(() => {parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\')}, 5000)</script>';
    exit;
}

if (isset($_POST['testMail']))
{
  try {
      Mail::to($_POST['receiveraddress'], $_POST['receivername'])
            ->subject('SLiMS :: Outgoing Mail Testing')
            ->message($_POST['dummyMessage'])
            ->send();

      toastr('Mail testing has been sent')->success();
  } catch (Exception $exception) {
      toastr('Mail testing could not be sent. Mailer Error: ' . Mail::getInstance()->ErrorInfo)->error();
  }
  exit;
}

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

?>
<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?= __('E-Mail Configuration'); ?></h2>
    </div>
    <div class="<?= $isConfigWriteable ? 'info' : 'error' ?>Box">
      <?php
        if ($isConfigWriteable)
        {
            echo (isset($_GET['section']) ? __('E-Mail test') : __('Modify E-Mail preferences'));
        }
        else
        {
            echo '<b>' . __('Directory config is not writeable.') . "</b><br/>";
            echo __('Make the following files and directories (and their contents) writeable (i.e., by changing the owner or permissions with chown or chmod)');
        }
      ?>
    </div>
  </div>
</div>
<?php
if (!isset($_GET['section'])):
  /* Set field */
  $form->submit_button_attr = 'name="saveData" value="'.__('Save Settings').'" class="btn btn-default"';

  // edit mode
  if ($isConfigExists) $form->addHidden('edit', true);

  // Debug
  $form->addSelectList('debug', __('Environment'), array_values(Mail::availableEnv()), (int)setValue('debug') ,'class="form-control col-3"');

  // SMTPSecure
  $form->addTextField('text', 'SMTPSecure', __('SMTP Encryption'), setValue('SMTPSecure'), 'style="width: 60%;" class="form-control" placeholder="'.setPlaceholder('SMTPSecure').'"');

  // Server
  $form->addTextField('text', 'server', __('SMTP Server address'), setValue('server'), 'style="width: 60%;" class="form-control" placeholder="'.setPlaceholder('server').'"');

  // Server Port
  $form->addTextField('text', 'serverport', __('SMTP Port'), setValue('server_port'), 'style="width: 60%;" class="form-control" placeholder="'.setPlaceholder('server_port').'"');

  // set field username
  $form->addTextField('text', 'authusername', __('Authentication Username'), setValue('auth_username'), 'style="width: 60%;" class="form-control" placeholder="'.setPlaceholder('auth_username').'"',);

  // set field password
  $form->addTextField('password', 'authpassword', __('Authentication Password'), '', 'style="width: 60%;" class="form-control" placeholder="'.setPlaceholder('auth_password').'"', __('Password does not appearance for security reason. If you want to look it, open sysconfig.mail.inc.php file.'));

  // set email sender address
  $form->addTextField('text', 'from', __('Email Sender Address'), setValue('from'), 'style="width: 60%;" class="form-control" placeholder="'.setPlaceholder('from').'"');

  // set email sender label
  $form->addTextField('text', 'fromname', __('Email Sender Label'), setValue('from_name'), 'style="width: 60%;" class="form-control" placeholder="'.setPlaceholder('from_name').'"');

  // test mail
  if ($isConfigExists)
  {
    $BtnLabel = __('Do Test');
    $Url = $_SERVER['PHP_SELF'] . '?section=test';
    $html = '
      <button data-href="'.$Url.'" class="testMail btn btn-primary">  
        <i class="fa fa-gears"></i>
        '.$BtnLabel.'
      </button>';
    $form->addAnything('Test Mail Configuration', $html);
  }

  // print out the object
  echo $form->printOut();
  // End main configuration

else:

  // Dummy content
  $content = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book";
  // start test page
  $form->submit_button_attr = 'name="testMail" value="'.__('Send Mail').'" class="btn btn-default"';
  $form->addTextField('text', 'receivername',  __('Receiver Name'),'', 'style="width: 60%;" class="form-control"');
  $form->addTextField('text', 'receiveraddress', __('Receiver Address'), '', 'style="width: 60%;" class="form-control"');
  $form->addTextField('textarea', 'dummyMessage', __('Dummy Content'), $content, 'style="margin-top: 0px; margin-bottom: 0px; height: 149px;" class="form-control"');
  // print out the object
  echo $form->printOut();
  // end test page

endif;
?>
<script>
  $('.testMail').click(function(){
    $('#mainContent').simbioAJAX($(this).data('href'))
  })
</script>