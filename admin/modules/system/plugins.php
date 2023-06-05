<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 05/11/20 21.33
 * @File name           : plugins.php
 */

use SLiMS\DB;
use SLiMS\Plugins;
use SLiMS\Migration\Runner;
use SLiMS\Parcel\Package;

define('INDEX_AUTH', 1);

require __DIR__ . '/../../../sysconfig.inc.php';

require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';

require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO . 'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

if (!$can_read) die('<div class="errorBox">' . __('You don\'t have enough privileges to view this section') . '</div>');

$plugins = Plugins::getInstance();

// get plugins from composer packages
if(method_exists(\Composer\InstalledVersions::class, 'getInstalledPackagesByType')) 
    foreach (\Composer\InstalledVersions::getInstalledPackagesByType('slims-plugin') as $package) 
            $plugins->addLocation(\Composer\InstalledVersions::getInstallPath($package));

if (isset($_GET['view']) && !empty($_GET['view'])) $_SESSION['view'] = $_GET['view'];

if (isset($_GET['upload']))
{
    $package = Package::prepare(SB . 'files/temp/test.zip');
    ob_start();
    if ($package?->isCompressorExists()) {
        // create new instance
        $form = new simbio_form_table_AJAX('mainForm', MWB . 'system/plugin_action.php', 'post');
        $form->submit_button_attr = 'name="upload" value="' . __('Save') . '" class="s-btn btn btn-default"';
        // form table attributes
        $form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
        $form->table_header_attr = 'class="alterCell"';
        $form->table_content_attr = 'class="alterCell2"';

        $form->addAnything(__('Plugin'), simbio_form_element::textField('file', 'plugin', '',''));
        $form->addSelectList('enable', __('Enable plugin'), [[0,__('No')],[1,__('Yes')]], '', 'class="form-control"', '');

        echo $form->printOut();
    } else {
        echo '<div class="alert alert-danger">' . __('Extension ZIP is not exists.') . '</div>';
    }
    $content = ob_get_clean();
    require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/notemplate_page_tpl.php';
    exit();
} 
else 
{
?>
    <div class="menuBox">
        <div class="menuBoxInner masterFileIcon">
            <div class="per_title">
                <h2><?php echo __('Plugin List'); ?></h2>
            </div>
            <div class="sub_section">
                <div class="btn-group">
                    <a title="<?= __('Install plugin from .zip file') ?>" href="<?= $_SERVER['PHP_SELF'] ?>?upload=yes" class="notAJAX openPopUp btn btn-primary"><?= __('Upload Plugin') ?></a>&nbsp;
                    <a href="<?= $_SERVER['PHP_SELF'] ?>?view=<?= (!isset($_SESSION['view']) || $_SESSION['view'] == 'list' ? 'card' : 'list') ?>" class="btn btn-sm btn-outline-secondary items-center flex p-2">
                        <?php if (!isset($_SESSION['view']) || $_SESSION['view'] == 'list'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-grid" viewBox="0 0 16 16">
                                <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"></path>
                            </svg>
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                                <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z"></path>
                            </svg>
                        <?php endif; ?>
                    </a>
                </div>
                <div id="search" method="get" class="form-inline"><?= __('Search') ?> 
                    <input type="text" name="keywords" onkeyup="searchPlugin(this)" class="form-control col-md-3">
                </div>
            </div>
        </div>
    </div>

    <?php
    // scanning plugins directory
    $plugin_actives = $plugins->getActive();

    ?>
    <?php if (!isset($_SESSION['view']) || $_SESSION['view'] == 'list'): ?>
        <table class="table">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col"><?= __('Plugin') ?></th>
                <th scope="col"><?= __('Description')?></th>
                <th scope="col"><?= __('Enable/Disable') ?></th>
            </tr>
            </thead>
            <tbody>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 mx-3">
    <?php endif; ?>
        <?php

        $n = 1;
        foreach ($plugins->getPlugins() as $plugin) {
            $hash = md5($plugin->path);
            if (isset($plugin_actives[$hash])) {
                $enable_disable = __('Enabled');
                $is_active = 'checked';

                // if have migration and version is different
                // disable it.
                $version = (json_decode($plugin_actives[$hash]->options??''))->version ?? '';
                if ($version !== $plugin->version && $plugin->migration->is_exist) {
                    $enable_disable = __('Disabled');
                    $is_active = '';
                }

            } else {
                $enable_disable = __('Disabled');
                $is_active = '';
            }

            $label = ['version' => __('Version'), 'by' => __('By'), 'viewDetail' => __('View Detail')];
            extract($label);

            if (!isset($_SESSION['view']) || $_SESSION['view'] == 'list') {
                echo <<<HTML
                    <tr id="section{$n}">
                        <th scope="row">{$n}</th>
                        <td width="300px" class="plugin-title" data-section="{$n}">{$plugin->name}</td>
                        <td>
                            <div class="mb-2">{$plugin->description}</div>
                            <div>{$version} <code>{$plugin->version}</code> | {$by} <a target="_blank" href="{$plugin->author_uri}">{$plugin->author}</a> | <a target="_blank" href="{$plugin->uri}">{$viewDetail}</a></div>
                        </td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input onchange="enablePlugin(event, {$plugin->migration->is_exist})" type="checkbox" class="custom-control-input" id="{$hash}" {$is_active}>
                                <label class="custom-control-label" for="{$hash}">{$enable_disable}</label>
                            </div>
                        </td>
                    </tr>
                HTML;
            } else {
                echo <<<HTML
                    <div id="section{$n}" class="col my-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title plugin-title" data-section="{$n}">{$plugin->name}</h5>
                                <p class="card-text">{$plugin->description}</p>
                                <p class="card-text">
                                    <small>{$version} <code>{$plugin->version}</code> | {$by} <a target="_blank" href="{$plugin->author_uri}">{$plugin->author}</a> | <a target="_blank" href="{$plugin->uri}">{$viewDetail}</a></small>
                                </p>
                                <hr/>
                                <div class="custom-control custom-switch float-right">
                                    <input onchange="enablePlugin(event, {$plugin->migration->is_exist})" type="checkbox" class="custom-control-input" id="{$hash}" {$is_active}>
                                    <label class="custom-control-label" for="{$hash}">{$enable_disable}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                HTML;
            }
            $n++;
        }

        ?>
        </tbody>
    </table>
    <script>
        function enablePlugin(e, m = false) {

            let runDown = false
            if (!e.target.checked && m) runDown = confirm("<?= __('Plugin has been disabled.\nRun Migration too? This may will drop this plugin\'s table and the data can not be restored!') ?>")

            fetch('<?= MWB ?>system/plugin_action.php', {
                method: 'POST',
                body: JSON.stringify({
                    format: 'json',
                    enable: e.target.checked,
                    id: e.target.getAttribute('id'),
                    runDown
                })
            })
                .then(res => res.json())
                .then(res => {
                    if (res.status) {
                        parent.toastr.success(res.message, 'Plugin')
                    } else {
                        parent.toastr.error(res.message, 'Plugin')
                        e.target.checked = !e.target.checked
                    }
                    labelMod(e)
                })
                .catch(err => {
                    parent.toastr.error(err)
                    e.target.checked = !e.target.checked
                    labelMod(e)
                })
        }

        function labelMod(e) {
            let label = document.querySelectorAll('label[for="' + e.target.getAttribute('id') + '"]')[0];
            if (e.target.checked) {
                label.innerHTML = '<?= __('Enabled') ?>'
            } else {
                label.innerHTML = '<?= __('Disabled') ?>'
            }
        }

        function searchPlugin(e)
        {
            let keywords = new RegExp(e.value, 'gi')
            document.querySelectorAll('.plugin-title')?.forEach(el => {
                var section = document.getElementById(`section${el.dataset.section}`)

                if (!el.innerHTML.match(keywords))
                {
                    section.classList.add('d-none')
                }
                else
                {
                    section.classList.remove('d-none')
                }
            })
        }
    </script>
<?php
}
?>