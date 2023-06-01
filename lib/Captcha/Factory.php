<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-02-12 11:31:06
 * @modify date 2023-05-17 07:53:37
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Captcha;

use Exception;
use SLiMS\Config;
use SLiMS\Captcha\Providers\Contract;
use SLiMS\Captcha\Providers\ReCaptcha;

final class Factory
{
    private static $instance = null;
    private static $providerInstance = null;
    private string $providerName = '';
    private array $providerList = [];
    private string $captchaSection = '';

    private function __construct(string $providerName)
    {
        $this->providerName = $providerName;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new Factory(config('captcha.default', 'ReCaptcha'));
        return self::$instance;
    }

    /**
     * Define section page 
     * 
     * SLiMS by default have 3 section page
     * memberarea, librarian login and forgot
     * password
     *
     * @param string $sectionName
     * @return Factory
     */
    public static function section(string $sectionName)
    {
        self::getInstance()->captchaSection = $sectionName;
        return self::getInstance();
    }

    /**
     * Override provider name
     *
     * @param string $providerName
     * @return void
     */
    public function setProvider(string $providerName)
    {
        self::getInstance()->providerName = $providerName;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getProviderName()
    {
        return self::getInstance()->providerName;
    }

    /**
     * Captcha config
     *
     * @param string $key
     * @param boolean $default
     * @return void
     */
    public function getConfig(string $key = '', bool $default = false)
    {
        return !empty($key) ? config('captcha.' . $key, $default) : config('captcha');
    }

    /**
     * Get default captcha provider config
     *
     * @param string $key
     * @return mixed
     */
    public function getProviderConfig(string $key = '')
    {
        return $this->getConfig('providers.' . $this->providerName . ($key ? '.' . $key : ''));
    }

    /**
     * Create from sample if not exists
     */
    public static function createConfigFromSample()
    {
        Config::create('captcha', function(){
            $filesample = Config::getFile('captcha.sample');
            // set data
            $filesample = str_replace('<captcha-provider>', $_POST['default']??'ReCaptcha', $filesample);
    
            // loop for activate section
            $defaultSection = ['librarian', 'memberarea'];
            foreach ($defaultSection as $section) {
                $status = 'false';
                if (in_array($section, $_POST['section']??[])) $status = 'true';
                $filesample = str_replace("'<".trim($section)."-status>'", $status, $filesample);   
            }

            // is pub and priv key ready?
            $ready = isset($_POST['recaptcha']) && isset($_POST['recaptcha']['publickey']) && isset($_POST['recaptcha']['privatekey']);

            // key
            if ($_POST['default'] === 'ReCaptcha') {
                $filesample = str_replace('<publickey>', ($ready ? $_POST['recaptcha']['publickey'] : ReCaptcha::PUBKEY), $filesample);
                $filesample = str_replace('<privatekey>', ($ready ? $_POST['recaptcha']['privatekey'] : ReCaptcha::PRIVKEY), $filesample);
            }
            else
            {
                $searchClass = array_values(array_filter(array_keys(self::$instance->getProviderList()), fn($class) => strpos($class, $_POST['default'])));
                $class = $searchClass[0]??ReCaptcha::class;
                $filesample = str_replace('// Add another providers here', ",\n\t\t'{$_POST['default']}' => ['class' => '{$class}']\n\t\t// Add another providers here", $filesample);
            }
    
            return $filesample;
        });
    }

    public function getProviderList()
    {
        $providers = array_diff(scandir(__DIR__ . '/Providers/'), ['.','..','Contract.php']);
        $this->providerList = array_merge($this->providerList, array_map(fn($provider) => str_replace('.php', '', $provider), $providers));
        return $this->providerList;
    }

    public function registerProvider(string $providerName, string $providerClass)
    {
        $this->providerList[$providerClass] = $providerName;
    }

    /**
     * Get captcha
     *
     * @return string
     */
    public function getCaptcha()
    {
        return self::$providerInstance->generateCaptcha();
    }

    public function getError()
    {
        return self::$providerInstance->getError();
    }

    public function getCaptchaSection()
    {
        return $this->captchaSection;
    }

    /**
     * Get validation result from
     * provider
     *
     * @return boolean
     */
    public function isValid()
    {
        return self::$providerInstance->validate();
    }

    /**
     * Determine if captcha section is
     * active or not
     *
     * @return boolean
     */
    public function isSectionActive()
    {
        return $this->getConfig('sections.' . $this->captchaSection . '.active');
    }

    /**
     * Open factory to operate captcha
     *
     * @return void
     */
    public static function operate()
    {
        self::getInstance();
        
        if (is_null(self::$providerInstance)) {
            $class = config('captcha.providers.' . self::getInstance()->providerName . '.class', ReCaptcha::class);
            self::$providerInstance = new $class(self::getInstance());

            // is provider use our contract?
            if (!self::$providerInstance instanceof Contract) 
                throw new Exception(str_replace('{class}', $class, __("Captcha Error : {class} is not compatible with captcha contract!")));
        }
    }
}   