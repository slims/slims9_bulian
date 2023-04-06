<?php
/**
 * SLiMS Storage disk configuration
 * 
 * Disk provider
 * - \SLiMS\Filesystems\Providers\Local - default,
 * 
 * You can use your own provider adapter with type it into "provider" field,
 */
return [
    'disks' => [
        
        /**
         * Files Disk
         * 
         * store all static files such as report, backup, cache
         * etc.
         */
        'files' => [
            'provider' => \SLiMS\Filesystems\Providers\Local::class,
            'options' => [
                'root' => SB . 'files/'
            ]
        ],

        /**
         * Images disk
         * 
         * store image file such as barcode, person, doc etc
         */
        'images' => [
            'provider' => \SLiMS\Filesystems\Providers\Local::class,
            'options' => [
                'root' => SB . 'images/'
            ]
        ],

        /**
         * Repository disk
         * 
         * store attachment file such as pdf, docx, etc.
         */
        'repository' => [
            'provider' => \SLiMS\Filesystems\Providers\Local::class,
            'options' => [
                'root' => SB . 'repository/'
            ]
        ],

        /**
         * Plugin disk
         * 
         * store plugin file such as pdf, docx, etc.
         */
        'plugin' => [
            'provider' => \SLiMS\Filesystems\Providers\Local::class,
            'options' => [
                'root' => SB . 'plugins/'
            ]
        ],
    ]
];