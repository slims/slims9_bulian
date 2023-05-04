<?php
return [

    /**
     * default cache provider
     */
    'default' => 'Database',

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
        'Database' => [
            'class' => \SLiMS\Cache\Providers\Database::class,
            'options' => [
                'expire' => [
                    'time' => 60, // in seconds
                    'diffIn' => 'minutes', // seconds, minutes, hours
                    'criteria' => ['>=', 1] // based on diffIn option
                ]
            ]
        ]
        /*'Redis' => [
            'class' => <another-cache-provider-namespace>
            'options' => [
                'prefix' => 'SLiMSCache:'
            ]
        ]*/
    ]
];