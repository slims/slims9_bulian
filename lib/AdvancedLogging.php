<?php
namespace SLiMS;
/**
 * Advanced logging class using monolog class
 * Copyright (C) 2020  Hendro Wicaksono (hendrowicaksono@gmail.com)
 * This program is free software;
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ElasticSearchHandler;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\ElasticsearchFormatter;
use Elasticsearch\Common\Exceptions\RuntimeException as ElasticsearchRuntimeException;
use Elasticsearch\Client;
use Ramsey\Uuid\Uuid;

class AdvancedLogging
{
    protected $msg = array();
    protected $desc;
    private $member_actions = array ('login', 'logout');
    private $allowed_status = array (0, 1);

    public function writeLog ($desc, $msg)
    {
        global $sysconf;
        if ($sysconf['log']['adv']['enabled']) {
            $log = new Logger('system');
            if ($sysconf['log']['adv']['handler'] == 'fs') {
                $log->pushHandler(new \Monolog\Handler\StreamHandler($sysconf['log']['adv']['path'].'/system.log', Logger::DEBUG));
            } elseif ($sysconf['log']['adv']['handler'] == 'es') {
                $client = \Elasticsearch\ClientBuilder::create()
                    ->setHosts(array($sysconf['log']['adv']['host']))
                    ->build();        
                $options = array(
                    'index' => $sysconf['log']['adv']['index'],
                    'type'  => '_doc',
                );
                $handler = new \Monolog\Handler\ElasticsearchHandler($client, $options);
                $log->pushHandler($handler);
            }
            $log->info($this->getDesc(), $this->getMsg());
        }
    }

    public function setChannel ($channel)
    {
        $this->msg['channel'] = $channel;
    }

    public function setRole ($role)
    {
        $this->msg['role'] = $role;
    }

    public function setCode ($code)
    {
        $this->msg['code'] = $code;
    }

    public function setUuid ()
    {
        $uuid = Uuid::uuid4();
        $this->msg['uuid'] =  $uuid->toString();
    }

    public function setUid ($uid)
    {
        $this->msg['uid'] = $uid;
    }

    public function setRealname ($realname)
    {
        $this->msg['realname'] = $realname;
    }

    public function setModule ($module)
    {
        $this->msg['module'] = $module;
    }

    public function setModuleAction ($module_action)
    {
        $this->msg['module_action'] = $module_action;
    }

    public function setWhen ()
    {
        $this->msg['when'] =  date('Y-m-d H:i:s');
    }

    public function setFrom ()
    {
        $this->msg['from'] =  $_SERVER['REMOTE_ADDR'];
    }

    public function getMsg ()
    {
        return $this->msg;
    }

    public function getMsgVar ($key)
    {
        return $this->msg[$key];
    }

    public function setDesc ()
    {
    }
    public function getDesc ()
    {
        return $this->desc;
    }
}