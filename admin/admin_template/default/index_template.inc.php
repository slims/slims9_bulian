<?php
/**
 * Template for Backend
 *
 * Copyright (C) 2015 Arie Nugraha (dicarve@gmail.com), Eddy Subratha (eddy.subratha@slims.web.id)
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

// Need to modified script to adaptive new theme
include 'function.php';
?>
<!-- =====================================================================
 ___  __    ____  __  __  ___      __    _  _    __    ___  ____    __
/ __)(  )  (_  _)(  \/  )/ __)    /__\  ( )/ )  /__\  / __)(_  _)  /__\
\__ \ )(__  _)(_  )    ( \__ \   /(__)\  )  (  /(__)\ \__ \ _)(_  /(__)\
(___/(____)(____)(_/\/\_)(___/  (__)(__)(_)\_)(__)(__)(___/(____)(__)(__)

========================================================================== -->
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
  <title><?php echo $page_title; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" />
  <meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT" />

  <link rel="icon" href="<?php echo SWB; ?>webicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="<?php echo SWB; ?>webicon.ico" type="image/x-icon" />
  <link href="<?php echo SWB; ?>template/core.style.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo JWB; ?>colorbox/colorbox.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo JWB; ?>chosen/chosen.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo JWB; ?>jquery.imgareaselect/css/imgareaselect-default.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo $sysconf['admin_template']['css']; ?>" rel="stylesheet" type="text/css" />

  <script type="text/javascript" src="<?php echo JWB; ?>jquery.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>updater.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>gui.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>form.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>calendar.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>ckeditor/ckeditor.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>keyboard.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>chosen/chosen.jquery.min.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>chosen/ajax-chosen.min.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>tooltipsy.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>colorbox/jquery.colorbox-min.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>jquery.imgareaselect/scripts/jquery.imgareaselect.pack.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>webcam.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>scanner.js"></script>
  <script type="text/javascript" src="<?php echo AWB; ?>admin_template/<?php echo $sysconf['admin_template']['theme']?>/assets/vendor/slimscroll/jquery.slimscroll.min.js"></script>
  <?php if($sysconf['chat_system']['enabled']) : ?>
  <script src="<?php echo JWB; ?>fancywebsocket.js"></script>
  <?php endif; ?>
</head>

<body>
  <aside class="s-sidebar">
    <nav class="s-menu" role="navigation">
      <header class="s-header">
        <div class="s-user">
          <div class="s-user-frame">
            <a href="<?php echo MWB.'system/app_user.php?changecurrent=true&action=detail'; ?>" class="s-user-photo">
              <img src="<?php echo '../lib/minigalnano/createthumb.php?filename=../../'.IMG.'/persons/'.urlencode(urlencode($_SESSION['upict'])).'&width=200'?>" alt="Photo <?php echo $_SESSION['realname']?>">
            </a>
          </div>
          <h4 class="s-user-name"><?php echo $_SESSION['realname']?></h4>
        </div>
      </header>
      <div id="mainMenu"><?php main_menu(); ?>
      </div>
    </nav>
  </aside>
  <main class="s-content" role="main">
    <div class="loader"><?php echo $info;?></div>
    <a href="#" name="top" class="s-help"><i class="fa fa-question-circle"></i></a>
    <div id="main">
      <div class="left">
        <div class="s-help-header"><?php echo __('Help'); ?></div>
        <div class="s-help-content">
          <!-- Place to put documentation -->
        </div>
      </div>
      <div class="right">
        <div id="mainContent">
          <?php
            if(isset($_GET['mod']) && ($_GET['mod'] == 'system')) {
              include "modules/system/index.php";
              echo "<script>$('#mainForm').attr('action','".AWB."modules/system/index.php');</script>";
            } else {
              echo $main_content;
            }
          ?>
        </div>
      </div>
    </div>
    <footer class="s-footer">
      <div class="s-footer-about"><a href="http://www.slims.web.id/" target="_blank"><?php echo SENAYAN_VERSION; ?></a></div>
      <div class="s-footer-brand"><?php echo $sysconf['library_name'].' - '.$sysconf['library_subname']?> </div>
    </footer>
  </main>

  <!-- fake submit iframe for search form, DONT REMOVE THIS! -->
  <iframe name="blindSubmit" style="visibility: hidden; width: 0; height: 0;"></iframe>
  <!-- fake submit iframe -->
  <script>

    var toggleMainMenu = function() {
      $('.per_title').bind('click',function(){
        $('.s-content').toggleClass('active');
        $('.s-sidebar').toggleClass('active');
        $('.s-user-frame').toggleClass('active');
        $('.s-menu').toggleClass('active');
      });
    }

    //trigger to hide the current sidebar
    $('.s-current-child').click(function(){
      $('.s-current').trigger('click');
    });

    //create a help anchor by current menu
    $('.s-current-child').click(function(){
      $('.left, .right, .loader').removeClass('active');
      $('.s-help > i').removeClass('fa-times').addClass('fa-question-circle');
      $('.s-help-content').html();
      $('.s-help').removeClass('active');
      var get_url       = $(this).attr('href');
      var path_array    = get_url.split('/');
      var clean_path    = path_array[path_array.length-1].split('.');
      var new_pathname  = '<?php echo AWB?>help.php?url='+path_array[path_array.length-2]+'/'+clean_path[0]+'.md';
      $('.s-help').attr('href', new_pathname);
    });

    //generate help file
    $('.s-help').click(function(e){
      e.preventDefault();
      if($(this).attr('href') != '#') {
        // load active style
        $('.left, .right, .loader').toggleClass('active');
        $(this).toggleClass('active');
        $.ajax({
          type: 'GET',
          url: $(this).attr('href')
        }).done(function( data ) {
          $('.s-help-content').html(data);
          $('.s-help > i').toggleClass('fa-question-circle fa-times');
        });
      }else{
        alert('Help content will show according to available menu.')
      }
    });

    $('.s-user-photo').bind('click', function(e) {
      e.preventDefault();
      $('a.submenu-user-profile').trigger('click');
    });

    // toggle main menu event register
    toggleMainMenu();
    $('body').on('simbioAJAXloaded', function(evt) {
      toggleMainMenu();
    })

    $('#mainMenu a.opac').bind('click', function(evt) {
    	evt.preventDefault();
    	top.jQuery.colorbox({iframe:true,
    	  href: $(this).attr('href'),
          width: function() { return parseInt($(window).width())-50; },
          height: function() { return parseInt($(window).height())-50; },
          title: function() { return 'Online Public Access Catalog'; } }
        );
    });

    // hide menu if click on main content
    $('.s-content').click(function(){
      $('#mainMenu input[type=radio]').each(function(){
        $(this).removeAttr('checked');
      });
    })
  </script>
  <?php include "chat.php" ?>
</body>
</html>