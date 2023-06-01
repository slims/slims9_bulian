<?php
/**
 * @original modified by Hendro Wicaksono (hendrowicaksono@yahoo.com)
 * @rebuild by Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-14 12:49:19
 * @modify date 2023-01-12 08:07:16
 * @desc 
*/
/*
    Heavily modified for SLiMS by Hendro Wicaksono (hendrowicaksono@yahoo.com)
    (Senayan Library Management System), http://slims.web.id / http://senayan.diknas.go.id
    It is derived from:
    ---------------------
    MINIGAL NANO
    - A PHP/HTML/CSS based image gallery script
    This script and included files are subject to licensing from Creative Commons (http://creativecommons.org/licenses/by-sa/2.5/)
    You may use, edit and redistribute this script, as long as you pay tribute to the original author by NOT removing the linkback to www.minigal.dk ("Powered by MiniGal Nano x.x.x")
    MiniGal Nano is created by Thomas Rybak
    Copyright 2010 by Thomas Rybak
    Support: www.minigal.dk
    Community: www.minigal.dk/forum
    Please enjoy this free script!
    USAGE EXAMPLE:
    File: createthumb.php
    Example: <img src="createthumb.php?filename=photo.jpg&amp;width=100&amp;height=100">
    ----------------------
    Updated Example: $size is not used. Only width and height.
*/

namespace Minigalnano;

// error_reporting(E_ALL ^ E_DEPRECATED);

class Thumb
{
    /**
     * X and Y coordinate
     *
     * @var string
     */
    private $target = "";
    private $xoord = 0;
    private $yoord = 0;

    /**
     * Resolution
     *
     * @var integer
     */
    private $defaultResWidth = 42;
    private $resulutionWidth = 0;
    private $resulutionHeight = 0;

    /**
     * Cache property
     *
     * @var array
     */
    private $cache = [
        'enable' => false,
        'folder' => '../../images/cache/',
        'handle' => '',
        'prefix' => ''
    ];

    /**
     * Measurement
     *
     * @var string
     */
    private $width = 0;
    private $height = 0;
    private $imageWidth = 0;
    private $imageHeight = 0;

    /**
     * file
     *
     * @var string
     */
    private $storage = '';
    private $filePath = '';
    private $error = '';
    
    public function __construct($storage, $filePath, $cachePrefix = '_slims_img_cache_resolutionWidth_x_resolutionHeight_')
    {
        $this->storage = $storage;
        $this->filePath = $filePath;
        $this->cache['prefix'] = $cachePrefix;
        $this->cache['file'] = $this->cache['folder'] . $cachePrefix . basename($filePath);
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Get cache option
     *
     * @param [type] $key
     * @return void
     */
    public function getCacheOption($key)
    {
        return $this->cache[$key]??null;
    }

    /**
     * Set header content type
     *
     * @return void
     */
    public function setContentType(string $type = '')
    {
        if (defined('THUMB_HEADER_CACHE')) header('Cache-Control: max-age=86400');
        if (preg_match("/.jpg$|.jpeg$/i", $this->filePath) && empty($type)) header('Content-type: image/jpeg');
        if (preg_match("/.gif$/i", $this->filePath) && empty($type)) header('Content-type: image/gif');
        if (preg_match("/.png$/i", $this->filePath) && empty($type)) header('Content-type: image/png');
        if (!empty($type)) header('Content-type: ' . $type);
    }

    /**
     * Set cache option
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setCacheOption($key, $value)
    {
        if (isset($this->cache[$key])) $this->cache[$key] = $value;
    }

    /**
     * Check if file is allow or not
     *
     * @return value
     */
    public function isFileAllow()
    {
        if (!((preg_match("/.jpg$|.jpeg$/i", $this->filePath)) OR (preg_match("/.gif$/i", $this->filePath)) OR (preg_match("/.png$/i", $this->filePath)))) {
            $this->error = 'wrongcontenttype';
        }
        return $this;
    }

    /**
     * Existing file
     *
     * @return bool
     */
    public function isFileExists()
    {
        if (!$this->storage->isExists($this->filePath))
        {
            $this->error = 'filenotfound';
        }
        return $this;
    }

    /**
     * Read permission is important
     *
     * @return bool
     */
    public function isReadable()
    {
        if (!$this->storage->read($this->filePath))
        {
            $this->error = 'filecantbeopened';
        }
        return $this;
    }

    /**
     * Cache check
     *
     * @return bool
     */
    public function isCacheExists()
    {
        return file_exists($this->cache['file']);
    }

    /**
     * Setter for width
     *
     * @param int|string $width
     * @return void
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Setter for height
     *
     * @param int|string $height
     * @return void
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * Sizing processs
     *
     * @return Thumb
     */
    public function prepare()
    {
        $imageSize = getimagesizefromstring($this->storage->read($this->filePath));
        $this->imageWidth = $imageSize[0];
        $this->imageHeight = $imageSize[1];

        $this->resulutionWidth = $this->width != 0 ? $this->width : $this->defaultResWidth;
        $this->resulutionHeight = $this->height == 0 ? number((($this->resulutionWidth/$this->imageWidth) * $this->imageHeight))->toInteger() : $this->height;
        
        $this->cache['file'] = str_replace(['resolutionWidth','resolutionHeight'], [$this->resulutionWidth,$this->resulutionHeight], $this->cache['file']);

        return $this;
    }

    /**
     * Set error state
     *
     * @return value
     */
    public function orError()
    {
        if (empty($this->error)) return;
        self::setError($this->error);
    }

    public static function setError(string $error = 'filenotfound')
    {
        $static = new static(null, __DIR__);
        // Create http header based on file extension
        $static->setContentType('image/png');
        echo file_get_contents(__DIR__ . '/' . $error . '.png');
        exit;
    }

    /**
     * Generate thumbnail image and caching image
     *
     * @return void
     */
    public function generate()
    {
        // Create http header based on file extension
        $this->setContentType();

        if (!$this->isCacheExists())
        {
            ob_start();
            // Create image source
            $target = imagecreatetruecolor($this->resulutionWidth, $this->resulutionHeight);
            if (preg_match("/.jpg$|.jpeg$/i", $this->filePath)) $source = imagecreatefromstring($this->storage->read($this->filePath));
            if (preg_match("/.gif$/i", $this->filePath)) $source = imagecreatefromstring($this->storage->read($this->filePath));
            if (preg_match("/.png$/i", $this->filePath)) $source = imagecreatefromstring($this->storage->read($this->filePath));

            // preserve transparency
            imagealphablending($target, false);
            imagesavealpha($target,true);
            $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
            imagefilledrectangle($target, 0, 0, $this->resulutionWidth, $this->resulutionHeight, $transparent);

            imagecopyresampled($target,$source,0,0,$this->xoord,$this->yoord,$this->resulutionWidth, $this->resulutionHeight,$this->imageWidth,$this->imageHeight);
            imagedestroy($source);
            ob_end_clean();

            if (preg_match("/.jpg$|.jpeg$/i", $this->filePath)) {
                imagejpeg($target,null,90);
                if ($this->cache['enable']) $this->createCache($target, 'jpeg');
            }

            if (preg_match("/.gif$/i", $this->filePath)) {
                imagegif($target,null);
                if ($this->cache['enable']) $this->createCache($target, 'gif');
            }

            if (preg_match("/.png$/i", $this->filePath)) {
                imagepng($target,null,9);
                if ($this->cache['enable']) $this->createCache($target, 'png');
            }
            
            imagedestroy($target);
        }
        else
        {
            echo file_get_contents($this->cache['file']);
        }
        exit;
    }

    /**
     * Create image cache
     *
     * @param [type] $target
     * @param string $imageType
     * @return void
     */
    private function createCache($target, string $imageType)
    {
        if ($this->storage->getProviderName() == 'Local')
        {
            switch ($imageType) {
                case 'jpeg':
                    imagejpeg($target,$this->cache['file'],90);
                    break;
                
                case 'gif':
                    imagegif($target,$this->cache['file']);
                    break;
    
                case 'png':
                    imagepng($target,$this->cache['file'],9);
                    break;
            }
        }
    }
}
