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
    <h4 class="text-secondary text-center text-thin mt-5 mb-4">Select the topic you are interested</h4>
    <ul class="topic d-flex flex-wrap justify-content-center">
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

<section class="mt-5 container">
    <h4 class=" mb-4">
        Popular in our collection.
        <br>
        <small class="subtitle-section">Singulis noster incididunt eu pariatur tempor veniam litteris dolor.</small>
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

    <div class="card-deck mt-4 collection">
      <?php
      // ------------------------------------------------------------------------
      // get popular title by loan
      // ------------------------------------------------------------------------
      $populars = getPopularBiblio($dbs, 6);
      foreach ($populars as $p) {
        $o = '';
        $o .= '<a href="index.php?p=show_detail&id='.$p['biblio_id'].'" class="card border-0 hover:shadow cursor-pointer text-decoration-none">';
        $o .= '<div class="card-body">';
        $o .= '<div class="card-image fit-height">';
        $o .= '<img src="' . getImagePath($sysconf, $p['image']) . '" class="img-fluid" alt="">';
        $o .= '</div>';
        $o .= '<div class="card-text mt-2 text-grey-darker">';
        $o .= truncate($p['title'], 30);
        $o .= '</div>';
        $o .= '</div>';
        $o .= '</a>';
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

<section class="mt-5 container">
    <h4 class=" mb-4">
        New collection + updated.
        <br>
        <small class="subtitle-section">Senserit malis duis iudicem legam an quem si probant ea quae.</small>
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

    <div class="card-deck mt-4 collection">
      <?php
      // ------------------------------------------------------------------------
      // get popular title by loan
      // ------------------------------------------------------------------------
      $latest = getLatestBiblio($dbs, 6);
      foreach ($latest as $l) {
        $o = '';
        $o .= '<a href="index.php?p=show_detail&id='.$l['biblio_id'].'"  class="card border-0 hover:shadow cursor-pointer text-decoration-none">';
        $o .= '<div class="card-body">';
        $o .= '<div class="card-image fit-height">';
        $o .= '<img src="' . getImagePath($sysconf, $l['image']) . '" class="img-fluid" alt="">';
        $o .= '</div>';
        $o .= '<div class="card-text mt-2 text-grey-darker">';
        $o .= truncate($l['title'], 30);
        $o .= '</div>';
        $o .= '</div>';
        $o .= '</a>';
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

<section class="mt-5 bg-white">
    <div class="container py-5">
        <h4 class="mb-4">
            Top reader of the year.
            <br>
            <small class="subtitle-section">Noster voluptate ita distinguantur aut est velit reprehenderit.</small>
        </h4>
        <div class="card-deck">
          <?php
          $members = getActiveMembers($dbs, date('Y'));
          foreach ($members as $member) {
            $m = '<div class="card border-0 shadow">';
            $m .= '<div class="card-body">';
            $m .= '<div class="card-image-rounded mx-auto">';
            $m .= '<img src="images/persons/' . $member['image'] . '" class="img-fluid" alt="photo">';
            $m .= '</div>';
            $m .= '<h5 class="card-title text-center mt-3">' . $member['name'] . '<br><small>' . $member['type'] . '</small></h5>';
            $m .= '<p class="card-text text-center">Nescius culpa deserunt laborum, vidisse sunt legam quamquam esse.</p>';
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

<section class="my-5 container">
    <div class="row align-items-center">
        <div class="col-md-6">
            <iframe class="embed-responsive"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.288723306273!2d106.80038831428296!3d-6.225610995493402!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f14efd9abf05%3A0x1659580cc6981749!2sPerpustakaan+Kemendikbud!5e0!3m2!1sid!2sid!4v1516601731218"
                    height="420" frameborder="0" style="border:0" allowfullscreen></iframe>
        </div>
        <div class="col-md-6">
            <h4><?php echo $sysconf['library_name']; ?></h4>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque et nunc mi. Donec vehicula turpis a
                quam venenatis posuere. Aliquam nibh lectus, gravida et leo sit amet, dignissim dapibus mauris.</p>
            <p class="mb-2">Telp. (021) 9172638<br>Fax. (021) 9172638</p>
            <p class="d-flex flex-row">
                <a href="javascript:void(0)" class="btn btn-primary mr-2" name="button"><i class="fab fa-facebook-square text-white"></i></a>
                <a href="javascript:void(0)" class="btn btn-info mr-2" name="button"><i class="fab fa-twitter-square text-white"></i></a>
                <a href="javascript:void(0)" class="btn btn-danger mr-2" name="button"><i class="fab fa-youtube text-white"></i></a>
            </p>
        </div>
    </div>
</section>
