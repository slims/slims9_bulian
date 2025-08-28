<?php utility::loadUserTemplate($dbs,$_SESSION['uid']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $page_title??''; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo SWB.'css/bootstrap.min.css'; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo SWB.'css/printed.css?v='.date('this'); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo SWB.'admin/'.$sysconf['admin_template']['css'].'?'.date('this'); ?>" />
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
