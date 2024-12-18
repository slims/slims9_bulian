<?php

/**
 * @author              : Waris Agung Widodo
 * @Date                : 2017-07-05 13:05:36
 * @Last Modified by    : ido
 * @Last Modified time  : 2017-07-05 13:12:51
 *
 * Copyright (C) 2017  Waris Agung Widodo (ido.alit@gmail.com)
 */

require_once 'Controller.php';

class HomeController extends Controller
{
    
    public function index()
    {
        $response = array(
            'error' => false,
            'message' => 'Sugeng rawuh is selamat datang.'
            );
        parent::withJson($response);
    }
}