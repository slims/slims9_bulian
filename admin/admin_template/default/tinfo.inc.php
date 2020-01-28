<?php

$sysconf['admin_template']['default_color'] = 'style.css';

$sysconf['admin_template']['option'][$sysconf['admin_template']['theme']] = [
  'mode' => [
    'dbfield' => 'default_color',
    'label' => 'Mode',
    'type' => 'dropdown',
    'default' => 0,
    'data' => [
      ['style.css', 'Default'],
      ['style-night.css', 'Night Mode']
    ]
    ]
];