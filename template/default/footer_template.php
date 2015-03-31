<?php 
if(isset($_GET['search']) || isset($_GET['p'])) {
  $footer_position = 'relative';
} else {
  $footer_position = 'fixed';
}

?>
<footer class="s-footer container" style="position: <?php echo $footer_position ?>;">
  <div class="row">
    <div class="col-lg-6">
      <div class="s-footer-tagline">
        <a href="//slims.web.id" target="_blank"><?php echo SENAYAN_VERSION; ?></a>
      </div>
    </div>
    <div class="col-lg-6">
      <ul class="s-footer-menu">
        <li><a target="_blank" rel="archives" href="//www.facebook.com/groups/senayan.slims">Facebook</a></li>
        <li><a target="_blank" rel="archives" href="//twitter.com/#!/slims_official">Twitter</a></li>
        <li><a target="_blank" rel="archives" href="//www.youtube.com/user/senayanslims">Youtube</a></li>
        <li><a target="_blank" rel="archives" href="//github.com/slims">Github</a></li>
        <li><a target="_blank" rel="archives" href="//slims.web.id/forum">Forum</a></li>
        <li><a target="_blank" rel="archives" href="index.php?rss=true" title="RSS" class="rss" >RSS</a></li>
      </ul>
    </div>
  </div>
</footer>
