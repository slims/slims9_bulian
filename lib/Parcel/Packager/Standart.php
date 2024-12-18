<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-04-06 07:59:14
 * @modify date 2023-04-06 15:26:23
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Parcel\Packager;

abstract class Standart
{
    /**
     * Compressor instance
     */
    protected $compressor = null;

    /**
     * Location of source file
     */
    protected $filepath = null;

    /**
     * Rinch info about source file
     * such as exstension, dirname etc.
     * @var array
     */
    protected array $filepathInfo = [];

    /**
     * An object of compressor stream file
     */
    protected $resource = null;

    /**
     * Randome string of file path
     */
    protected $uniquePath = '';

    /**
     * An method to make stream file by compressor
     */
    abstract protected function open(int $flag = 0);

    /**
     * Close compressor stream
     */
    abstract protected function close();

    /**
     * Create some archvie file based on callback
     *
     * @param \Closure $callback
     */
    abstract protected function create(\Closure $callback);

    /**
     * Extract an existing archive file
     */
    abstract protected function extract();

    /**
     * Move exstracting file to new destination
     *
     * @param string $newDestination
     */
    abstract protected function to(string $newDestination);

    /**
     * Method to check if compressor exstension 
     * is exist or not
     *
     * @return boolean
     */
    abstract protected function isCompressorExists();

    /**
     * Add file to a new/existing archive
     *
     * @param string $filepath
     * @param string $newFilePath
     */
    abstract protected function addFile(string $filepath, string $newFilePath = '');

    /**
     * Add file into a folder in a new/existing archive
     *
     * @param string $directoryName
     * @param string $filepath
     */
    abstract protected function addFileToDirectory(string $directoryName, string $filepath);

    public function getFilePath()
    {
        return $this->filepath;
    }

    public function getFilePathInfo()
    {
        return $this->filepathInfo;
    }

    public function getUniquePath()
    {
        return $this->uniquePath;
    }

    protected function createDirIfnotExists(string $path, int $mode = 0755, bool $recursive = true)
    {
        if (!file_exists($path)) mkdir($path, $mode, $recursive);
        return $path;
    }
}