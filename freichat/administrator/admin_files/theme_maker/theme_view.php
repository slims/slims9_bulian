<link rel="stylesheet" type="text/css" href="../administrator/admin_files/theme_maker/lib/css/gradX.css" />

<link rel="stylesheet" type="text/css" href="../client/jquery/freichat_themes/freichatcss.php?do=theme&get_latest=true" />
<link rel="stylesheet" type="text/css" href="../administrator/admin_files/theme_maker/style.css" />
<link rel="stylesheet" type="text/css" href="../administrator/admin_files/theme_maker/lib/css/switches.css" />
<link rel="stylesheet" type="text/css" href="../administrator/admin_files/theme_maker/lib/colorpicker/css/colorpicker.css" />


<script type="text/javascript">
<?php
//session_start();
//error_reporting(-1);

$path = '../';


//require $path . 'arg.php';
//$_SESSION[$uid . 'new_project'] = $chk->chk_project();


$thm = new FreiChat();
$thm->init_vars();
$thm->get_js_config();
$uid = $thm->uid;
$valid_exts = $thm->valid_exts;
$frei_trans = $thm->inc_lang();
$_SESSION[$uid . 'curr_theme'] = $_GET['theme_name'];
require $path . 'administrator/admin_files/theme_maker/lib/colorpicker/js/colorpicker.min.js';
require $path . 'administrator/admin_files/theme_maker/lib/js/gradX.js';

require $path . 'client/themes/' . $_SESSION[$uid . 'curr_theme'] . '/argument.php';
//require $path . 'client/jquery/js/jquery.1.7.1.js';
//require $path . 'client/jquery/js/jquery-ui.js';
require $path . 'administrator/admin_files/theme_maker/definitions.js';
require $path . 'administrator/admin_files/theme_maker/plugins.js';
require $path . 'administrator/admin_files/theme_maker/functions.js';
require $path . 'administrator/admin_files/theme_maker/lib/js/md5.js';
require $path . 'client/plugins.js';
//require $path . 'client/chatroom.js';

require $path . 'administrator/admin_files/theme_maker/theme_builder.js';
?>
</script>

<div id='notification' class="hideme_now"></div>

<div id="css_tester_div"></div>



<div id="element_css_container" class="row-fluid sortable ui-sortable editor_window">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-pencil"></i> Editor</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">
                <div class="gradient_selector_content hideme_now" title="select and modify gradient" id="gradient_selector_content">
                    <div id="gradient_selector_content_div"></div>
                    <br/>
                    <div id="close_gradient_selector" class="btn btn-success">Close</div>
                </div>

                <div class='upload_div  hideme_now' title="upload image to be replaced" id='upload_div'> 
                    <div>
                        <form action='' name='upload' method='post' enctype='multipart/form-data'>
                            <input id='file-upload-status' disabled='disabled' class='saveas_theme_input'/>

                            <div class='theme_button upload_submit_button' onclick='return FreiChat.file_upload();'>UPLOAD</div>
                            <div class='file-upload'>
                                <div>SELECT</div>
                                <input id='file_input_upload' accept='jpeg,jpg,png,gif,zip' data-no-uniform="true" type='file' name='file' /> 
                            </div>

                        </form>
                    </div>

                </div>


                <!-- All element rules filled onclick -->
                <div id='style_rules' title="edit css styles on fly" class='style_rules' id='parameters'>
                    <div id='style_header'>
                        <div class="style_rules_description" id="style_rules_description"><div style="text-align:center;">please select any element from left</div></div>
                        <div id="actions_style_rules" class="hideme_now">
                            <div class='btn add_new_style add_styles' id='add_styles'>add css</div>
                            <div class='btn add_new_style' id='add_new_style'>add new style</div>
                            <div class='btn add_new_style get_parent' id='get_parent'>get parent element</div>
                            <div class='btn add_new_style' id='get_states'>states</div>
                            <div class='btn add_new_style hideme_now' id='replace_image'>replace this image</div>

                        </div>
                    </div>

                    <div id="style_rules_body">
                        <div id='style_rules_content'></div>

                        <div style="height: auto;max-height: 440px;" class="add_new_style_content" id="add_styles_textarea">

                            <textarea data-rel="tootltip" title="for eg. color:green; width:100px;" class="autogrow" style="max-height: 400px" id="add_styles_value">
                            </textarea>
                            <div>
                                <span class="btn add_style_btn" id="add_styles_button">add css</span>
                                <span class="btn" id="cancel_styles_button">cancel</span>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- All element psuedo-classes / states -->
                <div id="element_states" title="edit element states on fly" class="style_rules hideme_now">
                    <div id="ele_states_header" class="ele_states_header">
                        <div class='btn add_new_state' id='add_new_state'>add new state</div>
                        <div class='btn add_new_state' id='return_to_normal'>return</div>
                    </div>

                    <div id="element_states_content" class="ele_states_content">
                    </div>
                    <div id="add_new_state_style_content" class="add_new_style_content">
                        <span class="saveas_theme_font style_theme_font">property:</span>
                        <input class="input_add_style" id="property_add_state_style" type="text"/> <br/>
                        <span class="saveas_theme_font style_theme_font"> value:</span>
                        <input class="input_add_style" id="value_add_state_style" type="text"/> 
                        <div>
                            <span class="btn add_style_btn" id="add_state_style_button">add style</span>
                            <span class="btn" id="cancel_state_style_button">cancel</span>
                        </div>
                    </div>

                    <div id="add_new_state_div" class="add_new_style_content">
                        <input class="input_add_style" id="state_name" type="text" placeholder="valid state name"/> <br/>
                        <div>
                            <span class="btn add_style_btn" id="add_state_button">add state</span>
                            <span class="btn" id="cancel_state_button">cancel</span>
                        </div>
                    </div>

                    <div style="height: auto;max-height: 440px;" class="add_new_style_content" id="add_state_styles_textarea">

                        <textarea class="autogrow" style="max-height: 400px" id="add_state_styles_value">
                        </textarea>
                        <div>
                            <span class="btn add_style_btn" id="add_state_styles_button">add css</span>
                            <span class="btn" id="cancel_state_styles_button">cancel</span>
                        </div>

                    </div>


                </div>

            </div>                   
        </div>
    </div>
</div>


<!--- THEME MENU --->


<div class="theme_top">    
    <div id='theme_menu' class='theme_menu'>

        <div style="display:none" class="switch candy blue menu_switch">
            <input data-no-uniform="true" id="parameters" name="view" type="radio">
            <label for="css" onclick="FreiChat.switch_button('theme_mode', 'parameters')">Edit CSS</label>

            <input data-no-uniform="true" id="image" name="view" type="radio">	
            <label for="images" onclick="FreiChat.switch_button('theme_mode', 'image')">Edit images</label>

            <span class="slide-button"></span>
        </div>
        <div style="display:none" class="switch candy blue menu_switch">
            <input data-no-uniform="true" id="chat" name="view2" type="radio">
            <label for="chat" onclick="FreiChat.switch_button('freichat_switch', 'chat')">Edit chat</label>

            <input data-no-uniform="true" id="chatroom" name="view2" type="radio">	
            <label for="chatroom" onclick="FreiChat.switch_button('freichat_switch', 'chatroom')">Edit chatroom</label>

            <span class="slide-button"></span>
        </div>
        <div class='btn' id='back_to_menu'><i class="icon-arrow-left"></i> back to menu</div>

        <div class='btn' id='restore'><i class="icon-repeat"></i> undo</div>
        <div id='save_theme'  class='btn' id='save'><i class="icon-file"></i> save</div>
        <div class='btn' id='info_button'><i class="icon-arrow-down"></i> info</div>
        <div class='btn' id='set_default_button'><i class="icon-star"></i> set as default theme</div>
        <div class='btn' id='help'><i class="icon-info-sign"></i> help</div>


    </div>

    <div class="theme_info" id="theme_info">

        <div id="current_theme" class="btn current_theme">

            editing theme: <span id="current_theme_name"></span>
        </div>
        <div class="btn current_theme">
            Hint: <span id="theme_hint"></span>
        </div>
    </div>
</div>


<!--- THEME FREICHAT ELEMENTS --->

<div class="row-fluid sortable ui-sortable element_window">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-eye-open"></i> Elements</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">

                <div class="back_to_list btn" id="back_to_list"><i class="icon-arrow-left"></i> Back to element list</div>
                <div class="t_grid hideme_now" id="t_grid"></div>

                <div id="t_blocks">
                    <div class="t_block t_chatwindow_min" id="t_chatwindow_min">
                        <div class="t_block_img t_chatwindow_min_img"></div>
                        <div class="t_block_label">chatwindow [minimized]</div>
                    </div>

                    <div class="t_block t_chatwindow_options" id="t_chatwindow_options">
                        <div class="t_block_img t_chatwindow_options_img"></div>
                        <div class="t_block_label">chatwindow options</div>
                    </div>

                    <div class="t_block" id="t_frei_options">
                        <div class="t_block_img t_frei_options_img"></div>
                        <div class="t_block_label">status options</div>
                    </div>


                    <div class="t_block t_freichat" id="t_freichat">
                        <div class="t_block_img t_freichat_img"></div>
                        <div class="t_block_label">chatbox</div>
                    </div>

                    <div class="t_block t_chatwindow_max" id="t_chatwindow_max">
                        <div class="t_block_img t_chatwindow_max_img"></div>
                        <div class="t_block_label">chatwindow [maximized]</div>
                    </div>



                    <div class="t_block t_chatroom" id="t_chatroom">
                        <div class="t_block_img t_chatroom_img"></div>
                        <div class="t_block_label">chatroom</div>
                    </div>
                </div>
                <div id="freichathtml"></div>

            </div>                   
        </div>
    </div>
</div>

