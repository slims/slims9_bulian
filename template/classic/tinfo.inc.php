<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-15 11:05:31
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-19 20:41:12
 */


$sysconf['template']['base'] = 'php';
$sysconf['template']['responsive'] = false;

// default value
$sysconf['template']['new-books'] = 6;
$sysconf['template']['promoted-books'] = 10;

// option
$sysconf['template']['option'][$sysconf['template']['theme']] = array(
  'option_1' => array(
		'dbfield' => 'new-books',
		'label' => 'Display number new book',
		'type' => 'text',
		'default' => 6,
    'max' => '50',
    'data' => false,
    'width' => 50
  ),
  'option_2' => array(
		'dbfield' => 'promoted-books',
		'label' => 'Display number promoted book',
		'type' => 'text',
		'default' => 10,
    'max' => '50',
    'data' => false,
    'width' => 50
  )
);
