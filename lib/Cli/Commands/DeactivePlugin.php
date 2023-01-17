<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:56:39
 * @modify date 2023-01-15 07:50:12
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

use SLiMS\DB;
use SLiMS\Plugins;

class DeactivePlugin extends \SLiMS\Cli\Command
{
    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'plugin:deactivate {pluginid?} {--rundown=true}';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'deactive a plugin';

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        if (null !== $this->argument('pluginid')) return $this->deactiveById();
        else return $this->deactiveAll();
    }

    private function deactiveById()
    {
        $plugins = Plugins::getInstance();

        $id = $this->argument('pluginid');
        $plugin = array_filter($plugins->getPlugins(), function ($plugin) use ($id) {
                return $plugin->id === $id;
            })[$id] ?? false;

        if ($plugin === false) {
            $this->error(__('Plugin not found'));
            return self::INVALID;
        }
        
        if ($plugin->migration->is_exist && !$this->option('rundown')) {
            $query = DB::getInstance()->prepare("UPDATE plugins SET deleted_at = :deleted_at WHERE id = :id");
            $query->bindValue('deleted_at', date('Y-m-d H:i:s'));
        } elseif ($plugin->migration->is_exist && $this->option('rundown')) {
            Runner::path($plugin->path)->setVersion($plugin->migration->{Plugins::DATABASE_VERSION})->runDown();
            $query = DB::getInstance()->prepare("DELETE FROM plugins WHERE id = :id");
        } else {
            $query = DB::getInstance()->prepare("DELETE FROM plugins WHERE id = :id");
        }
        $query->bindValue(':id', $id);
        $query->execute();
        $this->success(sprintf(__('Plugin %s disabled'), $plugin->name));
        return 1;
    }

    public function deactiveAll()
    {
        DB::getInstance()->query("TRUNCATE plugins");
        $this->success(__('All plugin has been disabled'));
        return 1;
    }
}