<header class="s-header container" role="banner">
  <div class="row">
    <div class="col-lg-10">
      <a href="index.php" class="s-brand">
        <?php
          if(isset($sysconf['logo_image']) && $sysconf['logo_image'] != '' && file_exists('images/default/'.$sysconf['logo_image'])){
            echo '<img class="s-logo animated flipInY delay7" src="lib/minigalnano/createthumb.php?filename=images/default/'.$sysconf['logo_image'].'&width=100" alt="'.$sysconf['library_name'].'">';
          }else{
            echo '<img class="s-logo animated flipInY delay7" src="'.$sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/img/logo.png" alt="'.$sysconf['library_name'].'" />';
          } ?>
        <h1 class="animated fadeInUp delay2"><?php echo $sysconf['library_name']; ?></h1>
        <div class="s-brand-tagline animated fadeInUp delay3"><?php echo $sysconf['library_subname']; ?></div>
      </a>
    </div>
    <div class="col-lg-2">
      <div class="s-pmenu">
        <div class="s-menu animated fadeInUp delay4">

        <div class="hamburger hamburger--3dy s-menu-toggle" role="navigation">
          <div class="hamburger-box">
            <div class="hamburger-inner"></div>
          </div>
        </div>

        </div>
      </div>
    </div>
  </div>
</header>
