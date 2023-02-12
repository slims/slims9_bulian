<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-02-12 11:31:06
 * @modify date 2023-02-13 06:03:21
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Captcha;

final class Factory
{
    private static $instance = null;
    private static $providerInstance = null;
    private string $providerName = '';

    private function __construct(string $providerName)
    {
        $this->providerName = $providerName;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new Factory(config('captcha.default'));
        return self::$instance;
    }

    public function setProvider(string $providerName)
    {
        self::getInstance()->providerName = $providerName;
    }

    public function getProvider()
    {
        return self::getInstance()->providerName;
    }

    public function getConfig(string $key = '')
    {
        return !empty($key) && !is_null(config('captcha.' . $key)) ? config('captcha.' . $key) : config('captcha');
    }

    public function getProviderConfig()
    {
        return $this->getConfig('providers');
    }

    public static function operate()
    {
        self::getInstance();
        
        if (is_null(self::$providerInstance)) {
            $class = config('captcha.providers.' . self::getInstance()->providerName . '.class');
            self::$providerInstance = new $class;
        }
    }
}   