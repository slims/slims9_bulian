<div id="advance-search-wrapper" style="display:none;">
<div id="advance-search">
  <form action="index.php" method="get" class="form-horizontal form-search">
    <div class="control-group">
      <label class="label"><?php echo __('Author(s)'); ?></label>
      <div class="controls">
        <input type="text" name="author" class="form-control" placeholder="<?php echo __('Author(s)'); ?>" />
      </div>
    </div>

    <div class="control-group">
      <label class="label"><?php echo __('Subject(s)'); ?></label>
      <div class="controls">
        <input type="text" name="subject" class="form-control" placeholder="<?php echo __('Subject(s)'); ?>" />
      </div>
    </div>

    <div class="control-group">
      <label class="label"><?php echo __('ISBN/ISSN'); ?></label>
      <div class="controls">
        <input type="text" name="isbn" class="form-control" placeholder="<?php echo __('ISBN/ISSN'); ?>" />
      </div>
    </div>

    <div class="control-group">
    <label class="label"><?php echo __('GMD'); ?></label>
    <div class="controls">
      <select name="gmd" class="form-control"><?php echo $gmd_list; ?></select>
    </div>
    </div>

    <div class="control-group">
      <label class="label"><?php echo __('Collection Type'); ?></label>
      <div class="controls">
        <select name="colltype" class="form-control"><?php echo $colltype_list; ?></select>
      </div>
    </div>

    <div class="control-group">
      <label class="label"><?php echo __('Location'); ?></label>
      <div class="controls">
        <select name="location" class="form-control"> <?php echo $location_list; ?></select>
      </div>
    </div>

    <div class="control-group">
      <input type="hidden" name="searchtype" value="advance" />
      <input type="submit" name="search" value="<?php echo __('Search'); ?>" />
    </div>
  </form>
</div>
</div>
