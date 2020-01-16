<!doctype html>
<html>
<head>
    <title><?php echo $page_title; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" />
    <meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT" />

    <link rel="icon" href="<?php echo SWB; ?>webicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="<?php echo SWB; ?>webicon.ico" type="image/x-icon" />
    <link href="<?php echo SWB; ?>css/bootstrap.min.css?<?php echo date('this') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo SWB; ?>css/core.css?<?php echo date('this') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo JWB; ?>colorbox/colorbox.css?<?php echo date('this') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo JWB; ?>chosen/chosen.css?<?php echo date('this') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo JWB; ?>toastr/toastr.min.css?<?php echo date('this') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo JWB; ?>jquery.imgareaselect/css/imgareaselect-default.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $sysconf['admin_template']['css'].'?'.date('this'); ?>" rel="stylesheet" type="text/css" />

    <script type="text/javascript" src="<?php echo JWB; ?>jquery.js"></script>
    <script type="text/javascript" src="<?php echo AWB; ?>admin_template/<?php echo $sysconf['admin_template']['theme']?>/vendor/slimscroll/jquery.slimscroll.min.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>updater.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>gui.js?"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>form.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>calendar.js?v=<?php echo date('this') ?>"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>chosen/chosen.jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>chosen/ajax-chosen.min.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>tooltipsy.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>colorbox/jquery.colorbox-min.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>jquery.imgareaselect/scripts/jquery.imgareaselect.pack.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>webcam.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>scanner.js"></script>
    <script type="text/javascript" src="<?php echo SWB; ?>js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo SWB; ?>js/popper.min.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>toastr/toastr.min.js"></script>
</head>
<body>

<header id="header">
    <nav id="mainMenu">
        <?php echo $main_menu; ?>
    </nav>
</header>
    
<nav id="sidepan">
    <div class="s-user" id="profile">
        <div class="s-user-frame">
            <a href="<?php echo MWB.'system/app_user.php?changecurrent=true&action=detail'; ?>" class="s-user-photo subMenuItem">
                <img src="<?php echo '../lib/minigalnano/createthumb.php?filename=../../'.IMG.'/persons/'.urlencode(urlencode($_SESSION['upict'])).'&width=200'?>" alt="Photo <?php echo $_SESSION['realname']?>">
            </a>
        </div>
        <a href="<?php echo MWB.'system/app_user.php?changecurrent=true&action=detail'; ?>">
        <h4 class="s-user-name"><?php echo $_SESSION['realname']?></h4>
        <?php echo isset($_SESSION['nname']) ? $_SESSION['nname'] : __('Librarian'); ?>
        </a>
    </div>

    <?php echo $sub_menu; ?>
</nav>

<div class="loader">
    <div style="display:none;">Error</div>
    <div class="bounce1"></div>
    <div class="bounce2"></div>
    <div class="bounce3"></div>
</div>

<div id="mainContent">
    <?php echo $main_content; ?>
</div>

<footer>
    <div class="row">
        <div class="col-md-6"><?php echo SENAYAN_VERSION; ?></div>
        <div class="col-md-6 text-right"><?php echo $sysconf['page_footer']; ?></div>
    </div>
</footer>

<!-- fake submit iframe for search form, DONT REMOVE THIS! -->
<iframe name="blindSubmit" style="display: none; visibility: hidden; width: 0; height: 0;"></iframe>
<!-- <iframe name="blindSubmit" style="visibility: visible; width: 100%; height: 300px;"></iframe> -->
<!-- fake submit iframe -->

<script>
$('.loader').toggleClass('hidden');
</script>
</body>
</html>
