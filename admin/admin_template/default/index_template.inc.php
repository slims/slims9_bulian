<?php 
  include 'function.php'; 
?>
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
  <script type="text/javascript" src="<?php echo JWB; ?>tinymce/tiny_mce.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>keyboard.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>chosen/chosen.jquery.min.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>chosen/ajax-chosen.min.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>tooltipsy.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>colorbox/jquery.colorbox-min.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>jquery.imgareaselect/scripts/jquery.imgareaselect.pack.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>webcam.js"></script>
  <script type="text/javascript" src="<?php echo JWB; ?>scanner.js"></script>
  <script type="text/javascript" src="<?php echo AWB; ?>admin_template/<?php echo $sysconf['admin_template']['theme']?>/assets/vendor/slimscroll/jquery.slimscroll.min.js"></script>  
</head>

<body>
  <aside class="s-sidebar">
    <nav class="s-menu" role="navigation">
      <header class="s-header">
        <div class="s-user">
          <div class="s-user-frame">            
            <a href="<?php echo AWB; ?>" class="s-user-photo">
              <img src="<?php echo SWB.'lib/minigalnano/createthumb.php?filename=../../images/persons/'.urlencode($_SESSION['upict']); ?>&amp;width=100" alt="Photo <?php echo $_SESSION['realname']?>">
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
    <a name="top"></a>
    <div class="loader"><?php echo $info;?></div>
    <div id="main">
      <div id="mainContent">
      <?php 
        if(isset($_GET['mod']) && ($_GET['mod'] == 'system')) {
          include "modules/system/index.php";
        } else {
          echo $main_content;        
        }
      ?>
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
    $('.s-current-child').click(function(){
      $('.s-current').trigger('click');
    });
  </script>

  <?php echo $chat?>


</body>

</html>