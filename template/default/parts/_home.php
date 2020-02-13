<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2018-01-23T11:27:04+07:00
# @Email:  ido.alit@gmail.com
# @Filename: _home.php
# @Last modified by:   user
# @Last modified time: 2018-01-26T18:43:45+07:00

?>

<section id="section1 container-fluid">
    <header class="c-header">
        <div class="mask"></div>
      <?php
      // ------------------------------------------------------------------------
      // include navbar
      // ------------------------------------------------------------------------
      include '_navbar.php'; ?>
    </header>
  <?php
  // --------------------------------------------------------------------------
  // include search form part
  // --------------------------------------------------------------------------
  include '_search-form.php'; ?>
</section>

<section class="mt-5 container">
    <h4 class="text-secondary text-center text-thin mt-5 mb-4">Select the topic you are interested in</h4>
    <ul class="topic d-flex flex-wrap justify-content-center px-0">
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=8&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/8-books.png'); ?>" width="80" class="mb-3 mx-auto"/>
                literature
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=3&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/3-diploma.png'); ?>" width="80" class="mb-3 mx-auto"/>
                social sciences
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=6&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/6-blackboard.png'); ?>" width="80" class="mb-3 mx-auto"/>
                applied sciences
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=7&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/7-quill.png'); ?>" width="80" class="mb-3 mx-auto"/>
                art and recreation
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="javascript:void(0)" class="d-flex flex-column" data-toggle="modal" data-target="#exampleModal">
                <img src="<?php echo assets('images/icon/grid_icon.png'); ?>" width="80"
                     class="mb-3 mx-auto"/>
                see more.
            </a>
        </li>
    </ul>
</section>

<?php if ($sysconf['template']['classic_popular_collection']) : ?>
<section class="mt-5 container">
    <h4 class=" mb-4">
        Popular among our collections.
        <br>
        <small class="subtitle-section">Our library's line of collection that have been favoured by our users were shown here. Look for them. Borrow them. Hope you also like them.</small>
    </h4>
    <div class="d-flex flex-wrap">
      <?php
      // ------------------------------------------------------------------------
      // get popular topic
      // ------------------------------------------------------------------------
      $topics = getPopularTopic($dbs);
      foreach ($topics as $topic) {
        echo '<a href="index.php?subject='.$topic.'&search=search" class="btn btn-outline-secondary btn-rounded btn-sm mr-2 mb-2">' . $topic . '</a>';
      }
      ?>
    </div>

    <div class="flex flex-wrap mt-4 collection">
      <?php
      // ------------------------------------------------------------------------
      // get popular title by loan
      // ------------------------------------------------------------------------
      $populars = getPopularBiblio($dbs, $sysconf['template']['classic_popular_collection_item']);
      foreach ($populars as $p) {
        $o = '<div class="w-48 pr-4 pb-4">';
        $o .= '<a href="index.php?p=show_detail&id='.$p['biblio_id'].'" class="card border-0 hover:shadow cursor-pointer text-decoration-none h-full">';
        $o .= '<div class="card-body">';
        $o .= '<div class="card-image fit-height">';
        $o .= '<img src="' . getImagePath($sysconf, $p['image']) . '" class="img-fluid" alt="">';
        $o .= '</div>';
        $o .= '<div class="card-text mt-2 text-grey-darker">';
        $o .= truncate($p['title'], 30);
        $o .= '</div>';
        $o .= '</div>';
        $o .= '</a></div>';
        echo $o;
      }
      ?>
<!--        <div class="card border-0 bg-transparent">-->
<!--            <div class="card-body">-->
<!--                <a href="#" class="d-flex flex-column justify-content-center link-see-more">-->
<!--                    <img src="--><?php //echo assets('images/icon/ios7-arrow-thin-right.png'); ?><!--" width="60%" class="mb-3"/>-->
<!--                    <span>see more.</span>-->
<!--                </a>-->
<!--            </div>-->
<!--        </div>-->
    </div>

</section>
<?php endif; ?>

<?php if ($sysconf['template']['classic_new_collection']) : ?>
<section class="mt-5 container">
    <h4 class=" mb-4">
        New collections + updated.
        <br>
        <small class="subtitle-section">These are new collections list. Hope you like them. Maybe not all of them are new. But in term of time, we make sure that these are fresh from our processing oven.</small>
    </h4>
    <div class="d-flex flex-wrap">
      <?php
      // ------------------------------------------------------------------------
      // get latest topic
      // ------------------------------------------------------------------------
      $topics = getLatestTopic($dbs);
      foreach ($topics as $topic) {
        echo '<a href="index.php?subject='.$topic.'&search=search" class="btn btn-outline-secondary btn-rounded btn-sm mr-2 mb-2">' . $topic . '</a>';
      }
      ?>
    </div>

    <div class="flex flex-wrap mt-4 collection">
      <?php
      // ------------------------------------------------------------------------
      // get popular title by loan
      // ------------------------------------------------------------------------
      $latest = getLatestBiblio($dbs, $sysconf['template']['classic_new_collection_item']);
      foreach ($latest as $l) {
        $o = '<div class="w-48 pr-4 pb-4">';
        $o .= '<a href="index.php?p=show_detail&id='.$l['biblio_id'].'"  class="card border-0 hover:shadow cursor-pointer text-decoration-none h-full">';
        $o .= '<div class="card-body">';
        $o .= '<div class="card-image fit-height">';
        $o .= '<img src="' . getImagePath($sysconf, $l['image']) . '" class="img-fluid" alt="">';
        $o .= '</div>';
        $o .= '<div class="card-text mt-2 text-grey-darker">';
        $o .= truncate($l['title'], 30);
        $o .= '</div>';
        $o .= '</div>';
        $o .= '</a></div>';
        echo $o;
      }
      ?>
<!--        <div class="card border-0 bg-transparent">-->
<!--            <div class="card-body">-->
<!--                <a href="#" class="d-flex flex-column justify-content-center link-see-more">-->
<!--                    <img src="--><?php //echo assets('images/icon/ios7-arrow-thin-right.png'); ?><!--" width="60%" class="mb-3"/>-->
<!--                    <span>see more.</span>-->
<!--                </a>-->
<!--            </div>-->
<!--        </div>-->
    </div>

</section>
<?php endif; ?>

<?php if ($sysconf['template']['classic_top_reader']) : ?>
<section class="mt-5 bg-white">
    <div class="container py-5">
        <h4 class="mb-4">
            Top reader of the year.
            <br>
            <small class="subtitle-section">Our best users, readers, so far. Continue to read if you want your name being mentioned here.</small>
        </h4>
        <div class="flex flex-wrap">
          <?php
          $members = getActiveMembers($dbs, date('Y'));
          foreach ($members as $member) {
            $member_image = $member['image'] ?? 'person.png';
            $m = '<div class="w-full md:w-1/3 px-3 mb-2">';
            $m .= '<div class="card hover:shadow-md">';
            $m .= '<div class="card-body">';
            $m .= '<div class="card-image-rounded mx-auto">';
            $m .= '<img src="'.getImagePath($sysconf, $member_image, 'persons').'" class="img-fluid h-auto" alt="photo">';
            $m .= '</div>';
            $m .= '<h5 class="card-title text-center mt-3">' . $member['name'] . '<br><small class="text-grey-darker">' . $member['type'] . '</small></h5>';
            $m .= '<p class="card-text text-center"><b>'.$member['total'].'</b> <span class="text-grey-darker">Loans</span><span style="width: 1px" class="inline-block h-4 mx-3 relative bg-grey align-middle"></span><b>'.$member['total_title'].'</b> <span class="text-grey-darker">Title</span></p>';
            $m .= '</div>';
            $m .= '</div>';
            $m .= '</div>';

            echo $m;
          }

          if (count($members) < 1) {
            echo '<span class="ml-3">Not Available.</span>';
          }
          ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($sysconf['template']['classic_map']) : ?>
<section class="my-5 container">
    <div class="row align-items-center">
        <div class="col-md-6">
            <iframe class="embed-responsive"
                    src="<?= $sysconf['template']['classic_map_link']; ?>"
                    height="420" frameborder="0" style="border:0" allowfullscreen></iframe>
        </div>
        <div class="col-md-6 pt-8 md:pt-0">
            <h4><?= $sysconf['library_name']; ?></h4>
            <p><?= $sysconf['template']['classic_map_desc']; ?></p>
            <p class="d-flex flex-row pt-2">
                <a target="_blank" href="<?= $sysconf['template']['classic_fb_link'] ?>" class="btn btn-primary mr-2" name="button"><i class="fab fa-facebook-square text-white"></i></a>
                <a target="_blank" href="<?= $sysconf['template']['classic_twitter_link'] ?>" class="btn btn-info mr-2" name="button"><i class="fab fa-twitter-square text-white"></i></a>
                <a target="_blank" href="<?= $sysconf['template']['classic_youtube_link'] ?>" class="btn btn-danger mr-2" name="button"><i class="fab fa-youtube text-white"></i></a>
            </p>
        </div>
    </div>
</section>
<?php endif; ?>
