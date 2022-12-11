<?php
return [

    /**
     * default cache provider
     */
    'default' => 'Files',

    /**
     * Supported providers.
     * 
     * You can create yours, read the contract to create it
     */
    'providers' => [
        'Files' => [
            'class' => \SLiMS\Cache\Providers\Files::class,
            'options' => [
                'directory' => SB . 'files/cache/'
            ]
        ],
        /*'Redis' => [
            'class' => <another-cache-provider-namespace>
            'options' => [
                'prefix' => 'SLiMSCache:'
            ]
        ]*/
    ]
];