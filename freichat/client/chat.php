<?php
if (!isset($_GET['id']) || !isset($_GET['xhash']))
    exit;

require '../arg.php';

$FC = new FreiChat();

$FC->init_vars();
$frei_trans = $FC->frei_trans;
$url = $FC->url;

if (isset($_SERVER['HTTP_REFERER'])) {
    $referer_url = $_SERVER['HTTP_REFERER'];
} else {
    $referer_url = $url;
}


if (strpos($referer_url, 'www.') == TRUE) {
    $url = str_replace('http://', 'http://www.', $url);
    $url = str_replace('https://', 'https://www.', $url);
} else {

    $url = str_replace('http://www.', 'http://', $url);
    $url = str_replace('https://www.', 'https://', $url);
}

if (strpos($url, 'www.www.') == TRUE) {
    $url = str_replace('http://www.www.', 'http://www.', $url);
    $url = str_replace('https://www.www.', 'https://www.', $url);
}


$id = $_GET['id'];
$xhash = $_GET['xhash'];

$url = str_replace("chat.php", "", $url);
?>


<!DOCTYPE html>
<html>
    <head>
        <title>Chat</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!--
            jQuery mobile full version 1.3.1 updated on Tue Jul 2, 17:00 2013
        -->

        <link rel="stylesheet" href="<?php echo $url; ?>jquery/freichat_themes/freichatcss.php" type="text/css" />
        <script src="<?php echo $url; ?>main.php?id=<?php echo $id; ?>&xhash=<?php echo $xhash; ?>&mobile=1" type="text/javascript"></script>
        <link rel="stylesheet" href="<?php echo $url; ?>plugins/mobile/jquerymobile.css" />
        <link rel="stylesheet" href="<?php echo $url; ?>plugins/mobile/simpledialog2.css" />
        <script src="<?php echo $url; ?>plugins/mobile/jquerymobile.js"></script>
        <script src="<?php echo $url; ?>plugins/mobile/simpledialog2.js"></script>
        <script src="<?php echo $url; ?>plugins/mobile/iscroll.js"></script>

        <script>
            FreiChat.init();
        </script>

        <link rel="stylesheet" href="<?php echo $url; ?>plugins/mobile/styles.css" />

    </head>
    <body>
        <!-- first page: online users -->
        <div id="users" data-role="page">

            <div data-role="header">
                <h1><?php echo $frei_trans["mobile_list_title"]; ?></h1>
            </div><!-- /header -->

            <div data-role="content">
                <div class="frei_mobile_users" id="frei_mobile_users">
                    <div id="users_scroller" class="u_scroller">
                        <ul class="users_ul" id="users_ul" data-role="listview" data-inset="true" data-filter="true" data-filter-placeholder="<?php echo $frei_trans["mobile_filter_list"];?>">
                            <?php echo $frei_trans["mobile_loading"]; ?>
                        </ul>
                    </div>
                </div>


            </div>

            <div data-role="footer" data-position="fixed" data-id="navigator" data-iconpos="left">
                <div data-role="navbar">
                    <ul class="frei_bottom_menu_no_chatroom">
                        <li> <a class="ui-btn-active ui-state-persist" data-icon="chat" href="#"><?php echo $frei_trans["mobile_chat"]; ?></a></li>
                        <li> <a data-icon="gear" href="#settings"><?php echo $frei_trans["mobile_settings"]; ?></a></li>

                    </ul>
                    <ul class="frei_bottom_menu">
                        <li> <a class="ui-btn-active ui-state-persist" data-icon="chat" href="#"><?php echo $frei_trans["mobile_chat"]; ?></a></li>
                        <li> <a data-icon="chatroom" href="#chatroom"><?php echo $frei_trans["chatroom_title"]; ?></a></li>
                        <li> <a data-icon="gear" href="#settings"><?php echo $frei_trans["mobile_settings"]; ?></a></li>

                    </ul>

                </div>
            </div>
        </div>
        <!-- end of first page -->


        <!-- second page: one-one chat -->
        <div id="chat" data-role="page">

            <div id="chat_to_header" data-role="header">
                <a data-role="button" data-inline="true" data-icon="back" onclick="mobile.go_back();"><?php echo $frei_trans["mobile_back"]; ?></a>

                <span style="display:none" id="chat_my_avatar" style='display:block' class='freichat_userscontentavatar'></span>
                <h1 id="chat_to_header_text"><?php echo $frei_trans["mobile_private_def_head"]; ?></h1>

                <!--smileys container -->
                <span id='freismileboxchatroom'>
                    <span id='frei_smileys_mobile' class=' none'>

                    </span>
                </span>

                <!--smileys container -->


                <span class="widgets">
                <span id="smiley_widget" class=" smiley_widget"></span>
                <span id="attachment_widget" class=" attachment_widget"></span>
                </span>
            </div>
            <div id="option_bar" class="option_bar">
                <a href="#chatroom_users" id="chatroom_users_btn" data-role="button" data-mini="true" data-inline="true" data-theme="a"><?php echo $frei_trans["mobile_users"]; ?></a>
                <span id="clear_messages_widget" class="option_bar_widgets clear_messages_widget"></span>
                <span id="mail_messages_widget" class="option_bar_widgets mail_messages_widget"></span>
                <a target="_blank" data-rel="dialog" data-transition="pop" id="save_messages_widget_a" href="javascript:void"><span id="save_messages_widget" class="option_bar_widgets save_messages_widget"></span></a>

            </div>

            <div id="message_content">

                <div id="messages_scroller" class="scroller">
                    <ul id="messages_list"></ul>
                </div>
            </div>

            <div data-role="footer" data-id="foo1" data-position="fixed" data-tap-toggle="false">
                <form id="send_message" data-ajax="false" onsubmit="return false">
                    <input id="chat_message" type="text" name="chat_message" placeholder="<?php echo $frei_trans["mobile_private_enter_text"]; ?>">
                </form>
            </div>

        </div>


        <!-- end of second page -->

        <!-- third page: chat settings -->
        <div id="settings" data-role="page">
            <div data-role="header">
                <h1><?php echo $frei_trans["mobile_settings"]; ?></h1>
            </div><!-- /header -->

            <div data-role="content">

                <label for="sound"><?php echo $frei_trans["mobile_sound_toggle"]; ?></label>
                <select name="flip-1" id="sound" data-role="slider">
                    <option value="off">Off</option>
                    <option value="on">On</option>
                </select>
                <label for="notify"><?php echo $frei_trans["mobile_notify_toggle"]; ?></label>
                <select name="flip-1" id="notify" data-role="slider">
                    <option value="off">Off</option>
                    <option value="on">On</option>
                </select>

            </div>

            <script type="text/javascript">
                $jn("#settings").on("pageinit", function(event) {

                    var sound = Get_Cookie("frei_sound");
                    var notify = Get_Cookie("frei_notify");

                    $jn("#sound").val(sound).slider("refresh");
                    $jn("#notify").val(notify).slider("refresh");

                    $jn("#sound").change(function() {

                        Set_Cookie("frei_sound", $jn(this).val());
                        mobile.set.notify_snd = Get_Cookie("frei_sound");
                    });
                    $jn("#notify").change(function() {

                        Set_Cookie("frei_notify", $jn(this).val());
                        mobile.set.notify = Get_Cookie("frei_notify");
                    });
                });

            </script>

            <div data-role="footer" data-position="fixed" data-id="navigator" data-iconpos="left">
                <div data-role="navbar">
                    <ul class="frei_bottom_menu_no_chatroom">
                        <li> <a data-icon="chat" href="#users"><?php echo $frei_trans["mobile_chat"]; ?></a></li>
                        <li> <a class="ui-btn-active ui-state-persist" data-icon="gear" href="#"><?php echo $frei_trans["mobile_settings"]; ?></a></li>

                    </ul>
                    <ul class="frei_bottom_menu">
                        <li> <a data-icon="chat" href="#users"><?php echo $frei_trans["mobile_chat"]; ?></a></li>
                        <li> <a data-icon="chatroom" href="#chatroom"><?php echo $frei_trans["chatroom_title"]; ?></a></li>
                        <li> <a class="ui-btn-active ui-state-persist" data-icon="gear" href="#"><?php echo $frei_trans["mobile_settings"]; ?></a></li>

                    </ul>

                </div>
            </div>
        </div>
        <!-- end of third page -->


        <!-- fourth page: chatroom -->
        <div id="chatroom" data-role="page">
            <div data-role="header">
                <a href="#create_chatroom" data-role="button" data-icon="plus"><?php echo $frei_trans["chatroom_creator"]; ?></a>
                <h1><?php echo $frei_trans["chatroom_title"]; ?></h1>
            </div><!-- /header -->

            <div data-role="content">
                <div class="frei_mobile_users" id="frei_mobile_rooms">
                    <div class="u_scroller">
                        <ul class="users_ul" id="rooms_ul" data-role="listview" data-inset="true" data-filter="true"  data-filter-placeholder="<?php echo $frei_trans["mobile_filter_list"];?>">
                            <?php echo $frei_trans["mobile_loading"]; ?>
                        </ul>
                    </div>
                </div>


            </div>

            <script type="text/javascript">
                
                jQuery('document').ready(function($) {
                   
                    if (FreiChat.plugins.is_allowed('CHATROOM')) {
                        
                        $('.frei_bottom_menu_no_chatroom').hide();
                        $('.frei_bottom_menu').show();
                    }else{

                        $('.frei_bottom_menu_no_chatroom').show();
                        $('.frei_bottom_menu').hide();                        
                    }                 
                });

                function fill_room_data() {

                    if (!mobile.room_array_loaded) {
                        setTimeout(fill_room_data, 500);
                        return;
                    }

                    mobile.room_data_filled = true;
                    var len = mobile.room_array.length,
                            wrapper = "", data, del, lock,
                            room_name;


                    for (var i = 0; i < len; i++) {

                        del = lock = "";
                        room_name = mobile.room_array[i].room_name.replace(/&#039;/g, "\\'");

                        if (mobile.room_array[i].room_author == freidefines.GEN.fromid) {
                            del = '<a class="chatroom_delete_button" data-shadow="false" data-role="button" data-corners="false" data-inline="true" data-mini="true" onclick="FreiChat.delete_chatroom(\'' + mobile.room_array[i].room_id + '\',event)">Delete</a>';
                        }

                        if (mobile.room_array[i].room_type == 1 || mobile.room_array[i].room_type == 3) {
                            lock = "<img class='chatroom_lock_img' src='" + FreiChat.make_url(freidefines.lockedimg) + "' />";
                        }

                        data = mobile.room_array[i].room_type;
                        wrapper += "<li id='frei_lobby_room_" + mobile.room_array[i].room_id + "' data-role='button' data-corners='false' class='rooms_li' \n\
                                    onclick=\"FreiChat.open_panel('" + room_name + "','" + mobile.room_array[i].room_id + "','chatroom', '" + data + "')\" class=\"freichat_userlist\" \n\
                                    ><div> \n\
                                    <div class=\"freichat_userscontentname no-padding\">" + mobile.room_array[i].room_name + "</div>\n\
                                    <div style='margin-top:1px' class='new_messages_no' id='room_new_messages_" + mobile.room_array[i].room_id + "'>" + mobile.room_online_count[i].online_count + "</div>\n\
                                    <span>" + lock + "</span>\n\
                                    " + del + "\n\
                                    </div></li>";

                    }

                    FreiChat.divrooms.html(wrapper).trigger("create");
                }
                ;

                $jn("#chatroom").on("pageinit", fill_room_data);

            </script>

            <div data-role="footer" data-position="fixed" data-id="navigator" data-iconpos="left">
                <div data-role="navbar">
                    <ul class="frei_bottom_menu_no_chatroom">
                        <li> <a data-icon="chat" href="#users"><?php echo $frei_trans["mobile_chat"]; ?></a></li>
                        <li> <a data-icon="gear" href="#settings"><?php echo $frei_trans["mobile_settings"]; ?></a></li>

                    </ul>
                    <ul class="frei_bottom_menu">
                        <li> <a data-icon="chat" href="#users"><?php echo $frei_trans["mobile_chat"]; ?></a></li>
                        <li> <a class="ui-btn-active ui-state-persist" data-icon="chatroom" href="#"><?php echo $frei_trans["chatroom_title"]; ?></a></li>
                        <li> <a data-icon="gear" href="#settings"><?php echo $frei_trans["mobile_settings"]; ?></a></li>

                    </ul>

                </div>
            </div>
        </div>
        <!-- end of fourth page -->

        <!-- fifth page: chatroom users -->
        <div id="chatroom_users" data-role="page">
            <div data-role="header">
                <a href="#chat" data-role="button" data-icon="back"><?php echo $frei_trans["mobile_back"]; ?></a>
                <h1><?php echo $frei_trans["mobile_chatroom_users"]; ?></h1>
            </div><!-- /header -->

            <div data-role="content">
                <div class="frei_mobile_users" id="frei_mobile_rooms_users">
                    <div class="u_scroller">
                        <ul class="users_ul" id="rooms_users_ul" data-role="listview" data-inset="true" data-filter="true"  data-filter-placeholder="<?php echo $frei_trans["mobile_filter_list"];?>">
                            <?php echo $frei_trans["mobile_loading"]; ?>
                        </ul>
                    </div>
                </div>


            </div>

            <script type="text/javascript">


                function fill_room_user_data() {
                    FreiChat.room_users_div = $jn('#rooms_users_ul');
                    fill_room_my_data();
                    fill_room_other_data();
                }
                function fill_room_my_data() {

                    var wrapper = "<li class='users_li rooms_li rooms_li_hovered' \n\
                                    class=\"freichat_userlist\" \n\
                                    ><div> \n\
                                    <div class=\"freichat_userscontentname no-padding\">" + freidefines.GEN.fromname + "</div>\n\
                                    </div></li>";


                    FreiChat.room_users_div.html(wrapper);

                }

                function fill_room_other_data() {
                    var in_room = Get_Cookie("frei_inroom");
                    if (FreiChat.in_room == -1 && in_room) {
                        FreiChat.in_room = in_room;
                    }

                    if (!mobile.chatroom_users[FreiChat.in_room] && mobile.called_func < 10) {
                        setTimeout(fill_room_other_data, 500);
                        mobile.called_func++;
                        return;
                    } else if (mobile.called_func === 10) {
                        //check for 5 seconds
                        mobile.called_func = 0;
                        return;
                    }


                    mobile.room_userdata_filled = true;
                    var userdata = mobile.chatroom_users[FreiChat.in_room];
                    var len = userdata.length,
                            wrapper = "",
                            show_avatar = "block";

                    for (var i = 0; i < len; i++) {

                        wrapper += "<li class='users_li' id='freichat_user_" + userdata[i].userid + "' \n\
                            onclick=\"FreiChat.open_panel('" + userdata[i].username + "','" + userdata[i].userid + "')\" class=\"freichat_userlist\" \n\
                            > \n\
                            <span>\n\
                            <span style='display:" + show_avatar + "' class='freichat_userscontentavatar'>\n\
                            <img src='" + userdata[i].avatar + "' height='22' width='22' alt='avatar' align='left' class='freichat_userscontentavatarimage'/></span>\n\
                            </span>\n\
                            <span class=\"freichat_userscontentname\">" + userdata[i].username + "</span>\n\
                            <span>&nbsp;<img class ='freichat_userscontentstatus' style='padding-top:4px; padding-right:4px;' src='" + userdata[i].img_url + "' height='12' width='12' alt='status' /></span>\n\
                            </li>";

                    }

                    FreiChat.room_users_div.append(wrapper).listview("refresh");
                }
                ;

                $jn("#chatroom_users").on("pageinit", fill_room_user_data);

            </script>

        </div>
        <!-- end of fifth page -->

        <!-- popups here -->

        <!-- upload/ send file -->


        <!-- index.php could be any script server-side for receive uploads. -->


        <div data-overlay-theme="b" data-theme="a" data-role="dialog" id="attachment_widget_popup">
            <div data-role="header" data-theme="a">
                <h1><?php echo $frei_trans["mobile_file_title"]; ?> </h1>
            </div>
            <div data-role="content" data-theme="d">
                <form id="jqm_u_form" data-ajax="false" name="upload" action="" method="post" enctype="multipart/form-data">

                    <input id="jqm_u_fromid" type="hidden" name="fromid"/>
                    <input id="jqm_u_fromname" type="hidden" name="fromname"/>
                    <input id="jqm_u_toid" type="hidden" name="toid"/>
                    <input id="jqm_u_toname" type="hidden" name="toname"/>

                    <input data-role="button" data-theme="b" type="file" name="file" id="attachment_widget_file" /><br/>
                    <input id="jqm_u_form_submit" data-inline="true" data-role="button" data-theme="a" class ="frei_upload_button" type="button" value="<?php echo $frei_trans["mobile_send"]; ?>" />
                    <a data-inline="true" data-rel="back" data-role="button" data-theme="b" ><?php echo $frei_trans["mobile_cancel"]; ?></a>
                    <!--  <div id="upload_status"></div>
                       <div id="file_upload_status_error"></div>
                    -->         </form>
                <div style="display:none" id="on_upload_complete">
                    <p><span id="file_upload_status"></span></p>
                    <a onclick="mobile.reset_upload_dialog()" id="go_back" data-role="button" data-theme="a" href="#" data-rel="back" ><?php echo $frei_trans["mobile_back"]; ?></a>
                </div>
            </div>
        </div>




        <!-- upload / send file-->


        <!-- send mail -->



        <div data-overlay-theme="b" data-theme="a" data-role="dialog" id="mail_widget_popup">
            <div data-role="header" data-theme="a">
                <h1><?php echo $frei_trans["mobile_mail_title"]; ?></h1>
            </div>
            <div data-role="content" data-theme="d">
                <form id="jqm_m_form" data-ajax="false" name="upload" action="" method="post" enctype="multipart/form-data">

                    <label for="subject"><?php echo $frei_trans["mobile_mail_subject"]; ?>:</label>
                    <input type="text" id="jqm_m_subject" name="subject" value=""/>

                    <label for="mailto"><?php echo $frei_trans["mobile_mail_rec_email"]; ?>:</label>
                    <input type="text" id="jqm_m_mailto" name="mailto" value=""/>


                    <input id="jqm_m_form_submit" data-inline="true" data-role="button" data-theme="a" class ="frei_mail_button" type="button" value="<?php echo $frei_trans["mobile_send"]; ?>" />
                    <a data-inline="true" data-rel="back" data-role="button" data-theme="b" ><?php echo $frei_trans["mobile_cancel"]; ?></a>
                    <span style="text-align:center;display:none" id="jqm_m_loader" ><img src="plugins/mobile/images/ajax-loader.gif"/></span>

                    <!--  <div id="upload_status"></div>
                       <div id="file_upload_status_error"></div>
                    -->         </form>
                <div style="display:none" id="on_mail_complete">
                    <p><span id="file_mail_status"></span></p>
                    <a onclick="mobile.reset_mail_dialog()" id="go_back" data-role="button" data-theme="a" href="#" data-rel="back" ><?php echo $frei_trans["mobile_back"]; ?></a>
                </div>
            </div>
        </div>




        <!-- send mail-->



        <!-- create chatroom -->



        <div data-overlay-theme="b" data-theme="a" data-role="dialog" id="create_chatroom">
            <div data-role="header" data-theme="a">
                <h1><?php echo $frei_trans["create_chatroom_title"]; ?></h1>
            </div>
            <div data-role="content" data-theme="d">
                <form id="jqm_m_chatroom_form" data-ajax="false" name="upload" action="" method="post" enctype="multipart/form-data">

                    <div style="display: none" id='frei_chatroom_creator_error' class='frei_chatroom_creator_error'>
                        <label for="error"><?php echo $frei_trans["chatroom_creator_exists"]; ?>:</label>
                    </div>

                    <div id="frei_chatroom_creator"></div>
                    <input placeholder="<?php echo $frei_trans["chatroom_creator_new"]; ?>" id="frei_chatroom_creator_input" type="text" name="chatroom_name" value=""/>

                    <label>
                        <input type="checkbox" id="frei_chatroom_creator_check" name="checkbox-0 "><?php echo $frei_trans["chatroom_creator_paswd"]; ?>
                    </label>
                    <input placeholder="<?php echo $frei_trans["chatroom_creator_pass"]; ?>" id="frei_chatroom_creator_password" type="text" name="chatroom_password" value=""/>


                    <input data-rel="back" id="jqm_m_chatroom_form_submit" data-inline="true" data-role="button" data-theme="a" type="button" value="<?php echo $frei_trans["chatroom_creator"]; ?>" />
                    <a data-inline="true" id="jqm_m_chatroom_form_cancel" data-role="button" data-theme="b" ><?php echo $frei_trans["mobile_cancel"]; ?></a>

                </form>
            </div>
        </div>
        <script type="text/javascript">
            $jn('#jqm_m_chatroom_form_submit').click(function() {

                FreiChat.create_chatroom(true);

            });

            $jn("#frei_chatroom_creator_check").change(function() {
                if ($jn(this).is(":checked")) {
                    $jn("#frei_chatroom_creator_password").textinput('enable');
                } else {
                    $jn("#frei_chatroom_creator_password").textinput('disable');
                }
            });

            $jn('#jqm_m_chatroom_form_cancel').click(function() {

                $jn('#create_chatroom').dialog('close');
            });

            $jn('#create_chatroom').on("pageinit", function() {
                $jn("#frei_chatroom_creator_password").textinput('disable');
            });
        </script>




        <!-- create chatroom -->


        <!-- popups here -->

    </body>

    <script>

        mobile.toast = function(msg) {
            $jn("<div class='notification'>" + msg + "</div>")
                    .appendTo($jn.mobile.pageContainer)
                    .animate({
                        top: "4%",
                        left: "8%",
                        height: "toggle"
                    }, 1000, function() {
                        $jn(this).delay(3000)
                                .fadeOut(400, function() {
                                    $jn(this).remove();
                                });
                    });
        };

    </script>

</html>
