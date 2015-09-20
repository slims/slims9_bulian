<header class="s-header container" role="banner">
  <div class="row">
    <div class="col-lg-6">
      <a href="index.php" class="s-brand">
        <img class="s-logo animated flipInY delay7" src="<?php echo $sysconf['template']['dir']; ?>/default/img/logo.png" alt="<?php echo $sysconf['library_name']; ?>" />
        <h1 class="animated fadeInUp delay2"><?php echo $sysconf['library_name']; ?></h1>
        <div class="s-brand-tagline animated fadeInUp delay3"><?php echo $sysconf['library_subname']; ?></div>
      </a>
    </div>
    <div class="col-6-lg" >
      <div class="s-pmenu">
        <?php 
          // show chat button when its activated by user
          if($sysconf['chat_system']['enabled'] && $sysconf['chat_system']['opac']) : 
        ?>
          <div class="s-pchat animated fadeInUp delay3">
            <a href="#" id="show-pchat" class="s-pchat-toggle" role="navigation" ><i class="fa fa-comment-o"></i></a>
          </div>
        <?php endif; ?>
        <div class="s-menu animated fadeInUp delay4">
          <a href="#" id="show-menu" class="s-menu-toggle" role="navigation"><span></span></a>
        </div>
      </div>
    </div>
  </div>
</header>
