<?php
namespace SLiMS;
/**
 * Advanced logging class for librarian action.
 * Copyright (C) 2020  Hendro Wicaksono (hendrowicaksono@gmail.com)
 * This program is free software;
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

class AlLibrarian extends AdvancedLogging
{
    private $librarian_actions = array ('login', 'logout', 'access');

    public function __construct ($code, $log_options = array())
    {
        if (!isset($log_options['username'])) {
            die('Not enough parameters.');
        } else {
            $this->msg = $log_options;
        }
        $this->setChannel('system');
        $this->setRole('librarian');
        $this->setCode($code);
        $this->setUuid();
        $this->setFrom();
        $this->setWhen();
        $this->setDesc();
        $this->writeLog($this->getDesc(), $this->getMsg());    

    }

    public function setDesc ()
    {
        switch ($this->getMsgVar('code')) {
            # 1xxx - 2000 for librarian activities
            # 1001 - 1100 for librarian activities directly related to system activities
            case '1001': # Librarian Login
                $this->desc =  $this->getMsgVar('username').' ('.$this->getMsgVar('role').') succeeded to login to application from address '.$this->getMsgVar('from').' at '.$this->getMsgVar('when');
            break;
            case '1003': # Librarian Logout
                $this->desc =  $this->getMsgVar('username').' ('.$this->getMsgVar('role').') succeeded to logout from application from address '.$this->getMsgVar('from').' at '.$this->getMsgVar('when');
            break;
            # 1101 - 1150 for librarian activities directly related to cross related modul
            case '1101': # Access main interface of each module
                $this->desc =  $this->getMsgVar('username').' ('.$this->getMsgVar('role').') accessed '.$this->getMsgVar('module').' module from address '.$this->getMsgVar('from').' at '.$this->getMsgVar('when');
            break;
            # 1151 - 1300 for librarian activities directly related to bibliography module activities
            case '1151': # List bibliography record
                $this->desc =  $this->getMsgVar('username').' ('.$this->getMsgVar('role').') list bibliography records from address '.$this->getMsgVar('from').' at '.$this->getMsgVar('when');
            break;
            case '1153': # show form for creating new bibliography
                $this->desc =  $this->getMsgVar('username').' ('.$this->getMsgVar('role').') show form for creating new bibliography record from address '.$this->getMsgVar('from').' at '.$this->getMsgVar('when');
            break;
            case '1155': # view bibliography record detail
                $this->desc =  $this->getMsgVar('username').' ('.$this->getMsgVar('role').') view bibliography record detail with biblio_id '.$this->getMsgVar('biblio_id').' from address '.$this->getMsgVar('from').' at '.$this->getMsgVar('when');
            break;
        }
    }

}