<?php


/* Make me secure */

if(!isset($_SESSION))
    session_start();

if (isset($_SESSION['nocheck']) && $_SESSION['nocheck']) {
    $_SESSION['nocheck'] = false;
} else {


    if (!isset($_SESSION['FREIX']) || $_SESSION['FREIX'] != 'authenticated') {
        header("Location:index.php");
        exit;
    }
}
/* Now i am secure */

date_default_timezone_set('America/Los_Angeles');
?>
<!DOCTYPE html>
<html>

    <meta charset="utf-8">
    <title>FreiChat Backend</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CODOLOGIC Backend">
    <meta name="author" content="CODOLOGIC">


    <head>
        <title>
            FreiChatX
        </title>

        <!-- The styles -->
        <link id="bs-css" href="../administrator/css/bootstrap-cerulean.css" rel="stylesheet">
        <link href="../administrator/css/bootstrap-responsive.css" rel="stylesheet">
        <link href="../administrator/css/charisma-app.css" rel="stylesheet">
        <link href="../administrator/css/jquery-ui-1.8.21.custom.css" rel="stylesheet">
        <!--<link href='../administrator/css/fullcalendar.css' rel='stylesheet'>
        <link href='../administrator/css/fullcalendar.print.css' rel='stylesheet'  media='print'>-->
        <link href='../administrator/css/chosen.css' rel='stylesheet'>
        <link href='../administrator/css/uniform.default.css' rel='stylesheet'>
        <!--<link href='../administrator/css/colorbox.css' rel='stylesheet'>
        <link href='../administrator/css/jquery.cleditor.css' rel='stylesheet'>-->
        <link href='../administrator/css/jquery.noty.css' rel='stylesheet'>
        <link href='../administrator/css/noty_theme_default.css' rel='stylesheet'>
        <!--<link href='../administrator/css/elfinder.min.css' rel='stylesheet'>
        <link href='../administrator/css/elfinder.theme.css' rel='stylesheet'>
        <link href='../administrator/css/jquery.iphone.toggle.css' rel='stylesheet'>
        <link href='../administrator/css/opa-icons.css' rel='stylesheet'>
        <link href='../administrator/css/uploadify.css' rel='stylesheet'>-->
        <link href="../administrator/css/bootstrap-extend.css" rel="stylesheet">



        <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- The fav icon -->
        <link rel="shortcut icon" href="../favicon.ico">
        <link href="../favicon.ico" rel="shortcut icon" type="image/x-icon" />
        <style type="text/css">

            .hideme {
                display: none;
            }

            .frei_content {
                margin: 0px auto;
            }

            .centerme {
                margin: 0 auto;
                display: block;
                float: none;
                margin-bottom: 50px;
            }

            * {
                margin: 0;
            }
            html, body {
                height: 100%;
            }
            .wrapper {
                min-height: 100%;
                height: auto !important;
                height: 100%;
                margin: 0 auto -2em;
            }
            .footer, .push {
                position: relative;
                bottom: 0;
            }



            .logo{

                text-align:center;
            }


            .adminbutton {
                -moz-box-shadow:inset 0px 0px 0px 0px #bbdaf7;
                -webkit-box-shadow:inset 0px 0px 0px 0px #bbdaf7;
                box-shadow:inset 0px 0px 0px 0px #bbdaf7;
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #79bbff), color-stop(1, #378de5) );
                background:-moz-linear-gradient( center top, #79bbff 5%, #378de5 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#79bbff', endColorstr='#378de5');
                background-color:#79bbff;
                -moz-border-radius:10px;
                -webkit-border-radius:10px;
                border-radius:10px;
                border:2px solid #84bbf3;
                display:inline-block;
                color:#ffffff;
                font-family:Arial;
                font-size:28px;
                font-weight:bold;
                padding:10px 42px;
                text-decoration:none;
                text-shadow:1px 0px 0px #528ecc;
            }.adminbutton:hover {
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #378de5), color-stop(1, #79bbff) );
                background:-moz-linear-gradient( center top, #378de5 5%, #79bbff 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#378de5', endColorstr='#79bbff');
                background-color:#378de5;
            }.adminbutton:active {
                position:relative;
                top:1px;
            }


            .acceptbutton {
                -moz-box-shadow:inset 0px 0px 0px 0px #caefab;
                -webkit-box-shadow:inset 0px 0px 0px 0px #caefab;
                box-shadow:inset 0px 0px 0px 0px #caefab;
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #77d42a), color-stop(1, #5cb811) );
                background:-moz-linear-gradient( center top, #77d42a 5%, #5cb811 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#77d42a', endColorstr='#5cb811');
                background-color:#77d42a;
                -moz-border-radius:10px;
                -webkit-border-radius:10px;
                border-radius:10px;
                border:2px solid #268a16;
                display:inline-block;
                color:#306108;
                font-family:Arial;
                font-size:28px;
                font-weight:bold;
                padding:10px 42px;
                text-decoration:none;
                text-shadow:1px 0px 0px #aade7c;
            }.acceptbutton:hover {
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #5cb811), color-stop(1, #77d42a) );
                background:-moz-linear-gradient( center top, #5cb811 5%, #77d42a 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#5cb811', endColorstr='#77d42a');
                background-color:#5cb811;
            }.acceptbutton:active {
                position:relative;
                top:1px;
            }


            .rejectbutton {
                -moz-box-shadow:inset 0px 0px 0px 0px #f29c93;
                -webkit-box-shadow:inset 0px 0px 0px 0px #f29c93;
                box-shadow:inset 0px 0px 0px 0px #f29c93;
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #fe1a00), color-stop(1, #ce0100) );
                background:-moz-linear-gradient( center top, #fe1a00 5%, #ce0100 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fe1a00', endColorstr='#ce0100');
                background-color:#fe1a00;
                -moz-border-radius:10px;
                -webkit-border-radius:10px;
                border-radius:10px;
                border:2px solid #d83526;
                display:inline-block;
                color:#ffffff;
                font-family:Arial;
                font-size:28px;
                font-weight:bold;
                padding:10px 42px;
                text-decoration:none;
                text-shadow:1px 0px 0px #b23e35;
            }.rejectbutton:hover {
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #ce0100), color-stop(1, #fe1a00) );
                background:-moz-linear-gradient( center top, #ce0100 5%, #fe1a00 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ce0100', endColorstr='#fe1a00');
                background-color:#ce0100;
            }.rejectbutton:active {
                position:relative;
                top:1px;
            }


            .nextbutton {
                -moz-box-shadow:inset 0px 1px 0px 0px #caefab;
                -webkit-box-shadow:inset 0px 1px 0px 0px #caefab;
                box-shadow:inset 0px 1px 0px 0px #caefab;
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #77d42a), color-stop(1, #5cb811) );
                background:-moz-linear-gradient( center top, #77d42a 5%, #5cb811 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#77d42a', endColorstr='#5cb811');
                background-color:#77d42a;
                -moz-border-radius:18px;
                -webkit-border-radius:18px;
                border-radius:18px;
                border:1px solid #268a16;
                display:inline-block;
                color:#306108;
                font-family:arial;
                font-size:28px;
                font-weight:bold;
                padding:18px 76px;
                text-decoration:none;
                text-shadow:1px 1px 0px #aade7c;
            }.nextbutton:hover {
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #5cb811), color-stop(1, #77d42a) );
                background:-moz-linear-gradient( center top, #5cb811 5%, #77d42a 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#5cb811', endColorstr='#77d42a');
                background-color:#5cb811;
                color:inherit;
                text-decoration: none;
            }.nextbutton:active {
                position:relative;
                top:1px;
            }
            .refreshbutton {
                -moz-box-shadow:inset 0px 1px 0px 0px #bbdaf7;
                -webkit-box-shadow:inset 0px 1px 0px 0px #bbdaf7;
                box-shadow:inset 0px 1px 0px 0px #bbdaf7;
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #79bbff), color-stop(1, #378de5) );
                background:-moz-linear-gradient( center top, #79bbff 5%, #378de5 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#79bbff', endColorstr='#378de5');
                background-color:#79bbff;
                -moz-border-radius:18px;
                -webkit-border-radius:18px;
                border-radius:18px;
                border:1px solid #84bbf3;
                display:inline-block;
                color:#ffffff;
                font-family:arial;
                font-size:28px;
                font-weight:bold;
                padding:18px 76px;
                text-decoration:none;
                text-shadow:1px 1px 0px #528ecc;
            }.refreshbutton:hover {
                background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #378de5), color-stop(1, #79bbff) );
                background:-moz-linear-gradient( center top, #378de5 5%, #79bbff 100% );
                filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#378de5', endColorstr='#79bbff');
                background-color:#378de5;
                color:#efefef;
                text-decoration: none;

            }.refreshbutton:active {
                position:relative;
                top:1px;
            }



            @font-face {
                font-family: 'Sonsie One';
                font-style: normal;
                font-weight: 400;
                src: local('Sonsie One'), local('SonsieOne-Regular'), url('images/sonsieone.woff') format('woff');
            }

            @font-face {
                font-family: 'Changa One';
                font-style: normal;
                font-weight: normal;
                src: local('Changa One'), local('ChangaOne'), url('images/changaone.woff') format('woff');
            }

            @font-face {
                font-family: 'Exo';
                font-style: italic;
                font-weight: 600;
                src: local('Exo DemiBold Italic'), local('Exo-DemiBoldItalic'), url('images/exo.woff') format('woff');
            }
        </style>
        <style>
            /**
         * Dreamweaver theme
         *
         * @author Sean Coker
         * @url http://seancoker.com
         * @version 1.0
            */

            pre {
                /* original is white background with no border */
                background-color: #fff;
                word-wrap: break-word;
                margin: 0;
                padding: 10px;
                color: #000;
                font-size: 13px;
                line-height: 16px;
                margin-bottom: 20px
            }

            pre, code {
                font-family: monospace;
            }

            pre .comment {
                color: #888;
            }

            pre .support {
                color: #cd57d5;
            }

            pre .constant.numeric, pre .php.embedded {
                color: #fa0002;
                font-weight: bold;
            }

            pre .keyword, pre .constant.language {
                color: #000789;
                font-weight: bold;
            }

            pre .selector, pre .support.property, pre .entity.name.function {
                color: #000;
            }

            pre .storage.function, pre .variable.self, pre .support.function, pre .constant.language {
                color: #000;
                font-weight: bold;
            }

            pre .string {
                color: #0d43fa;
                font-weight: normal;
            }

            pre .css-property + span, pre .keyword.unit, pre .support.css-value {
                color: #0d43fa !important;
                font-weight: normal !important;
            }

            pre .entity.tag.style + .string, pre .php.embedded .constant.language, pre .php.embedded .keyword {
                color: #37a348 !important;
            }

            pre .support.method {
                color: #2bd5bb;
            }

            pre .entity.name {
                color: #fd74e0;
            }

            pre .support.css-property, pre .support.tag-name, pre .support.tag, pre .support.attribute, pre .support.attribute + .operator {
                color: #000789;
            }

            pre .storage.module, pre .storage.class {
                color: #122573;
                font-weight: bold;
            }

            pre .css.embedded .support.tag, pre .css.embedded .style.tag {
                color: #cd57d5;
            }

            pre .keyword.operator {
                color: #2852eb;
                font-weight: normal;
            }

            pre .php.embedded .variable, pre .php.embedded .storage.function {
                color: #0d43fa;
                font-weight: normal;
            }

            pre .php.embedded .string, pre .js.embedded .tag.script {
                color: #c4001e;
            }

            pre .php.embedded .comment {
                color: #f4b441;
                font-weight: normal;
            }

            pre .php.embedded .function.name {
                color: #000;
                font-weight: normal;
            }
        </style>

        <script>var X_load_jn = "i am defined";</script>
        <script src="../client/jquery/js/jquery.1.7.1.js"></script>
        <script src="../client/jquery/js/jquery-ui.js"></script>
    </head>
    <body>


        <div class="wrapper">
            <div style="text-align:center">

                <a href="http://codologic.com"><img src="../administrator/admin_files/home/head.png" height=100  /></a>
            </div>