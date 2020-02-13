<?php
// clean request uri from xss
$request_uri = urlencode(strip_tags(urldecode($_SERVER['REQUEST_URI'])));
?>
<!-- Page Title
============================================= -->
<title><?php echo $page_title; ?></title>

<!-- Meta
============================================= -->
<meta charset="utf-8">

<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" />
<meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT" />

<?php if(isset($_GET['p']) && ($_GET['p'] == 'show_detail')): ?>
<meta name="description" content="<?php echo substr($notes,0,152).'...'; ?>">
<meta name="keywords" content="<?php echo $subject; ?>">
<?php else: ?>
<meta name="description" content="<?php echo $page_title; ?>">
<meta name="keywords" content="<?php echo $sysconf['library_subname']; ?>">
<?php endif; ?>
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
<meta name="generator" content="<?php echo SENAYAN_VERSION ?>">

<!-- Opengraph
============================================= -->
<meta property="og:locale" content="<?php echo str_replace('-','_',$sysconf['default_lang']); ?>"/>
<meta property="og:type" content="book"/>
<meta property="og:title" content="<?php echo $page_title; ?>"/>
<?php if(isset($_GET['p']) && ($_GET['p'] == 'show_detail')): ?>
<meta property="og:description" content="<?php echo substr($notes,0,152).'...'; ?>"/>
<?php else: ?>
<meta property="og:description" content="<?php echo $sysconf['library_subname']; ?>"/>
<?php endif; ?>
<meta property="og:url" content="//<?php echo $_SERVER["SERVER_NAME"].$request_uri; ?>"/>
<meta property="og:site_name" content="<?php echo $sysconf['library_name']; ?>"/>
<?php if(isset($_GET['p']) && ($_GET['p'] == 'show_detail')): ?>
<meta property="og:image" content="//<?php echo $_SERVER["SERVER_NAME"].SWB.$image_src ?>"/>
<?php else: ?>
<meta property="og:image" content="//<?php echo $_SERVER["SERVER_NAME"].SWB.$sysconf['template']['dir']; ?>/default/img/logo.png"/>
<?php endif; ?>

<!-- Twitter
============================================= -->
<meta name="twitter:card" content="summary">
<meta name="twitter:url" content="//<?php echo $_SERVER["SERVER_NAME"].$request_uri; ?>"/>
<meta name="twitter:title" content="<?php echo $page_title; ?>"/>
<?php if(isset($_GET['p']) && ($_GET['p'] == 'show_detail')): ?>
<meta property="twitter:image" content="//<?php echo $_SERVER["SERVER_NAME"].SWB.$image_src ?>"/>
<?php else: ?>
<meta property="twitter:image" content="//<?php echo $_SERVER["SERVER_NAME"].SWB.$sysconf['template']['dir']; ?>/default/img/logo.png"/>
<?php endif; ?>

<!-- Theme
============================================= -->
<link rel="shortcut icon" href="webicon.ico" type="image/x-icon"/>
<link rel="stylesheet" href="<?php echo $sysconf['template']['dir']; ?>/core.style.css" type="text/css" />
<link rel="stylesheet" href="<?php echo JWB; ?>colorbox/colorbox.css" type="text/css" />
<link rel="profile" href="http://www.slims.web.id/">
<link rel="canonical" href="//<?php echo $_SERVER["SERVER_NAME"].$request_uri; ?>" />
<?php echo $metadata; ?>

<!-- Style Minified
============================================= -->
<link rel="stylesheet" type="text/css" href="<?php echo SWB.$sysconf['template']['dir']; ?>/<?php echo $sysconf['template']['theme']; ?>/css/minified.css" />
<link rel="stylesheet" type="text/css" href="<?php echo SWB.$sysconf['template']['dir']; ?>/<?php echo $sysconf['template']['theme']; ?>/css/hamburgers.css" />

<!-- Style
============================================= -->
<!-- <link rel="stylesheet" type="text/css" href="<?php echo $sysconf['template']['css']; ?>" /> -->

<!-- Less
============================================= -->
<!-- For Developmet Only
<link rel="stylesheet/less" type="text/css" href="<?php echo SWB; ?>template/default/style.min.css"/>
<script>less = { env: "development" };</script>
<script src="<?php echo $sysconf['template']['dir']; ?>/default/js/less.min.js"></script>
-->
<script src="<?php echo JWB;?>jquery.js"></script>
<style>
    .s-search-advances {
        color: #fff;
        text-decoration: none;
    }
    .s-search-advances:hover {
        color: #0f0f0f;
    }

    #advance-search {
        opacity: 0;
        width: 100%;
        margin: 0 auto;
        z-index: -1;
        background: rgba(0,0,0,0.7);
        padding-bottom: 20px;
    }
    @media only screen and (min-width: 768px) {
        #advance-search {
            top: 0;
            position: fixed;
            width: 100%;
            padding-left: 100px;
            padding-right: 100px;
            left: 0;
            right: 0;
        }
        .s-main-page .s-search-advances {
            font-size: 12px;
            position: absolute;
            top: 70px;
            right: 190px;
        }
    }
    @media only screen and (min-width: 992px) {
        #advance-search {
            padding-left: 200px;
            padding-right: 200px;
        }
    }
    @media only screen and (min-width: 1200px) {
        #advance-search {
            padding-left: 300px;
            padding-right: 300px;
        }
    }
    .s-search-advances {
        color: #fff;
    }
    .s-search-advances:hover {
        color: #ff0;
    }
    .s-search-advances:focus {
        color: #fff;
    }
    #advance-search select,
    #advance-search input[type='text'] {
        border: none;
        border-radius: 3px;
        box-shadow: none;
        margin-bottom: 2px !important;
    }
    @media only screen and (min-width: 768px) {
        #advance-search select,
        #advance-search input[type='text'] {
            padding: 10px 15px;
            height: 45px;
            margin-bottom: 5px !important;
        }
    }
    #advance-search .label {
        line-height: 1;
        letter-spacing: 1px;
        font-size: 9pt;
        color: #999;
        font-weight: bold;
        text-transform: capitalize;
    }
    @media only screen and (min-width: 768px) {
        #advance-search .label {
            line-height: 2;
        }
    }
    #advance-search h2 {
        font-size: 10pt;
        color: #39c;
        line-height: 2;
    }
    @media only screen and (min-width: 768px) {
        #advance-search h2 {
            font-size: 16pt;
        }
    }
    #advance-search button {
        background-color: #f0fafb;
        color: #333333;
        padding: 10px 20px;
        border: none;
        border-radius: 50px;
    }
    #advance-search .hamburger {
        position: fixed;
        top: 40px;
        right: 40px;
    }
    #advance-search .hamburger--3dy.is-active .hamburger-inner::after,
    #advance-search .hamburger--3dy.is-active .hamburger-inner::before {
        background: #fff;
    }
</style>