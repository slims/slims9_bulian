<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-01 16:04:53
 * @modify date 2023-08-17 20:07:12
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Filesystems;
require LIB.'SimpleImage/SimpleImage.php';

trait Utils
{
    /**
     * Convert from '2MByte' to '2000000Byte'
     *
     * @param string $NumberLimit
     * @return void
     */
    public function toByteSize(string $NumberLimit) 
    {
        $NumberLimit = str_replace(',', '.', $NumberLimit);
        $Unitmap = ['B'=> 0, 'KB'=> 3, 'MB'=> 6, 'GB'=> 9, 'TB'=> 12, 'PB'=> 15, 'EB'=> 18, 'ZB'=> 21, 'YB'=> 24];
        $InjectUnit = strtoupper(trim(substr($NumberLimit, -2)));
        $Number = trim(str_replace($InjectUnit, '', $NumberLimit));

        if (intval($InjectUnit) !== 0) {
            $InjectUnit = 'B';
        }

        if (!in_array($InjectUnit, array_keys($Unitmap))) {
            return false;
        }

        $inByte = $Number * ('1' . str_repeat(0,$Unitmap[$InjectUnit]));
        return $inByte;
    }   

    /**
     * Took from https://stackoverflow.com/questions/5501427/php-filesize-mb-kb-conversion#answer-5501447
     *
     * @param [type] $bytes
     * @return void
     */
    public function toUnitSize($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 0) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 0) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 0) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * Get extension from path info
     *
     * @param string $fileToUpload
     * @return void
     */
    public function getExt(string $fileToUpload)
    {
        $info = pathinfo($_FILES[$fileToUpload]['name']??$fileToUpload);
        return '.' . $info['extension']??null;
    }

    /**
     * Get filename of uploaded file
     *
     * @return string
     */
    public function getUploadedFileName()
    {
        return basename($this->uploadedFile);
    }

    /**
     * Get uploaded status
     *
     * @return boolean
     */
    public function getUploadStatus()
    {
        return $this->uploadStatus;
    }

    /**
     * Clean sensitive exif info
     * @return void
     */
    public function cleanExifInfo()
    {
        if (!empty($this->uploadedFile)) {
            try {
                $originName = $this->uploadedFile;
                $buffer = $this->filesystem->read($this->uploadedFile);
                $imageIndetification = pathinfo($originName);

                if (!in_array('.' . ($imageIndetification['extension']??'?'), config('allowed_images'))) {
                    return;
                }

                $ext = $imageIndetification['extension'];

                //  no gif
                if (strtolower($ext) == 'gif') return;

                //  jpg is jpeg
                if ($ext == 'jpg') $ext = 'jpeg';

                // create image from string
                #$image = imagecreatefromstring($buffer);
                $image = new \claviska\SimpleImage();
                $image->fromDataUri($buffer);
                $image->save();

                if (function_exists(($functionName = 'image' . $ext))) {
                    $compressLevel = [
                        'png' => 9,
                        'jpeg' => 90
                    ];

                    $functionName($image, $this->path . DS . ($cleanName = 'clean' . $originName), $compressLevel[$ext]);
                }

                // move clean version to original file
                $this->filesystem->move($cleanName, $originName);
            } catch (\Exception $e) {
                writeLog('system', $e->getCode(), 'failed exif check', $e->getMessage());
            }
        }
    }
}
