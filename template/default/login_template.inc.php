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
  <?php include "meta_template.php"; ?>

</head>
<body itemscope itemtype="http://schema.org/WebPage" id="login-page">

  <!-- Header
  ============================================= -->
  <?php include "header_template.php"; ?>

  <!-- Login
  ============================================= -->
  <main id="content" class="s-main s-login" role="main">
    <div class="s-login-content animated fadeInUp delay9">
      <?php echo $main_content; ?>
    </div>
  </main>

  <!-- Navigation
  ============================================= -->
  <?php include "navigation_template.php"; ?>

  <!-- Footer
  ============================================= -->
  <?php include "footer_template.php"; ?>

  <!-- Background
  ============================================= -->
  <?php include "bg_template.php"; ?>

</body>

</html>
