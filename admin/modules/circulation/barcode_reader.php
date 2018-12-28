<?php
// key to authenticate
if (!defined('INDEX_AUTH')) {
  define('INDEX_AUTH', '1');
}

// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';

// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');

// start the session
require SB.'admin/default/session.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');

if (!$can_read) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

    <title>index</title>
    <meta name="description" content="" />
    <meta name="author" content="Christoph Oberhofer" />
    <meta name="viewport" content="width=device-width; initial-scale=1.0; user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="<?php echo SWB ?>css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo JWB ?>quaggaJS/css/styles.css?v=<?php echo date('this') ?>" />
  </head>

  <body>
		<audio id="barcodeAudio">
			<source src="<?php echo JWB ?>quaggaJS/sound.ogg" type="audio/ogg">
			<source src="<?php echo JWB ?>quaggaJS/sound.mp3" type="audio/mpeg">
			Your browser does not support the audio element.
		</audio>
    <section id="container">
      <div id="interactive" class="viewport"></div>
			<div class="controls">
				<div class="reader-config-group">

						<div class="form-group">
						<label>Barcode-Type</label>
						<select name="decoder_readers" class="form-control">
								<option value="code_128" selected="selected">Code 128</option>
								<option value="code_39">Code 39</option>
								<option value="code_39_vin">Code 39 VIN</option>
								<option value="ean">EAN</option>
								<option value="ean_extended">EAN-extended</option>
								<option value="ean_8">EAN-8</option>
								<option value="upc">UPC</option>
								<option value="upc_e">UPC-E</option>
								<option value="codabar">Codabar</option>
								<option value="i2of5">Interleaved 2 of 5</option>
								<option value="2of5">Standard 2 of 5</option>
								<option value="code_93">Code 93</option>
						</select>
						</div>

						<div class="form-group">
						<label>Resolution (width)</label>
						<select name="input-stream_constraints" class="form-control">
							<option selected="selected" value="640x480">640px</option>
							<option value="320x240">320px</option>
							<option value="800x600">800px</option>
							<option value="1280x720">1280px</option>
							<option value="1600x960">1600px</option>
							<option value="1920x1080">1920px</option>
						</select>
						</div>

						<div class="form-group">
						<label>Patch-Size</label>
						<select name="locator_patch-size" class="form-control">
							<option value="x-small">x-small</option>
							<option value="small">small</option>
							<option selected="selected" value="medium">medium</option>
							<option value="large">large</option>
							<option value="x-large">x-large</option>
						</select>
						</div>

						<div class="form-group">
							<label>Half-Sample</label>
								<input type="checkbox" checked="checked" name="locator_half-sample" />
						</div>

						<div class="form-group">
							<label>Workers</label>
							<select name="numOfWorkers" class="form-control">
								<option value="0">0</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option selected="selected" value="4">4</option>
								<option value="8">8</option>
							</select>
						</div>

						<div class="form-group">
							<label>Camera</label>
							<select name="input-stream_constraints" id="deviceSelection" class="form-control"></select>
						</div>

						<div class="form-group" style="display: none">
							<label>Zoom</label>
							<select name="settings_zoom" class="form-control"></select>
						</div>
						
						<div class="form-group" style="display: none">
								<label>Torch</label>
								<input type="checkbox" name="settings_torch" />
						</div>
						
				</div>
			</div>
    </section>

    <script src="<?php echo JWB ?>jquery.js" type="text/javascript"></script>
    <script src="<?php echo JWB ?>quaggaJS/adapter.js" type="text/javascript"></script>
    <script src="<?php echo JWB ?>quaggaJS/quagga.js" type="text/javascript"></script>
    <script src="<?php echo JWB ?>barcodereader.js?v=<?php echo date('this') ?>" type="text/javascript"></script>
  </body>
</html>
