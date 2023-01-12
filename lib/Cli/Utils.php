<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-01-12 15:15:06
 * @modify date 2023-01-12 15:44:24
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Cli;

trait Utils
{
    protected $output = '';

    public function output($content)
    {
        if (is_string($content)) $this->output = $content;
        else $this->json($content);
    }

    public function info($content)
    {
        if (is_string($content)) $this->output = '<info>' . $content . '</info>';
        else $this->json($content);
    }

    public function error($content)
    {
        if (is_string($content)) $this->output = '<error>' . $content . '</error>';
        else $this->json($content);
    }

    public function json($content)
    {
        $this->output = json_encode($content);
    }

    public function table(array $header, array $data)
    {
        $this->output = ['table' => func_get_args()];
    }
}
