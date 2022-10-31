<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-06 23:55:09
 * @modify date 2022-10-19 00:42:18
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Mail;

abstract class TemplateContract
{
    protected $contents = '';
    protected $mail = null;
    protected $minify = false;

    /**
     * Host check
     *
     * @return bool
     */
    public function isLocal()
    {
        // host check
        if (preg_match('/localhost/i', $_SERVER['HTTP_HOST'])) return true;

        // ip address = local
        if (preg_match('/(([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\\.){3}([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])/i', $_SERVER['HTTP_HOST'])) return true;

        // schema check
        if ($_SERVER['REQUEST_SCHEME'] == 'http' && !ip()->isBehindProxy()) return true;

        // online
        return false;
    }

    /**
     * Minify html template
     *
     * @param boolean $status
     * @return void
     */
    public function setMinify(bool $status)
    {
        $this->minify = $status;
    }

    /**
     * Book cover generator
     * 
     * https is required to make our resource is secure at 
     * browser (CSP issue)
     *
     * @param string $filename
     * @return void
     */
    public function generateCoverUrl($filename)
    {
        // in local environment? ok, use dummy image from shutterstock
        if ($this->isLocal()) return 'https://image.shutterstock.com/image-vector/abstract-a4-printable-brochure-book-600w-2105860082.jpg';

        // get from SLiMS with https
        return 'https://' . $_SERVER['SERVER_NAME'] . SWB . 'lib/minigalnano/createthumb.php?filename=images/' . (empty($filename) ? 'default/image.png' : 'docs/' . $filename) . '&width=60';
    }

    /**
     * Rendering processs
     *
     * @return object
     */
    abstract public function render();
    
    /**
     * Generate email body without html tag
     *
     * @return value
     */
    public function asAltBody()
    {
        if (!$this->minify) return strip_tags($this->contents);
        
        return strip_tags(preg_replace(['/ {2,}/','/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'], [' ',''],$this->contents));
    }

    /**
     * Convert object into string
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->minify) return $this->contents;

        return preg_replace(['/ {2,}/','/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'], [' ',''],$this->contents);
    }
}