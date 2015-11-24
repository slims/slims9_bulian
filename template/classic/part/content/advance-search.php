<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-15 20:11:13
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-21 15:38:21
 */

?>

<div class="slims-card slims-card--default">
	<div class="slims-card--header">
      <h4><?php echo __('Advanced Search'); ?></h4>
    </div>
     <form name="advSearchForm" id="advSearchForm" action="index.php" method="get" class="form-horizontal form-search">
        <div class="simply" >
          <div class="input-append">
          <input type="hidden" name="search" value="search" />
          <label class="control-label"><?php echo __('Title'); ?></label>
          <input type="text" name="title" id="title" class="input-xxlarge search-query" />
          </div>
        </div>
        <div class="advance">
          <div class="row-fluid">
          <div class="span5">
            <div class="control-group">
              <label class="control-label"><?php echo __('Author(s)'); ?></label>
              <div class="controls">
                <?php echo $advsearch_author; ?>
              </div>
            </div>

            <div class="control-group">
              <label class="control-label"><?php echo __('Subject(s)'); ?></label>
              <div class="controls">
                <?php echo $advsearch_topic; ?>
              </div>
            </div>

            <div class="control-group">
              <label class="control-label"><?php echo __('ISBN/ISSN'); ?></label>
              <div class="controls">
                <input type="text" name="isbn" />
              </div>
            </div>
          </div>
          <div class="span6">

            <div class="control-group">
            <label class="control-label"><?php echo __('GMD'); ?></label>
            <div class="controls">
              <select name="gmd"><?php echo $gmd_list; ?></select>
            </div>
            </div>

            <div class="control-group">
              <label class="control-label"><?php echo __('Collection Type'); ?></label>
              <div class="controls">
                <select name="colltype"><?php echo $colltype_list; ?></select>
              </div>
            </div>

            <div class="control-group">
              <label class="control-label"><?php echo __('Location'); ?></label>
              <div class="controls">
                <select name="location"> <?php echo $location_list; ?></select>
              </div>
            </div>
        </div>
        </div>
        <button type="submit" class="slims-button slims-button--blue" name="search" value="search"><?php echo __('Search'); ?></button>
        </form>
    </div>
</div>