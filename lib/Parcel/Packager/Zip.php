<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-04-06 07:59:28
 * @modify date 2023-04-06 15:55:48
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Parcel\Packager;

use SLiMS\Filesystems\Storage;

class Zip extends Standart
{
    private $storage = null;
    protected $uniquePath = '';

    public function __construct(string $filepath)
    {
        if (!$this->isCompressorExists()) throw new \Exception("Extension Zip is not available!");
        $this->compressor = new \ZipArchive;
        $this->storage = Storage::files();
        $this->filepath = $filepath;
        $this->filepathInfo = pathinfo($filepath);
        $this->uniquePath = 'temp' . DS . \utility::createRandomString(int_num_string: 5);
    }

    public function open(int $flag = 0)
    {
        $this->resource = $this->compressor->open($this->filepath, $flag);
        return $this;
    }

    public function create(\Closure $callback)
    {
        $this->open(\ZipArchive::CREATE);
        $callback($this);
    }

    public function extract()
    {
        $this->open();
        if ($this->resource !== TRUE) throw new \Exception($this->resource);

        if ($this->storage->isExists($this->uniquePath) === FALSE) $this->storage->makeDirectory($this->uniquePath);

        $this->compressor->extractTo(SB . 'files' . DS . $this->uniquePath);
        return $this;
    }

    public function addFile(string $filepath, string $newFilePath = '')
    {
        $this->compressor->addFile($filepath, empty($newFilePath) ? basename($filepath) : $newFilePath);
    }

    public function addFileToDirectory(string $directoryName, string|array $filepath)
    {
        $this->compressor->addEmptyDir($directoryName);
        if (is_string($filepath)) $filepath = [$filepath];
        
        foreach ($filepath as $path) {
            $this->addFile($path, $directoryName . DS . basename($path));
        }
    }

    public function to($newDestionation)
    {
        if (is_callable($newDestionation)) return $newDestionation($this);

        for( $i = 0; $i < $this->compressor->numFiles; $i++ ){ 
            $stat = $this->compressor->statIndex( $i ); 
            
            $this->createDirIfNotExists($newDestionation . DS . dirname($stat['name']));

            $fullPath = $this->storage->getPath() . $this->uniquePath . DS . $stat['name'];
            
            if (is_dir($fullPath . DS)) continue;
            rename($fullPath, $newDestionation . $stat['name']);
        }

        $this->storage->delete($this->uniquePath . DS );
        
        $this->close();
    }

    public function isCompressorExists()
    {
        return class_exists('\ZipArchive');
    }

    public function close()
    {
        $this->compressor->close();
    }
}