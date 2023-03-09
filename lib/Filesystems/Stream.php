<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-03 09:54:25
 * @modify date 2023-03-10 02:43:27
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Filesystems;

trait Stream
{
    /**
     * Open and stream a file
     *
     * @param string $filePath
     * @param string $callback
     * @return void
     */
    public function streamFile(string $filePath, $callback = '')
    {
        header('Content-Disposition: inline; filename="'.basename($filePath).'"');
        header('Content-Type: '. mime_content_type($filePath));

        echo $this->filesystem->read($filePath);
        if (is_callable($callback))
        {   
            $callback();
        }
        exit;
    }

    /**
     * Download somae file
     *
     * @param string $filePath
     * @return void
     */
    public function download(string $filePath)
    {
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
        header('Content-Type: '.$this->filesystem->mimeType($filePath));
        exit($this->filesystem->read($filePath));
    }
}
