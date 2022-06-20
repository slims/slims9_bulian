<?php

//image background
$extensions= array("jpeg","jpg","png","svg","gif");
$img_path = UPLOAD.'membercard'.DS.$sysconf['print'][$theme_key]['template'].DS.'images';
$list_file = scandir($img_path);
foreach ($list_file as $key => $value) {
	if(@in_array(strtolower(end(explode('.',$value))),$extensions)){
		$img[] = array((string)$value,$value);
	}
}

$sysconf['print'][$theme_key] = [
  'template' => [
    'dbfield' => 'template',
    'label' => 'Template',
    'type' => 'hidden',
    'default' => 'classic'
  ],
    'front_header1_text' => [
    'dbfield' => 'data[front_header1_text]',
    'label' => __('Front Header1 Text'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['front_header1_text'],
    'width' => '100'
  ],
    'front_header2_text' => [
    'dbfield' => 'data[front_header2_text]',
    'label' => __('Front Header2 Text'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['front_header2_text'],
    'width' => '100'
  ],
    'back_header1_text' => [
    'dbfield' => 'data[back_header1_text]',
    'label' => __('Back Header1 Text'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['back_header1_text'],
    'width' => '100'
  ],
    'back_header2_text' => [
    'dbfield' => 'data[back_header2_text]',
    'label' => __('Back Header2 Text'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['back_header2_text'],
    'width' => '100'
  ],
    'box_width' => [
    'dbfield' => 'data[box_width]',
    'label' => __('Box Width'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['box_width'],
    'width' => '20'
  ],
    'box_height' => [
    'dbfield' => 'data[box_height]',
    'label' => __('Box Height'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['box_height'],
    'width' => '20'
  ],
    'factor' => [
    'dbfield' => 'data[factor]',
    'label' => __('Factor'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['factor'],
    'width' => '50'
  ],
    'barcode_scale' => [
    'dbfield' => 'data[barcode_scale]',
    'label' => __('Barcode Scale'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['barcode_scale'],
    'width' => '20'
  ],
    'rules' => [
    'dbfield' => 'data[rules]',
    'label' => __('Rules'),
    'type' => 'ckeditor',
    'default' => $sysconf['print'][$theme_key]['rules'],
  ],
    'f_color' => [
    'dbfield' => 'data[f_color]',
    'label' => __('Font Color'),
    'type' => 'text',
    'width' => '20',
    'default' => $sysconf['print'][$theme_key]['f_color']??'#000000',
    'class' => 'colorpicker'
  ],
  'fr_color' => [
    'dbfield' => 'data[fr_color]',
    'label' => __('Card Front Color'),
    'type' => 'text',
    'width' => '20',
    'default' => $sysconf['print'][$theme_key]['b_color']??'#E5E5E5',
    'class' => 'colorpicker'
  ],
  'b_color' => [
    'dbfield' => 'data[b_color]',
    'label' => __('Card Back Color'),
    'type' => 'text',
    'width' => '20',
    'default' => $sysconf['print'][$theme_key]['b_color']??'#ffffff',
    'class' => 'colorpicker'
  ],
    'city' => [
    'dbfield' => 'data[city]',
    'label' => __('City'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['city']
  ],
    'title' => [
    'dbfield' => 'data[title]',
    'label' => __('Title'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['title']
  ],
    'officials' => [
    'dbfield' => 'data[officials]',
    'label' => __('Officials'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['officials']
  ],
    'officials_id' => [
    'dbfield' => 'data[officials_id]',
    'label' => __('Officials ID'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['officials_id']
  ], 
  'back_side_image' => [
    'dbfield' => 'data[back_side_image]',
    'label' => __('Back Side Image'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['back_side_image'],
    'width' => '40',
    'data' => json_decode(json_encode($img),true)
  ]
];
