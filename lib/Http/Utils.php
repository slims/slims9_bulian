<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-15 08:26:27
 * @modify date 2022-11-15 17:27:18
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Http;

use SLiMS\Json;

trait Utils
{
    public function getContent()
    {
        return $this->content;
    }

    public function getHeader(string $key = '')
    {
        return empty($key) ? $this->request->getHeaders() : $this->request->getHeader($key);
    }

    public function getStatusCode()
    {
        return $this->request->getStatusCode();
    }

    public function toJson()
    {
        return Json::parse($this->getContent());
    }

    public function toArray()
    {
        return $this->toJson()->toArray();
    }

    public function setError(string $error)
    {
        $this->error = $error;
    }
}