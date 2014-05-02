<?php
/*------------------------------------------------------------

Template 	: Slims Meranti Mobile Template
Create Date : March 24, 2012
Author  	: Eddy Subratha (eddy.subratha@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA

-------------------------------------------------------------*/
// be sure that this file not accessed directly

if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}
//set default index page
$p = 'home';

if (isset($_GET['p']))
{
 if ($_GET['p'] == 'libinfo') {
  $p = 'libinfo';
 } elseif ($_GET['p'] == 'help') {
  $p = 'help';
 } elseif ($_GET['p'] == 'member') {
  $p = 'member';
 } elseif ($_GET['p'] == 'login') {
  $p = 'login';
 }
}

/*----------------------------------------------------
  menu list
  you may modified as you need
----------------------------------------------------*/
$menus = array (
  'home'   => array('url'  => 'index.php',
        'text' => __('Home')
       ),
  'libinfo'  => array('url'  => 'index.php?p=libinfo',
        'text' => __('Library Information')
       ),
  'help'   => array('url'  => 'index.php?p=help',
        'text' => __('Help on Search')
       ),
  'member'   => array('url'  => 'index.php?p=member',
        'text' => __('Member Area')
       )
);

/*----------------------------------------------------
  social button
  you may modified as you need.
----------------------------------------------------*/
$social = array (
  'facebook'  => array(	'url'  => 'http://www.facebook.com/groups/senayan.slims/',
        				'text' => 'Facebook'
       ),
  'twitter'  => array(	'url'  => 'http://twitter.com/#!/slims_official',
        				'text' => 'Twitter'
       ),
  'youtube'  => array(	'url'  => 'http://www.youtube.com/user/senayanslims',
        				'text' => 'Youtube'
       ),
  'gihub'  => array(	'url'  => 'https://github.com/slims/',
        				'text' => 'Github'
       ),
  'forum'  => array(	'url'  => 'http://slims.web.id/forum/',
        				'text' => 'Forum'
       )
  );

?>
<!DOCTYPE html>
<html><head><title><?php echo $page_title; ?></title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" /> 
<link rel="icon" href="webicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="webicon.ico" type="image/x-icon" />
<link href="<?php echo $sysconf['template']['css']; ?>" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.js"></script>
<?php echo $metadata; ?>
<body>
	<div id="content">
		<div id="header">
			<div class="treding">
				<ul class="social">
				    <?php foreach ($menus as $path => $menu) { ?>
				    <li><a href="<?php echo $menu['url']; ?>" title="<?php echo $menu['text']; ?>" <?php if ($p == $path) {echo ' class="active"';} ?>><?php echo $menu['text']; ?></a></li>
				    <?php } ?>
				</ul>
			</div>	
			<div class="title">
				<div class="sitename"><a href="index.php" title="Home"><?php echo $sysconf['library_name']; ?></a></div>
			</div>
			<div class="tools">
			    <form name="simpleSearch" action="index.php" method="get">
			    <input type="hidden" name="search" value="Search" /> 
			    <input type="text" name="keywords" id="keyword" class="keywords"  placeholder="<?php echo __('Keyword'); ?>" x-webkit-speech="x-webkit-speech" />
			    <input type="submit" name="search" value="<?php echo __('Search'); ?>" class="search"/>
			    </form>			    
			</div>
		</div>
		<div id="section">
		    <?php if($header_info != '') {?>
	    	<div class="subinfo"><?php echo $header_info; ?></div>		    	
		    <?php } else { ?>
		    <div class="subinfo">
		    <?php echo $info; ?>
		    </div>		    
		    <?php } ?>
		    <?php if(isset($_GET['p']) || isset($_GET['search'])) : ?>
		    <div class="content"><?php echo $main_content; ?></div>
		    <?php endif; ?>
		</div>
		<div id="footer">
			<div class="footer_info">
			    This software and this template are released Under GNU GPL License Version 3
			</div>
			<div class="treding">
			    <?php if(isset($social) && count($social) > 0) { ?>
			    <ul class="social">
			    <?php foreach ($social as $path => $menu) { ?>
			     <li><a target="_blank" href="<?php echo $menu['url']; ?>" title="<?php echo $menu['text']; ?>" <?php if ($p == $path) {echo ' class="active"';} ?>><?php echo $menu['text']; ?></a></li>
			    <?php } ?>
			    </ul>
			    <?php } ?>			    
			</div>
			<div class="language">
			    <form name="langSelect" action="index.php" method="get">
			    <select name="select_lang"><?php echo $language_select; ?></select>
			    <input type="submit" name="changeLang" value="Change Language" class="search" />
			    <a href="m/index.php?fullsite=1" class="search" /><?php echo __('Full Site'); ?></a>
			    </form>			    
				
			</div>

		</div>
	</div>
    <script type="text/javascript">
    $('document').ready(function() {
	// Google Voice Search    
	$('#keyword').bind('webkitspeechchange', function() {
	    $(this).parents().submit();
	});                    
    });	
    </script>
</body>
</html>
