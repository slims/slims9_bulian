<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2020-01-02 15:12
 * @File name           : tinfo.inc.php
 */

$sysconf['template']['base'] = 'php';
$sysconf['template']['responsive'] = false;

$sysconf['template']['classic_library_subname'] = 0;
$sysconf['template']['classic_slide_transition'] = 'blur';
$sysconf['template']['classic_slide_animation'] = 'none';
$sysconf['template']['classic_slide_delay'] = 5000;
$sysconf['template']['classic_popular_collection'] = 1;
$sysconf['template']['classic_popular_collection_item'] = 6;
$sysconf['template']['classic_new_collection'] = 1;
$sysconf['template']['classic_new_collection_item'] = 6;
$sysconf['template']['classic_top_reader'] = 1;
$sysconf['template']['classic_suggestion'] = 1;
$sysconf['template']['classic_map'] = 1;
$sysconf['template']['classic_map_link'] = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.288723306273!2d106.80038831428296!3d-6.225610995493402!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f14efd9abf05%3A0x1659580cc6981749!2sPerpustakaan+Kemendikbud!5e0!3m2!1sid!2sid!4v1516601731218';
$sysconf['template']['classic_map_desc'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque et nunc mi. Donec vehicula turpis a quam venenatis posuere. Aliquam nibh lectus, gravida et leo sit amet, dignissim dapibus mauris.<br>Telp. (021) 9172638<br>Fax. (021) 9172638<br>';
$sysconf['template']['classic_fb_link'] = 'https://www.facebook.com/groups/senayan.slims';
$sysconf['template']['classic_twitter_link'] = 'https://twitter.com/slims_official';
$sysconf['template']['classic_youtube_link'] = 'https://youtube.com';
$sysconf['template']['classic_instagram_link'] = 'https://instagram.com/slims.sdc';
$sysconf['template']['visitor_log_voice'] = 1;
$sysconf['template']['classic_footer_about_us'] = <<<HTML
<p>As a complete Library Management System, SLiMS (Senayan Library Management System) has many features that will help libraries and librarians to do their job easily 
and quickly. Follow <a target="_blank" href="https://slims.web.id/web/pages/about/">this link</a> to show some features provided by SLiMS.</p>
HTML;


$sysconf['template']['option'][$sysconf['template']['theme']] = [
    'responsive' => [
        'dbfield' => 'responsive',
        'label' => __('Enable this theme for mobile?'),
        'type' => 'dropdown',
        'default' => 0,
        'data' => [
            [1, __('Yes, please!')],
            [0, __('No, I want use lighweight theme')]
        ]
    ],
    'subtitle' => [
        'dbfield' => 'classic_library_subname',
        'label' => __('Library Sub Name'),
        'type' => 'dropdown',
        'default' => 0,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'transition' => [
        'dbfield' => 'classic_slide_transition',
        'label' => __('Slide Transition'),
        'type' => 'dropdown',
        'default' => 'fade',
        'data' => [
            ['fade', __('Fade')],
            ['slideLeft', __('Slide Left')],
            ['slideRight', __('Slide Right')],
            ['slideUp', __('Slide Up')],
            ['slideDown', __('Slide Down')],
            ['zoomIn', __('Zoom In')],
            ['burn', __('Burn')],
            ['flash', __('Flash')]
        ]
    ],
    'animation' => [
        'dbfield' => 'classic_slide_animation',
        'label' => __('Slide Animation'),
        'type' => 'dropdown',
        'default' => 'none',
        'data' => [
            ['none', __('None')],
            ['random', 'Random'],
            ['kenburns', 'Kenburns'],
            ['kenburnsUp', 'Kenburns Up'],
            ['kenburnsDown', 'Kenburns Down'],
            ['kenburnsLeft', 'Kenburns Left'],
            ['kenburnsRight', 'Kenburns Right'],
            ['kenburnsUpLeft', 'Kenburns Up-Left'],
            ['kenburnsUpRight', 'Kenburns Up-Right'],
            ['kenburnsDownLeft', 'Kenburns Down-Left'],
            ['kenburnsDownRight', 'Kenburns Down-Right'],
        ]
    ],
    'delay' => [
        'dbfield' => 'classic_slide_delay',
        'label' => __('Delay'),
        'type' => 'text',
        'default' => 5000
    ],
    'popular-collection' => [
        'dbfield' => 'classic_popular_collection',
        'label' => __('Popular Collection'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'popular-collection-item' => [
        'dbfield' => 'classic_popular_collection_item',
        'label' => __('Popular Items'),
        'type' => 'text',
        'default' => 6
    ],
    'new-collection' => [
        'dbfield' => 'classic_new_collection',
        'label' => __('New Collection'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'new-collection-item' => [
        'dbfield' => 'classic_new_collection_item',
        'label' => __('New Items'),
        'type' => 'text',
        'default' => 6
    ],
    'top-reader' => [
        'dbfield' => 'classic_top_reader',
        'label' => __('Top Reader'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'suggestion' => [
        'dbfield' => 'classic_suggestion',
        'label' => __('Suggestion'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'map' => [
        'dbfield' => 'classic_map',
        'label' => __('Map'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'map-link' => [
        'dbfield' => 'classic_map_link',
        'label' => __('Map URL'),
        'type' => 'longtext',
        'default' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.288723306273!2d106.80038831428296!3d-6.225610995493402!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f14efd9abf05%3A0x1659580cc6981749!2sPerpustakaan+Kemendikbud!5e0!3m2!1sid!2sid!4v1516601731218',
        'width' => '100',
        'max' => 1000
    ],
    'map-desc' => [
        'dbfield' => 'classic_map_desc',
        'label' => __('Map Description'),
        'type' => 'longtext',
        'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque et nunc mi. Donec vehicula turpis a quam venenatis posuere. Aliquam nibh lectus, gravida et leo sit amet, dignissim dapibus mauris.<br>Telp. (021) 9172638<br>Fax. (021) 9172638<br>',
        'width' => '100',
        'class' => 'ckeditor',
        'max' => 1000
    ],
    'fb-link' => [
        'dbfield' => 'classic_fb_link',
        'label' => __('Facebook URL'),
        'type' => 'longtext',
        'default' => 'https://www.facebook.com/groups/senayan.slims',
        'width' => '100',
        'max' => 1000
    ],
    'twitter-link' => [
        'dbfield' => 'classic_twitter_link',
        'label' => __('Twitter URL'),
        'type' => 'longtext',
        'default' => 'https://twitter.com/slims_official',
        'width' => '100',
        'max' => 1000
    ],
    'youtube-link' => [
        'dbfield' => 'classic_youtube_link',
        'label' => __('Youtube URL'),
        'type' => 'longtext',
        'default' => 'https://youtube.com',
        'width' => '100',
        'max' => 1000
    ],
    'instagram-link' => [
        'dbfield' => 'classic_instagram_link',
        'label' => __('Instagram URL'),
        'type' => 'longtext',
        'default' => 'https://www.instagram.com/slims.sdc',
        'width' => '100',
        'max' => 1000
    ],
    'footer_about_us' => [
        'dbfield' => 'classic_footer_about_us',
        'label' => __('Footer About Us'),
        'type' => 'longtext',
        'default' => '<p>As a complete Library Management System, SLiMS (Senayan Library Management System) has many features that will help libraries and librarians to do their job easily and quickly. Follow <a target="_blank" href="https://slims.web.id/web/pages/about/">this link</a> to show some features provided by SLiMS.</p>',
        'width' => '100',
        'class' => 'ckeditor',
        'max' => 1000
    ],
    'visitor_voice' => [
        'dbfield' => 'visitor_log_voice',
        'label' => __('Visitor log voice'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Enable')],
            [0, __('Disable')]
        ]
    ],
];
