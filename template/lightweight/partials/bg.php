<!-- Background 
============================================= -->
<div class="s-background">

  <!-- Gradient Effect 
  ============================================= -->
  <div class="gradients">
    <div class="default"></div>
  </div>

  <?php
    $sysconf['template']['background_mode'] = false;
    if($sysconf['template']['background_mode'] == 'image') : ?>
    <img class="bg-image" src='<?php echo $sysconf['template']['dir']; ?>/<?php echo $sysconf['template']['theme']; ?>/img/3.jpg'/>
  <?php else: ?>
    <div class="s-background-none"></div>
  <?php endif; ?>

</div>