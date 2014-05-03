<?php
header('Content-Type: text/html; charset=utf-8');
session_start();



if (isset($_GET['logout'])) {

    unset($_SESSION['phplogin']);
}

if (!isset($_SESSION['phplogin']) || $_SESSION['phplogin'] !== true) {
    header('Location: ../administrator/index.php'); //Replace that if login.php is somewhere else
    exit;
}

define('FREI_ADMIN', 'true');
require '../arg.php';
require 'admin_files/admin_base.php';
require_once 'admin_files/home/magic_gpc_unset.php';

$construct = new FreiChat();
$db = $construct->db;

function get_file_names($path, $type, $replace = false) {
    $handle = opendir($path);
    $store = array();
    if ($type == "dir" || $type == "file") {
        if ($path) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != '.svn') {
                    if ((is_dir($path . $file) && $type == "dir") || $type == "file") {
                        if ($type == "file" && $replace == true) {
                            $file = str_replace(".php", "", $file);
                        }
                        $store[] = $file;
                    }
                }
            }
            closedir($handle);
        }
    }
    return $store;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>FreiChat Backend</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Charisma, a fully featured, responsive, HTML5, Bootstrap admin template.">
        <meta name="author" content="Muhammad Usman">

        <!-- The styles -->
        <link id="bs-css" href="css/bootstrap-cerulean.css" rel="stylesheet">
        <style type="text/css">
            body {
                padding-bottom: 40px;
            }
            .sidebar-nav {
                padding: 9px 0;
            }
        </style>
        <link href="css/bootstrap-responsive.css" rel="stylesheet">
        <link href="css/charisma-app.css" rel="stylesheet">
        <link href="css/jquery-ui-1.8.21.custom.css" rel="stylesheet">
        <!--<link href='css/fullcalendar.css' rel='stylesheet'>
        <link href='css/fullcalendar.print.css' rel='stylesheet'  media='print'>-->
        <link href='css/chosen.css' rel='stylesheet'>
        <link href='css/uniform.default.css' rel='stylesheet'>
        <!--<link href='css/colorbox.css' rel='stylesheet'>
        <link href='css/jquery.cleditor.css' rel='stylesheet'>-->
        <link href='css/jquery.noty.css' rel='stylesheet'>
        <link href='css/noty_theme_default.css' rel='stylesheet'>
        <!--<link href='css/elfinder.min.css' rel='stylesheet'>
        <link href='css/elfinder.theme.css' rel='stylesheet'>-->
        <link href='css/jquery.iphone.toggle.css' rel='stylesheet'>
        <link href='css/opa-icons.css' rel='stylesheet'>
        <!--<link href='css/uploadify.css' rel='stylesheet'>-->
        <!-- jQuery -->
        <script src="../client/jquery/js/jquery.1.8.3.js"></script>
        <!-- jQuery UI -->
        <script src="../client/jquery/js/jquery-ui.js"></script>

        <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- The fav icon -->
        <link rel="shortcut icon" href="../favicon.ico">


    </head>

    <body>
        <!-- topbar starts -->
        <div class="navbar">
            <div class="navbar-inner">
                <div class="container-fluid">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".top-nav.nav-collapse,.sidebar-nav.nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="admin.php"> <img alt="Charisma Logo" src="frei_images/head.png" /> </a>

                    <!-- user dropdown starts -->
                    <div class="btn-group pull-right" >
                        <a class="btn" href="admin.php?logout=true">
                            <i class="icon-off"></i>
                            Logout
                        </a>
                    </div>
                    <!-- user dropdown ends -->

                    <div class="top-nav nav-collapse">
                        <ul class="nav">
                            <li><a href="../../">Visit Site</a></li>
                            <!--<li>
                                    <form class="navbar-search pull-left">
                                            <input placeholder="Search" class="search-query span2" name="query" type="text">
                                    </form>
                            </li>-->
                        </ul>
                    </div><!--/.nav-collapse -->
                </div>
            </div>
        </div>
        <!-- topbar ends -->
        <div class="container-fluid">
            <div class="row-fluid">
                <?php
                if (isset($_REQUEST['freiload']) && $_REQUEST['freiload'] == "theme_maker") {
                    $ajax_link = "";
                } else {
                    $ajax_link = "ajax-link";
                }
                ?>
                <!-- left menu starts -->
                <div class="span2 main-menu-span">
                    <div class="well nav-collapse sidebar-nav">
                        <ul class="nav nav-tabs nav-stacked main-menu">
                            <li class="nav-header hidden-tablet">General</li>
                            <li><a href="admin.php"><i class="icon-home"></i><span class="hidden-tablet"> Dashboard</span></a></li>
                            <li><a class="<?php echo $ajax_link; ?>" href="admin.php?freiload=home/client_side"><i class="icon-book"></i><span class="hidden-tablet"> Client side</span></a></li>
                            <li><a class="<?php echo $ajax_link; ?>" href="admin.php?freiload=_file_send"><i class="icon-file"></i><span class="hidden-tablet"> File Send</span></a></li>
                            <li><a class="<?php echo $ajax_link; ?>" href="admin.php?freiload=_send_conv"><i class="icon-folder-close"></i><span class="hidden-tablet"> Email</span></a></li>
                            <li class="nav-header hidden-tablet">Chatrooms</li>
                            <li><a href="admin.php?freiload=chatrooms/chatrooms"><i class="icon-comment"></i><span class="hidden-tablet"> Create chatroom</span></a></li>
                            <li><a class="<?php echo $ajax_link; ?>" href="admin.php?freiload=chatrooms/configuration"><i class="icon-asterisk"></i><span class="hidden-tablet"> Configuration</span></a></li>
                            <li class="nav-header hidden-tablet">Design</li>
                            <li><a href="admin.php?freiload=home/smilies"><i class="icon-eye-open"></i><span class="hidden-tablet"> Smilies</span></a></li>
                            <li><a href="admin.php?freiload=theme_maker"><i class="icon-picture"></i><span class="hidden-tablet"> Theme maker</span></a></li>
                            <li class="nav-header hidden-tablet">Access Control</li>
                            <li><a class="<?php echo $ajax_link; ?>" href="admin.php?freiload=home/acl"><i class="icon-lock"></i><span class="hidden-tablet"> ACL</span></a></li>
                            <li><a  href="admin.php?freiload=moderate_users"><i class="icon-user"></i><span class="hidden-tablet"> Moderation</span></a></li>
                            <li class="nav-header hidden-tablet">Advanced</li>
                            <li><a class="<?php echo $ajax_link; ?>" href="admin.php?freiload=home/polling"><i class="icon-arrow-up"></i><span class="hidden-tablet"> Polling</span></a></li>
                            <li><a class="<?php echo $ajax_link; ?>" href="admin.php?freiload=home/server_side"><i class="icon-wrench"></i><span class="hidden-tablet"> Server side</span></a></li>
                            <li><a class="<?php echo $ajax_link; ?>" href="admin.php?freiload=home/additional"><i class="icon-plus"></i><span class="hidden-tablet"> Additional</span></a></li>

                        </ul>
                        <label id="for-is-ajax" class="hidden-tablet" for="is-ajax"><input id="is-ajax" type="checkbox"> Ajax on menu</label>
                    </div><!--/.well -->
                </div><!--/span-->
                <!-- left menu ends -->

                <noscript>
                <div class="alert alert-block span10">
                    <h4 class="alert-heading">Warning!</h4>
                    <p>You need to have <a href="http://en.wikipedia.org/wiki/JavaScript" target="_blank">JavaScript</a> enabled to use this site.</p>
                </div>
                </noscript>

                <div id="content" class="span10">




                    <!--
                <table border=0 cellpadding="6" align="center">
                    <tr>
        
        
                        <td valign=top width="80%">-->
                    <?php
                    if (isset($_REQUEST['freiload'])) {



                        if (is_file('admin_files/' . $_REQUEST['freiload'] . '/index.php')) {
                            require('admin_files/' . $_REQUEST['freiload'] . '/index.php');
                        } else {
                            var_dump($_REQUEST);
                            echo "requested loadable could not be found!";
                        }
                    } else {
                        require('admin_files/default/index.php');
                    }
                    ?>
                    <!--</td>
                </tr>
    
            </table>-->

                    <!-- content ends -->
                </div><!--/#content.span10-->
            </div><!--/fluid-row-->

            <hr id="hr_footer_frei">

            <div class="modal hide fade" id="myModal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3>Settings</h3>
                </div>
                <div class="modal-body">
                    <p>Here settings can be configured...</p>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn" data-dismiss="modal">Close</a>
                    <a href="#" class="btn btn-primary">Save changes</a>
                </div>
            </div>

        </div><!--/.fluid-container-->
        <footer id="theme_footer">
            <div style="text-align: center;margin: 0px auto;width:60%;font-size:small;">
                <div style="text-align:center;font-size: small;">

                    <div style="text-align:left;width:150px;margin: 0px auto">


                        Powered By <a target="_blank" style="color:blue" href="http://codologic.com">Codologic</a>

                    </div>


                </div>
            </div>
        </footer>
        <!-- external javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->

        <!-- transition / effect library -->
        <script src="js/bootstrap-transition.js"></script>
        <!-- alert enhancer library 
        <script src="js/bootstrap-alert.js"></script>-->
        <!-- modal / dialog library -->
        <script src="js/bootstrap-modal.js"></script>
        <!-- custom dropdown library 
        <script src="js/bootstrap-dropdown.js"></script>-->
        <!-- scrolspy library 
        <script src="js/bootstrap-scrollspy.js"></script>-->
        <!-- library for creating tabs 
        <script src="js/bootstrap-tab.js"></script>-->
        <!-- library for advanced tooltip -->
        <script src="js/bootstrap-tooltip.js"></script>
        <!-- popover effect library 
        <script src="js/bootstrap-popover.js"></script>-->
        <!-- button enhancer library -->
        <script src="js/bootstrap-button.js"></script>
        <!-- accordion library (optional, not used in demo) 
        <script src="js/bootstrap-collapse.js"></script>-->
        <!-- carousel slideshow library (optional, not used in demo) 
        <script src="js/bootstrap-carousel.js"></script>-->
        <!-- autocomplete library 
        <script src="js/bootstrap-typeahead.js"></script>-->
        <!-- tour library 
        <script src="js/bootstrap-tour.js"></script>-->
        <!-- library for cookie management -->
        <script src="js/jquery.cookie.js"></script>
        <!-- calander plugin 
        <script src='js/fullcalendar.min.js'></script>-->
        <!-- data table plugin -->
        <script src='js/jquery.dataTables.min.js'></script>

        <!-- chart libraries start -->
        <script src="js/excanvas.js"></script>
        <script src="js/jquery.flot.min.js"></script>
        <script src="js/jquery.flot.pie.min.js"></script>
        <script src="js/jquery.flot.stack.js"></script>
        <script src="js/jquery.flot.resize.min.js"></script>
        <!-- chart libraries end -->

        <!-- select or dropdown enhancer -->
        <script src="js/jquery.chosen.min.js"></script>
        <!-- checkbox, radio, and file input styler -->
        <script src="js/jquery.uniform.min.js"></script>
        <!-- plugin for gallery image view 
        <script src="js/jquery.colorbox.min.js"></script>-->
        <!-- rich text editor library 
        <script src="js/jquery.cleditor.min.js"></script>-->
        <!-- notification plugin -->
        <script src="js/jquery.noty.js"></script>
        <!-- file manager library 
        <script src="js/jquery.elfinder.min.js"></script>-->
        <!-- star rating plugin 
        <script src="js/jquery.raty.min.js"></script>-->
        <!-- for iOS style toggle switch -->
        <script src="js/jquery.iphone.toggle.js"></script>
        <!-- autogrowing textarea plugin 
        <script src="js/jquery.autogrow-textarea.js"></script>-->
        <!-- multiple file upload plugin 
        <script src="js/jquery.uploadify-3.1.min.js"></script>-->
        <!-- history.js for cross-browser state change on ajax -->
        <script src="js/jquery.history.js"></script>
        <!-- application script for Charisma demo -->
        <script src="js/charisma.js"></script>
        <?php
        // required js script code for pop up help
        if (isset($_REQUEST['freiload'])) {
            if (is_file('admin_files/' . $_REQUEST['freiload'] . '/head.php')) {
                require('admin_files/' . $_REQUEST['freiload'] . '/head.php');
            } else {
                //var_dump($_REQUEST);


                echo "\n<!-- requested loadable header could not be found! -->\n";
            }
        } else {
            require('admin_files/home/head.php');
            //require('admin_files/home/index.php');
        }
        ?>



        <script type="text/javascript">
            function purge_mesg_history() {

                var days = $('#purge_mesg_period').val();
                $.get('admin.php?freiload=home&purge=true', {days: days}, function(resp) {
                    alert('Messages Purged successfully.');
                });
            }
        </script>

    </body>
</html>

