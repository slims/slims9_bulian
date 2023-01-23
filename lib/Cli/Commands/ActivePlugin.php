<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-23 06:06:39
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

use SLiMS\DB;
use SLiMS\Plugins;
use SLiMS\Migration\Runner;

class ActivePlugin extends GetPlugin
{
    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'plugin:activate {pluginid?}';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'active a plugin';

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        $plugins = Plugins::getInstance();

        $inputId = $this->argument('pluginid');

        if (null === $inputId) {
            $this->getAllPlugin();
            $inputIds = array_map(fn($plugin) => $plugin['id'], $this->plugins);
        }

        foreach ($inputId ? [$inputId] : $inputIds as $id) {
            $plugin = array_filter($plugins->getPlugins(), function ($plugin) use ($id) {
                return $plugin->id === $id;
            })[$id] ?? false;

            if ($plugin === false) {
                $this->error(__('Plugin not found'));
                return self::INVALID;
            }

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
            $query->bindValue(':uid', 1);
            $query->execute();
            $this->success(sprintf(__('Plugin %s enabled'), $plugin->name));
        }
        return 1;
    }
}