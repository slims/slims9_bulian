<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-15 08:26:27
 * @modify date 2023-02-12 10:54:50
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Http;

use SLiMS\Json;

trait Utils
{
    public function getContent()
    {
        return $this->response->content;
    }

    public function getHeader(string $key = '')
    {
        return empty($key) ? $this->response->headers : ($this->response->headers[$key]??null);
    }

    public function getStatusCode()
    {
        return $this->request->getStatusCode();
    }

    public static function compileOptions(array $options)
    {
        if (isset($options['header']) || isset($options['body'])) return $options;

        // as form params
        return $options ? array_merge(self::$instance->httpOptions, [
            'form_params' => $options[0]??[]
        ]) : self::$instance->httpOptions;
    }

    public function isJson()
    {
        $jsonDecode = Json::parse($this->getContent());
        return is_array($jsonDecode) || is_object($jsonDecode) || is_string($jsonDecode);
    }

    public function toArray()
    {
        return $this->isJson() ? Json::parse($this->getContent()) : (array)$this->getContent();
    }

    public function setError(string $error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }
}