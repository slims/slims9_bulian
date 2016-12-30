<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-15 22:24:57
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-21 15:37:55
 */

?>

<div class="slims-card slims-card--default">
	<div class="slims-card--header">
	  <h4><?php echo __('Select Language'); ?></h4>
	</div>
	<form name="langSelect" action="index.php" method="get">
      <select name="select_lang" id="select_lang"  onchange="document.langSelect.submit();">
        <?php echo $language_select; ?>
      </select>
    </form>
</div>