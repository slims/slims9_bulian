<?php
return [
    /**
     * Default auth methods
     * - native : retrive user data from database
     * - ldap : retrieve user data from LDAP server (3rd party integration)
     * 
     * Feel free to add your authentication method. 
     * Read our method in lib/Auth to build your own.
     */
    'methods' => [
        'native' => \SLiMS\Auth\Methods\Native::class,
        'ldap' => \SLiMS\Auth\Methods\Ldap::class,
    ],

    /**
     * Define your method here.
     * - user : librarian login
     * - member : memberarea login, for student, staff etc
     */
    'sections' => [
        'user' => 'native',
        'member' => 'native'
    ],

    /**
     * Optional area for some method
     */
    'options' => [
        'ldap' => [
            'server' => '127.0.0.1',
            'base_dn' => 'dc=diknas,dc=go,dc=id',
            'suffix' => '',
            'port' => null,
            'options' => [
                defined('LDAP_OPT_PROTOCOL_VERSION') ? [LDAP_OPT_PROTOCOL_VERSION, 3] : [],
                defined('LDAP_OPT_REFERRALS') ? [LDAP_OPT_REFERRALS, 0] : []
            ],
            'user' => [
                'ou' => 'librarian',
                'bind_dn' => 'uid=#loginUserName,{ou},{base_dn}',
                'search_filter' => '(|(uid=#loginUserName)(cn=#loginUserName*))',
                'userid_field' => 'uid',
                'fullname_field' => 'displayname',
                'mail_field' => 'mail'
            ],
            'member' => [
                'ou' => 'member',
                'bind_dn' => 'uid=#loginUserName,{ou},{base_dn}',
                'search_filter' => '(|(uid=#loginUserName)(cn=#loginUserName*))',
                'userid_field' => 'uid',
                'fullname_field' => 'displayname',
                'mail_field' => 'mail'
            ]
        ]
    ]
];