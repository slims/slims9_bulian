<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-01 16:04:53
 * @modify date 2022-11-03 11:38:21
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
}
