<?php
/*------------------------------------------------------------

Template    : Slims Akasia Template
Create Date : March 14, 2015
Author      : Eddy Subratha (eddy.subratha{at}slims.web.id)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA

-------------------------------------------------------------*/
// be sure that this file not accessed directly

if (!defined('INDEX_AUTH')) {
  die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
  die("can not access this file directly");
}

?>
<!--
==========================================================================
   ___  __    ____  __  __  ___      __    _  _    __    ___  ____    __
  / __)(  )  (_  _)(  \/  )/ __)    /__\  ( )/ )  /__\  / __)(_  _)  /__\
  \__ \ )(__  _)(_  )    ( \__ \   /(__)\  )  (  /(__)\ \__ \ _)(_  /(__)\
  (___/(____)(____)(_/\/\_)(___/  (__)(__)(_)\_)(__)(__)(___/(____)(__)(__)

==========================================================================
-->

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
  <link href="<?php echo $sysconf['template']['dir']; ?>/core.style.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo JWB; ?>colorbox/colorbox.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo $sysconf['template']['css']; ?>" rel="stylesheet" type="text/css" />
  <link type="text/css" rel="stylesheet" media="all" href="<?php echo SWB; ?>template/default/css/tango/skin.css"/>
  <?php echo $metadata; ?>

  <!-- Script
  ============================================= -->
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/jquery.min.js"></script>
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/custom.js"></script>

</head>

<body itemscope itemtype="http://schema.org/WebPage">

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

<!-- Content
============================================= -->
<?php if(isset($_GET['keywords']) || isset($_GET['p'])): ?>

  <main  id="content" class="s-main-page" role="main">

        <!-- Search on Front Page
        ============================================= -->
        <div class="s-main-search animated fadeInUp delay1">
          <h1 class="s-main-title animated fadeInUp delay2">
          <?php
              if(!isset($_GET['p'])) :
                echo __('Collections');
              elseif ($_GET['p'] == 'show_detail') :
                echo __("Record Detail");
              elseif ($_GET['p'] == 'member') :
                echo __("Member Area");
              else :
                echo $page_title;
              endif;
          ?>
          </h1>
          <form action="index.php" method="get" autocomplete="off">
            <h1 class="animated fadeInUp delay2">SEARCHING</h1>
            <p class="s-search-info animated fadeInUp delay3">you can start it by typing one or more keywords for title, author or subject</p>
            <input type="text" class="s-search animated fadeInUp delay4" name="keywords" value="" lang="<?php echo $sysconf['default_lang']; ?>" x-webkit-speech="x-webkit-speech">
            <button type="submit" name="search" value="search" class="s-btn animated fadeInUp delay4">Search</button>
          </form>
        </div>

        <!-- Main
        ============================================= -->
        <div class="s-main-content container animated fadeInUp delay2">
          <div class="row">
            <div class="col-lg-8">
              <?php echo $main_content; ?>
            </div>
            <div class="col-lg-4">

              <!--// If Member Logged //-->
              <?php if (utility::isMemberLogin()) { ?>
                  <h2><?php echo __('Information'); ?></h2>
                  <hr/>
                <p>
                  <?php echo $header_info; ?>
                </p>
              </div>
              <?php } else { ?>
                  <h2><?php echo __('Information'); ?></h2>
                  <hr/>
                  <p><?php echo $info; ?></p>
              <?php } ?>
              <!--// End Member Logged //-->
              <br/>

              <!--// Show if clustering search is enabled //-->
              <?php if(!isset($_GET['p'])) { ?>
              <?php if ($sysconf['enable_search_clustering']) { ?>
                  <h2><?php echo __('Search Cluster'); ?></h2>
                  <hr/>
                  <div id="search-cluster"><div class="cluster-loading"><?php echo __('Generating search cluster...');  ?></div></div>
                  <script type="text/javascript">
                  $('document').ready( function() {
                    $.ajax({
                      url: 'index.php?p=clustering&q=<?php echo urlencode($criteria); ?>',
                      type: 'GET',
                      success: function(data, status, jqXHR) {
                        $('#search-cluster').html(data);
                      }
                    });
                  });
                  </script>
              </div>
              <?php } ?>
              <!--// End Show if clustering search is enabled //-->
              <?php } ?>

            </div>
          </div>
        </div>

  </main>

<?php else: ?>
<!-- Result
============================================= -->

  <main id="content" class="s-main" role="main">

        <!-- Search on Result Page
        ============================================= -->
        <div class="s-main-search animated fadeInUp delay1">
          <form action="index.php" method="get" autocomplete="off">
            <h1 class="animated fadeInUp delay2">SEARCHING</h1>
            <p class="s-search-info animated fadeInUp delay3">you can start it by typing one or more keywords for title, author or subject</p>
            <input type="text" class="s-search animated fadeInUp delay4" name="keywords" value="" lang="<?php echo $sysconf['default_lang']; ?>" x-webkit-speech="x-webkit-speech">
            <button type="submit" name="search" value="search" class="s-btn animated fadeInUp delay4">Search</button>
          </form>
        </div>

        <!-- Feature
        ============================================= -->

        <a href="#" class="s-feature animated fadeInUp delay6">see also our featured collections
          <div class="s-menu-toggle animated fadeInUp delay7"><span></span></div>
        </a>

  </main>
<?php endif; ?>

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
