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
			<source src="<?php echo JWB ?>quaggaJS/sound.ogg?v=1" type="audio/ogg">
			<source src="<?php echo JWB ?>quaggaJS/sound.mp3?v=1" type="audio/mpeg">
			Your browser does not support the audio element.
		</audio>
    <section id="container">
      <div id="interactive" class="viewport"></div>
			<div class="controls d-none">
				<div class="reader-config-group">
						<div class="form-group">
						<label>Barcode-Type</label>
						<select name="decoder_readers" class="form-control">
							<?php foreach($barcodes_encoding as $type => $barcode) : ?>
								<option value="<?php echo strtolower(str_replace(' ','_',$barcode[1])) ?>" <?php echo (($sysconf['barcode_encoding'] == $barcode[0])?'selected':'') ?>><?php echo $barcode[1] ?></option>
								<?php endforeach ?>
					</select>
						</div>

						<div class="form-group">
						<label>Resolution (width)</label>
						<select name="input-stream_constraints" class="form-control">
							<option value="320x240">320px</option>
							<option value="640x480">640px</option>
							<option value="800x600" selected="selected">800px</option>
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
							<option value="medium" selected="selected">medium</option>
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
    <script src="<?php echo JWB ?>quaggaJS/adapter-latest.js" type="text/javascript"></script>
    <script src="<?php echo JWB ?>quaggaJS/quagga.js" type="text/javascript"></script>
    <script src="<?php echo JWB ?>barcodereader.js?v=<?php echo date('this') ?>" type="text/javascript"></script>
		<?php if(isset($script)) {
		echo '<script>'.$script.'</script>';
    } ?>
  </body>
</html>