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
  <!-- <link type="text/css" rel="stylesheet" media="all" href="<?php echo SWB; ?>template/default/css/tango/skin.css"/> -->
  <?php echo $metadata; ?>

  <!-- Script
  ============================================= -->
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/jquery.min.js"></script>
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/jquery.transit.min.js"></script>
  <script src="<?php echo $sysconf['template']['dir']; ?>/default/js/custom.js"></script>

</head>

<body itemscope itemtype="http://schema.org/WebPage">

<!-- Header
============================================= -->
<?php include "header.php"; ?>

<!-- Navigation
============================================= -->
<?php include "navigation.php"; ?>

<!-- Content
============================================= -->
<?php if(isset($_GET['keywords']) || isset($_GET['p'])): ?>

  <main  id="content" class="s-main-page" role="main">

        <!-- Search on Front Page
        ============================================= -->
        <div class="s-main-search">
          <h1 class="s-main-title animated fadeInUp delay1">
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
        <div class="s-main-content container">
          <div class="row">

            <!-- Show Result
            ============================================= -->
            <div class="col-lg-8">
              <?php echo $main_content; ?>
            </div>

            <div class="col-lg-4">
              <?php if(isset($_GET['search'])) : ?>
              <h2>Search Result</h2>
              <hr>
              <?php echo $search_result_info; ?>
              <?php endif; ?>

              <br>

              <!-- If Member Logged
              ============================================= -->
              <?php if (utility::isMemberLogin()) : ?>
                <h2><?php echo __('Information'); ?></h2>
                <hr/>
                <p><?php echo $header_info; ?></p>
              <?php else: ?>
                <h2><?php echo __('Information'); ?></h2>
                <hr/>
                <p><?php echo $info; ?></p>
              <?php endif; ?>

              <br/>

              <!-- Show if clustering search is enabled
              ============================================= -->
              <?php
                if(!isset($_GET['p'])) :
                  if ($sysconf['enable_search_clustering']) : ?>
                  <h2><?php echo __('Search Cluster'); ?></h2>
                  <hr/>
                  <div id="search-cluster">
                    <div class="cluster-loading"><?php echo __('Generating search cluster...');  ?></div>
                  </div>

                  <script type="text/javascript">
                    $('document').ready( function() {
                      $.ajax({
                        url     : 'index.php?p=clustering&q=<?php echo urlencode($criteria); ?>',
                        type    : 'GET',
                        success : function(data, status, jqXHR) {
                                    $('#search-cluster').html(data);
                                  }
                      });
                    });
                  </script>

                  <?php endif; ?>
                <?php endif ?>
            </div>
          </div>
        </div>

  </main>

<?php else: ?>
  <!-- Homepage
  ============================================= -->
  <main id="content" class="s-main" role="main">

        <!-- Search form
        ============================================= -->
        <div class="s-main-search animated fadeInUp delay1">
          <form action="index.php" method="get" autocomplete="off">
            <h1 class="animated fadeInUp delay2">SEARCHING</h1>
            <p class="s-search-info animated fadeInUp delay3">you can start it by typing one or more keywords for title, author or subject</p>
            <input type="text" class="s-search animated fadeInUp delay4" name="keywords" value="" lang="<?php echo $sysconf['default_lang']; ?>" x-webkit-speech="x-webkit-speech">
            <button type="submit" name="search" value="search" class="s-btn animated fadeInUp delay4">Search</button>
          </form>
        </div>

        <!-- Featured
        ============================================= -->
        <a href="#" class="s-feature animated fadeInUp delay6">see also our featured collections
          <div class="s-menu-toggle animated fadeInUp delay7"><span></span></div>
        </a>
        <div class="s-feature-content">

        </div>


  </main>
<?php endif; ?>

<!-- Footer
============================================= -->
<?php include "footer.php"; ?>

<!-- Background
============================================= -->
<?php include "bg.php"; ?>

</body>
</html>
