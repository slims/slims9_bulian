<?php
/**
 * Template for OPAC
 *
 * Copyright (C) 2015 Arie Nugraha (dicarve@gmail.com)
 * Create by Eddy Subratha (eddy.subratha@slims.web.id)
 * 
 * Slims 8 (Akasia)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

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
<html lang="<?php echo substr($sysconf['default_lang'], 0, 2); ?>" xmlns="http://www.w3.org/1999/xhtml" prefix="og: http://ogp.me/ns#">
<head>

<?php
// Meta Template
include "partials/meta.php"; 
?>

</head>

<body itemscope="itemscope" itemtype="http://schema.org/WebPage">

<!--[if lt IE 9]>
<div class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</div>
<![endif]-->

<?php
// Header
include "partials/header.php"; 
?>

<?php 
// Navigation
include "partials/nav.php"; 
?>

<?php 
// Content
?>
<?php if(isset($_GET['search']) || isset($_GET['p'])): ?>
<section  id="content" class="s-main-page" role="main">

  <!-- Search on Front Page
  ============================================= -->
  <div class="s-main-search">
    <?php
    if(isset($_GET['p'])) {    
      switch ($_GET['p']) {
      case ''             : $page_title = __('Collections'); break;
      case 'show_detail'  : $page_title = __("Record Detail"); break;              
      case 'member'       : $page_title = __("Member Area"); break;              
      case 'member'       : $page_title = __("Member Area"); break;              
      default             : $page_title; break; }            
    } else {
      $page_title = __('Collections');  
    }
    ?>
    <h1 class="s-main-title animated fadeInUp delay1"><?php echo $page_title ?></h1>
    <form action="index.php" method="get" autocomplete="off">
      <input type="text" id="keyword" class="s-search animated fadeInUp delay4" name="keywords" value="" lang="<?php echo $sysconf['default_lang']; ?>" role="search">
      <button type="submit" name="search" value="search" class="s-btn animated fadeInUp delay4"><?php echo __('Search'); ?></button>
    </form>
  </div>

  <!-- Main
  ============================================= -->
  <div class="s-main-content container">
    <div class="row">

      <!-- Show Result
      ============================================= -->
      <div class="col-lg-8 col-sm-9 col-xs-12 animated fadeInUp delay2">

        <?php 
          // Generate Output
          // catch empty list
          if(strlen($main_content) == 7) {
            echo '<h2>No Result</h2><hr/><p>Please try again</p>';
          } else {
            echo $main_content;
          }

          // Somehow we need to hack the layout
          if(isset($_GET['search']) || (isset($_GET['p']) && $_GET['p'] != 'member')){
            echo '</div>'; 
          } else {
            if(isset($_SESSION['mid'])) {
              echo  '</div></div>';            
            }            
          }

        ?>

      <div class="col-lg-4 col-sm-3 col-xs-12 animated fadeInUp delay4">
        <?php if(isset($_GET['search'])) : ?>
        <h2><?php echo __('Search Result'); ?></h2>
        <hr>
        <?php echo $search_result_info; ?>
        <?php endif; ?>

        <br>

        <!-- If Member Logged
        ============================================= -->
        <h2><?php echo __('Information'); ?></h2>
        <hr/>
        <p><?php echo (utility::isMemberLogin()) ? $header_info : $info; ?></p>
        <br/>

        <!-- Show if clustering search is enabled
        ============================================= -->
        <?php
          if(isset($_GET['keywords']) && (!empty($_GET['keywords']))) :
            if (($sysconf['enable_search_clustering'])) : ?>
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
                  success : function(data, status, jqXHR) { $('#search-cluster').html(data); }
                });
              });
            </script>

            <?php endif; ?>
          <?php endif; ?>
      </div>
    </div>
  </div>

</section>

<?php else: ?>

<!-- Homepage
============================================= -->
<main id="content" class="s-main" role="main">

    <!-- Search form
    ============================================= -->
    <div class="s-main-search animated fadeInUp delay1">
      <form action="index.php" method="get" autocomplete="off">
        <h1 class="animated fadeInUp delay2"><?php echo __('SEARCHING'); ?></h1>
        <div class="marquee down">
          <p class="s-search-info">
          <?php echo __('start it by typing one or more keywords for title, author or subject'); ?>
          <!--
          <?php echo __('use logical search "title=library AND author=robert"'); ?>
          <?php echo __('just click on the Search button to see all collections'); ?>
          -->
          </p>
        </div>
        <input type="text" class="s-search animated fadeInUp delay4" id="keyword" name="keywords" value="" lang="<?php echo $sysconf['default_lang']; ?>" aria-hidden="true" autocomplete="off">
        <button type="submit" name="search" value="search" class="s-btn animated fadeInUp delay4"><?php echo __('Search'); ?></button>
        <div id="fkbx-spch" tabindex="0" aria-label="Telusuri dengan suara" style="display: block;"></div>
      </form>
    </div>

<?php endif; ?>

</main>


<?php 
// Footer
include "partials/footer.php"; 

// Chat Engine
include LIB."contents/chat.php"; 

// Background
include "partials/bg.php"; 
?>

<script>
  <?php if(isset($_GET['search']) && ($_GET['keywords']) != '') : ?>
  $('.biblioRecord .detail-list, .biblioRecord .title, .biblioRecord .abstract, .biblioRecord .controls').highlight(<?php echo $searched_words_js_array; ?>);
  <?php endif; ?>

  //Replace blank cover
  $('.book img').error(function(){
    var title = $(this).parent().attr('title').split(' ');
    $(this).parent().append('<div class="s-feature-title">' + title[0] + '<br/>' + title[1] + '<br/>... </div>');
    $(this).attr({
      src   : './template/default/img/book.png',  
      title : title + title[0] + ' ' + title[1]
    });
  });

  //Replace blank photo
  $('.librarian-image img').error(function(){
    $(this).attr('src','./template/default/img/avatar.jpg');
  });

  //Feature list slider
  function mycarousel_initCallback(carousel)
  {
    // Disable autoscrolling if the user clicks the prev or next button.
    carousel.buttonNext.bind('click', function() {
      carousel.startAuto(0);
    });

    carousel.buttonPrev.bind('click', function() {
      carousel.startAuto(0);
    });

    // Pause autoscrolling if the user moves with the cursor over the clip.
    carousel.clip.hover(function() {
      carousel.stopAuto();
    }, function() {
      carousel.startAuto();
    });
  };

  jQuery('#topbook').jcarousel({
      auto: 5,
      wrap: 'last',
      initCallback: mycarousel_initCallback
  });

$(window).scroll(function() {    
  console.log($(window).scrollTop());
  if ($(window).scrollTop() > 50) {
    $('.s-main-search').removeClass("animated fadeIn").addClass("animated fadeOut");
  } else {
    $('.s-main-search').removeClass("animated fadeOut").addClass("animated fadeIn");
  }  
});

</script>

</body>
</html>