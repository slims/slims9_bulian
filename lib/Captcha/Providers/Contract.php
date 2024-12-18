<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-02-13 06:49:16
 * @modify date 2023-02-13 07:57:00
 * @license GPLv3
 * @desc [description]
 */
namespace SLiMS\Captcha\Providers;

use SLiMS\Captcha\Factory;

abstract class Contract
{
    protected $factory;
    protected $error;
    abstract public function __construct(Factory $factory);
    abstract public function validate();
    abstract public function generateCaptcha();

    public function getError()
    {
        return $this->error;
    }
}