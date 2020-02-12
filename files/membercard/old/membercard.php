<?php
function floatvalue($val){
  $val = str_replace(",",".",$val);
  $val = preg_replace('/\.(?=.*\.)/', '', $val);
  return floatval($val);
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Member Card</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" />
  <meta http-equiv="Expires" content="Fry, 02 Oct 2012 12:00:00 GMT" />
  <style>
    * {
    font: <?php echo $sysconf['print']['membercard']['bio_font_size'] ?>px Arial, Helvetica, sans-serif;
  }

  p,
  li {
    position: relative;
  }

  p {
    margin-bottom: 0px;
    margin-top: 0px;
    font-weight: bold;
  }

  li {
    margin-bottom: 0px;
    margin-top: 0px;
    list-style-type: disc;
    font-size: <?php echo $sysconf['print']['membercard']['rules_font_size'] ?>px;
  }

  ul {
    margin: 0px;
    padding-left: 10px;
  }

  h1 {
    margin: 0px;
    font-weight: bold;
    text-align: center;
    font-size: <?php echo $sysconf['print']['membercard']['front_header1_font_size'] ?>px;
  }

  h2 {
    margin: 0px;
    font-weight: bold;
    text-align: center;
    padding-bottom: 3px;
    font-size: <?php echo $sysconf['print']['membercard']['front_header2_font_size'] ?>px;
  }

  h3 {
    margin: 0px;
    font-weight: bold;
    text-align: center;
    padding-bottom: 3px;
    font-size: <?php echo $sysconf['print']['membercard']['back_header2_font_size'] ?>px;
  }

  hr {
    margin: 0px;
    border: 1px solid<?php echo $sysconf['print']['membercard']['header_color'] ?>;
    position: relative;
  }

  #header1_div {
    z-index: 2;
    position: absolute;
    left: 61px;
    top: 4px;
    width: 245px;
    height: 45px;
    color:<?php echo $sysconf['print']['membercard']['header_color'] ?>;
  }

  #header2_div {
    z-index: 3;
    position: absolute;
    left: 10px;
    top: 4px;
    width: 300px;
    height: 43px;
    color:<?php echo $sysconf['print']['membercard']['header_color'] ?>;
  }

  #rules_div {
    z-index: 4;
    position: absolute;
    left: 12px;
    top: 58px;
    width: 300px;
    height: 142px;
    text-align: justify;
  }

  #address_div {
    z-index: 4;
    position: absolute;
    left: 9px;
    top: 175px;
    width: 300px;
    height: 20px;
    font-size: <?php echo $sysconf['print']['membercard']['address_font_size'] ?>px;
  }

  #logo_div {
    z-index: 5;
    position: absolute;
    left: 10px;
    top: 4px;
    width: 35px;
    height: 35px;
  }

  #photo_blank_div {
    z-index: 5;
    position: absolute;
    left: 10px;
    top: 130px;
    font-size: 7px;
    text-align: center;
    border: #cccccc solid 1px;
    width: <?= round($sysconf['print']['membercard']['photo_width']*$sysconf['print']['membercard']['factor']); ?>px;
    height: <?= round($sysconf['print']['membercard']['photo_height']*$sysconf['print']['membercard']['factor']) ?>px;
  }

  #photo_div {
    z-index: 6;
    position: absolute;
    left: 10px;
    top: 130px;
    border: #cccccc solid 1px;
    width: <?= round($sysconf['print']['membercard']['photo_width']*$sysconf['print']['membercard']['factor']) ?>px;
    height: <?= round($sysconf['print']['membercard']['photo_height']*$sysconf['print']['membercard']['factor']) ?>px;
  }

  #front_side {
    background: url(<?php echo SWB.'files/membercard/old/'.$sysconf['print']['membercard']['front_side_image'] ?>) center center;
  }

  #back_side {
    background: url(<?php echo SWB.'files/membercard/old/'.$sysconf['print']['membercard']['back_side_image'] ?>) center center;
  }

  .container_div {
    z-index: 1;
    position: relative;
    width: <?= round($sysconf['print']['membercard']['box_width']*$sysconf['print']['membercard']['factor']) ?>px;
    height: <?= round($sysconf['print']['membercard']['box_height']*$sysconf['print']['membercard']['factor']) ?>px;
    margin-bottom: <?= round($sysconf['print']['membercard']['items_margin']*$sysconf['print']['membercard']['factor']) ?>px;
    ;
    border: #CCCCCC solid 1px;
    -moz-border-radius: 8px;
    border-radius: 8px;
  }

  .bio_div {
    z-index: 7;
    position: absolute;
    left: 0px;
    top: 48px;
    height: 110px;
    margin: 0px;
    text-align: justify;
  }

  .bio_address {
    z-index: 8;
    top: 0px;
  }

  .bio_label {
    z-index: 9;
    float: left;
    width: 100px;
    text-align: left;
    padding-left: 10px;
  }

  .label_address {
    z-index: 10;
    float: left;
    width: 200px;
    margin-bottom: 0px;
    margin-left: 3px;
  }

  .stamp_div {
    z-index: 11;
    position: absolute;
    left: 100px;
    top: 140px;
    margin-bottom: 34px;
    width: 118px;
  }

  .stamp {
    z-index: 12;
    text-align: left;
    margin: 0px;
  }

  .city {
    z-index: 13;
    font-size: 8px;
    margin: 0px;
  }

  .title {
    z-index: 14;
    font-size: 8px;
    margin: 0px;
  }

  .officials {
    z-index: 15;
    top: 0px;
    font-size: 8px;
    margin: 0px;
  }

  .sign_file_div {
    z-index: 16;
    position: absolute;
    left: -10px;
    top: 10px;
    width: 107px;
    height: 25px;
  }

  .stamp_file_div {
    z-index: 17;
    position: absolute;
    left: -20px;
    top: 5px;
    width: 40px;
    height: 40px;
  }

  .exp_div {
    z-index: 18;
    position: absolute;
    left: 200px;
    top: 142px;
    width: 110px;
    height: 12px;
    font-size: 8px;
    text-align: right;
  }

  .barcode_div {
    z-index: 19;
    position: absolute;
    left: 200px;
    top: 154px;
    width: 112px;
    height: 42px;
  }
  </style>
</head>

<body>
  <a href="#" onclick="window.print()"><?php echo __('Print Again') ?></a><br /><br />
  <table style="margin: 0; padding: 0;" cellspacing="0" cellpadding="0">

    <?php foreach ($chunked_card_arrays as $membercard_rows) : ?>
    <tr>
      <?php foreach ($membercard_rows as $card) : ?>
      <td valign="top">
        <div class="container_div" id="front_side">
          <div></div>
          <div id="logo_div">
            <img height="40px" width="40px" src="<?php echo SWB.'files/membercard/old/'.$sysconf['print']['membercard']['logo'] ?>" />
          </div>
          <div id="header1_div">
            <h1>
              <?php echo $sysconf['print']['membercard']['front_header1_text'] ?>
            </h1>
            <h2>
              <?php echo $sysconf['print']['membercard']['front_header2_text'] ?>
            </h2>
          </div>
          <div class="bio_div">
            <?php echo $sysconf['print']['membercard']['include_id_label']?'':'<!--' ?>
            <p class="bio">
                <label class="bio_label"><?php echo __('Member ID') ?></label>
                <span>: </span>
              <?php echo $card['member_id'] ?>
            </p>
            <?php echo $sysconf['print']['membercard']['include_id_label']?'':'-->' ?>
            <?php echo $sysconf['print']['membercard']['include_name_label']?'':'<!--' ?>
            <p class="bio">
                <label class="bio_label"><?php echo __('Member Name') ?></label>
                <span>: </span>
              <?php echo $card['member_name'] ?>
            </p>
            <?php echo $sysconf['print']['membercard']['include_name_label']?'':'-->' ?>
            <?php echo $sysconf['print']['membercard']['include_pin_label']?'':'<!--' ?>
            <p class="bio">
                <label class="bio_label"><?php echo __('Personal ID Number') ?></label>
                <span>: </span>
                <?php echo $card['pin'] ?>
            </p>
            <?php echo $sysconf['print']['membercard']['include_pin_label']?'':'-->' ?>
            <?php echo $sysconf['print']['membercard']['include_inst_label']?'':'<!--' ?>
            <p class="bio_address">
              <label class="bio_label"><?php echo __('Institution') ?></label>
              <span style="float:left">: </span>
              <?php echo $sysconf['print']['membercard']['include_inst_label']?'':'-->' ?>
              <?php echo $sysconf['print']['membercard']['include_inst_label']?'':'<!--' ?>
              <span class="label_address"><?php echo $card['inst_name'] ?></span></p>
            <?php echo $sysconf['print']['membercard']['include_inst_label']?'':'-->' ?>
            <?php echo $sysconf['print']['membercard']['include_email_label']?'':'<!--' ?>
            <p class="bio">
                <label class="bio_label"><?php echo __('E-mail') ?></label>
                <span>:</span>
                <?php echo $card['member_email'] ?>
            </p>
            <?php echo $sysconf['print']['membercard']['include_email_label']?'':'-->' ?>
            <?php echo $sysconf['print']['membercard']['include_address_label']?'':'<!--' ?>
            <p class="bio_address">
                <label class="bio_label">
                <?php echo __('Address') ?> /
                <?php echo __('Phone Number') ?>
                </label>
                <span style="float:left">: </span>
                <?php echo $sysconf['print']['membercard']['include_address_label']?'':'-->' ?>
                <?php echo $sysconf['print']['membercard']['include_address_label']?'':'<!--' ?>
                <span class="label_address">
                  <?php echo $card['member_address'] ?> /
                  <?php echo $card['member_phone'] ?>
                </span>
              </p>
            <?php echo $sysconf['print']['membercard']['include_address_label']?'':'-->' ?>
          </div>
          <div id="photo_blank_div">
            <br />
            Photo size:<br />
            <?php echo $sysconf['print']['membercard']['photo_width'] ?> X
            <?php echo $sysconf['print']['membercard']['photo_height'] ?> cm</div>
          <div id="photo_div"><img width="<?php echo $sysconf['print']['membercard']['photo_width'] * $sysconf['print']['membercard']['factor'] ?>px"
              height="<?php echo $sysconf['print']['membercard']['photo_height']*$sysconf['print']['membercard']['factor'] ?>px"
              src="<?php echo SWB.IMG ?>/persons/<?php echo $card['member_image'] ?>" /></div>
          <?php echo $sysconf['print']['membercard']['include_expired_label']?'':'<!--' ?>
          <div class="exp_div">
            <?php echo __('Expiry Date') ?> :
            <?php echo $card['expire_date'] ?>
          </div><?php echo $sysconf['print']['membercard']['include_expired_label']?'':'-->' ?>
          <?php echo $sysconf['print']['membercard']['include_barcode_label']?'':'<!--' ?>
          <div class="barcode_div">
            <img width="175px" height="40px" src="<?php echo SWB.IMG.'/barcodes/'.str_replace(array(' '), '_', $card['member_id']) ?>.png"
              style="width:<?php echo $sysconf['print']['membercard']['barcode_scale'] ?>%; border=" 0px" /></div>              
          <?php echo $sysconf['print']['membercard']['include_barcode_label']?'':'-->' ?>
          <div class="stamp_div">
            <div class="stamp_file_div"><img class="" height="35px" width="35px" src="<?php echo SWB.'files/membercard/old/'.$sysconf['print']['membercard']['stamp_file'] ?>"></div>
            <div class="sign_file_div"><img class="" height="30px" width="100px" src="<?php echo SWB.'files/membercard/old/'.$sysconf['print']['membercard']['signature_file'] ?>"></div>
            <p class="stamp city">
              <?php echo $sysconf['print']['membercard']['city'] ?>,
              <?php echo $card['register_date'] ?>
            </p>
            <p class="stamp title">
              <?php echo $sysconf['print']['membercard']['title'] ?>
            </p><br>
            <p class="stamp officials">
              <?php echo $sysconf['print']['membercard']['officials'] ?><br />
              <?php echo $sysconf['print']['membercard']['officials_id'] ?>
            </p>
          </div>
        </div>
      </td>
      <td valign="top">
        <div class="container_div" id="back_side">
          <div></div>
          <div id="logo_div"><img height="35px" width="35px" src="<?php echo SWB.'files/membercard/old/'.$sysconf['print']['membercard']['logo'] ?>" /></div>
          <div id="header2_div">
            <h1>
              <?php echo $sysconf['print']['membercard']['back_header1_text'] ?>
            </h1>
            <h3>
              <?php echo $sysconf['print']['membercard']['back_header2_text'] ?>
            </h3>
            <hr>
          </div>
          <div id="rules_div">
            <?php echo html_entity_decode($sysconf['print']['membercard']['rules']) ?>
          </div>
          <div id="address_div">
            <?php echo html_entity_decode($sysconf['print']['membercard']['address']) ?>
          </div>
        </div>
      </td>
      <?php endforeach ?>
    <tr>
      <?php endforeach ?>
  </table>

  <script type="text/javascript">
    self.print();
  </script>

</body>

</html>