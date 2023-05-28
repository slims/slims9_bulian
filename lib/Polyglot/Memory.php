<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-05-27 21:37:29
 * @modify date 2023-05-28 09:41:58
 * @license GPLv3
 * @desc [description]
 */
namespace SLiMS\Polyglot;

use SLiMS\Config;
use Gettext\Loader\MoLoader;

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
        $this->locale = Config::getInstance()->get('default_lang');
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
        $findInDictionary = self::getInstance()->dictionary->find(null, $content);
        $additionalWords = self::getInstance()->findFromAdditionalWords($content);
        return $findInDictionary?->getTranslation()??$additionalWords??$content;
    }

    public function findFromAdditionalWords(string $content)
    {
        return $this->additionalWords[$content]??null;
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
    public function registerLanguage(string $code, string $englishName, string $nativeName): Memory
    {
        $this->languages = array_merge($this->languages, [[$code, $englishName, $nativeName]]);
        return $this;
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

    /**
     * @param string $remeberAs
     * @return boolean
     */
    public function hasTempLanguage(string $remeberAs = 'select_lang'): bool
    {
        return isset($_COOKIE['select_lang']);
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
        if ($this->isLocaleExists() === false) $this->locale = 'en_US';
        $this->dictionary = $this->loader?->loadFile($this->getLocalePath());
    }
}