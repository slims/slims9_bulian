<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-10-27 10:43:48
 * @modify date 2021-10-27 10:43:48
 * @desc [description]
 */

use PHPMailer\PHPMailer\{SMTP,PHPMailer};

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

$configPath = SB.'config'.DS.'sysconfig.mail.inc.php';
$samplePath = SB.'config'.DS.'sysconfig.mail.inc-sample.php';
$isConfigExists = file_exists($configPath);
$isConfigWriteable = is_writable(SB.'config'.DS);

function setValue($key)
{
    global $isConfigExists, $configPath, $sysconf;

    if ($isConfigExists && isset($sysconf['mail'][$key]))
    {
        include_once $configPath;
        return $sysconf['mail'][$key];
    }

    return '';
}

function setPlaceholder($key, $ignore = 'auth_password')
{
    global $sysconf;

    if (isset($sysconf['mail'][$key]))
    {
        $label = ($key === $ignore) ? ' 123. ' . __('NB : Password is hidden for security reason') : $sysconf['mail'][$key];
        return __('Example') . ' : ' . $label;
    }

    return '';
}

if (isset($_POST['saveData']))
{
    if (!$isConfigWriteable)
    {
        utility::jsToastr(__('Error'), __('Directory config is not writeable.'), 'error');
        exit;
    }

    if (isset($_POST['edit']) && empty(trim($_POST['authpassword'])))
    {
      include_once $configPath;
      $_POST['authpassword'] = $sysconf['mail']['auth_password']; // if user change configuration without password, use available password
    }

    // include sample
    include $samplePath;
    // get sample
    $Config = file_get_contents($samplePath);

    foreach ($sysconf['mail'] as $key => $value) 
    {
        $key = str_replace('_', '', $key);
        $customConfig = ($_POST[$key]) ?? '?';
        $Config = str_replace('_' . $key . '_', $customConfig, $Config);
    }

    // write mail configuration
    file_put_contents($configPath, $Config);

    // alert
    utility::jsToastr(__('E-Mail Configuration'), __('Settings inserted.'), 'success');

    // redirect
    echo '<script>setTimeout(() => {parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\')}, 5000)</script>';
    exit;
}

if (isset($_POST['testMail']))
{
  include_once $configPath;
  $mail = new PHPMailer(true);
  try {
      ob_start();
      //Server settings
      $mail->SMTPDebug = $sysconf['mail']['debug'];                      // Enable verbose debug output
      $mail->isSMTP();                                                                // Send using SMTP
      $mail->Host = $sysconf['mail']['server'];                                       // Set the SMTP server to send through
      $mail->SMTPAuth = $sysconf['mail']['auth_enable'];                              // Enable SMTP authentication
      $mail->Username = $sysconf['mail']['auth_username'];                            // SMTP username
      $mail->Password = $sysconf['mail']['auth_password'];                            // SMTP password
      if ($sysconf['mail']['SMTPSecure'] === 'tls') {                                 // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      } else if ($sysconf['mail']['SMTPSecure'] === 'ssl') {
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      }
      $mail->Port = $sysconf['mail']['server_port'];                                  // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

      //Recipients
      $mail->setFrom($sysconf['mail']['from'], $sysconf['mail']['from_name']);
      $mail->addReplyTo($sysconf['mail']['reply_to'], $sysconf['mail']['reply_to_name']);
      $mail->addAddress($_POST['receiveraddress'], $_POST['receivername']);

      $mail->isHTML(true);                                  // Set email format to HTML
      $mail->Subject = 'SLiMS :: Outgoing Mail Testing';
      $mail->msgHTML($_POST['dummyMessage']);
      $mail->AltBody = strip_tags($_POST['dummyMessage']);

      $mail->send();
      $result = ob_get_clean();

      utility::jsToastr(__('Success'), 'Mail testing has been sent', 'success');
  } catch (Exception $exception) {
      utility::jsToastr(__('Success'), 'Mail testing could not be sent. Mailer Error: ' . $mail->ErrorInfo, 'success');
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
      <h2><?php echo __('E-Mail Configuration'); ?></h2>
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
$DebugOptions = [
  [SMTP::DEBUG_OFF, __('Production')],
  [SMTP::DEBUG_SERVER, __('Development')]
];
$form->addSelectList('debug', __('Environment'), $DebugOptions, (int)setValue('debug') ,'class="form-control col-3"');

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
  $html = <<<HTML
    <button data-href="{$Url}" class="testMail btn btn-primary">  
      <i class="fa fa-gears"></i>
      {$BtnLabel}
    </button>
  HTML;
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