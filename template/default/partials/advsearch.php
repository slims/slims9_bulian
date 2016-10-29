<div id="advance-search" class="container">

<div class="hamburger hamburger--3dy is-active" id="hide-advance-search" role="navigation">
  <div class="hamburger-box">
    <div class="hamburger-inner"></div>
  </div>
</div>


<h2>Advance Search</h2>
<div class="row">
  <form action="index.php" method="get" class="form-horizontal form-search">

    <div class="col-sm-6">
      <div class="control-group">
        <label class="label"><?php echo __('Title'); ?></label>
        <div class="controls">
          <input type="text" name="title" class="form-control" />
        </div>
      </div>
    </div>

    <div class="col-sm-6">
      <div class="control-group">
        <label class="label"><?php echo __('Author(s)'); ?></label>
        <div class="controls">
          <input type="text" name="author" class="form-control" />
        </div>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="col-sm-6">
      <div class="control-group">
        <label class="label"><?php echo __('Subject(s)'); ?></label>
        <div class="controls">
          <input type="text" name="subject" class="form-control" />
        </div>
      </div>
    </div>

    <div class="col-sm-6">
      <div class="control-group">
        <label class="label"><?php echo __('ISBN/ISSN'); ?></label>
        <div class="controls">
          <input type="text" name="isbn" class="form-control" placeholder="<?php echo __('ISBN/ISSN'); ?>" />
        </div>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="col-sm-6">
      <div class="control-group">
        <label class="label"><?php echo __('Collection Type'); ?></label>
        <div class="controls">
          <select name="colltype" class="form-control"><?php echo $colltype_list; ?></select>
        </div>
      </div>
    </div>

    <div class="col-sm-6">
      <div class="control-group">
        <label class="label"><?php echo __('Location'); ?></label>
        <div class="controls">
          <select name="location" class="form-control"> <?php echo $location_list; ?></select>
        </div>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="col-sm-6">
      <div class="control-group">
      <label class="label"><?php echo __('GMD'); ?></label>
      <div class="controls">
        <select name="gmd" class="form-control"><?php echo $gmd_list; ?></select>
      </div>
      </div>
    </div>

    <div class="clearfix"></div>
    <div class="col-sm-6">
      <div class="control-group">
        <label></label>
        <div class="controls">
          <input type="hidden" name="searchtype" value="advance" />
          <button type="submit" name="search" value="search" clas="btn btn-danger btn-block"><?php echo __('Search'); ?></button>
        </div>
      </div>
    </div>

  </form>
</div>
</div>
