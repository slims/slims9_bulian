<?php
if (!isset($_SESSION['phplogin'])
        || $_SESSION['phplogin'] !== true) {
    header('Location: ../administrator/index.php'); //Replace that if login.php is somewhere else
    exit;
}

//require "../arg.php";

/* * ***************************************************************************************** */

class param extends FC_admin {

    public function __construct() {
        parent::__construct();
        $this->init_vars();
        $this->name_supported_drivers = array('JCB', 'CBE', 'JSocial', 'Joomla', 'CBE_2', 'Elgg','SMF2','SMF');
        $this->buddy_supported_drivers = array('JCB', 'CBE', 'JSocial', 'Custom', 'CBE_2', 'Elgg', "se4", 'Jcow');
        $this->link_profile_supported_drivers = array('JCB', 'JSocial', 'Custom', 'CBE_2','se4', 'Jcow','Phpfusion');
        $this->sef_rec_supported_drivers = array('JCB', 'CBE_2', 'JSocial');
        $this->joomla_drivers = array('JCB', 'CBE', 'JSocial', 'Joomla', 'CBE_2');
        $this->smiley_id = 0;
    }

    //------------------------------------------------------------------
    public function build_config_array() {
        $config = array();


        $special_cases = array(
            "displayname" => "name_supported_drivers",
            "link_profile" => "link_profile_supported_drivers",
            "sef_link_profile" => "sef_rec_supported_drivers",
            "ug_ids" => "joomla_drivers",
            
        );
        

        foreach ($_POST as $post_key => $post_value) {

            if (array_key_exists($post_key, $special_cases)) {
                if (in_array($this->driver, $this->$special_cases[$post_key])) {
                    $config[$post_key] = $post_value;
                }
            } else {
                $config[$post_key] = $post_value;
            }
        }

        return $config;
    }

    public function create_url($name) {
        $url = str_replace("administrator/admin.php", "", $this->url);

        return $url . 'client/themes/' . $this->freichat_theme . '/' . $name;
    }

//--------------------------------------------------------------------------------------------
}

$param = new param();
if (isset($_POST['draggable']) == true) {
    $config = $param->build_config_array();
    $param->update_config($config);
    $param->build_vars();
}

if (isset($_REQUEST['purge'])) {
    $param->purge_mesg_history($_GET['days']);
    die('Messages Purged successfully.');
}
?>

<div class="row-fluid sortable ui-sortable">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-pencil"></i> Client side Configuration</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">


                <form name="params" action='<?php $_SERVER['PHP_SELF']; ?>' method="POST">

                    <p>Disable FreiChat ?</p>
                    <select name="BOOT">
                        <option value="no"<?php $param->default_param("BOOT", "no"); ?>>Yes</option>
                        <option value="yes"<?php $param->default_param("BOOT", "yes"); ?>>No</option>
                    </select>
                    <br/><br/>


                    <p>Disable FreiChat for guests ?</p>
                    <select name="exit_for_guests">
                        <option value="no"<?php $param->default_param("exit_for_guests", "no"); ?>>No</option>
                        <option value="yes"<?php $param->default_param("exit_for_guests", "yes"); ?>>Yes</option>
                    </select>
                    <br/><br/>


                    <p>Who should be on the online users list</p>
                    <select name="show_name">
                        <option value="guest"<?php $param->default_param("show_name", "guest"); ?>>Everyone</option>
                        <option value="user"<?php $param->default_param("show_name", "user"); ?>>Only registered Users</option>

                        <?php
                        if (in_array($param->driver, $param->buddy_supported_drivers) == true) {
                            echo '<option value=' . "buddy ";
                            $param->default_param("show_name", "buddy");
                            echo">Only your Buddies</option>";
                        }
                        ?>

                    </select>
                    <?php
                    if (in_array($param->driver, $param->joomla_drivers) == true) {
                        echo '<br/><br/>Enter group ids separated by commas [user groups created in joomla user manager]<br/>


                                                  
                            <a href="admin_files/home/example_ug_ids.png" target="_blank">click here</a>  to see an example of group ids

                            <div id="fade"></div>

                            <input type="text" name="ug_ids" value="' . $param->default_value("ug_ids") . '"/>
                            <br/><span style="color:#999"><em>if left empty freichat will fetch users as per the previous option else will override it.</em></span>';
                    }
                    ?>
                    <br/><br/>                <?php
//echo $param->default_param("link_profile", "enabled");
                    if (in_array($param->driver, $param->link_profile_supported_drivers) == true) {
                        echo '
                        <p>Show link to user\'s profile</p>
                        <select name="link_profile">
                            <option value="enabled" ';
                        $param->default_param("link_profile", "enabled");
                        echo ' >Yes</option>
                            <option value="disabled" ';
                        $param->default_param("link_profile", "disabled");
                        echo '>No</option>
                            

                        </select><br/><br/>';
                        if (in_array($param->driver, $param->sef_rec_supported_drivers) == true) {
                            echo 'SEF support for profile link<br/>
                        <select name="sef_link_profile">
                            <option value="enabled" ';
                            $param->default_param("sef_link_profile", "enabled");
                            echo ' >Yes</option>
                            <option value="disabled" ';
                            $param->default_param("sef_link_profile", "disabled");
                            echo '>No</option>
                            

                        </select>';
                        }

                        echo ' <br/><br/>
                    ';
                    }
                    ?>



                    <p>Allow guests to change their name[ Changed names will get a prefix "guest"]</p>
                    <select name="allow_guest_name_change">
                        <option value="yes"<?php $param->default_param("allow_guest_name_change", "yes"); ?>>yes</option>
                        <option value="no"<?php $param->default_param("allow_guest_name_change", "no"); ?>>no</option>
                    </select>
                    <br/><br/>

                    <p>Show chat time for messages in the chatwindow</p>
                    <select name="chat_time_shown_always">
                        <option value="yes"<?php $param->default_param("chat_time_shown_always", "yes"); ?>>always</option>
                        <option value="no"<?php $param->default_param("chat_time_shown_always", "no"); ?>>only on hover for that message</option>
                    </select>
                    <br/><br/>

                    
                    <p>Show Avatar</p>
                    <select name="show_avatar">
                        <option value="block"<?php $param->default_param("show_avatar", "block"); ?>>Yes</option>
                        <option value="none"<?php $param->default_param("show_avatar", "none"); ?>>No</option>
                    </select>
                    <br/><br/>

                    <p>The message content box for the chat window should </p>
                    <select name="content_height">
                        <option value="auto"<?php $param->default_param("content_height", "auto"); ?>>resize with content</option>
                        <option value="200px"<?php $param->default_param("content_height", "200px"); ?>>have fixed height</option>
                    </select>
                    <br/><br/>

                    <p>The options like clear history, save history , send email , send files in the chat window should be by-default </p>
                    <select name="addedoptions_visibility">
                        <option value="HIDDEN"<?php $param->default_param("addedoptions_visibility", "HIDDEN"); ?>>hidden</option>
                        <option value="SHOWN"<?php $param->default_param("addedoptions_visibility", "SHOWN"); ?>>shown</option>
                    </select>
                    <br/><br/>



                    <?php
                    if (in_array($param->driver, $param->name_supported_drivers) == true) {
                        echo '<p>Show Username or Nickname(name)</p><select name="displayname">';
                        echo '<option value=' . "username ";
                        $param->default_param("displayname", "username");
                        echo">username</option>";
                        echo '<option value=' . "name ";
                        $param->default_param("displayname", "name");
                        echo">nickname</option>";
                        if ($param->driver == 'JCB') {
                            echo '<option value=' . "fullname ";
                            $param->default_param("displayname", "fullname");
                            echo">fullname</option>";
                        }
                        echo '</select><br/><br/>';
                    }
                    ?>


                    <p>Select a theme for the chat</p>
                    <select name="freichat_theme">
                        <?php
                        if ($handle = opendir('../client/themes/')) {
                            while (false !== ($file = readdir($handle))) {
                                if ($file != "." && $file != ".." && $file != '.svn' && is_dir('../client/themes/' . $file)) {


                                    echo '<option value=' . "$file ";
                                    $param->default_param("freichat_theme", $file);
                                    echo">$file</option>";
                                }
                            }
                            closedir($handle);
                        } else {
                            echo 'directory open failed';
                        }
                        ?>
                    </select>
                    <br/><br/>


                    <p>Draggable chatwindows feature should be </p>
                    <select name="draggable">
                        <option value="enable"<?php $param->default_param("draggable", "enable"); ?>>Enabled</option>
                        <option value="disable"<?php $param->default_param("draggable", "disable"); ?>>Disabled</option>
                    </select>
                    <br/><br/>

                    <p>ChatBox on load should be</p>
                    <select name="load">
                        <option value="show"<?php $param->default_param("load", "show"); ?>>Maximized</option>
                        <option value="hide"<?php $param->default_param("load", "hide"); ?>>Minimized</option>
                    </select><br/><br/>

                    <p>ChatBox on load can be set by user</p>
                    <select name="chatbox_status">
                        <option value="true"<?php $param->default_param("chatbox_status", "true"); ?>>Yes</option>
                        <option value="false"<?php $param->default_param("chatbox_status", "false"); ?>>No</option>
                    </select><br/>                Note: Setting this to yes will override your previous setting, thus giving more preference to the user . 
                    <br/><br/>

                    <p>Remove Jquery Conflicts <span onmousedown="helpme1()"><img src="<?php echo '../client/jquery/img/about.jpeg' ?>" alt="About"/></a></span></p>
                    <select name="conflict">
                        <option value="true"<?php $param->default_param("conflict", "true"); ?>>Yes</option>
                        <option value=""<?php $param->default_param("conflict", ""); ?>>No</option>
                    </select><br/><br/>

                    <p>Show Jquery Animations</p><br/>
                    <select name="fxval">
                        <option value="true"<?php $param->default_param("fxval", "true"); ?>>Yes</option>
                        <option value="false"<?php $param->default_param("fxval", "false"); ?>>No</option>
                    </select>
                    <br/><br/>

                    <!--
                    <p>Play sound on new message </p><br/>
                    <select name="playsound">
                        <option value="true"<?php $param->default_param("playsound", "true"); ?>>Yes</option>
                        <option value="false"<?php $param->default_param("playsound", "false"); ?>>No</option>
                    </select>
                    -->


                    <br/>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>                   
        </div>
    </div><!--/span-->
</div>