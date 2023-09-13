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
     * Clean exif info
     * modification from https://stackoverflow.com/a/38862429/13322576
     * @return void
     */
    public function cleanExifInfo()
    {
        if (!empty($this->uploadedFile)) {
            // Open the input file for binary reading
            $originName = $this->uploadedFile;
            $f1 = fopen($original = $this->path . DS . $originName, 'rb');
            // Open the output file for binary writing
            $cleanName = 'clean' . $this->uploadedFile;
            $f2 = fopen($newFile = $this->path . DS . $cleanName, 'wb');

            // Find EXIF marker
            while (($s = fread($f1, 2))) {
                $word = unpack('ni', $s)['i'];
                if ($word == 0xFFE1) {
                    // Read length (includes the word used for the length)
                    $s = fread($f1, 2);
                    $len = unpack('ni', $s)['i'];
                    // Skip the EXIF info
                    fread($f1, $len - 2);
                    break;
                } else {
                    fwrite($f2, $s, 2);
                }
            }

            // Write the rest of the file
            while (($s = fread($f1, 4096))) {
                fwrite($f2, $s, strlen($s));
            }

            fclose($f1);
            fclose($f2);

            // move clean version to original file
            $this->filesystem->move($cleanName, $originName);
        }
    }
}
