<div class="s-video animated fadeIn">
  <div class="gradients">
    <?php if($sysconf['template']['run_gradient_animation']) : ?>
    <div class="green"></div>
    <div class="beach"></div>
    <div class="mint"></div>
    <div class="purple"></div>
    <div class="default"></div>
    <div class="pink current"></div>
    <?php endif; ?>
    <div class="blue"></div>
  </div>
  <video loop autoplay muted>
    <source src='<?php echo $sysconf['template']['dir']; ?>/default/video/bg.mp4' type='video/mp4' />
    Your browser does not support the video tag.
  </video>
</div>
<?php if($sysconf['template']['run_gradient_animation']) : ?>
<script>
$(document).ready(function(){

    // Animate background color
    // ============================================
    var bg = $('.gradients');
    function fade() {
      var divs = bg.children();
      $(".current").transition({opacity: 1}, 5000, 'linear', function() {
        $('.current').removeClass('current');
        firstDiv = divs.first();
        firstDiv.addClass('current').css({opacity: 0});
        firstDiv.appendTo(bg);
        fade();
      });
    }
    fade();  
});
</script>
<?php endif; ?>