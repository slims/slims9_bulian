<?php
function floatvalue($val){
  $val = str_replace(",",".",$val);
  $val = preg_replace('/\.(?=.*\.)/', '', $val);
  return floatval($val);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Document</title>
<style>
<?php
$card_path = str_replace("\\", "/", $card_path);
?>
@font-face {
  font-family: "Quicksand";
  src: url('<?php echo $card_path ?>fonts/Quicksand/Quicksand-Regular.ttf') format('truetype');
  font-weight: 400;
  font-style: normal;
}
@font-face {
  font-family: "Quicksand";
  src: url('<?php echo $card_path ?>fonts/Quicksand/Quicksand-Bold.ttf') format('truetype');
  font-weight: 700;
  font-style: bold;
}

body {
  font: 7pt/1.4 'Quicksand', sans-serif;
  color:<?php echo $sysconf['print']['membercard']['f_color']??'#000';  ?>;  ;
}

p,
h1,
strong {
  margin:0;
  padding:0;
}

.personality {
  flex: 0cm      
}
.personality div {
  flex-direction: column;
}

#front-card,
#back-card {
  width: <?= round($sysconf['print']['membercard']['box_width']*$sysconf['print']['membercard']['factor']); ?>px;
  height: <?= round($sysconf['print']['membercard']['box_height']*$sysconf['print']['membercard']['factor']); ?>px;
  border: solid 1px #e4e4e4;
  position: relative;
}

#front-card {
  background-color: <?php echo $sysconf['print']['membercard']['fr_color']??'#E5E5E5';  ?>;    
}

#front-card header {
  padding: 15px 10px;
  background-color: #fff;
  text-transform: uppercase;
  color: <?php echo $sysconf['print']['membercard']['header_font_color']??'#000'; ?> !important;  
}

#front-card header .brand {
  font-size: 9pt;
  font-weight: bold;
}

#front-card header .sub-brand {
  font-size: 7pt;
}

#front-card header .brand,
#front-card header .sub-brand {
  padding-left: 45px;
}

#front-card .logo img {
  position: absolute;
  top: 12px;
}

.identity {
  padding: 15px;
}

.identity h1 {
  max-width: 80%;
  height: 40px;
  text-transform: uppercase;
}

.photo {
  position: absolute;
  top: 25px;
  right: 15px;
  width: 55px;
  height: 55px;
  overflow: hidden;
  border-radius: 5px;
  border: solid 3px #fff;
}

.photo img {
  width: 55px;
  border-radius: 3px;
}

.personality {      
  display: flex;
  margin-top: 5px;
  margin-bottom: 0;
}

.personality > div {
  width: 35%;      
}

#front-card .address {
  width: 200px;
}

.personality .noid {
  position: relative;
}

.personality .noid:after {
  position: absolute;
  content: '';
  border-right: solid 1px #000;
  height: 25px;
  top: 0;
  right: 0;
}

.personality .nophone {
  padding-left: 5px;
}

.personality .noid strong,
.personality .nophone strong {
  white-space: nowrap; 
}
.personality .expired {
  margin-top: -10px;
}

.personality strong {      
  margin:0;
  line-height: 0;
}

.code {
  position: absolute;
  right: 10px;
  bottom: 5px;
}

.barcode {
  text-align: center;
  margin:0;
  height: 15px;
  overflow: hidden;
  width: 100px;
  border-top: solid 2px #fff;
  border-bottom: solid 2px #fff;
}

.barcode img {
  margin:0;
  width: <?php echo $sysconf['print']['membercard']['barcode_scale'] ?>%;
}

#back-card {
  background: <?php echo $sysconf['print']['membercard']['b_color']??'#ffffff';  ?> url("<?php echo $card_path.'images/'.$sysconf['print']['membercard']['back_side_image'] ?>") center center no-repeat;
  background-size: cover;
}

#back-card .rules {
  padding: 15px;
}

#back-card .rules ul {
  margin-top: 10px;
  padding: 0 15px;
}

#back-card .sign {
  text-align: center;
  position: absolute;
  right: 15px;
  bottom: 15px;
  line-height: 1;
}

#back-card .signature {
  width: 35px;
}

#back-card .stamp {
  position: absolute;
  top: 15px;
  left: 20px;
  width: 25px;
}

.librarian,
.position {
  font-weight: bold;
}

#back-card footer {
  position: absolute;
  padding-left: 15px;
  bottom: 15px;
}

#back-card .title {
  font-weight: 700;
  text-transform: uppercase;
}
#back-card address {
  font-style: normal;
}
.print_btn {
  background: #333;
  padding: 10px 15px;
  text-decoration: none;
  color: #fff;
  margin-bottom: 5px;
  display: inline-block;
}

@media print {
  .print_btn {
    display: none;
  }  
}
</style>
</head>
<body>
<a class="print_btn" href="#" onclick="window.print()"><?php echo __('Print Again') ?></a>
<table cellpadding="0" cellspacing="0">
  <?php foreach ($chunked_card_arrays as $membercard_rows) : ?>
  <tr>
    <?php foreach ($membercard_rows as $card) : ?>
    <td>
    <!-- Frontcard -->
    <section id="front-card">
      <header>
        <div class="logo">
          <img src="<?php echo $card_logo ?>" alt="No Photo">
          <div class="sub-brand"><?php echo $sysconf['print']['membercard']['front_header1_text'] ?></div>
          <div class="brand"><?php echo $sysconf['print']['membercard']['front_header2_text'] ?></div>
        </div>
      </header>
      <main>
        <div class="identity">
          <h1><?php echo $card['member_name'] ?></h1>
          <?php if(($card['member_image'] != '') || (file_exists(SWB.IMG.'/persons/'.$card['member_image']))) : ?>
          <div class="photo">
            <img src="<?php echo SWB.IMG ?>/persons/<?php echo $card['member_image'] ?>" alt="">
          </div>
          <?php endif ?>
          <?php if($card['member_address'] != ''): ?>
          <div class="address">
            <strong><?php echo __('Address') ?></strong>
            <p><?php echo $card['member_address'] ?></p>
          </div>
          <?php endif ?>
          <div class="personality">
            <?php if($card['pin'] != '') : ?>
            <div class="noid">
              <strong><?php echo __('Personal ID Number') ?></strong>
              <p><?php echo $card['pin'] ?></p>
            </div>
            <?php endif ?>
            <?php if($card['member_phone'] != '') : ?>
            <div class="nophone">
              <strong><?php echo __('Phone Number') ?></strong>
              <p><?php echo $card['member_phone'] ?></p>
            </div>
            <?php endif ?>
            <div class="code">
              <div class="expired">Exp. <strong><?php echo $card['expire_date'] ?></strong></div>
              <div class="barcode">
                <img src="<?php echo SWB.IMG.'/barcodes/'.str_replace(array(' '), '_', $card['member_id']) ?>.png" alt="No  Barcode">
              </div>
            </div>
          </div>
        </div>
      </main>
    </section>
    <!-- End Frontcard -->
    </td>
    <td>
    <!-- Backcard -->
    <section id="back-card">
        <div class="rules">
          <strong><?php echo __('Library Rules') ?></strong>
          <?php echo html_entity_decode($sysconf['print']['membercard']['rules']) ?>
        </div>

        <div class="sign">
          <div class="time"><?php echo $sysconf['print']['membercard']['city']?>, <?php echo date('d M Y',strtotime($card['register_date'])) ?></div>
          <div class="position"><?php echo $sysconf['print']['membercard']['title'] ?></div>
          <img class="signature" src="<?php echo $card_signature ?>" alt="No signature">
          <img class="stamp" src="<?php echo $card_stamp ?>" alt="No stamp">
          <div class="librarian"><?php echo $sysconf['print']['membercard']['officials'] ?></div>
          <div class="uid"><?php echo $sysconf['print']['membercard']['officials_id'] ?></div>
        </div>

        <footer>
          <div class="title"><?php echo $sysconf['print']['membercard']['back_header1_text'] ?></div>
          <address>
            <?php echo $sysconf['print']['membercard']['back_header2_text'] ?>
          </address>
        </footer>

    </section>
    <!-- End Backcard -->
    </td>
    <?php endforeach ?>
  </tr>
  <?php endforeach ?>
</table>

<script type="text/javascript">
  self.print();
</script>

</body>
</html>