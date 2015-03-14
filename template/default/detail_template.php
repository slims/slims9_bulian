<div class="row-fluid coll-detail">
  <div class="span2">
      <div class="cover">
      <?php print $image ?>
      </div>
      <br/>
    <a target="_blank" href="index.php?p=show_detail&inXML=true&id=<?php echo $biblio_id ?>" class="btn btn-mini btn-danger" title="View record detail description in XML Format">XML</a>
  </div>
  <div class="span10" itemscope itemtype="http://schema.org/Book" vocab="http://schema.org/" typeof="Book">
    <h4 class="title" itemprop="name" property="name"><?php print $title ?></h4>
    <div><?php print $social_shares ?></div>
    <span class="abstract" itemprop="description" property="description">
    <hr/>
    <?php print $notes ?>
    <hr/>
    </span>
    <div class="form-horizontal">
      <div class="control-group">
        <label class="control-label key"><?php print __('Statement of Responsibility'); ?></label>
        <div class="controls" itemprop="author" property="author"><?php print $sor ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Author(s)'); ?></label>
        <div class="controls" itemprop="author" property="author" itemscope itemtype="http://schema.org/Person"><?php print $authors ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Edition'); ?></label>
        <div class="controls" itemprop="bookEdition" property="bookEdition"><?php print $edition ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Call Number'); ?></label>
        <div class="controls"><?php print $call_number ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('ISBN/ISSN'); ?></label>
        <div class="controls" itemprop="isbn" property="isbn"><?php print $isbn_issn ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Subject(s)'); ?></label>
        <div class="controls" itemprop="keywords" property="keywords"><?php print $subjects ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Classification'); ?></label>
        <div class="controls"><?php print $classification ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Series Title'); ?></label>
        <div class="controls" itemprop="alternativeHeadline" property="alternativeHeadline"><?php print $series_title ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('GMD'); ?></label>
        <div class="controls" itemprop="bookFormat" property="bookFormat"><?php print $gmd_name ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Content Type'); ?></label>
        <div class="controls" itemprop="bookFormat" property="bookFormat"><?php if ($content_type) : print $content_type; else : print __('No data'); endif; ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Media Type'); ?></label>
        <div class="controls" itemprop="bookFormat" property="bookFormat"><?php if ($media_type) : print $media_type; else : print __('No data'); endif; ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Carrier Type'); ?></label>
        <div class="controls" itemprop="bookFormat" property="bookFormat"><?php if ($carrier_type) : print $carrier_type; else : print __('No data'); endif; ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Language'); ?></label>
        <div class="controls"><meta itemprop="inLanguage" property="inLanguage" content="<?php print $language_name ?>" /><?php print $language_name ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Publisher'); ?></label>
        <div class="controls" itemprop="publisher" property="publisher" itemtype="http://schema.org/Organization" itemscope><?php print $publisher_name ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Publishing Year'); ?></label>
        <div class="controls" itemprop="datePublished" property="datePublished"><?php print $publish_year ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Publishing Place'); ?></label>
        <div class="controls" itemprop="publisher" property="publisher"><?php print $publish_place ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Collation'); ?></label>
        <div class="controls" itemprop="numberOfPages" property="numberOfPages"><?php print $collation ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Specific Detail Info'); ?></label>
        <div class="controls"><?php if ($spec_detail_info) : print $spec_detail_info; else : print __('No data'); endif; ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('File Attachment'); ?></label>
        <div class="controls" itemprop="associatedMedia"><?php if ($file_att) : print $file_att; else : print '<p class="bg-danger">'.__('No attachment data').'</p>'; endif; ?></div>
      </div>
      <div class="control-group">
        <label class="control-label key"><?php print __('Availability'); ?></label>
        <div class="controls"><?php if ($availability) : print $availability; else : print '<p class="bg-danger">'.__('No copy data').'</p>'; endif; ?></div>
      </div>
    </div>
  </div>
  <div class="clearfix"></div>
  <?php echo showComment($biblio_id); ?>
</div>