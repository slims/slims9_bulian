<?php
// Main Setup
$sysconf['template']['base'] 					= 'php';
$sysconf['template']['responsive']  			= true;

// Please use at your own risk.
// Animation need big memories. Please adjust with your computer capability
// ========================================================================

// Run gradient animation - you may need a big memory to run it well.
$sysconf['template']['run_gradient_animation']  = false; // true or false
$sysconf['template']['run_animation']  = false; // true or false

// Choose gradient color
// Available color:  red, blue(default) , green, beach, mint, purple, pink
$sysconf['template']['default_gradient']		= 'blue';

// Show video or image for the background.
$sysconf['template']['background_mode']  		= 'image'; // video, image or none



// option
$sysconf['template']['option'][$sysconf['template']['theme']] = array(
  'option_1' => array(
		'dbfield' => 'run_gradient_animation',
		'label' => 'Run gradient animation',
		'type' => 'dropdown',
		'default' => 0, // 0 to false; 1 to true
		'data' => array(
			array(0, 'Disable'),
			array(1, 'Enable')
			)
  ),
  'option_2' => array(
		'dbfield' => 'run_animation',
		'label' => 'Run animation',
		'type' => 'dropdown',
		'default' => 0, // 0 to false; 1 to true
		'data' => array(
			array(0, 'Disable'),
			array(1, 'Enable')
			)
  ),
  'option_3' => array(
		'dbfield' => 'default_gradient',
		'label' => 'Gradient color',
		'type' => 'dropdown',
		'default' => 'blue',
		'data' => array(
			array('blue', 'Blue'),
			array('red', 'Red'),
      array('green', 'Green'),
      array('beach', 'Beach'),
      array('mint', 'Mint'),
      array('purple', 'Purple'),
      array('pink', 'Pink')
			)
  ),
  'option_4' => array(
		'dbfield' => 'background_mode',
		'label' => 'Background mode',
		'type' => 'dropdown',
		'default' => 'image',
		'data' => array(
			array('none', 'none'),
			array('image', 'Image'),
      array('video', 'Video')
  ))
);
