<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2019-01-30 00:58
 * @File name           : detail_template.php
 */

?>

<div class="container">
    <div class="flex flex-wrap">
        <div class="w-64 mb-2">
            <div class="bg-grey-light p-12 rounded">
                <div class="shadow">
                  <?= $image; ?>
                </div>
            </div>
        </div>
        <div class="flex-1 p-0 px-md-4">
            <p class="lead"><i class="fas fa-bookmark text-green"></i> <?= $gmd_name; ?></p>
            <blockquote class="blockquote">
                <h4 class="mb-2"><?= $title; ?></h4>
                <footer class="blockquote-footer"><?= str_replace("<br />", '; ', $authors); ?></footer>
            </blockquote>
            <hr>
            <p class="text-grey-darker">
              <?= $notes ? $notes : '<i>Description not available</i>'; ?>
            </p>
            <hr>

            <h5 class="mt-4 mb-1"><?= __('Availability'); ?></h5>
          <?= ($availability) ? $availability : '<p class="text-grey-dark">' . __('No copy data') . '</p>'; ?>

            <h5 class="mt-4 mb-1"><?= __('Detail Information'); ?></h5>
            <dl class="row">
                <dt class="col-sm-3"><?= __('Series Title'); ?></dt>
                <dd class="col-sm-9">
                    <div itemprop="alternativeHeadline"
                         property="alternativeHeadline"><?php echo ($series_title) ? $series_title : '-'; ?></div>
                </dd>

                <dt class="col-sm-3"><?= __('Call Number'); ?></dt>
                <dd class="col-sm-9">
                    <div><?php echo ($call_number) ? $call_number : '-'; ?></div>
                </dd>
                <dt class="col-sm-3"><?= __('Publisher'); ?></dt>
                <dd class="col-sm-9">
                    <span itemprop="publisher" property="publisher" itemtype="http://schema.org/Organization"
                          itemscope><?php echo $publish_place ?></span> :
                    <span itemprop="publisher" property="publisher"><?php echo $publisher_name ?></span>.,
                    <span itemprop="datePublished" property="datePublished"><?php echo $publish_year ?></span>
                </dd>
                <dt class="col-sm-3"><?= __('Collation'); ?></dt>
                <dd class="col-sm-9">
                    <div itemprop="numberOfPages"
                         property="numberOfPages"><?php echo ($collation) ? $collation : '-'; ?></div>
                </dd>
                <dt class="col-sm-3"><?= __('Language'); ?></dt>
                <dd class="col-sm-9">
                    <div>
                        <meta itemprop="inLanguage" property="inLanguage"
                              content="<?php echo $language_name ?>"/><?php echo $language_name ?></div>
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
                    <div itemprop="bookFormat"
                         property="bookFormat"><?php echo ($content_type) ? $content_type : '-'; ?></div>
                </dd>
                <dt class="col-sm-3"><?= __('Media Type'); ?></dt>
                <dd class="col-sm-9">
                    <div itemprop="bookFormat"
                         property="bookFormat"><?php echo ($media_type) ? $media_type : '-'; ?></div>
                </dd>
                <dt class="col-sm-3"><?= __('Carrier Type'); ?></dt>
                <dd class="col-sm-9">
                    <div itemprop="bookFormat"
                         property="bookFormat"><?php echo ($carrier_type) ? $carrier_type : '-'; ?></div>
                </dd>
                <dt class="col-sm-3"><?= __('Edition'); ?></dt>
                <dd class="col-sm-9">
                    <div itemprop="bookEdition" property="bookEdition"><?php echo ($edition) ? $edition : '-'; ?></div>
                </dd>
                <dt class="col-sm-3"><?= __('Subject(s)'); ?></dt>
                <dd class="col-sm-9">
                    <div class="s-subject" itemprop="keywords"
                         property="keywords"><?php echo ($subjects) ? $subjects : '-'; ?></div>
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

          <?php if (count($biblio_custom) > 0) {
            ; ?>
              <h5 class="mt-4 mb-1"><?= __('Other Information'); ?></h5>
              <dl class="row">
                <?php foreach ($biblio_custom as $item) { ?>
                    <dt class="col-sm-3"><?= $item['label']; ?></dt>
                    <dd class="col-sm-9">
                        <div itemprop="alternativeHeadline"
                             property="alternativeHeadline"><?php echo ($item['value']) ? $item['value'] : '-'; ?></div>
                    </dd>
                <?php }; ?>
              </dl>
          <?php }; ?>

            <h5 class="mt-4 mb-1"><?= __('Other version/related'); ?></h5>
            <div>
              <?php echo ($related) ? $related : '<p class="text-grey-dark">' . __('No other version available') . '</p>'; ?>
            </div>

            <h5 class="mt-4 mb-1"><?= __('File Attachment'); ?></h5>
            <div itemprop="associatedMedia">
              <?php echo $file_att; ?>
            </div>

            <h5 class="mt-4 mb-1"><?= __('Comments'); ?></h5>
          <?php echo showComment($biblio_id); ?>
          <?php if(!isset($_SESSION['mid']) && $sysconf['comment']['enable']) : ?>
              <hr>
              <a href="index.php?p=member" class="btn btn-outline-primary">You must be logged in to post a comment</a>
          <?php endif; ?>
        </div>
    </div>
</div>
