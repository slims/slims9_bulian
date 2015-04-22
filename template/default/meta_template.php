  <!-- Page Title
  ============================================= -->
  <title><?php echo $page_title; ?></title>

  <!-- Meta
  ============================================= -->
  <meta charset="utf-8">

  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" />
  <meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT" />

  <meta name="description" content="SLiMS (Senayan Library Management System) is an open source Library Management System. It is build on Open source technology like PHP and MySQL">
  <meta name="keywords" content="senayan,slims,library automation,free library application, library, perpustakaan, aplikasi perpustakaan">
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
  <meta name="generator" content="<?php echo SENAYAN_VERSION ?>">

  <!-- Opengraph
  ============================================= -->
  <meta property="og:locale" content="en_US"/>
  <meta property="og:type" content="book"/>
  <meta property="og:title" content="<?php echo $page_title; ?>"/>
  <meta property="og:url" content="//<?php echo $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]; ?>"/>
  <meta property="og:site_name" content="<?php echo $sysconf['library_name']; ?>"/>
  <?php if(isset($_GET['p']) && ($_GET['p'] == 'show_detail')): ?>
  <meta property="og:image" content="//<?php echo $_SERVER["SERVER_NAME"].SWB.$image_src ?>"/>
  <?php else: ?>
  <meta property="og:image" content="//<?php echo $_SERVER["SERVER_NAME"].SWB.$sysconf['template']['dir']; ?>/default/img/logo.png"/>
  <?php endif; ?>
  <!-- Twitter
  ============================================= -->
  <meta name="twitter:card" content="summary">
  <meta name="twitter:url" content="//<?php echo $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]; ?>"/>
  <meta name="twitter:title" content="<?php echo $page_title; ?>"/>
  <?php if(isset($_GET['p']) && ($_GET['p'] == 'show_detail')): ?>
  <meta property="twitter:image" content="//<?php echo $_SERVER["SERVER_NAME"].SWB.$image_src ?>"/>
  <?php else: ?>
  <meta property="twitter:image" content="//<?php echo $_SERVER["SERVER_NAME"].SWB.$sysconf['template']['dir']; ?>/default/img/logo.png"/>
  <?php endif; ?>

  <!-- Theme
  ============================================= -->
  <link rel="shortcut icon" href="webicon.ico" type="image/x-icon"/>
  <link rel="stylesheet" href="<?php echo $sysconf['template']['dir']; ?>/core.style.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo JWB; ?>colorbox/colorbox.css" type="text/css" />
  <?php echo $metadata; ?>

  <!-- Script
  ============================================= -->
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/jquery.min.js"></script>
  <script src="<?php echo JWB; ?>modernizr.js"></script>
  <script src="<?php echo JWB; ?>form.js"></script>
  <script src="<?php echo JWB; ?>gui.js"></script>
  <script src="<?php echo JWB; ?>highlight.js"></script>
  <script src="<?php echo JWB; ?>fancywebsocket.js"></script>
  <script src="<?php echo JWB; ?>colorbox/jquery.colorbox-min.js"></script>
  <script src="<?php echo SWB; ?>template/default/js/jquery.jcarousel.min.js"></script>
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/jquery.transit.min.js"></script>
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/bootstrap.min.js"></script>
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/custom.js"></script>

  <!-- Less
  ============================================= -->
  <link rel="stylesheet/less" type="text/css" href="<?php echo SWB; ?>template/default/style.less"/>
  <script>less = { env: "development" };</script>
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/less.min.js"></script>

