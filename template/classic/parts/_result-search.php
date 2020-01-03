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
            <div class="col-md-8">
                <div class="wraper">
                  <?php
                  // catch empty list
                  if (strlen($main_content) == 7) {
                    echo '<h2 class="text-danger">' . __('No Result') . '</h2><hr/><p class="text-danger">' . __('Please try again') . '</p>';
                  } else {
                      echo $main_content;
                  }
                  ?>
                </div>
            </div>
            <div class="col-md-4">
                <h4 class="mb-2">Search Result</h4>
                <?php
                echo '<div class=" mb-4 text-sm">'.$search_result_info.'</div><hr>';
                ?>
<!--                <h6>Sugestion</h6>-->
<!--                <div class="card-list d-flex flex-column">-->
<!--                  --><?php
//                  for ($i = 0; $i < 5; $i++) {
//                    ?>
<!--                      <div class="card sugestion border-0 elevation-1 mb-2">-->
<!--                          <div class="card-body">-->
<!--                              <div class="container-img elevation-2">-->
<!--                                  <img src="images/docs/ajax.jpg" alt="image" class="img-fluid">-->
<!--                              </div>-->
<!--                              <div class="card-text title">-->
<!--                                  Ut malis arbitror qui fabulas magna litteris constias.-->
<!--                              </div>-->
<!--                              <div class="card-text author">-->
<!--                                  <i>Commodo enim - consequat est enim</i>-->
<!--                              </div>-->
<!--                          </div>-->
<!--                      </div>-->
<!--                    --><?php
//                  }
//                  ?>
<!--                </div>-->
            </div>
        </div>
    </section>
</div>
