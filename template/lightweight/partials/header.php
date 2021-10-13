<header class="s-header container" role="banner">
  <div class="row">
    <div class="col-lg-6">
      <a href="index.php" class="s-brand">
        <?php
          if(isset($sysconf['logo_image']) && $sysconf['logo_image'] != '' && file_exists('images/default/'.$sysconf['logo_image'])){
            echo '<img class="s-logo animated flipInY delay7" src="lib/minigalnano/createthumb.php?filename=images/default/'.$sysconf['logo_image'].'&width=100" alt="'.$sysconf['library_name'].'">';
          }else{
            echo '<img class="s-logo" src="'.$sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/img/logo.png" alt="'.$sysconf['library_name'].'" />';
          } ?>
        <h1><?php echo $sysconf['library_name']; ?></h1>
        <div class="s-brand-tagline"><?php echo $sysconf['library_subname']; ?></div>
      </a>
    </div>
    <div class="col-6-lg" >
      <div class="s-pmenu">
        <div class="s-menu">
          <a href="#" id="show-menu" class="s-menu-toggle" role="navigation"><span></span></a>
        </div>
      </div>
    </div>
  </div>
</header>
