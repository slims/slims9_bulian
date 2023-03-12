<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-03 09:54:25
 * @modify date 2023-03-12 08:28:19
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
        header('Content-Type: '. mime_content_type($this->getPath($filePath)));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($this->getPath($filePath)));
        ob_clean();
        flush();

        echo $this->filesystem->read($filePath);
        if (is_callable($callback))
        {   
            $callback('afterContent', $filePath);
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
        ob_clean();
        flush();
        exit($this->filesystem->read($filePath));
    }
}
