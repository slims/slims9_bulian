<?php
/**
 * SLiMS Storage disk configuration
 * 
 * Disk provider
 * - \SLiMS\Filesystems\Providers\Local - default,
 * - \SLiMS\Filesystems\Providers\Sftp - optional,
 * 
 * You can use your provider adapter with type it in "provider" field,
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
         * An example disk profile with Sftp provider
         * 
         * store file into other server over sftp connection.
         */
        //  'repository' => [
        //     'provider' => \SLiMS\Filesystems\Providers\Sftp::class,
        //     'options' => [
        //         [
        //             /** 
        //              * Hostname or ip address of Sftp server (required)
        //              */
        //             'host', 
        
        //             /** 
        //              * Username (required)
        //              */
        //             'foo',
        
        //             /** 
        //              * password (optional, default: null) set to null if privateKey is used
        //              */
        //             'lan',
        
        //             /** 
        //              * private key (optional, default: null) can be used instead of password, set to null if password is set
        //              */
        //             null,
        
        //             /** 
        //              * passphrase (optional, default: null), set to null if privateKey is not used or has no passphrase
        //              */
        //             null,
        
        //             /** 
        //              * port (optional, default: 22)
        //              */
        //             22,
        
        //             /** 
        //              * use agent (optional, default: false)
        //              */
        //             false,
        
        //             /** 
        //              * timeout (optional, default: 10)
        //              */
        //             30,
        
        //             /** 
        //              * max tries (optional, default: 4)
        //              */
        //             10,
        
        //             /** 
        //              * host fingerprint (optional, default: null)
        //              */
        //             null, 
        
        //             /** 
        //              * connectivity checker (must be an implementation of 'League\Flysystem\PhpseclibV2\ConnectivityChecker'
        //              * to check if a connection can be established (optional, 
        //              * omit if you don't need some special handling for setting reliable connections)
        //              */
        //             null, 
        //         ],

        //        /**
        //         * Path in server
        //         */
        //         '/home/tamu/',
        //        /**
        //         * Unix permission style
        //         */
        //        [
        //            'file' => [
        //                'public' => 0640,
        //                'private' => 0604,
        //            ],
        //            'dir' => [
        //                'public' => 0755,
        //                'private' => 0755,
        //            ],
        //        ]
        //    ]
        // ]
    ]
];