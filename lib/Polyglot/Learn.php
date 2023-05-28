<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-05-28 07:42:15
 * @modify date 2023-05-28 13:43:06
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Polyglot;

final class Learn
{
    public static function newLanguage()
    {
        $learn = new static;
        return $learn;
    }

    /**
     * 
     * Merge additional language
     * from .mo file
     * 
     * @param string $localePath
     * @return void
     */
    public function fromMoFile(string $localePath)
    {
        $memory = Memory::getInstance();
        
        $newLanguage = $memory->getLoader()->loadFile($localePath);
        $mergeLanguage = $memory->getDictionary()->mergeWith($newLanguage);
        $memory->setDictionary($mergeLanguage);
    }

    /**
     * Append additional language
     * from php file
     *
     * @param string $localePath
     * @return void
     */
    public function fromPhpFile(string $localePath): void
    {
        Memory::getInstance()->appendWords(require $localePath);
    }
}