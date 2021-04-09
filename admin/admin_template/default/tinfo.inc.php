<?php

$sysconf['admin_template']['option'][$sysconf['admin_template']['theme']] = [
    'color' => [
        'dbfield' => 'default_color',
        'label' => __('Color Theme.<br><small>( Default : #004db6 )</small>'),
        'type' => 'text',
        'default' => $sysconf['admin_template']['default_color']??'#004db6',
        'class' => 'colorpicker'
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
