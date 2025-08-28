<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-09-17 12:01:45
 * @modify date 2022-11-13 18:39:35
 * @license GPLv3
 * @desc [description]
 */

defined('INDEX_AUTH') or die('Direct access is not allowed!');

// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

function httpQuery($query = [])
{
    return http_build_query(array_unique(array_merge($_GET, $query)));
}

if (isset($_POST['saveData']))
{
    $isConfigWritable = is_writable(SB . 'config');

    if (!$isConfigWritable)
    {
        toastr('Folder config tidak dapat ditulis!')->error();
        exit;
    }

    $template = file_get_contents(__DIR__ . '/csp.template');
    foreach ($_POST['csp'] as $policyName => $value) {
        $template =  str_replace('{'.$policyName.'}', $value, $template);
    }

    // changes detail
    $template = str_replace(['{realname}', '{timeModified}', '{hostName}'], [$_SESSION['realname'], date('Y-m-d H:i:s'), $_SERVER['HTTP_HOST']??'-'], $template);

    // put content to file
    $overWrite = file_put_contents(SB . 'config/csp.php', $template);

    toastr('File ' . ($overWrite ? 'berhasil' : 'tidak berhasil') . ' disimpan')->{($overWrite ? 'success' : 'error')}();
    exit;
}
?>
<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2>CSP (Content Security Policy)</h2>
    </div>
  </div>
</div>
<?php
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'] . '?' . httpQuery(), 'post');
$form->submit_button_attr = 'name="saveData" value="'.__('Save Settings').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

foreach (config('csp') as $csp) {
    if (empty($csp)) continue;
    $csp = explode(' ', $csp);
    $policyName = $csp[0];
    $policyValue = implode(' ', array_slice($csp, 1));

    $form->addTextField('text', 'csp[' . simbio_security::xssFree($policyName) . ']', ucwords(str_replace('-', ' ', $policyName)), trim($policyValue), 'style="width: 100%;" class="form-control"');
}

// print out the object
echo $form->printOut();
