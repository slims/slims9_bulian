<!doctype html>
<html>
<head><title><?php echo $page_title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" /><meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT" />
<link rel="stylesheet" type="text/css" href="<?php echo SWB; ?>template/default/css/bootstrap.min.css" />
<link href="<?php echo JWB; ?>chosen/chosen.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="<?php echo SWB.'template/core.style.css'; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo SWB.'admin/'.$sysconf['admin_template']['css']; ?>" />
<?php if (isset($css)) { echo $css; } ?>
<style type="text/css">
body { background: #FFFFFF; }
</style>
<script type="text/javascript" src="<?php echo JWB; ?>jquery.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>updater.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>form.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>gui.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>chosen/chosen.jquery.min.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>chosen/ajax-chosen.min.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>colorbox/jquery.colorbox-min.js"></script>
<?php if (isset($js)) { echo $js; } ?>
</head>
<body>
<div id="pageContent">
<?php echo $content; ?>
</div>

<!-- block if we inside iframe -->
<script type="text/javascript">
// if we are inside iframe
jQuery(document).ready( function() {

<?php if (isset($_GET['block'])) { ?>

var parentWin = self.parent;
if (parentWin && parentWin.jQuery('.editFormLink').length > 0) {
  var enabler = parentWin.jQuery('.editFormLink');
  jQuery(document.body).append('<div id="blocker" style="position: fixed; width: 100%; height: 100%; top: 0; left: 0; background: #ccc; opacity: 0.3">&nbsp;</div>');
  enabler.click(function(evt) { evt.preventDefault(); self.parent.jQuery('form').enableForm();
	self.jQuery('#blocker').remove();
	self.parent.jQuery('.makeHidden').removeClass('makeHidden'); });
}

<?php } ?>

});
</script>

</body>
</html>
