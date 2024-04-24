<?php
/**
 * @author Drajat Hasan
 * @email <drajathasan20@gmail.com>
 * @create date 2024-04-17 09:44:53
 * @modify date 2024-04-17 11:15:23
 * @license GPLv3
 * @desc [description]
 */
namespace SLiMS;

final class Extension
{
    private array $required = [
        'gd' => 'gdValidator',
        'pdo' => 'pdoValidator',
        'mbstring', 'gettext',
        'fileinfo'
    ];

    private array $requiredByFeature = [
        'p2p' => 'simplexml',
        'MARC' => ['pearValidator','xml']
    ];

    public static function forFeature(string $featureName)
    {
        $extension = new static;

        if (!isset($extension->requiredByFeature[$featureName])) {
            throw new \Exception(sprintf(__('Feature %s is not available.'), $featureName));
        }

        return new Class($extension, $extension->requiredByFeature[$featureName]) {
            private ?\SLiMS\Extension $extension = null;
            private array $requiredByFeature = [];

            public function __construct(\SLiMS\Extension $extension, array|string $requiredByFeature) {
                $this->extension = $extension;
                $this->requiredByFeature = !is_array($requiredByFeature) ? [$requiredByFeature] : $requiredByFeature;
            }

            public function isFulfilled(array &$notMatch = [])
            {
                return $this->extension->checkAndCompare(
                    $this->requiredByFeature,
                    $notMatch
                );
            }
        };
    }

    public function hasLoaded(string $extensionName)
    {
        return extension_loaded($extensionName);
    }

    private function pdoValidator()
    {
        return class_exists('PDO') && in_array('mysql', \PDO::getAvailableDrivers());
    }

    private function pearValidator()
    {
        try {
            include 'System.php';
            include 'File/MARC.php';
            return class_exists('System') && class_exists('File_MARC');
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Took from SLiMS installer
     * created by Waris Agung Widodo (ido.alit@gmail.com)
     *
     * @return void
     */
    private function gdValidator()
    {
        // Homeboy is not rockin GD at all
        if (!function_exists('gd_info')) {
            return false;
        }
    
        $gd_info = gd_info();
        $gd_version = preg_replace('/[^0-9\.]/', '', $gd_info['GD Version']);
    
        // Image extension Support
        $Need = ['GIF Read Support','GIF Create Support','JPEG Support','PNG Support'];
        $extensionCheck = array_filter($Need, function($Extension) use($gd_info) {
            if (isset($gd_info[$Extension]) && ($gd_info[$Extension]))
            {
                return true;
            }
        });
    
        // If the GD version is at least 1.0
        return ($gd_version >= 1 && count($extensionCheck) == 4);
    }

    public function checkAndCompare(array $required, array &$notMatch = [])
    {
        $matchedTotal = 0;
        foreach ($required as $extOrKey => $extOrMethod) {
            if (method_exists($this, $extOrMethod)) {
                $result = (int)(isset($fnExists) ? $fnExists() : $this->$extOrMethod());
                $ext = $extOrKey;
            } else {
                $result = (int)$this->hasLoaded($extOrMethod);
                $ext = $extOrMethod;
            }

            if ($result === 0) $notMatch[] = $ext;
            $matchedTotal += $result;
        }

        return $matchedTotal === count($required);
    }

    public static function isCoreRequiredFulfilled(array &$notFulfilfilled) {
        $extension = new static;
        return $extension->checkAndCompare($extension->required, $notFulfilfilled);
    }

    public static function throwIfNotFulfilled()
    {
        $notFulfilfilled = [];
        self::isCoreRequiredFulfilled($notFulfilfilled);

        if ($notFulfilfilled) throw new \Error(sprintf(
            __('Some PHP extension required by SLiMS is not loaded : %s'),
            ucwords(implode(',', $notFulfilfilled))
        ));
        
    }
}