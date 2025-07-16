<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-05-27 21:37:29
 * @modify date 2024-04-17 11:29:33
 * @license GPLv3
 * @desc [description]
 */
namespace SLiMS\Polyglot;

use SLiMS\Config;
use SLiMS\Json;
use Gettext\Loader\MoLoader;
use SLiMS\Filesystems\Storage;

final class Memory
{
    /**
     * Memory instance
     *
     * @var Memory|null
     */
    private static $instance = null;

    /**
     * locale code e.g : id_ID
     *
     * @var string
     */
    private string $locale = '';

    /**
     * Locale file loader
     *
     * @var object|null
     */
    private ?object $loader = null;

    /**
     * Dictionary is part of loader
     * as text translatation engine
     *
     * @var object|null
     */
    private ?object $dictionary = null;

    /**
     * Available language
     *
     * @var array
     */
    private array $languages = [];

    /**
     * Path to locale at <slims-root>/lib/lang/
     *
     * @var string
     */
    private string $localePath = LANG . 'locale' . DS;

    /**
     * Translation pattern
     *
     * @var string
     */
    private string $localeFormat = DS . 'LC_MESSAGES' . DS . 'messages.mo';

    /**
     * List of translatation word based on 
     * php file
     *
     * @var array
     */
    private array $additionalWords = [];

    /**
     * Opac temporary language 
     * time to live
     *
     * @var integer
     */
    private int $tempLanguageTtl = 14400;

    // initilialization locale and loader
    private function __construct(){
        $this->locale = Config::getInstance()->get('default_lang', 'en_US');
        $this->loader = new MoLoader;
    }

    public static function getInstance(): Memory
    {
        if (self::$instance === null) self::$instance = new Memory;
        return self::$instance;
    }

    public function setDictionary(Object $newDictionary): void
    {
        $this->dictionary = $newDictionary;
    }

    public function getDictionary(): ?object
    {
        return $this->dictionary;
    }

    public function getLoader(): ?object
    {
        return $this->loader;
    }

    /**
     * Translate a content. If not exists
     * turn it back
     *
     * @param string $content
     * @return string
     */
    public static function find(string $content): string
    {
        $static = self::getInstance();
        $findInDictionary = $static->dictionary->find(null, $content);
        return $findInDictionary?->getTranslation()??$static->findFromAdditionalWords($content)??$content;
    }

    public function findFromAdditionalWords(string $content)
    {
        $locale = $this->additionalWords[$this->locale]??null;
        return $locale[$content]??null;
    }

    /**
     * Massive registration language list
     *
     * @param array $languages
     * @return Memory
     */
    public function registerLanguages(array $languages): Memory
    {
        $this->languages = array_merge($this->languages, $languages);
        return $this;
    }

    /**
     * Register a language into list
     *
     * @param string $code
     * @param string $englishName
     * @param string $nativeName
     * @return memory
     */
    public function registerLanguage(string $code, string $englishName, string $nativeName, string $path = LIB . 'lang/locale/'): Memory
    {
        $this->languages = array_merge($this->languages, [[$code, $englishName, $nativeName, $path]]);
        return $this;
    }

    public function registerLanguageFromPlugin()
    {
        $base = SB . 'plugins/lang/';
        if (is_dir($base) === false) return;
        foreach (array_diff(scandir($base), ['.','..']) as $dir) {
            $this->registerLanguage(...Json::parse(file_get_contents($base . '/' . $dir . '/LC_MESSAGES/meta.json'))->toArray());
        }
    }

    /**
     * Add additional word
     *
     * @param array $words
     * @return void
     */
    public function appendWords(array $words): void
    {
        $this->additionalWords = array_merge($this->additionalWords, $words);
    }

    /**
     * Getter for language list
     *
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * Setter for locate prop
     *
     * @param string $locale
     * @return void
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function setLocalePath(string $localePath): void
    {
        $this->localePath = $localePath;
    }

    public function setLocaleFormat(string $localeFormat): void
    {
        $this->localeFormat = $localeFormat;
    }

    /**
     * @return bool
     */
    public function isLocaleExists(): bool
    {
        return file_exists($this->getLocalePath());
    }

    /**
     * Concating some property into string
     * as locale path
     *
     * @return string
     */
    public function getLocalePath(): string
    {
        return $this->localePath . basename($this->locale) . $this->localeFormat;
    }

    /**
     * Remove last temp language cookie
     *
     * @param string $languageName
     * @param string $remeberAs
     * @return void
     */
    public function forgetTempLanguage(string $languageName, string $remeberAs = 'select_lang'): void
    {
        @setcookie($remeberAs, $languageName, [
            'expires' => time()-$this->tempLanguageTtl,
            'path' => SWB,
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Register new temp language
     *
     * @param string $languageName
     * @param string $remeberAs
     * @return string
     */
    public function rememberTempLanguage(string $languageName, string $remeberAs = 'select_lang'): string
    {
        @setcookie($remeberAs, $languageName, [
            'expires' => time()+$this->tempLanguageTtl,
            'path' => SWB,
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        return $languageName;
    }

    /**
     * Getter for temp language
     *
     * @param string $remeberAs
     * @return string|null
     */
    public function getLastTempLanguage(string $remeberAs = 'select_lang'): ?string
    {
        return $this->hasTempLanguage($remeberAs) ? $_COOKIE['select_lang'] : null;
    }

    public function findLanguage(string $localeInput)
    {
        return array_values(array_filter($this->languages, function($language) use($localeInput) {
            return $language[0] === $localeInput;
        }))[0]??[];
    }

    /**
     * @param string $remeberAs
     * @return boolean
     */
    public function hasTempLanguage(string $remeberAs = 'select_lang'): bool
    {
        return isset($_COOKIE['select_lang']);
    }

    public function loadPluginLocale()
    {
        if ($this->isLocaleExists() === false && ($localePluginBase = $this->findLanguage($this->locale)))
        {
            list($code, $languageName, $nativeName, $path) = $localePluginBase;
            $this->localePath = $path . DS;
        }
    }

    /**
     * Load all configuration 
     * and setup dictionary
     *
     * @param Closure|string $callback
     * @return void
     */    
    public function load(\Closure|string $callback = ''): void
    {
        if (is_callable($callback)) $callback(self::getInstance());
        $this->loadPluginLocale();
        if ($this->isLocaleExists() === false) $this->locale = 'en_US';
        $this->dictionary = $this->loader?->loadFile($this->getLocalePath());
    }
}