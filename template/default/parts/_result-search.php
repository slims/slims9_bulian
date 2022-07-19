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
                <?= $engine->getFilter(true) ?>
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
                        <label class="mr-2 font-weight-bold" for="result-sort">Sort by</label>
                        <select class="custom-select custom-select-sm" id="search-order"><?= $sort_select ?></select>
                    </div>
                </div>
                <div class="wrapper">
                    <?php
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
