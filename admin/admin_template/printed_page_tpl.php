<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<title><?php echo $page_title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo SWB.'template/printed.style.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo SWB.'admin/'.$sysconf['admin_template']['css']; ?>" />
	<?php if (isset($css)) { echo $css; } ?>
	<style type="text/css">
		body { 
			background: #FFFFFF; 
		}
	</style>
	<?php if (isset($js)) { echo $js; } ?>
</head>
<body>
	<div id="pageContent">
		<?php echo $content; ?>
	</div>
</body>
</html>
