<?php
/*------------------------------------------------------------

Template : Slims Meranti Template
Create Date : March 24, 2012
Author  : Eddy Subratha (eddy.subratha@gmail.com)


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
 } else {
  $p = $_GET['p'];   
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
  'librarian'   => array('url'  => 'index.php?p=librarian',
        'text' => __('Librarian')
       ),
  'member'   => array('url'  => 'index.php?p=member',
        'text' => __('Member Area')
       ),
  'help'   => array('url'  => 'index.php?p=help',
        'text' => __('Help on Search')
       ),
  'login'   => array('url'  => 'index.php?p=login',
        'text' => __('Librarian LOGIN')
       )
);

/*----------------------------------------------------
  social button
  you may modified as you need.
----------------------------------------------------*/
$social = array (
  'facebook'  => array('url'  => 'http://www.facebook.com/groups/senayan.slims/',
        'text' => 'Facebook'
       ),
  'twitter'  => array('url'  => 'http://twitter.com/#!/slims_official',
        'text' => 'Twitter'
       ),
  'youtube'  => array('url'  => 'http://www.youtube.com/user/senayanslims',
        'text' => 'Youtube'
       ),
  'gihub'  => array('url'  => 'https://github.com/slims/',
        'text' => 'Github'
       ),
  'forum'  => array('url'  => 'http://slims.web.id/forum/',
        'text' => 'Forum'
       )
  );

?>
<!DOCTYPE html>
<html><head><title><?php echo $page_title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="SLiMS (Senayan Library Management System) is an open source Library Management System. It is build on Open source technology like PHP and MySQL">
<meta name="keywords" content="senayan,slims,library automation,free library application, library, perpustakaan, aplikasi perpustakaan">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="index, nofollow">
<!-- load style -->
<link rel="shortcut icon" href="webicon.ico" type="image/x-icon" />
<link href="template/core.style.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $sysconf['template']['css']; ?>" rel="stylesheet" type="text/css" />
<!--[if IE]>
<link type="text/css" rel="stylesheet" media="all" href="<?php echo SWB; ?>template/default/ie.css"/>
<![endif]-->
<!--[if IE 6]>
<link type="text/css" rel="stylesheet" media="all" href="<?php echo SWB; ?>template/default/ie6.css"/>
<![endif]-->
<?php echo $metadata; ?>
<script type="text/javascript" src="<?php echo JWB; ?>jquery.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>form.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>gui.js"></script>

</head>

<body>
 <div id="masking"></div>
 <div id="content">
  <div class="topic">
   <div class="container_12">
    <div class="language grid_5">
        <form name="langSelect" action="index.php" method="get">
      <?php echo __('Select Language'); ?>&nbsp;&nbsp;
         <select name="select_lang"  onchange="document.langSelect.submit();">
         <?php echo $language_select; ?>
         </select>
        </form>
    </div>
    <div class="treding grid_7">
     <?php if(isset($social) && count($social) > 0) { ?>
     <ul class="social">
     <?php foreach ($social as $path => $menu) { ?>
      <li><a href="<?php echo $menu['url']; ?>" title="<?php echo $menu['text']; ?>" <?php if ($p == $path) {echo ' class="active"';} ?>><?php echo $menu['text']; ?></a></li>
     <?php } ?>
     </ul>
     <?php } ?>
    </div>
   </div>
  </div>

  <div class="logo">
   <div class="container_12">
    <div class="grid_4 title">
     <div class="sitename"><a href="index.php" title="Home"><?php echo $sysconf['library_name']; ?></a></div>
     <div class="subname"><?php echo $sysconf['library_subname']; ?></div>
    </div>
    <ul class="nav">
     <?php foreach ($menus as $path => $menu) { ?>
      <li><a href="<?php echo $menu['url']; ?>" title="<?php echo $menu['text']; ?>" <?php if ($p == $path) {echo ' class="active"';} ?>><?php echo $menu['text']; ?></a></li>
     <?php } ?>
    </ul>
   </div>
  </div>

  <div class="content">
   <div class="container_12">
    <div class="grid_12 welcome">
     <?php if($_GET['p'] != 'show_detail' && (isset($_GET['search']) || isset($_GET['title']) || isset($_GET['keywords']))) { ?>
     <div class="sidebar">
      <div class="tagline">
       <?php echo __('Information'); ?>
      </div>
      <div class="info">
       <?php echo $info; ?>
      </div>
    <?php if (utility::isMemberLogin()) { ?>
    <div class="info">
	    <?php echo $header_info; ?>
    </div>
    <?php } ?>

      <?php if ($sysconf['enable_search_clustering'] && !isset($_GET['fromcluster'])) { ?>
      <div class="tagline">
       <?php echo __('Search Cluster'); ?>
      </div>
        <div id="search-cluster"><div class="cluster-loading"><?php echo __('Generating search cluster...');  ?></div></div>
        <script type="text/javascript">
        $('document').ready( function() {
         $.ajax({
           url: 'index.php?p=clustering&q=<?php echo isset($_GET['keywords'])?urlencode(trim($_GET['keywords'])):''; ?>',
           type: 'GET',
           success: function(data, status, jqXHR) {
             $('#search-cluster').html(data);
           }
         });
        });
       </script>
      <?php } ?>
     </div>

    <div class="section">
	    <div class="tagline">
		    <?php echo __('Collections'); ?>
		    <a href="javascript: history.back();" class="back to_right"> <?php echo __('Back'); ?> </a>
		    <br/>
	    </div>
	    <div class="search-result-info">
		 <?php echo $search_result_info; ?>
	    </div>
	    <div class="result-search">
		    <div id="simply-search">
			<div class="simply" >
			    <form name="advSearchForm" id="advSearchForm" action="index.php" method="get">
			    <input type="hidden" name="search" value="Search" />
			    <input type="text" name="keywords" id="keyword" placeholder="<?php echo __('Keyword'); ?>" x-webkit-speech="x-webkit-speech" />
			    </form>
			</div>
		    </div>
		    <div id="advance-search" style="display:none;" >
			<form name="advSearchForm" id="advSearchForm" action="index.php" method="get">
		    <input type="hidden" name="search" value="Search" />
			<div class="simply" >
			    <input type="text" name="title" id="title" placeholder="Title" />
			</div>
			<div class="advance">
			<table width="100%">
				<tr>
					<td class="value">
					<?php echo __('Author(s)'); ?>
					</td>
					<td class="value">
					<?php echo $advsearch_author; ?>
					</td>
					<td class="value">
					<?php echo __('Subject(s)'); ?>
					</td>
					<td class="value">
					<?php echo $advsearch_topic; ?>
					</td>
				</tr>
				<tr>
					<td class="value">
					<?php echo __('ISBN/ISSN'); ?>
					</td>
					<td class="value">
						<input type="text" name="isbn" />
					</td>
					<td class="value">
						<?php echo __('GMD'); ?>
					</td>
					<td class="value">
						<select name="gmd">
						<?php echo $gmd_list; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="value">
						<?php echo __('Collection Type'); ?>
					</td>
					<td class="value">
						<select name="colltype">
						<?php echo $colltype_list; ?>
						</select>
					</td>
					<td class="value">
						<?php echo __('Location'); ?>
					</td>
					<td class="value">
						<select name="location">
						<?php echo $location_list; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="4" class="value" style="text-align:center;">
					    <input type="submit" name="search" value="<?php echo __('Search'); ?>" class="searchButton" />
					</td>
				</tr>
			</table>
			</div>
			</form>
		    </div>
		    <div id="show_advance">
			    <a href="#"><?php echo __('Advanced Search'); ?></a>
		    </div>
	    </div>
	    <div class="collections-list">
		    <?php echo $main_content; ?>
		    <div class="clear">&nbsp;</div>
	    </div>
    </div>
    <?php } elseif($p == 'member') { ?>
    <div class="sidebar">
	    <div class="tagline">
		    <?php echo __('Information'); ?>
		    <a href="javascript: history.back();" class="back to_right"> <?php echo __('Back'); ?> </a>
	    </div>
	    <div class="info">
		    <?php echo $info; ?>
	    </div>
	    <?php if (utility::isMemberLogin()) { ?>
	    <div class="tagline">
		    <?php echo __('User Login'); ?>
	    </div>
	    <div class="info">
		    <?php echo $header_info; ?>
	    </div>
	    <?php } ?>
    </div>
    <div class="section">
	    <div class="collections-list">
		    <?php echo $main_content; ?>
		    <div class="clear">&nbsp;</div>
	    </div>
    </div>
    <?php } elseif(isset($_GET['p'])) { ?>
      <?php if ($_GET['p'] == 'show_detail') {
			    echo $main_content;
	    } else {
	    ?>
		    <div class="tagline">
			    <?php echo $page_title; ?>
			    <a href="javascript: history.back();" class="back to_right"> <?php echo __('Back'); ?> </a>
		    </div>
		    <?php if (utility::isMemberLogin()) { ?>
		    <div class="search-result-info">
			    <?php echo $header_info; ?>
		    </div>
		    <?php } ?>
		    <div class="section page">
			    <div class="collection-detail">
				    <div class="content-padding"><?php echo $main_content; ?></div>
				    <div class="clear">&nbsp;</div>
			    </div>
		    </div>
	    <?php } ?>
    <?php } else { ?>
    <div class="tagline">
	    <?php echo $info; ?>
    </div>
    <?php if (utility::isMemberLogin()) { ?>
    <div class="search-result-info">
	    <?php echo $header_info; ?>
    </div>
    <?php } ?>

    <div class="search">
	    <div id="simply-search">
			<div class="simply" >
			    <form name="advSearchForm" id="advSearchForm" action="index.php" method="get">
			    <input type="hidden" name="search" value="Search" />
			    <input type="text" name="keywords" id="keyword" placeholder="<?php echo __('Keyword'); ?>" x-webkit-speech="x-webkit-speech" />
			    </form>
			</div>
	    </div>
	    <div id="advance-search" style="display:none;" >
		<form name="advSearchForm" id="advSearchForm" action="index.php" method="get">
	    <input type="hidden" name="search" value="Search" />

		<div class="simply" >
		    <input type="text" name="title" id="title" placeholder="<?php echo __('Title'); ?>" />
		</div>
		<div class="advance">
		<table width="100%">
			<tr>
				<td class="value">
				<?php echo __('Author(s)'); ?>
				</td>
				<td class="value">
				<?php echo $advsearch_author; ?>
				</td>
				<td class="value">
				<?php echo __('Subject(s)'); ?>
				</td>
				<td class="value">
				<?php echo $advsearch_topic; ?>
				</td>
			</tr>
			<tr>
				<td class="value">
				<?php echo __('ISBN/ISSN'); ?>
				</td>
				<td class="value">
					<input type="text" name="isbn" />
				</td>
				<td class="value">
					<?php echo __('GMD'); ?>
				</td>
				<td class="value">
					<select name="gmd">
					<?php echo $gmd_list; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="value">
					<?php echo __('Collection Type'); ?>
				</td>
				<td class="value">
					<select name="colltype">
					<?php echo $colltype_list; ?>
					</select>
				</td>
				<td class="value">
					<?php echo __('Location'); ?>
				</td>
				<td class="value">
					<select name="location">
					<?php echo $location_list; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="4" class="value" style="text-align:center;">
				    <input type="submit" name="search" value="<?php echo __('Search'); ?>" class="searchButton" />
				</td>
			</tr>
		</table>
		</div>
		</form>
	    </div>
	    <div id="show_advance">
		    <a href="#"><?php echo __('Advanced Search'); ?></a>
	    </div>
    </div>
    <?php } ?>
</div>
</div>
</div>
  <div class="footer">
   <div class="container_12">
    <div class="grid_6 lisence">
     This software and this template are released Under GNU GPL License Version 3
    </div>
    <div class="grid_5 oss">
     The Winner in the Category of OSS Indonesia ICT Award 2009
    </div>
   </div>
  </div>

 </div>

 <script type="text/javascript" src="<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme']; ?>/js/supersized.3.1.3.min.js"></script>
 <script type="text/javascript">
 jQuery(function($){
  $.supersized(
  {
      transition  : 6,
      keyboard_nav  : 0,
      start_slide  : 0,
      vertical_center : 1,
      horizontal_center : 1,
      min_width : 1000,
      min_height : 700,
      fit_portrait  : 1,
      fit_landscape : 0,
      image_protect : 1,
      slides  : [
     { image : '<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme']; ?>/images/1.jpg' },
      { image : '<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme']; ?>/images/2.jpg' }
    ]
  });
 });

	var ADAPT_CONFIG = {
		path: 'assets/css/',
		range: [
		'0px    to 760px  = mobile.css',
		'760px  to 980px  = 720.css',
		'980px  to 1280px = 960.css',
		'1280px to 1600px = 1200.css',
		'1600px to 1920px = 1560.css',
		'1920px = fluid.css'
		]
	};
	$(document).ready(function()
	{
		$('#keyword').keyup(function(){
			$('#title').val();
			$('#title').val($('#keyword').val());
		});

		$('#title').keyup(function(){
			$('#keyword').val();
			$('#keyword').val($('#title').val());
		});

		$('#advSearchForm input').attr('autocomplete','off');
		$('#title').attr('style','');

		$('#show_advance').click(function(){
		    if ($("#advance-search").is(":hidden"))
		    {
				$("#advance-search").slideDown();
				$('#simply-search').hide();
		    } else {
				$("#advance-search").slideUp('fast');
				$('#simply-search').show();
		    }
		});

		$('#title').keypress(function(e){
		    if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
			this.form.submit();
		    }
		});
	});
	</script>
	<script type="text/javascript" src="<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme']; ?>/js/adapt.min.js"></script>

</body>
</html>
