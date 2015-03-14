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
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/custom.js"></script>

</head>
<body itemscope itemtype="http://schema.org/WebPage" id="login-page">

  <!-- Header
  ============================================= -->

  <header class="s-header container" role="header">
    <div class="row">
      <div class="col-lg-6">
        <a href="index.php" class="s-brand">
          <img class="s-logo animated fadeInUp delay1" src="<?php echo $sysconf['template']['dir']; ?>/default/img/slims-logo.png" alt="" />
          <h1 class="animated fadeInUp delay2"><?php echo $sysconf['library_name']; ?></h1>
          <div class="s-brand-tagline animated fadeInUp delay3"><?php echo $sysconf['library_subname']; ?></div>
        </a>
      </div>
      <div class="col-6-lg">
        <div class="s-menu animated fadeInUp delay4">
          <a href="#" id="show-menu" class="s-menu-toggle"><span></span></a>
        </div>
      </div>
    </div>
  </header>

  <main id="content" class="s-main" role="main">
    <?php echo $main_content; ?>
  </main>

  <!-- Navigation
  ============================================= -->
  <nav class="s-menu-content animated-fast">
    <a href="#" id="hide-menu" class="s-menu-toggle"><span></span></a>
    <h1>Menu</h1>
    <ul>
      <li><a href="index.php" rel="tab"><?php echo __('Home'); ?></a></li>
      <li><a href="index.php?p=libinfo" rel="tab"><?php echo __('Library Information'); ?></a></li>
      <li><a href="index.php?p=member" rel="tab"><?php echo __('Member Area'); ?></a></li>
      <li><a href="index.php?p=librarian" rel="tab"><?php echo __('Librarian'); ?></a></li>
      <li><a href="index.php?p=help" rel="tab"><?php echo __('Help on Search'); ?></a></li>
      <li><a href="index.php?p=login" rel="tab"><?php echo __('Librarian LOGIN'); ?></a></li>
    </ul>
    <div class="s-menu-info">
      <p>Thank you for using SLiMS</p>
      <p>Library Automation With Style</p>
    </div>
  </nav>

  <!-- Footer
  ============================================= -->
  <footer class="s-footer container" role="footer">
    <div class="row">
      <div class="col-lg-6">
        <div class="s-footer-tagline">
          SLIMS 8 Akasia
        </div>
      </div>
      <div class="col-lg-6">
        <ul class="s-footer-menu">
          <li><a target="_blank" href="http://www.facebook.com/groups/senayan.slims">Facebook</a></li>
          <li><a target="_blank" href="http://twitter.com/#!/slims_official">Twitter</a></li>
          <li><a target="_blank" href="http://www.youtube.com/user/senayanslims">Youtube</a></li>
          <li><a target="_blank" href="https://github.com/slims">Github</a></li>
          <li><a target="_blank" href="http://slims.web.id/forum">Forum</a></li>
        </ul>
      </div>
    </div>
  </footer>

  <!-- Background
  ============================================= -->
  <div class="s-video animated fadeIn">
    <video loop autoplay muted>
      <source src='<?php echo $sysconf['template']['dir']; ?>/default/video/sdc.webm' type='video/webm' />
      Your browser does not support the video tag.
    </video>
  </div>

</body>

</html>
