<?php
return [

    /**
     * default cache provider
     */
    'default' => 'Files',

    /**
     * Supported providers
     * 
     * 1. Files
     * 2. Redis
     */
    'providers' => [
        'Files' => [
            'class' => \SLiMS\Cache\Providers\Files::class,
            'options' => [
                'directory' => SB . 'files/cache/'
            ]
        ],
        'Redis' => [
            'class' => \SLiMS\Cache\Providers\Redis::class,
            'options' => [
                'prefix' => 'SLiMSCache:'
            ]
        ]
    ]
];