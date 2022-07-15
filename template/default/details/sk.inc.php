<dl class="row">
    <dt class="col-sm-3"><?= __('Series Title'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="alternativeHeadline" property="alternativeHeadline"><?php echo ($series_title) ? $series_title : '-'; ?></div>
    </dd>

    <dt class="col-sm-3"><?= __('Call Number'); ?></dt>
    <dd class="col-sm-9">
        <div><?php echo ($call_number) ? $call_number : '-'; ?></div>
    </dd>
    <dt class="col-sm-3"><?= __('Publisher'); ?></dt>
    <dd class="col-sm-9">
        <span itemprop="publisher" property="publisher" itemtype="http://schema.org/Organization" itemscope><?php echo $publish_place ?></span> :
        <span itemprop="publisher" property="publisher"><?php echo $publisher_name ?></span>.,
        <span itemprop="datePublished" property="datePublished"><?php echo $publish_year ?></span>
    </dd>
    <dt class="col-sm-3"><?= __('Collation'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="numberOfPages" property="numberOfPages"><?php echo ($collation) ? $collation : '-'; ?></div>
    </dd>
    <dt class="col-sm-3"><?= __('Language'); ?></dt>
    <dd class="col-sm-9">
        <div>
            <meta itemprop="inLanguage" property="inLanguage" content="<?php echo $language_name ?>" /><?php echo $language_name ?>
        </div>
    </dd>
    <dt class="col-sm-3"><?= __('ISBN/ISSN'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="isbn" property="isbn"><?php echo ($isbn_issn) ? $isbn_issn : '-'; ?></div>
    </dd>
    <dt class="col-sm-3"><?= __('Classification'); ?></dt>
    <dd class="col-sm-9">
        <div><?php echo ($classification) ? $classification : '-'; ?></div>
    </dd>
    <dt class="col-sm-3"><?= __('Content Type'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="bookFormat" property="bookFormat"><?php echo ($content_type) ? $content_type : '-'; ?></div>
    </dd>
    <dt class="col-sm-3"><?= __('Media Type'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="bookFormat" property="bookFormat"><?php echo ($media_type) ? $media_type : '-'; ?></div>
    </dd>
    <dt class="col-sm-3"><?= __('Carrier Type'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="bookFormat" property="bookFormat"><?php echo ($carrier_type) ? $carrier_type : '-'; ?></div>
    </dd>
    <dt class="col-sm-3"><?= __('Edition'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="bookEdition" property="bookEdition"><?php echo ($edition) ? $edition : '-'; ?></div>
    </dd>
    <dt class="col-sm-3"><?= __('Subject(s)'); ?></dt>
    <dd class="col-sm-9">
        <div class="s-subject" itemprop="keywords" property="keywords"><?php echo ($subjects) ? $subjects : '-'; ?></div>
    </dd>
    <dt class="col-sm-3"><?= __('Specific Detail Info'); ?></dt>
    <dd class="col-sm-9">
        <div><?php echo ($spec_detail_info) ? $spec_detail_info : '-'; ?></div>
    </dd>
    <dt class="col-sm-3"><?= __('Statement of Responsibility'); ?></dt>
    <dd class="col-sm-9">
        <div itemprop="author" property="author"><?php echo ($sor) ? $sor : '-'; ?></div>
    </dd>
</dl>