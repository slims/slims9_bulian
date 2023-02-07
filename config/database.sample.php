<?php

return [
    /**
     * Default database node profile
     */
    'default_profile' => 'SLiMS',

    /**
     * SLiMS as Service, One SLiMS for many library
     * ----------------------------------------------------
     * 
     * Switching database node access based on rule,
     * such as domain, ip, port etc
     * 
     * How to :
     * 1. make file with name database_proxy.php in config/
     * 2. make your own rule in that file.
     * 3. change this value to true
     */
    'proxy' => false,

    /**
     * Nodes profile
     */
    'nodes' => [
        'SLiMS' => [
            'host' => '_DB_HOST_',
            'database' => '_DB_NAME_',
            'port' => '_DB_PORT_',
            'username' => '_DB_USER_',
            'password' => '_DB_PASSWORD_',
            'options' => [
                'storage_engine' => '_STORAGE_ENGINE_'
            ]
        ]
    ]
];