<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-26 22:30:28
 * @modify date 2023-01-26 23:09:46
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli\Commands;

use Exception;
use Install\SLiMS;
use Install\Upgrade;
use SLiMS\DB;

class DatabaseMigrate extends \SLiMS\Cli\Command
{

    /**
     * Signature is combination of command name
     * argument and options
     *
     * @var string
     */
    protected string $signature = 'db:migrate {--F|from? : version number of updated database structure}';

    /**
     * Command description
     *
     * @var string
     */
    protected string $description = 'migrating database structure';

    /**
     * Handle command process
     *
     * @return void
     */
    public function handle()
    {
        // This method need some dependencies
        // from installer
        if (!file_exists(SB . 'install')) return 0;
        require_once SB . 'install/SLiMS.inc.php';
        require_once SB . 'install/Upgrade.inc.php';

        $slims = new SLiMS;
        $slims->setConnection(DB::getInstance('mysqli'));
        $upgrade = Upgrade::init($slims);

        try {
            // always get last version
            $version = $this->option('from')??($upgrade->getVersion() - 1);
            $upgrade->from($version);
            $this->success(__('Success migrating database'));
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        return 0;
    }
}