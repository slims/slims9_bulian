<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title><?php echo __('Library Member Card') ?></title>
<style type="text/css">
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
  font: 1rem 'Quicksand', sans-serif;
  color:<?php echo $sysconf['print']['membercard']['f_color']??'#000';  ?>;  ;
}

p,
h1,h2,
strong {
  margin:0;
  padding:0;
  text-align: center;
}

.personality {
  flex: 0cm      
}
.personality div {
  flex-direction: column;
}

#member-name {
  text-align: center;
  margin-bottom: 10px;
}

#front-card {
  width: 100%;
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
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;  
  color: <?php echo $sysconf['print']['membercard']['header_font_color']??'#000'; ?> !important;  
}

#front-card header .brand {
  font-size: 1rem;
  font-weight: bold;
}

#front-card header .sub-brand {
  font-size: 0.7rem;
}

#front-card header .brand,
#front-card header .sub-brand {
  padding-left: 45px;
}

#front-card .logo img {
  position: absolute;
  top: 12px;
}

#front-card main {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.identity {
  padding: 15px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.identity h1 {
  text-transform: uppercase;
}

.photo {
  width: 200px;
  border-radius: 5px;
  border: solid 3px #fff;
}

.photo img {
  width: 100%;
}

.personality {      
  display: flex;
  flex-direction: column;
  margin-top: 5px;
  margin-bottom: 0;
}

.personality .memberid {
  text-align: center;
}

.personality .expired {
  margin-top: 10px;
  margin-bottom: 10px;
  text-align: center;
}

.personality strong {      
  margin:0;
  line-height: 0;
}

.barcode {
  text-align: center;
  margin:0;
  width: 50%;
  max-width; 200px;
}

.barcode img {
  margin:0 auto;
}
</style>
</head>
<body>
<section id="front-card">
<header>
    <div class="logo">
    <img src="<?php echo $card_logo ?>" alt="No Photo">
    <div class="sub-brand"><?php echo $card_conf['front_header1_text'] ?></div>
    <div class="brand"><?php echo $card_conf['front_header2_text'] ?></div>
    </div>
</header>
<main>
    <div class="identity">
        <h1 id="member-name"><?php echo $_SESSION['m_name'] ?></h1>

        <?php if($_SESSION['m_image'] != '') : ?>
        <div class="photo">
        <img src="<?php echo SWB.IMG.'/persons/'.$_SESSION['m_image'] ?>" alt="<?php echo $_SESSION['m_name'] ?>"> 
        </div>
        <?php endif ?>

        <div class="personality">
          <div class="memberid">
              <h2><?php echo $_SESSION['mid'] ?></h2>
          </div>

          <div class="membertype">
              <h2><?php echo $_SESSION['m_member_type'] ?></h2>
          </div>

          <div class="code">
              <div class="expired">Exp. <strong><?php echo $_SESSION['m_expire_date'] ?></strong></div>
              <div class="barcode"><img src="<?php echo SWB.IMG.'/barcodes/'.str_replace(array(' '), '_', $_SESSION['mid']) ?>.png" alt="No  Barcode"></div>
          </div>
        </div>

    </div>
</main>
</section>
<script src="<?php echo JWB; ?>jquery.js"></script>
<script>
$(document).ready( () => {
    $.ajax({url: '<?php echo SWB.'lib/phpbarcode/barcode.php?code='.$_SESSION['mid'].'&encoding='.$sysconf['barcode_encoding'].'&scale='.$size.'&mode=png' ?>', 
        type: 'GET', 
        error: () => { alert('Error creating member card!'); } });
});
</script>
<body>
</html>