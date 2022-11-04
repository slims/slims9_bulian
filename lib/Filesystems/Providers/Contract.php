<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-01 10:18:42
 * @modify date 2022-11-04 08:03:09
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Filesystems\Providers;

use closure;
use ReflectionClass;

abstract class Contract
{
    /**
     * Default propperty
     *
     */
    protected $adapter;
    protected $filesystem;
    protected $path = '';
    protected $error = '';
    
    /**
     * Retrieve all content of directory
     *
     * @param string $directoryName
     * @return void
     */
    public function directories(string $directoryName = '/')
    {
        return $this->filesystem->listContents($directoryName);
    }

    /**
     * Make a directory
     *
     * @param string $directoryName
     * @return void
     */
    public function makeDirectory(string $directoryName)
    {
        $this->filesystem->createDirectory($directoryName);
    }

    /**
     * Check if file or directory exists or not
     *
     * @param string $path
     * @return boolean
     */
    public function isExists(string $path)
    {
        return $this->filesystem->has($path);
    }

    /**
     * Move file
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    public function move(string $from, string $to)
    {
        $this->filesystem->move($from, $to);
    }

    /**
     * Copy file 
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    public function copy(string $from, string $to)
    {
        $this->filesystem->copy($from, $to);
    }

    /**
     * Delete file
     *
     * @param string $path
     * @return void
     */
    public function delete(string $path)
    {
        return $this->filesystem->delete($path);
    }

    /**
     * Delete directory
     *
     * @param string $path
     * @return void
     */
    public function deleteDirectory(string $path)
    {
        return $this->filesystem->deleteDirectory($path);
    }

    /**
     * Create file
     *
     * @param string $path
     * @param mixed $content
     * @return void
     */
    public function put(string $path, $content)
    {
        return $this->filesystem->write($path, $content);
    }

    /**
     * Create stream file
     *
     * @param string $path
     * @param mixed $content
     * @return void
     */
    public function putStream(string $path, $content)
    {
        return $this->filesystem->writeStream($path, $content);
    }

    /**
     * Read a file
     *
     * @param string $path
     * @return void
     */
    public function read(string $path)
    {
        return $this->filesystem->read($path);
    }

    /**
     * Read a file with stream
     *
     * @param string $path
     * @return void
     */
    public function readStream(string $path)
    {
        return $this->filesystem->readStream($path);
    }

    /**
     * Get last modified
     */
    public function lastModified(string $path)
    {
        return $this->filesystem->lastModified($path);
    }

    /**
     * Upload
     * 
     * @param string $fileToUpload
     * @param closure $validation
     * @return object
     */
    abstract public function upload(string $fileToUpload, closure $validation);

    /**
     * Get class name
     *
     * @return string
     */
    public function getProviderName()
    {
        $Class = new ReflectionClass($this);
        return $Class->getShortName();
    }

    /**
     * Get size of file
     *
     * @param string $path
     * @return void
     */
    public function getSize(string $path)
    {
        return $this->filesystem->fileSize($path);
    }

    // Get error
    public function getError()
    {
        return $this->error;
    }
}