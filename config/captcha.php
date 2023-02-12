<?php

return [
    'default' => 'recaptcha',
    'sections' => [
        'librarian' => ['active' => false],
        'memberarea' => ['active' => false],
        'forgot' => ['active' => true],
    ],
    'providers' => [
        'recaptcha' => [
            'publickey' => '6LdCzFAUAAAAAKV0pEX3h3523MZA5ATRZf2GpgQC',
            'privatekey' => '6LdCzFAUAAAAABb8kVMaf97GiQFP9lfX56BPhhGs',
            'class' => \SLiMS\Captcha\Providers\ReCaptcha::class
        ]
    ]
];