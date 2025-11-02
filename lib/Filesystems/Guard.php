<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-01 12:06:21
 * @modify date 2023-01-01 21:26:13
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Filesystems;

use League\MimeTypeDetection\ExtensionMimeTypeDetector;

trait Guard
{
    /**
     * Check if mime is allowed or not
     *
     * @param array $allowedMime
     * @return boolean
     */
    public function isMimeAllowed(array $allowedMime = [])
    {
        if (!$this->uploadStatus) return false;
        
        $detector = new ExtensionMimeTypeDetector();
        $this->uploadStatus = in_array($detector->detectMimeTypeFromPath($this->path . $this->uploadedFile), ($allowedMime ? $allowedMime : config('mimetype')));

        if (!$this->uploadStatus) $this->error = str_replace('{mime}', $detector->detectMimeTypeFromPath($this->path . $this->uploadedFile), __('Mime {mime} is not allowed!'));

        return $this->uploadStatus;
    }

    /**
     * Check if extension is allowed or not
     *
     * @param array $allowedExtension
     * @return boolean
     */
    public function isExtensionAllowed(array $allowedExtension = [])
    {
        if (!$this->uploadStatus) return false;

        $this->uploadStatus = in_array($this->getExt($this->path . $this->uploadedFile)??'', ($allowedExtension ? $allowedExtension : config('allowed_file_att')));

        if (!$this->uploadStatus) $this->error = str_replace('{extension}', $this->getExt($this->path . $this->uploadedFile), __('Extension {extension} is not allowed!'));

        return $this->uploadStatus;
    }

    /**
     * File size limit check
     *
     * @param int|string $maxSize
     * @return boolean
     */
    public function isLimitExceeded($maxSize)
    {
        if (!$this->uploadStatus) return false;
        
        $this->uploadStatus = $maxSize > $this->getSize($this->uploadedFile);

        if (!$this->uploadStatus) $this->error = str_replace(['{fileSize}','{maxSize}'], [$this->toUnitSize($this->getSize($this->uploadedFile)), $this->toUnitSize($maxSize)], __('Size {fileSize} greater than {maxSize}.'));

        return $this->uploadStatus;
    }

    /**
     * File image check
     * @return boolean
     */
    public function isImageFile()
    {
        if (!$this->uploadStatus) return false;
        $this->uploadStatus = exif_imagetype($this->path.$this->uploadedFile);
        if (!$this->uploadStatus) {
            $this->error =  __('Wrong image filetype.');
        }
        return $this->uploadStatus;
    }

    /**
     * Sanitize image by regenerating it using PHP-GD
     * to clean potential data tampering or embedded scripts.
     * @return boolean
     */
    public function sanitizeImageWithGD()
    {
        if (!$this->uploadStatus) return false;

        $file_path = $this->path . $this->uploadedFile;
        $mime = mime_content_type($file_path);
        $img = false;

        if (!extension_loaded('gd')) {
            $this->uploadStatus = false;
            $this->error = __('PHP GD extension is not loaded, image sanitization failed.');
            return $this->uploadStatus;
        }

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $img = @imagecreatefromjpeg($file_path);
                if ($img) {
                    imagejpeg($img, $file_path, 90);
                }
                break;
            case 'image/png':
                $img = @imagecreatefrompng($file_path);
                if ($img) {
                    imagealphablending($img, false);
                    imagesavealpha($img, true);
                    imagepng($img, $file_path, 9);
                }
                break;
            case 'image/gif':
                $img = @imagecreatefromgif($file_path);
                if ($img) {
                    imagegif($img, $file_path);
                }
                break;
            default:
                return $this->uploadStatus;
        }

        if ($img === false) {
             $this->uploadStatus = false;
             $this->error = __('Failed to process image with GD (possible data tampering or invalid file structure).');
             return $this->uploadStatus;
        }

        if (isset($img)) {
            imagedestroy($img);
        }

        return $this->uploadStatus;
    }

    /**
     * get upload status
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->uploadStatus;
    }

    /**
     * Destroy uploaded file
     * if file have invalid condition
     *
     * @return void
     */
    public function destroyIfFailed()
    {
        if (!$this->uploadStatus) $this->filesystem->delete($this->uploadedFile);
    }
}