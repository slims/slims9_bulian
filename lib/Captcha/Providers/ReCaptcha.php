<?php
/**
 * @author Waris Agung Widodo
 * @email ido.alit@gmail.com
 * @Last Modified by drajathasan (drajathasan20@gmail.com)
 * @create date 2018-04-04 06:48:28
 * @modify date 2023-05-16 11:39:21
 *
 * Copyright (C) 2017  Waris Agung Widodo (ido.alit@gmail.com)
 */

namespace SLiMS\Captcha\Providers;

use Exception;
use SLiMS\Captcha\Factory;
use SLiMS\Http\Client;

class ReCaptcha extends Contract
{
    protected $factory = null;
    const PUBKEY = '6LdCzFAUAAAAAKV0pEX3h3523MZA5ATRZf2GpgQC';
    const PRIVKEY = '6LdCzFAUAAAAABb8kVMaf97GiQFP9lfX56BPhhGs';
    
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Make a request to google
     *
     * @return bool
     */
    public function validate()
    {
        $url = $this->factory->getProviderConfig('varify_url');

        $data = array(
            'secret' => $this->factory->getProviderConfig('privatekey'),
            'response' => $_POST["g-recaptcha-response"]??'',
            'remoteip' => ip()
        );

        $options = array(
            'http' => array(
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );

        try {
            $context  = stream_context_create($options);
            $verify = @file_get_contents($url, false, $context);
            $captcha = json_decode($verify);

            if ($captcha->success === false) {
                $keyError = "error-codes";
                throw new Exception(implode(', ', $captcha->$keyError));
            }
                
            return $captcha->success;
        } catch (Exception $e) {
            $this->error = 'Captcha Error : ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Generate html result
     *
     * @return void
     */
    public function generateCaptcha() {
        return '<script src="https://www.google.com/recaptcha/api.js"></script>
        <div class="g-recaptcha" data-sitekey="'.$this->factory->getProviderConfig('publickey').'"></div>';
    }
}