<?php
/*------------------------------------------------------------

Template    : Slims Cendana Template
Create Date : March 2, 2013
Author      : Eddy Subratha (eddy.subratha{at}gmail.com)


This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA

-------------------------------------------------------------*/
// be sure that this file not accessed directly

if (!defined('INDEX_AUTH')) {
  die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
  die("can not access this file directly");
}
//set default index page
$p = 'home';

if (isset($_GET['p']))
{
 if ($_GET['p'] == 'libinfo') {
  $p = 'libinfo';
} elseif ($_GET['p'] == 'help') {
  $p = 'help';
} elseif ($_GET['p'] == 'member') {
  $p = 'member';
} elseif ($_GET['p'] == 'login') {
  $p = 'login';
} else {
  $p = strtolower(trim($_GET['p']));
}
}

/*----------------------------------------------------
  menu list
  you may modified as you need
  ----------------------------------------------------*/
  $menus = array (
    'home'   => array('url'  => 'index.php',
      'text' => __('Home')
      ),
    'libinfo'  => array('url'  => 'index.php?p=libinfo',
      'text' => __('Library Information')
      ),
    'member'   => array('url'  => 'index.php?p=member',
      'text' => __('Member Area')
      ),
    'librarian'   => array('url'  => 'index.php?p=librarian',
      'text' => __('Librarian')
      ),
    'help'   => array('url'  => 'index.php?p=help',
      'text' => __('Help on Search')
      ),
    'login'   => array('url'  => 'index.php?p=login',
      'text' => __('Librarian LOGIN')
      )
    );

/*----------------------------------------------------
  social button
  you may modified as you need.
  ----------------------------------------------------*/
  $social = array (
    'facebook'  => array('url'  => 'http://www.facebook.com/groups/senayan.slims/',
      'text' => 'Facebook'
      ),
    'twitter'  => array('url'  => 'http://twitter.com/#!/slims_official',
      'text' => 'Twitter'
      ),
    'youtube'  => array('url'  => 'http://www.youtube.com/user/senayanslims',
      'text' => 'Youtube'
      ),
    'gihub'  => array('url'  => 'https://github.com/slims/',
      'text' => 'Github'
      ),
    'forum'  => array('url'  => 'http://slims.web.id/forum/',
      'text' => 'Forum'
      )
    );
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $page_title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="SLiMS (Senayan Library Management System) is an open source Library Management System. It is build on Open source technology like PHP and MySQL">
<meta name="keywords" content="senayan,slims,library automation,free library application, library, perpustakaan, aplikasi perpustakaan">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="index, nofollow">
<!-- load style -->
<link rel="shortcut icon" href="webicon.ico" type="image/x-icon" />
<link href="<?php echo $sysconf['template']['dir']; ?>/core.style.css" rel="stylesheet" type="text/css" />
<link href="<?php echo JWB; ?>colorbox/colorbox.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $sysconf['template']['css']; ?>" rel="stylesheet" type="text/css" />
<link type="text/css" rel="stylesheet" media="all" href="<?php echo SWB; ?>template/default/css/tango/skin.css"/>
<?php echo $metadata; ?>
<script type="text/javascript" src="<?php echo JWB; ?>jquery.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>form.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>gui.js"></script>
<script type="text/javascript" src="<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme']; ?>/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo JWB; ?>colorbox/jquery.colorbox-min.js"></script>
<script type="text/javascript" src="<?php echo SWB; ?>template/default/js/jquery.jcarousel.min.js"></script>
</head>
<body>
  <div id="masking"></div>
  <!--// Social Button //-->
  <div class="navbar navbar-social navbar-fixed-top">
    <div class="navbar-inner">
      <div class="container">
        <?php if(isset($social) && count($social) > 0) { ?>
        <ul class="nav">
          <?php foreach ($social as $path => $menu) { ?>
          <li><a href="<?php echo $menu['url']; ?>" title="<?php echo $menu['text']; ?>" <?php if ($p == $path) {echo ' class="active"';} ?>><?php echo $menu['text']; ?></a></li>
          <?php } ?>
          <li><a href="index.php?rss=true" target="_blank" title="RSS" class="rss" ><img src="<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme']; ?>/img/rss.png" /></a></li>
        </ul>
        <?php } ?>
        <form class="navbar-form pull-right language" name="langSelect" action="index.php" method="get">
          <span class="language-info"><?php echo __('Select Language'); ?></span>
          <select name="select_lang" id="select_lang"  onchange="document.langSelect.submit();" class="input-medium">
            <?php echo $language_select; ?>
          </select>
        </form>
      </div>
    </div>
  </div>  <!--// End Social Button //-->

  <!--// Menu //-->
  <div class="navbar navbar-menu navbar-fixed-top">
    <div class="navbar-inner">
      <div class="container">
        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="brand" href="index.php">
         <div class="sitename"><?php echo $sysconf['library_name']; ?></div>
         <div class="subname"><?php echo $sysconf['library_subname']; ?></div>
        </a>
        <div class="nav-collapse collapse">
          <ul class="nav nav-pills pull-right">
          <?php foreach ($menus as $path => $menu) { ?>
          <li <?php if ($p == $path) {echo ' class="active"';} ?>><a href="<?php echo $menu['url']; ?>" title="<?php echo $menu['text']; ?>"><?php echo ucwords($menu['text']); ?></a></li>
          <?php } ?>
          </ul>
        </div>
      </div>
    </div>
  </div>  <!--// End Menu //-->

<!--// Content Ouput //-->
<div class="content">
  <div class="container">
    <div class="row">
      <!--// Check For No Query //-->
      <?php if(isset($_GET['search']) || isset($_GET['title']) || isset($_GET['keywords']) || isset($_GET['p'])) { ?>
        <!-- Main Content -->
        <div class="span8">
          <?php if(@$_GET['p'] != 'member') { ?>
          <div class="tagline">
            <?php if(!isset($_GET['p'])) { ?>
            <?php echo __('Collections'); ?>
            <?php } elseif ($_GET['p'] == 'show_detail') { ?>
            <?php echo __("Record Detail"); ?>
            <?php } else { ?>
            <?php echo $page_title; ?>
            <?php } ?>
            <a href="javascript: history.back();" class="btn btn-mini btn-danger pull-right"><i class="icon icon-white icon-circle-arrow-left"></i> <?php echo __('Back'); ?> </a>
          </div>
          <?php } ?>

          <?php if(!isset($_GET['p'])) { ?>
            <div class="search">
            <div id="simply-search">
              <div class="simply" >
                <form name="advSearchForm" id="simplySearchForm" action="index.php" method="get" class="form-search">
                  <div class="input-append">
                  <input type="hidden" name="search" value="search" />
                  <input type="text" name="keywords" id="keyword" placeholder="<?php echo __('Keyword'); ?>" lang="<?php echo $sysconf['default_lang']; ?>" x-webkit-speech="x-webkit-speech" class="input-xxlarge search-query" />
                  <button type="submit" class="btn"><?php echo __('Search'); ?></button>
                  </div>
                </form>
              </div>
            </div>
            <div id="advance-search" style="display:none;" >
             <form name="advSearchForm" id="advSearchForm" action="index.php" method="get" class="form-horizontal form-search">
                <div class="simply" >
                  <div class="input-append">
                  <input type="text" name="title" id="title" placeholder="<?php echo __('Title'); ?>" class="input-xxlarge search-query" />
                  <button type="submit" class="btn" name="search" value="search"><?php echo __('Search'); ?></button>
                  </div>
                </div>
                <div class="advance">
                  <div class="row-fluid">
                  <div class="span5">
                    <div class="control-group">
                      <label class="control-label"><?php echo __('Author(s)'); ?></label>
                      <div class="controls">
                        <?php echo $advsearch_author; ?>
                      </div>
                    </div>

                    <div class="control-group">
                      <label class="control-label"><?php echo __('Subject(s)'); ?></label>
                      <div class="controls">
                        <?php echo $advsearch_topic; ?>
                      </div>
                    </div>

                    <div class="control-group">
                      <label class="control-label"><?php echo __('ISBN/ISSN'); ?></label>
                      <div class="controls">
                        <input type="text" name="isbn" />
                      </div>
                    </div>
                  </div>
                  <div class="span6">

                    <div class="control-group">
                    <label class="control-label"><?php echo __('GMD'); ?></label>
                    <div class="controls">
                      <select name="gmd"><?php echo $gmd_list; ?></select>
                    </div>
                    </div>

                    <div class="control-group">
                      <label class="control-label"><?php echo __('Collection Type'); ?></label>
                      <div class="controls">
                        <select name="colltype"><?php echo $colltype_list; ?></select>
                      </div>
                    </div>

                    <div class="control-group">
                      <label class="control-label"><?php echo __('Location'); ?></label>
                      <div class="controls">
                        <select name="location"> <?php echo $location_list; ?></select>
                      </div>
                    </div>
                </div>
                </div>
                <div class="clearfix"></div>
              </form>
              </div>
            </div>
            <div id="show_advance">
              <a href="#"><?php echo __('Advanced Search'); ?></a>
            </div>
            </div>
          <div class="info">
            <?php echo $search_result_info; ?>
          </div>
          <?php } ?>

          <?php if(isset($_GET['p'])) { ?>
            <?php if($_GET['p'] == 'member') { ?>
              <?php echo $main_content; ?>
            <?php } else { ?>
              <div class="page"><?php echo $main_content; ?></div>
            <?php } ?>
          <?php } else { ?>
            <?php echo $main_content; ?>
          <?php } ?>

        <?php if(@$_GET['p'] != 'member') { ?>
        </div>
        <?php } elseif(utility::isMemberLogin()) { ?>
        </div>
        </div>
        <?php } ?>
        <!-- End Main Content -->

        <div class="span4">
          <!--// If Member Logged //-->
          <?php if (utility::isMemberLogin()) { ?>
          <div class="sidebar">
            <div class="tagline">
              <?php echo __('Information'); ?>
            </div>
            <div class="info">
              <?php echo $header_info; ?>
            </div>
          </div>
          <?php } else { ?>
          <div class="sidebar">
            <div class="tagline">
              <?php echo __('Information'); ?>
            </div>
            <div class="info">
              <?php echo $info; ?>
            </div>
          </div>
          <?php } ?>
          <!--// End Member Logged //-->
          <br/>

          <!--// Show if clustering search is enabled //-->
          <?php if(!isset($_GET['p'])) { ?>
          <?php if ($sysconf['enable_search_clustering']) { ?>
          <div class="sidebar">
            <div class="tagline">
              <?php echo __('Search Cluster'); ?>
            </div>
            <div class="info">
              <div id="search-cluster"><div class="cluster-loading"><?php echo __('Generating search cluster...');  ?></div></div>
              <script type="text/javascript">
              $('document').ready( function() {
                $.ajax({
                  url: 'index.php?p=clustering&q=<?php echo urlencode($criteria); ?>',
                  type: 'GET',
                  success: function(data, status, jqXHR) {
                    $('#search-cluster').html(data);
                  }
                });
              });
              </script>
            </div>
          </div>
          <?php } ?>
          <!--// End Show if clustering search is enabled //-->
          <?php } ?>

        </div>

      <?php } else { ?>
        <!-- Default Frontpage -->
        <div class="span8 offset2">
          <div class="search">
            <div class="tagline"><?php echo $info; ?></div>
            <div id="simply-search">
              <div class="simply" >
                <form name="advSearchForm" id="simplySearchForm" action="index.php" method="get" class="form-search">
                  <div class="input-append">
                  <input type="hidden" name="search" value="search" />
                  <input type="text" name="keywords" id="keyword" placeholder="<?php echo __('Keyword'); ?>" lang="<?php echo $sysconf['default_lang']; ?>" x-webkit-speech="x-webkit-speech" class="input-xxlarge search-query" />
                  <button type="submit" class="btn"><?php echo __('Search'); ?></button>
                  </div>
                </form>
              </div>
            </div>
            <div id="advance-search" style="display:none;" >
              <form name="advSearchForm" id="advSearchForm" action="index.php" method="get" class="form-horizontal form-search">
                <div class="simply" >
                  <div class="input-append">
                  <input type="text" name="title" id="title" placeholder="<?php echo __('Title'); ?>" class="input-xxlarge search-query" />
                  <button type="submit" name="search" class="btn" value="search"><?php echo __('Search'); ?></button>
                  </div>
                </div>
                <div class="advance">
                  <div class="row-fluid">
                    <div class="span5">
                      <div class="control-group">
                        <label class="control-label"><?php echo __('Author(s)'); ?></label>
                        <div class="controls">
                          <?php echo $advsearch_author; ?>
                        </div>
                      </div>

                      <div class="control-group">
                        <label class="control-label"><?php echo __('Subject(s)'); ?></label>
                        <div class="controls">
                          <?php echo $advsearch_topic; ?>
                        </div>
                      </div>

                      <div class="control-group">
                        <label class="control-label"><?php echo __('ISBN/ISSN'); ?></label>
                        <div class="controls">
                          <input type="text" name="isbn" />
                        </div>
                      </div>
                    </div>
                    <div class="span6">

                      <div class="control-group">
                      <label class="control-label"><?php echo __('GMD'); ?></label>
                      <div class="controls">
                        <select name="gmd"><?php echo $gmd_list; ?></select>
                      </div>
                      </div>

                      <div class="control-group">
                        <label class="control-label"><?php echo __('Collection Type'); ?></label>
                        <div class="controls">
                          <select name="colltype"><?php echo $colltype_list; ?></select>
                        </div>
                      </div>

                      <div class="control-group">
                        <label class="control-label"><?php echo __('Location'); ?></label>
                        <div class="controls">
                          <select name="location"> <?php echo $location_list; ?></select>
                        </div>
                      </div>

                    </div>
                  </div>
                <div class="clearfix"></div>
                </div>
              </form>
            </div>
        </div>
        <div id="show_advance">
          <a href="#"><?php echo __('Advanced Search'); ?></a>
        </div>
        <!-- End Default Frontpage-->
      <?php } ?>
    </div>
  </div>

  <?php
  // Promoted titles
  // Only show at the homepage
  if(  !( isset($_GET['search']) || isset($_GET['title']) || isset($_GET['keywords']) || isset($_GET['p']) ) ) :
    // query top book
    $topbook = $dbs->query('SELECT biblio_id, title, image FROM biblio WHERE
        promoted=1 ORDER BY last_update LIMIT 10');
    if ($num_rows = $topbook->num_rows) :
  ?>
  <div class="row topbook-container">
      <div class="span8 offset2">
        <ul id="topbook" class="jcarousel-skin-tango">
          <?php
          while ($book = $topbook->fetch_assoc()) {
            if (!empty($book['image'])) :
            ?>
            <li class="book"><a href="./index.php?p=show_detail&id=<?php echo $book['biblio_id'] ?>" title="<?php echo $book['title'] ?>"><img src="images/docs/<?php echo $book['image'] ?>" /></a></li>
            <?php
            else:
            ?>
            <li class="book"><a href="./index.php?p=show_detail&id=<?php echo $book['biblio_id'] ?>" title="<?php echo $book['title'] ?>"><img src="./template/default/img/nobook.png" /></a></li>
            <?php
            endif;
          }
          ?>
        </ul>
      </div>
  </div>
    <?php endif; ?>
  <?php endif; ?>

</div>  <!--// End Content Ouput //-->

<div class="footer">
 <div class="container">
  <div class="row">
    <div class="span12 lisence">
     This software and this template are released Under GNU GPL License Version 3 - The Winner in the Category of OSS Indonesia ICT Award 2009
   </div>
 </div>
</div>
</div>

<script type="text/javascript" src="<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme']; ?>/js/supersized.3.2.7.min.js"></script>
<script type="text/javascript" src="./js/highlight.js"></script>
<script type="text/javascript">
jQuery(function($){
  $.supersized(
  {
    slides  : [
    {image : '<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme']; ?>/img/4.jpg'},
    {image : '<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme']; ?>/img/3.jpg'},
    {image : '<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme']; ?>/img/2.jpg'},
    {image : '<?php echo $sysconf['template']['dir'].'/'.$sysconf['template']['theme']; ?>/img/1.jpg'}
    ]
  });
});

$(document).ready(function()
{
  $('#keyword').keyup(function(){
    $('#title').val();
    $('#title').val($('#keyword').val());
  });

  $('#title').keyup(function(){
    $('#keyword').val();
    $('#keyword').val($('#title').val());
  });

  $('#advSearchForm input').attr('autocomplete','off');
  $('#title').attr('style','');

  $('#show_advance').click(function(){
    if ($("#advance-search").is(":hidden"))
    {
      $("#advance-search").slideDown('normal');
      $('#simply-search').slideUp('normal');
    } else {
      $("#advance-search").slideUp('normal');
      $('#simply-search').slideDown('normal');
    }
  });

  $('#title').keypress(function(e){
    if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
      this.form.submit();
    }
  });

  $(window).load(function () {
    $('#keyword').focus();
  });

  function mycarousel_initCallback(carousel)
  {
    // Disable autoscrolling if the user clicks the prev or next button.
    carousel.buttonNext.bind('click', function() {
      carousel.startAuto(0);
    });

    carousel.buttonPrev.bind('click', function() {
      carousel.startAuto(0);
    });

    // Pause autoscrolling if the user moves with the cursor over the clip.
    carousel.clip.hover(function() {
      carousel.stopAuto();
    }, function() {
      carousel.startAuto();
    });
  };

  jQuery('#topbook').jcarousel({
      auto: 5,
      wrap: 'last',
      initCallback: mycarousel_initCallback
  });

  jQuery('.container .item .detail-list, .coll-detail .title, .abstract, .coll-detail .controls').highlight(<?php echo $searched_words_js_array; ?>);

});
</script>

</body>
</html>
