<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-03 09:54:25
 * @modify date 2024-05-12 07:51:04
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
        if (is_callable($callback))
        {   
            $callback('beforeContent', $filePath);
        }

        header('Content-Description: File Transfer');
        header('Content-Disposition: inline; filename="'.basename($filePath).'"');
        header('Content-Type: '. $this->filesystem->mimeType($filePath));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $this->filesystem->fileSize($filePath));
        
        $resource = $this->filesystem->readStream($filePath);
        
        while (!feof($resource)) {
            echo fread($resource, 8192);

            ob_flush();
            flush();
        }

        if (is_callable($callback))
        {   
            $callback('afterContent', $filePath);
        }

        fclose($resource);
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
        
        $resource = $this->filesystem->readStream($filePath);
        
        while (!feof($resource)) {
            echo fread($resource, 8192);

            ob_flush();
            flush();
        }

        fclose($resource);
        exit;
    }
}
