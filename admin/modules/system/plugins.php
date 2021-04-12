<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 05/11/20 21.33
 * @File name           : plugins.php
 */

use SLiMS\DB;
use SLiMS\Migration\Runner;
use SLiMS\Plugins;

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

$_POST = json_decode(file_get_contents('php://input'), true);
if (isset($_POST['enable'])) {
    $id = $_POST['id'];
    $plugin = array_filter($plugins->getPlugins(), function ($plugin) use ($id) {
            return $plugin->id === $id;
        })[$id] ?? die(json_encode(['status' => false, 'message' => __('Plugin not found')]));

    try {
        if ($_POST['enable']) {
            $options = ['version' => $plugin->version];

            $query = DB::getInstance()->prepare('INSERT INTO plugins (id, path, options, created_at, deleted_at, uid) VALUES (:id, :path, :options, :created_at, :deleted_at, :uid)');
            if ($plugins->isActive($plugin->id))
                $query = DB::getInstance()->prepare('UPDATE `plugins` SET `path` = :path, `options` = :options, `updated_at` = :created_at, `deleted_at` = :deleted_at, `uid` = :uid WHERE `id` = :id');

            // run migration if available
            if ($plugin->migration->is_exist) {
                $options[Plugins::DATABASE_VERSION] = Runner::path($plugin->path)->setVersion($plugin->migration->{Plugins::DATABASE_VERSION})->runUp();
                $query->bindValue(':options', json_encode($options));
            } else {
                $query->bindValue(':options', null);
            }

            $query->bindValue(':id', $id);
            $query->bindValue(':path', $plugin->path);
            $query->bindValue(':created_at', date('Y-m-d H:i:s'));
            $query->bindValue(':deleted_at', null);
            $query->bindValue(':uid', $_SESSION['uid']);
            $message = sprintf(__('Plugin %s enabled'), $plugin->name);

        } else {
            if ($plugin->migration->is_exist && !$_POST['runDown']) {
                $query = DB::getInstance()->prepare("UPDATE plugins SET deleted_at = :deleted_at WHERE id = :id");
                $query->bindValue('deleted_at', date('Y-m-d H:i:s'));
            } elseif ($plugin->migration->is_exist && $_POST['runDown']) {
                Runner::path($plugin->path)->setVersion($plugin->migration->{Plugins::DATABASE_VERSION})->runDown();
                $query = DB::getInstance()->prepare("DELETE FROM plugins WHERE id = :id");
            } else {
                $query = DB::getInstance()->prepare("DELETE FROM plugins WHERE id = :id");
            }
            $query->bindValue(':id', $id);
            $message = sprintf(__('Plugin %s disabled'), $plugin->name);
        }

        $run = $query->execute();

        if ($run) {
            echo json_encode(['status' => true, 'message' => $message]);
        } else {
            echo json_encode(['status' => false, 'message' => DB::getInstance()->errorInfo()]);
        }
    } catch (Exception $exception) {
        echo json_encode(['status' => false, 'message' => $exception->getMessage()]);
    }

    exit();
}

?>

<div class="menuBox">
    <div class="menuBoxInner masterFileIcon">
        <div class="per_title">
            <h2><?php echo __('Plugin List'); ?></h2>
        </div>
    </div>
</div>

<?php
// scanning plugins directory
$plugin_actives = $plugins->getActive();

?>
<table class="table">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Plugin</th>
        <th scope="col">Description</th>
        <th scope="col">Enable/Disable</th>
    </tr>
    </thead>
    <tbody>
    <?php

    $n = 1;
    foreach ($plugins->getPlugins() as $plugin) {
        $hash = md5($plugin->path);
        if (isset($plugin_actives[$hash])) {
            $enable_disable = __('Enabled');
            $is_active = 'checked';

            // if have migration and version is different
            // disable it.
            $version = (json_decode($plugin_actives[$hash]->options))->version ?? '';
            if ($version !== $plugin->version && $plugin->migration->is_exist) {
                $enable_disable = __('Disabled');
                $is_active = '';
            }

        } else {
            $enable_disable = __('Disabled');
            $is_active = '';
        }

        echo <<<HTML
    <tr>
        <th scope="row">{$n}</th>
        <td width="300px">{$plugin->name}</td>
        <td>
            <div class="mb-2">{$plugin->description}</div>
            <div>Version <code>{$plugin->version}</code> | By <a target="_blank" href="{$plugin->author_uri}">{$plugin->author}</a> | <a target="_blank" href="{$plugin->uri}">View Detail</a></div>
        </td>
        <td>
            <div class="custom-control custom-switch">
                <input onchange="enablePlugin(event, {$plugin->migration->is_exist})" type="checkbox" class="custom-control-input" id="{$hash}" {$is_active}>
                <label class="custom-control-label" for="{$hash}">{$enable_disable}</label>
            </div>
        </td>
    </tr>
HTML;
        $n++;
    }

    ?>
    </tbody>
</table>
<script>
    function enablePlugin(e, m = false) {

        let runDown = false
        if (!e.target.checked && m) runDown = confirm("<?= __('Plugin has been disabled.\nRun Migration too? This may will drop this plugin\'s table and the data can not be restored!') ?>")

        fetch('<?= $_SERVER['PHP_SELF'] ?>', {
            method: 'POST',
            body: JSON.stringify({
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
</script>
