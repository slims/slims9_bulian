<?php

use SLiMS\Polyglot\Memory;
$options_languages = [];
$languages = Memory::getInstance()->getLanguages();
foreach ($languages as $lang) {
    $flag = Memory::getFlag($lang[0]);
    $displayText = trim($flag . ' ' . $lang[1]);
    $options_languages[] = [$lang[0], $displayText];
}

$sysconf['admin_template']['option'][$sysconf['admin_template']['theme']] = [
    'color' => [
        'dbfield' => 'default_color',
        'label' => __('Color Theme.<br><small>( Default : #004db6 )</small>'),
        'type' => 'text',
        'default' => $sysconf['admin_template']['default_color']??'#004db6',
        'class' => 'colorpicker'
    ],
    'language' => [
        'dbfield' => 'default_lang', 
        'label' => __('User Language'),
        'type' => 'dropdown',
        'default' => $sysconf['admin_template']['default_lang']??$sysconf['default_lang'],
        'data' => $options_languages,
        'class' => 'form-control'
    ],
    'always-show-tracks' => [
        'dbfield' => 'always_show_tracks',
        'label' => __('Always Show Tracks'),
        'type' => 'dropdown',
        'default' => $sysconf['admin_template']['always_show_tracks']??1,
        'data' => [
            [1, __('Yes')],
            [0, __('No')]
        ]
    ],
];