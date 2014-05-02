<?php
// biblio/record detail
// output the buffer
ob_start(); /* <- DONT REMOVE THIS COMMAND */
?>
<div class="row-fluid coll-detail">
  <div class="span2">
      <div class="cover">
      {image}
      </div>
      <br/>
    <a target="_blank" href="index.php?p=show_detail&inXML=true&id=<?php echo $_GET['id'];?>" class="btn btn-mini btn-danger">XML</a>
  </div>
  <div class="span10">
    <h4 class="title">{title}</h4>
    <div>{social_shares}</div>
    <span class="abstract">
    <hr/>
    {notes}
    <hr/>
    </span>
    <div class="form-horizontal">
      <div class="control-group">
        <label class="control-label key"><?php print __('Statement of Responsibility'); ?></label>
        <div class="controls">{sor}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Author(s)'); ?></label>
        <div class="controls">{authors}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Edition'); ?></label>
        <div class="controls">{edition}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Call Number'); ?></label>
        <div class="controls">{call_number}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('ISBN/ISSN'); ?></label>
        <div class="controls">{isbn_issn}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Subject(s)'); ?></label>
        <div class="controls">{subjects}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Classification'); ?></label>
        <div class="controls">{classification}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Series Title'); ?></label>
        <div class="controls">{series_title}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('GMD'); ?></label>
        <div class="controls">{gmd_name}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Language'); ?></label>
        <div class="controls">{language_name}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Publisher'); ?></label>
        <div class="controls">{publisher_name}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Publishing Year'); ?></label>
        <div class="controls">{publish_year}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Publishing Place'); ?></label>
        <div class="controls">{publish_place}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Collation'); ?></label>
        <div class="controls">{collation}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Specific Detail Info'); ?></label>
        <div class="controls">{spec_detail_info}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('File Attachment'); ?></label>
        <div class="controls">{file_att}</div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Availability'); ?></label>
        <div class="controls">{availability}</div>
      </div>
    </div>
  </div>
  <div class="clearfix"></div>
  <?php echo showComment($detail_id); ?>

</div>
<?php
// put the buffer to template var
$detail_template = ob_get_clean();
