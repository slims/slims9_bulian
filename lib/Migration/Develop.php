<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-09 16:30:13
 * @modify date 2023-01-26 16:06:23
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Migration;

use Exception;
use SLiMS\{Config,DB};
use Install\{SLiMS,Upgrade};

class Develop
{
    public static function migrate()
    {
        if (!isDev()) return; // just for development mode

        // This method need some dependencies
        // from installer
        if (!file_exists(SB . 'install')) return;
        require_once SB . 'install/SLiMS.inc.php';
        require_once SB . 'install/Upgrade.inc.php';

        $slims = new SLiMS;
        $slims->setConnection(DB::getInstance('mysqli'));
        $upgrade = Upgrade::init($slims);

        // don't run miggration if version is same!
        if (config('develop_migration') == $upgrade->getVersion()) return;

        try {
            // always get last version
            $upgrade->from($upgrade->getVersion() - 1);
        } catch (Exception $e) {
            dd($e->getMessage());
        }
        
        // Create/Update develop_migration
        Config::create('develop_migration', '<?php return '.$upgrade->getVersion().'; ?>');
    }
}