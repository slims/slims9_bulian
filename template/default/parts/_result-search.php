<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2018-01-23T11:32:46+07:00
# @Email:  ido.alit@gmail.com
# @Filename: _result-search.php
# @Last modified by:   user
# @Last modified time: 2018-01-26T16:53:58+07:00

?>

<div class="result-search">
    <section id="section1 container-fluid">
        <header class="c-header">
            <div class="mask"></div>
          <?php
          // ----------------------------------------------------------------------
          // include navbar part
          // ----------------------------------------------------------------------
          include '_navbar.php'; ?>
        </header>
      <?php
      // ------------------------------------------------------------------------
      // include search form part
      // ------------------------------------------------------------------------
      include '_search-form.php'; ?>
    </section>

    <section class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <h4><?= __('Filter by') ?></h4>
                <?= $engine->getFilter($opac, true) ?>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mt-1 mb-2 text-sm">
                    <div>
                        <?php
                        $keywords_info = '<span class="search-keyword-info" title="' . htmlentities($keywords) . '">' . ((strlen($keywords) > 30) ? substr($keywords, 0, 30) . '...' : $keywords) . '</span>';
                        $search_result_info = '<div class="search-found-info">';
                        $search_result_info .= __('Found <strong>{biblio_list->num_rows}</strong> from your keywords') . ': <strong class="search-found-info-keywords">' . $keywords_info . '</strong>';
                        $search_result_info .= '</div>';
                        echo str_replace('{biblio_list->num_rows}', $engine->getNumRows(), $search_result_info);
                        ?>
                    </div>
                    <div class="form-inline pl-3">
                        <label class="mr-2 font-weight-bold" for="result-sort"><?= __('Sort by') ?></label>
                        <select class="custom-select custom-select-sm" id="search-order"><?= $sort_select ?></select>
                        <form class="ml-2" method="POST" action="<?= $_SERVER['PHP_SELF'] . '?' . http_build_query(array_filter($_GET, fn($key) => $key !== 'csrf_token', ARRAY_FILTER_USE_KEY)) ?>">
                            <?php if(($_SESSION['LIST_VIEW'] ?? 'list') === 'list'): ?>
                                <input type="hidden" name="csrf_token" value="<?= $opac->getCsrf() ?>"/>
                                <input type="hidden" name="view" value="grid" />
                                <button type="submit" class="btn btn-sm btn-outline-secondary items-center flex py-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-grid" viewBox="0 0 16 16">
                                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                                    </svg>
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="view" value="list" />
                                <button type="submit" class="btn btn-sm btn-outline-secondary items-center flex py-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                                        <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z"/>
                                    </svg>
                                </button>
                            <?php endif; ?>
                            </form>
                    </div>
                </div>
                <div class="wrapper">
                    <?php
                    if (ENVIRONMENT == 'development' && !empty($engine->getError())) echo '<div class="alert alert-danger mt-2 text-center">' . $engine->getError() . '</div>';
                    // catch empty list
                    if (trim(strip_tags($main_content)) === '') {
                        echo '<div class="d-flex justify-content-center border-t">
                                <img src="'.assets('images/empty.svg').'" />
                              </div>
                              <div class="text-center text-danger"><strong>'.__('No Result').'.</strong> '.__('Please try again').'</div>';
                    } else {
                        echo $main_content;
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</div>
<?php if(($_SESSION['LIST_VIEW'] ?? 'list') === 'grid'): ?>
    <script>
        // This code modified from: https://www.seancdavis.com/posts/wait-until-all-images-loaded/
        $(document).ready(function () {
            // Images loaded is zero because we're going to process a new set of images.
            let imagesLoaded = 0;
            // Total images is still the total number of <img> elements on the page.
            let totalImages = $(".grid-item .img-thumbnail").length;

            // Step through each image in the DOM, clone it, attach an onload event
            // listener, then set its source to the source of the original image. When
            // that new image has loaded, fire the imageLoaded() callback.
            $(".grid-item .img-thumbnail").each(function (idx, img) {
                $("<img>").on("load", imageLoaded).attr("src", $(img).attr("src"));
            });

            // Do exactly as we had before -- increment the loaded count and if all are
            // loaded, call the allImagesLoaded() function.
            function imageLoaded() {
                imagesLoaded++;
                if (imagesLoaded == totalImages) {
                allImagesLoaded();
                }
            }

            function allImagesLoaded() {
                $('.biblioResult').addClass('row').masonry({ itemSelector: '.grid-item', columnWidth: '.grid-item' })
                $('.dropdown-toggle').dropdown()
            }
        });
    </script>
<?php endif; ?>