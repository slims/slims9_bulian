<!DOCTYPE html>
<html><head><title><?php echo $page_title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" />
<meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT" />
<link rel="icon" href="<?php echo SWB; ?>webicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="<?php echo SWB; ?>webicon.ico" type="image/x-icon" />
<link href="<?php echo SWB; ?>template/core.style.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $sysconf['admin_template']['css']; ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo JWB; ?>chosen/chosen.css" rel="stylesheet" type="text/css" />
<link href="<?php echo JWB; ?>colorbox/colorbox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo JWB; ?>jquery.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>updater.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>gui.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>form.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>calendar.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>keyboard.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>tinymce/tiny_mce.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>chosen/chosen.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>chosen/ajax-chosen.min.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>tooltipsy.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>colorbox/jquery.colorbox-min.js"></script>
<!-- new them for Meranti by Eddy Subratha -->
</head>
<body>
<!-- main menu -->
<div id="mainMenu"><?php echo $main_menu; ?></div>
<!-- main menu end -->

<!-- header-->
<div id="header">
	<div id="headerImage">&nbsp;</div>
	<div id="libraryName">
		<?php echo $sysconf['library_name']; ?>
	</div>
	<div id="librarySubName">
		<?php echo $sysconf['library_subname']; ?>
	</div>
</div>
<!-- header end-->

<table id="main" cellpadding="0" cellspacing="0">
<tr>
    <td id="sidepan">
	    <?php echo $sub_menu; ?>
    </td>
    <td>
    	<a name="top"></a>
	    <div class="loader"><?php echo $info; ?></div>
	    <div id="mainContent">
	    <?php echo $main_content; ?>
	    </div>
    </td>
</tr>
</table>

<!-- license info -->
<div id="footer"><?php echo $sysconf['page_footer']; ?></div>
<!-- license info end -->

<!-- fake submit iframe for search form, DONT REMOVE THIS! -->
<iframe name="blindSubmit" style="visibility: hidden; width: 0; height: 0;"></iframe>
<!-- <iframe name="blindSubmit" style="visibility: visible; width: 100%; height: 300px;"></iframe> -->
<!-- fake submit iframe -->

</body>
</html>
