<?php
/**
 * simbio_file_upload class
 * A File Upload helper class
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
  die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
  die("can not access this file directly");
}

/**
 * Upload status constant
 */
define('UPLOAD_FAILED', 0);
define('UPLOAD_SUCCESS', 1);
define('FILETYPE_NOT_ALLOWED', 2);
define('FILESIZE_EXCED', 3);

class simbio_file_upload extends simbio
{
  private $allowable_ext = array('.jpg', '.jpeg', '.gif', '.png', '.html', '.htm', '.pdf', '.doc', '.txt');
  private $max_size = 1024000; // in bytes
  private $upload_dir = './';
  public $new_filename;


  /**
   * Method to set allowable file formats
   *
   * @param   array   $array_allowable_ext
   * @return  void
   */
  public function setAllowableFormat($array_allowable_ext)
  {
    if ($array_allowable_ext == '*') {
      $this->allowable_ext = '*';
      return;
    }

    if (is_array($array_allowable_ext)) {
      $this->allowable_ext = $array_allowable_ext;
    } else {
      echo 'setAllowableFormat method error : The argument for must be an array';
      return;
    }
  }


  /**
   * Method to set maximum size of file to upload
   *
   * @param   integer $int_max_size
   * @return  void
   */
  public function setMaxSize($int_max_size)
  {
    // checking for php.ini upload_size
    $this->max_size = intval($int_max_size);
  }


  /**
   * Method to set upload file directory
   *
   * @param   string  $str_upload_dir
   * @return  void
   */
  public function setUploadDir($str_upload_dir)
  {
    $this->upload_dir = $str_upload_dir;
  }


  /**
   * Method to upload file
   *
   * @param   string  $file_input_name
   * @param   string  $str_new_filename
   * @return  integer
   */
  public function doUpload($file_input_name, $str_new_filename = '')
  {
    // get file extension
    $file_ext = substr($_FILES[$file_input_name]['name'], strrpos($_FILES[$file_input_name]['name'], '.'));
    if (empty($str_new_filename)) {
      $this->new_filename = basename($_FILES[$file_input_name]['name']);
    } else {
      $this->new_filename = $str_new_filename.$file_ext;
    }

    $_isTypeAllowed = 0;
    // checking file extensions
    if ($this->allowable_ext != '*') {
      foreach ($this->allowable_ext as $ext) {
        if ($ext == $file_ext) {
          $_isTypeAllowed++;
        }
      }

      if (!$_isTypeAllowed) {
        $this->error = 'Filetype is forbidden';
        return FILETYPE_NOT_ALLOWED;
      }
    }

    // check for file size
    $_size_kb = ((integer)$this->max_size)/1024;
    if ($_FILES[$file_input_name]['size'] > $this->max_size) {
      $this->error = 'Filesize is excedded maximum uploaded file size';
      return FILESIZE_EXCED;
    }

    // uploading file
    if (self::chunkUpload($_FILES[$file_input_name]['tmp_name'], $this->upload_dir.'/'.$this->new_filename)) {
      return UPLOAD_SUCCESS;
    } else {
      $upload_error = error_get_last();
      $error_msg = '';
      if ($upload_error) {
        $error_msg = 'PHP Error ('.$upload_error['message'].')';
      }
      $this->error = 'Upload failed. Upload directory is not writable or not exists. '.$error_msg;
      return UPLOAD_FAILED;
    }
  }

  public function chunkUpload($tmpfile,$target_file){
    set_time_limit(0);
    $orig_file_size = filesize($tmpfile);
    $chunk_size     = 256; // chunk in bytes
    $upload_start   = 0;
    $handle         = fopen($tmpfile, "rb");
    $fp             = fopen($target_file, 'w');
    while($upload_start < $orig_file_size) {
        $contents = fread($handle, $chunk_size);
        fwrite($fp, $contents);
        if($upload_start % 10000 == 0){
            $count = array('data'=>array('upload_progress' => ceil(($upload_start/$orig_file_size)*100).'%'));
        }
        if (ENVIRONMENT === 'development') {
            echo '<script type="text/javascript">';
            echo 'console.log(\''.json_encode($count).'\')';
            echo '</script>';
        }
        $upload_start += strlen($contents);
        fseek($handle, $upload_start);
    }
    fclose($handle);
    fclose($fp);
    unlink($tmpfile);
    return true;
  }

}
