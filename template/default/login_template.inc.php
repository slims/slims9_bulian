<?php
// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}
?>


<!DOCTYPE html>
<html lang="<?php echo substr($sysconf['default_lang'], 0, 2); ?>">
<head>

  <!-- Meta
  ============================================= -->

  <title><?php echo $page_title; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" />
  <meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT" />

  <!-- Meta
  ============================================= -->
  <meta name="description" content="SLiMS (Senayan Library Management System) is an open source Library Management System. It is build on Open source technology like PHP and MySQL">
  <meta name="keywords" content="senayan,slims,library automation,free library application, library, perpustakaan, aplikasi perpustakaan">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Theme
  ============================================= -->
  <link rel="stylesheet" type="text/css" href="<?php echo $sysconf['template']['dir']; ?>/core.style.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo JWB; ?>colorbox/colorbox.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo $sysconf['template']['css']; ?>" />
  <link rel="stylesheet" type="text/css" href="template/core.style.css" />
  <?php echo $metadata; ?>

  <!-- Script
  ============================================= -->
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/jquery.min.js"></script>
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/jquery.transit.min.js"></script>
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/custom.js"></script>

</head>
<body itemscope itemtype="http://schema.org/WebPage" id="login-page">

  <!-- Header
  ============================================= -->
  <?php include "header.php"; ?>

  <!-- Login
  ============================================= -->
  <main id="content" class="s-main s-login" role="main">
    <div class="s-login-content animated fadeInUp delay9">
      <?php echo $main_content; ?>
    </div>
  </main>

  <!-- Navigation
  ============================================= -->
  <?php include "navigation.php"; ?>

  <!-- Footer
  ============================================= -->
  <?php include "footer.php"; ?>

  <!-- Background
  ============================================= -->
  <?php include "bg.php"; ?>

</body>

</html>
