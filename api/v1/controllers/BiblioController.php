<?php

/**
 * @author              : Waris Agung Widodo
 * @Date                : 2017-07-05 12:15:12
 * @Last Modified by    : ido
 * @Last Modified time  : 2017-07-05 15:08:08
 *
 * Copyright (C) 2017  Waris Agung Widodo (ido.alit@gmail.com)
 */

require_once 'Controller.php';

class BiblioController extends Controller
{
    protected $sysconf;
    protected $db;

    function __construct($sysconf, $obj_db)
    {
        $this->sysconf = $sysconf;
        $this->db = $obj_db;
    }

    public function detail($id, $token)
    {
        require_once __DIR__ . './../../../lib/api.inc.php';
        $biblio = api::biblio_load($this->db, $id);
        parent::withJson($biblio[0]);
    }
}