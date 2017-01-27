<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-15 16:12:37
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-21 15:37:37
 */

?>

<div class="slims-card slims-card--default simple-search">
  <div class="slims-card--header">
    <h4><?php echo __('SEARCH'); ?></h4>
  </div>
	<form action="index.php" method="get" autocomplete="off">
    <div class="marquee down">
      <p class="s-search-info">
      <?php echo __('start it by typing one or more keywords for title, author or subject'); ?>
      <!--
      <?php echo __('use logical search "title=library AND author=robert"'); ?>
      <?php echo __('just click on the Search button to see all collections'); ?>
      -->
      </p>
    </div>
    <input type="text" placeholder="<?php echo __('Keyword'); ?>" class="s-search animated fadeInUp delay4" id="keyword" name="keywords" value="" lang="<?php echo $sysconf['default_lang']; ?>" aria-hidden="true" autocomplete="off">
    <button type="submit" name="search" value="search" class="slims-button slims-button--blue"><?php echo __('Search'); ?></button>
    <div id="fkbx-spch" tabindex="0" aria-label="Telusuri dengan suara" style="display: block;"></div>
  </form>
</div>