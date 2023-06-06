<?php
/**
 * @Source by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Created Date       : 05/11/20 21.33
 * @File name          : plugins.php
 * @Modified by        : Drajat Hasan (drajathasan20@gmail.com)
 */

use SLiMS\DB;
use SLiMS\Json;
use SLiMS\Jquery;
use SLiMS\Plugins;
use SLiMS\Parcel\Package;
use SLiMS\Migration\Runner;
use SLiMS\Filesystems\Storage;

define('INDEX_AUTH', 1);

require __DIR__ . '/../../../sysconfig.inc.php';

require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';

$plugins = Plugins::getInstance();
$upload_success = false;

if (count($_POST) == 0) $_POST = json_decode(file_get_contents('php://input'), true);

if (isset($_POST['upload']) && !empty($_FILES['plugin'])) {
    $files_disk = Storage::files();

    $files_upload = $files_disk->upload('plugin', function($plugin) {
        // Extension check
        $plugin->isExtensionAllowed(['.zip']);

        // File size check
        $plugin->isLimitExceeded(config('max_plugin_upload')*1024);

        // destroy it if failed
        if (!empty($plugin->getError())) $plugin->destroyIfFailed();

    })->as('temp' . DS . md5(utility::createRandomString(5) . date('Y-m-d')));

    if ($files_upload->getUploadStatus()) {
        toastr('Plugin has been success upload!')->success();
        $package = Package::prepare($tempZip = SB . 'files' . DS . 'temp' . DS .$files_upload->getUploadedFileName());

        $package->extract()->to(function($zip) use($files_disk,$files_upload,$tempZip,$plugins) {
            unlink($tempZip);

            $contents = $files_disk->directories($zip->getUniquePath())->toArray();

            foreach ($contents as $content) {
                @rename(SB . 'files' . DS . $content->path(), $pluginPath = SB . 'plugins' . DS . basename($content->path()));
            }

            $files_disk->deleteDirectory($zip->getUniquePath());

            if (isset($_POST['enable']) && $_POST['enable'] == '0') unset($_POST['enable']);

            $_POST['id'] = array_key_first(array_filter(isset($_POST['enable']) ? $plugins->getPlugins() : [], function($plugin) use($pluginPath) {
                if (dirname($plugin->path) == $pluginPath) return true;
            }));
        });
        $upload_success = true;
    }
    else
    {
        toastr('error')->error();
        exit;
    }
}

if (isset($_POST['enable'])) {
    $id = $_POST['id'];
    $plugin = array_filter($plugins->getPlugins(), function ($plugin) use ($id) {
            return $plugin->id === $id;
        })[$id] ?? die(isset($_POST['format']) ? json_encode(['status' => false, 'message' => __('Plugin not found')]) : toastr(__('Plugin not found'))->error());

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

        if (!$run) $message = __('Something error : turn on development mode to get more information');

        if (isset($_POST['format'])) echo Json::stringify(['status' => (bool)$run, 'message' => $message])->withHeader();
        else toastr($message . ' 1')->{($run === false ? 'error' : 'success')};

    } catch (Exception $exception) {
        if (isset($_POST['format'])) echo Json::stringify(['status' => false, 'message' => $exception->getMessage()])->withHeader();
        else toastr($exception->getMessage() . ' 2')->error();
    }

    // redirect content
    if ($upload_success) {
        Jquery::raw('colorbox.close()');
        redirect()->simbioAJAX(AWB . 'modules/system/plugins.php');
    }
    exit();
}