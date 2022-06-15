<?php
//image background
$extensions= array("jpeg","jpg","png","svg","gif");
$img_path = UPLOAD.'membercard'.DS.$sysconf['print'][$theme_key]['template'];
$list_file = scandir($img_path);
foreach ($list_file as $key => $value) {
    if(@in_array(strtolower(end(explode('.',$value))),$extensions)){
        $img[] = array((string)$value,$value);
    }
}
//font size
for ($i = 5; $i <= 16; $i++) {
    $font_size[] = array((string)$i,$i);
} 

$sysconf['print'][$theme_key] = [
  'template' => [
    'dbfield' => 'template',
    'label' => 'Template',
    'type' => 'hidden',
    'default' => 'old'
  ],
    'front_header1_text' => [
    'dbfield' => 'data[front_header1_text]',
    'label' => __('Front Header1 Text'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['front_header1_text'],
    'width' => '100'
  ],  
  'front_header1_font_size' => [
    'dbfield' => 'data[front_header1_font_size]',
    'label' => __('Front Header1 Font Size'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['front_header1_font_size'],
    'width' => '20',
    'data' => json_decode(json_encode($font_size),true)
  ],
    'front_header2_text' => [
    'dbfield' => 'data[front_header2_text]',
    'label' => __('Front Header2 Text'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['front_header2_text'],
    'width' => '100'
  ],  
  'front_header2_font_size' => [
    'dbfield' => 'data[front_header2_font_size]',
    'label' => __('Front Header2 Font Size'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['front_header2_font_size'],
    'width' => '20',
    'data' => json_decode(json_encode($font_size),true)
  ],
    'back_header1_text' => [
    'dbfield' => 'data[back_header1_text]',
    'label' => __('Back Header1 Text'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['back_header1_text'],
    'width' => '100'
  ],  
  'back_header1_font_size' => [
    'dbfield' => 'data[back_header1_font_size]',
    'label' => __('Back Header1 Font Size'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['back_header1_font_size'],
    'width' => '20',
    'data' => json_decode(json_encode($font_size),true)
  ],
    'back_header2_text' => [
    'dbfield' => 'data[back_header2_text]',
    'label' => __('Back Header2 Text'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['back_header2_text'],
    'width' => '100'
  ],  
  'back_header2_font_size' => [
    'dbfield' => 'data[back_header2_font_size]',
    'label' => __('Back Header2 Font Size'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['back_header2_font_size'],
    'width' => '20',
    'data' => json_decode(json_encode($font_size),true)
  ],
    'header_color' => [
    'dbfield' => 'data[header_color]',
    'label' => __('Header Color'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['header_color'],
    'width' => '20',
    'class' => 'colorpicker'
  ],
    'rules' => [
    'dbfield' => 'data[rules]',
    'label' => __('Rules'),
    'type' => 'ckeditor',
    'default' => $sysconf['print'][$theme_key]['rules'],
  ],  
  'rules_font_size' => [
    'dbfield' => 'data[rules_font_size]',
    'label' => __('Rules Font Size'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['rules_font_size'],
    'width' => '20',
    'data' => json_decode(json_encode($font_size),true)
  ],
    'address' => [
    'dbfield' => 'data[address]',
    'label' => __('Address'),
    'type' => 'ckeditor',
    'default' => $sysconf['print'][$theme_key]['address'],
  ],  
  'address_font_size' => [
    'dbfield' => 'data[address_font_size]',
    'label' => __('Address Font Size'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['address_font_size'],
    'width' => '20',
    'data' => json_decode(json_encode($font_size),true)
  ],
    'box_width' => [
    'dbfield' => 'data[box_width]',
    'label' => __('Box Width'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['box_width']
  ],
    'box_height' => [
    'dbfield' => 'data[box_height]',
    'label' => __('Box Height'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['box_height']
  ],
    'factor' => [
    'dbfield' => 'data[factor]',
    'label' => __('Factor'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['factor']
  ],
    'barcode_scale' => [
    'dbfield' => 'data[barcode_scale]',
    'label' => __('Barcode Scale'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['barcode_scale']
  ],
    'city' => [
    'dbfield' => 'data[city]',
    'label' => __('City'),
    'type' => 'text',
    'default' => $sysconf['print'][$theme_key]['city']
  ],
    'title' => [
    'dbfield' => 'data[title]',
    'label' => __('Job Title'),
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
  'include_id_label' => [
    'dbfield' => 'data[include_id_label]',
    'label' => 'Include ID Label',
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['include_id_label'],
    'width' => '40',
    'data' => [
      [1, 'Show'],
      [0, 'Hide']
    ]
  ],  
  'include_name_label' => [
    'dbfield' => 'data[include_name_label]',
    'label' => 'Include Name Label',
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['include_name_label'],
    'width' => '40',
    'data' => [
      [1, 'Show'],
      [0, 'Hide']
    ]
  ],  
  'include_pin_label' => [
    'dbfield' => 'data[include_pin_label]',
    'label' => 'Include PIN Label',
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['include_pin_label'],
    'width' => '40',
    'data' => [
      [1, 'Show'],
      [0, 'Hide']
    ]
  ],  
  'include_inst_label' => [
    'dbfield' => 'data[include_inst_label]',
    'label' => 'Include Institution Label',
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['include_inst_label'],
    'width' => '40',
    'data' => [
      [1, 'Show'],
      [0, 'Hide']
    ]
  ],  
  'include_email_label' => [
    'dbfield' => 'data[include_email_label]',
    'label' => 'Include Email Label',
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['include_email_label'],
    'width' => '40',
    'data' => [
      [1, 'Show'],
      [0, 'Hide']
    ]
  ],  
  'include_address_label' => [
    'dbfield' => 'data[include_address_label]',
    'label' => 'Include Address Label',
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['include_name_label'],
    'width' => '40',
    'data' => [
      [1, 'Show'],
      [0, 'Hide']
    ]
  ],  
  'include_barcode_label' => [
    'dbfield' => 'data[include_barcode_label]',
    'label' => 'Include Barcode Label',
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['include_barcode_label'],
    'width' => '40',
    'data' => [
      [1, 'Show'],
      [0, 'Hide']
    ]
  ],  
  'include_expired_label' => [
    'dbfield' => 'data[include_expired_label]',
    'label' => 'Include Expired Label',
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['include_expired_label'],
    'width' => '40',
    'data' => [
      [1, 'Show'],
      [0, 'Hide']
    ]
  ],  
  'front_side_image' => [
    'dbfield' => 'data[front_side_image]',
    'label' => __('Front Side Image'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['front_side_image'],
    'width' => '40',
    'data' => json_decode(json_encode($img),true)
  ],  
  'back_side_image' => [
    'dbfield' => 'data[back_side_image]',
    'label' => __('Back Side Image'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['back_side_image'],
    'width' => '40',
    'data' => json_decode(json_encode($img),true)
  ],  
  'stamp_file' => [
    'dbfield' => 'data[stamp_file]',
    'label' => __('Stamp file'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['stamp_file'],
    'width' => '40',
    'data' => json_decode(json_encode($img),true)
  ],  
  'signature_file' => [
    'dbfield' => 'data[signature_file]',
    'label' => __('Signature_File'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['signature_file'],
    'width' => '40',
    'data' => json_decode(json_encode($img),true)
  ],  
  'logo' => [
    'dbfield' => 'data[logo]',
    'label' => __('Logo'),
    'type' => 'dropdown',
    'default' => $sysconf['print'][$theme_key]['logo'],
    'width' => '40',
    'data' => json_decode(json_encode($img),true)
  ]
];
