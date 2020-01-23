<?php

$sysconf['admin_template']['default_color'] = '#004db6';

$sysconf['admin_template']['option'][$sysconf['admin_template']['theme']] = [
  'color' => [
    'dbfield' => 'default_color',
    'label' => 'Color Theme',
    'type' => 'text',
    'default' => $sysconf['admin_template']['default_color']
  ]
];