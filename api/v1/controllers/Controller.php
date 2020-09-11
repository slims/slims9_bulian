<?php

/**
 * @author              : Waris Agung Widodo
 * @Date                : 2017-07-05 12:17:02
 * @Last Modified by    : ido
 * @Last Modified time  : 2017-07-05 13:53:28
 *
 * Copyright (C) 2017  Waris Agung Widodo (ido.alit@gmail.com)
 */

class Controller
{
 
    public function withJson($data)
    {
        header('Content-type: application/json');
        if (is_array($data)) {
            echo json_encode($data);
        } else {
            echo $data;
        }

        return true;
    }
    
}