<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-06 23:55:09
 * @modify date 2022-10-08 11:31:01
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Mail;

abstract class TemplateContract
{
    protected $contents = '';
    protected $mail = null;
    protected $minify = false;

    abstract public function render();
    
    public function asAltBody()
    {
        if (!$this->minify) return strip_tags($this->contents);
        
        return strip_tags(preg_replace(['/ {2,}/','/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'], [' ',''],$this->contents));
    }

    public function __toString()
    {
        if (!$this->minify) return $this->contents;

        return preg_replace(['/ {2,}/','/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'], [' ',''],$this->contents);
    }
}