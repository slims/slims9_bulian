<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-01 10:16:14
 * @modify date 2023-02-04 18:34:15
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Filesystems\Providers;

use closure;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToWriteFile;
use SLiMS\Filesystems\{Guard,Utils,Stream};

class Local extends Contract
{
    use Guard,Utils,Stream;
    
    private $uploadedFile = '';
    private $uploadStatus = true;
    
    /**
     * Define adapter and filesystem
     *
     * @param string $root
     */
    public function __construct(string $root)
    {
        $this->adapter = new LocalFilesystemAdapter($root);
        $this->filesystem = new Filesystem($this->adapter);
        $this->path = $root;
    }

    /**
     * Upload file process with stream
     *
     * @param string $fileToUpload
     * @param closure $validation
     * @return object
     */
    public function upload(string $fileToUpload, closure $validation)
    {
        try {
            // create new random file name
            $this->uploadedFile = md5(date('this')) . $this->getExt($fileToUpload);

            // file resource
            $resource = fopen($_FILES[$fileToUpload]['tmp_name'], 'r+');

            // Write file 
            $this->filesystem->writeStream($this->uploadedFile, $resource);

            // Make a validation
            $validation($this);

            // Close resource
            if (is_resource($resource)) fclose($resource);

        } catch (FilesystemException | UnableToWriteFile $e) {
            $this->error = $e->getMessage();
        }
        
        return $this;
    }

    /**
     * Rename uploaded file with new name
     *
     * @param string $newName
     * @return object
     */
    public function as(string $newName)
    {
        if ($this->uploadStatus)
        {
            $this->filesystem->move($this->uploadedFile, $newName . $this->getExt($this->uploadedFile));
            $this->uploadedFile = $newName . $this->getExt($this->uploadedFile);
        }
        
        return $this;
    }
}