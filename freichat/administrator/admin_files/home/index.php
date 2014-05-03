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
        $this->name_supported_drivers = array('JCB', 'CBE', 'JSocial', 'Joomla', 'CBE_2', 'Elgg');
        $this->buddy_supported_drivers = array('JCB', 'CBE', 'JSocial', 'Custom', 'CBE_2', 'Elgg');
        $this->link_profile_supported_drivers = array('JCB', 'JSocial', 'Custom', 'CBE_2');
        $this->sef_rec_supported_drivers = array('JCB', 'CBE_2', 'JSocial');
        $this->joomla_drivers = array('JCB', 'CBE', 'JSocial', 'Joomla', 'CBE_2');
        $this->smiley_id = 0;
    }

    //------------------------------------------------------------------
    public function build_config_array() {
        $config = array();
        if (in_array($this->driver, $this->name_supported_drivers) == true)
            $config["displayname"] = $_POST['displayname'];
        if (in_array($this->driver, $this->link_profile_supported_drivers) == true)
            $config['link_profile'] = $_POST['link_profile'];
        if (in_array($this->driver, $this->sef_rec_supported_drivers) == true)
            $config['sef_link_profile'] = $_POST['sef_link_profile'];

        $config['ug_ids'] = $_POST['ug_ids'];
        $config["content_height"] = $_POST['content_height'];
        $config["chatbox_status"] = $_POST['chatbox_status'];
        $config["show_name"] = $_POST['show_name'];
        $config["chatspeed"] = $_POST['chatspeed'];
        $config["fxval"] = $_POST['fxval'];
        $config["draggable"] = $_POST['draggable'];
        $config["conflict"] = $_POST['conflict'];
        $config["msgSendSpeed"] = $_POST['msgSendSpeed'];
        $config["show_avatar"] = $_POST['show_avatar'];
        $config["debug"] = $_POST['debug'];
        $config["freichat_theme"] = $_POST['freichat_theme'];
        $config["lang"] = $_POST['lang'];
        $config["load"] = $_POST['load'];
        $config["JSdebug"] = $_POST['JSdebug'];
        $config["playsound"] = $_POST['playsound'];
        $config["busy_timeOut"] = $_POST['busy_timeOut'];
        $config["offline_timeOut"] = $_POST['offline_timeOut'];
        $config["GZIP_handler"] = $_POST['GZIP_handler'];
        $config['polling'] = $_POST['polling'];
        $config['polling_time'] = $_POST['polling_time'];
        $config['BOOT'] = $_POST['BOOT'];
        $config['exit_for_guests'] = $_POST['exit_for_guests'];
        $config['addedoptions_visibility'] = $_POST['addedoptions_visibility'];
        $config['ACL']['chatroom']['user'] = $this->return_checked_value('p_chatroom_user');
        $config['ACL']['chatroom']['guest'] = $this->return_checked_value('p_chatroom_guest');
        $config['ACL']['filesend']['user'] = $this->return_checked_value('p_filesend_user');
        $config['ACL']['filesend']['guest'] = $this->return_checked_value('p_filesend_guest');
        $config['ACL']['mail']['user'] = $this->return_checked_value('p_mail_user');
        $config['ACL']['mail']['guest'] = $this->return_checked_value('p_mail_guest');
        $config['ACL']['save']['user'] = $this->return_checked_value('p_save_user');
        $config['ACL']['save']['guest'] = $this->return_checked_value('p_save_guest');
        $config['ACL']['smiley']['user'] = $this->return_checked_value('p_smiley_user');
        $config['ACL']['smiley']['guest'] = $this->return_checked_value('p_smiley_guest');
        $config['ACL']['chat']['user'] = $this->return_checked_value('p_chat_user');
        $config['ACL']['chat']['guest'] = $this->return_checked_value('p_chat_guest');

        return $config;
    }

    public function purge_mesg_history($days) {
        //$time = $days * 24 * 60 * 60 * 10;
        $date_now = $this->mysql_now;
        $date_then = date('Y-m-d H:i:s', strtotime('-'.$days.' days', strtotime($date_now)));
        
        $delete_mesg_query = "DELETE FROM frei_chat WHERE recd =1 AND sent < '$date_then'";
        echo $delete_mesg_query;
        $this->db->query($delete_mesg_query);
    }

    public function create_url($name) {
        $url = str_replace("administrator/admin.php", "", $this->url);

        return $url . 'client/themes/' . $this->freichat_theme . '/' . $name;
    }

    public function create_tr($id, $symbol, $image_name) {

        $image = "<img id=$image_name src=" . $this->create_url($image_name) . " />";
        $delete = "<img height=12px width=12px onmousedown=delete_tr(" . $id . ",'" . $image_name . "') src='admin_files/theme_maker/delete.png' />";
        return "<tr id=smiley_tr_$id><td>$id</td><td id='smiley_symbol_" . $id . "'>$symbol</td><td>$image</td><td>" . $delete . "</td></tr>";
    }

    public function build_smiley_table() {

        $smileys = $this->get_smileys();
        $i = 1;

        $s_wrapper = "<table class='table_cls'><th>#</th><th>symbol</th><th>image</th><th>option</th>";
        $e_wrapper = "</table>";

        $content = '';
        foreach ($smileys as $smiley) {
            $content .= $this->create_tr($i, $smiley['symbol'], $smiley['image_name']);
            $i++;
        }

        $this->smiley_id = $i;

        if ($content != '') {
            return $s_wrapper . $content . $e_wrapper;
        }

        return '';
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
<link rel="stylesheet" type="text/css" href="admin_files/moderate_users/table_style.css" />

<style type="text/css">


    .table_cls th{
        color:black;
    }
</style>


<script type="text/javascript">
    function purge_mesg_history(){
        
        var days = $('#purge_mesg_period').val();
        $.get('admin.php?freiload=home&purge=true',{days:days},function(resp){
            alert('Messages Purged successfully.');
        });
    }
 
    function delete_tr(id,image_name){
        $('#smiley_tr_'+id).remove();
        
        $.get('admin_files/home/smiley.php?smiley=delete',{image_name:image_name});
    }
    
    
 
    function create_smiley_edit_div(id,image_name){
        
        if($('#smiley_edit_'+id).length > 0) {
            $('#smiley_edit_'+id).remove();
        }
        
        var symbol = $('#smiley_symbol_'+id).html();
        
        var div = "<div id='smiley_edit_"+id+"'>It is not necessary to upload any image for the smiley, if you only want to change the symbol of the smiley<br/><br/>symbol : <input id='smiley_symbol_input_"+id+"' type='text'/> <br/><br/> <input id='smiley_change_file_"+id+"' type='file' /> \n\
                    <br/><br/><span id='smiley_button_"+id+"'>done</span></div>";
        

        $('#dialog_box_smiley_change').html(div);
        $('#smiley_symbol_input_'+id).val(symbol);//alert($('#smiley_symbol_'+id).val());
        $('#smiley_edit_'+id).dialog({height:300,minWidth:400,title:'smiley edit'});
        $('#smiley_button_'+id).button().mousedown(function(){
            file_upload('smiley_change_file_'+id,'update',image_name);
            $('#smiley_edit_'+id).remove();
        });
    }
 
 
    
    function add_smiley_row(symbol,name) {
  
        var num=0; var max=0;
        var id = $(".table_cls tr").last().attr('id');
 
        id = parseInt(id.replace('smiley_tr_',''));
        id = id+1;
   

        var url = '<?php echo str_replace("administrator/admin.php", "", $param->url); ?>';
        
        url =  url+'client/themes/<?php echo $param->freichat_theme; ?>/'+name;
        var image = "<img id="+name+" src="+url+" />";
        var tr = "<tr id='smiley_tr_"+id+"'><td>"+id+"</td><td id='smiley_symbol_"+id+"'>"+symbol+"</td><td>"+image+"</td><td><img style='cursor:pointer' height=12px width=12px onmousedown=delete_tr("+id+",'"+name+"') src='admin_files/theme_maker/delete.png' /></td></tr>";
        $('.smiley_change').button();
 
        $('.table_cls').append(tr); 
    }
    
    function file_upload(id,action,image_name){
        var smiley_val='';
        
        
        
        if(action == 'insert'){
            smiley_val = $.trim($('#smiley_symbol').val());    
        }else{
            
            var formal_id = id.replace("smiley_change_file_","");
            formal_id = parseInt(formal_id);
            smiley_val = $.trim($('#smiley_symbol_input_'+formal_id).val());
        }
        var fileInput = document.getElementById(id);
        var file = fileInput.files[0];        
    
    
        if(action == 'insert'){
            if($('#smiley').val() == '' || smiley_val == ''){
                alert('Required fields not filled');
                return;
            }
        }
    
        if(smiley_val == ''){
            alert('symbol cannot be empty');
            return;
        }
        
        var xhr = new XMLHttpRequest();
        //var data = $('#upload_div').data("data");
        xhr.open('POST', 'admin_files/home/upload.php', true);                       
        xhr.setRequestHeader("Cache-Control", "no-cache");  
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");  
        xhr.setRequestHeader("X-File-Name", file.name);  
        xhr.setRequestHeader("X-File-Size", file.size);  
        xhr.setRequestHeader("X-File-Type", file.type);  
        xhr.setRequestHeader("X-Symbol", smiley_val);
        xhr.setRequestHeader("X-Action", action);   
            
        if(typeof image_name !== 'undefined'){
            xhr.setRequestHeader("X-Imagename", image_name);   
        }
        xhr.setRequestHeader("Content-Type", "application/octet-stream");  
        xhr.onreadystatechange = function() {
            if (xhr.readyState != 4)  {
                return; 
            }
            if(xhr.responseText == 'exceed') {
                alert('file size has exceeded the allowed limit');
            }else if (xhr.responseText == 'type') {
                alert('invalid file type');
            }
            else if(xhr.responseText == 'exists'){
                alert(' a smiley with the specified symbol already exists!') 
            }else{
                    
                add_smiley_row(smiley_val,xhr.responseText);
            }    
        };
                
            
        xhr.send(file);
    }
        
    
    $(document).ready(function(){
     

        $('#add_smiley').click(function(){file_upload('smiley','insert');})
    
        $('.smiley_change').button();
    
    
    });

    
</script>



<form name="params" action='<?php $_SERVER['PHP_SELF']; ?>' method="POST">

    <br/><br/>
    <div class="parameters">
        <div id="dialog_box_smiley_change"></div>
        <div id="tabs">
            <ul>
                <li><a href="#general">General</a></li>
                <li><a href="#smileys">Smileys</a></li>
                <li><a href="#polling">Polling</a></li>
                <li><a href="#client">Client side</a></li>
                <li><a href="#server">Server side</a></li>
                <!--<li><a href="#added">Plugins parameters</a></li>-->
                <li><a href="#account">Additional</a></li>
            </ul>







            <!-- -1 tab --->


            <!-- smiley tab -->
            <div id="smileys">

                <p>To add a new smiley , follow the three steps below : <br/>
                    1. enter a symbol for your smiley<br/> 
                    2. upload an image for your smiley <br/>
                    3. click on the button add new smiley .<p>

                    <input type="text" id="smiley_symbol" />
                    <input type="file" id="smiley" />
                    <input type="button" id="add_smiley" value="add new smiley" />


                    <br/><br/>

                    <?php
                    echo $param->build_smiley_table();
                    ?>
            </div>




            <!-- zero tab -->

            <div id="general">


                <style type="text/css">

                    .tablex {
                        border-top-width: 3px;
                        border-right-width: 3px;
                        border-bottom-width: 3px;
                        border-left-width: 3px;
                        border-top-left-radius: 16px 16px;
                        border-top-right-radius: 16px 16px;
                        border-bottom-right-radius: 16px 16px;
                        border-bottom-left-radius: 16px 16px;
                        padding-top: 8px;
                        padding-right: 8px;
                        padding-bottom: 8px;
                        padding-left: 8px;
                        margin-top: 0px;
                        margin-right: 8px;
                        margin-bottom: 8px;
                        margin-left: 8px;

                        height: auto;
                        border: solid white;
                    }

                    td {
                        padding:5px;
                        border-top: solid 1px #EFEFEF;
                        text-align:center;
                    }

                    .classleft{
                        text-align:left;
                    }

                    th {
                        width:300px;
                        background-color: #08F;
                        padding:5px;
                        color:white;
                        background-color: rgba(0, 136, 255, 1);
                    }
                </style>

                <table class="tablex">
                    <tr>
                        <th>Plugin</th>
                        <th>Guest Access</th>

                        <th>User Access</th>
                    </tr>
                    <tr>
                        <td class="classleft">Private chat</td>
                        <td><input type="checkbox"  name="p_chat_guest" <?php $param->default_param(array("ACL", "chat", "guest"), "allow", true); ?> value="checked" /></td>
                        <td><input type="checkbox" name="p_chat_user" <?php $param->default_param(array("ACL", "chat", "user"), "allow", true); ?> value="checked" /></td>

                    </tr>

                    <tr>
                        <td class="classleft">Chatroom</td>
                        <td><input type="checkbox"  name="p_chatroom_guest" <?php $param->default_param(array("ACL", "chatroom", "guest"), "allow", true); ?> value="checked" /></td>
                        <td><input type="checkbox" name="p_chatroom_user" <?php $param->default_param(array("ACL", "chatroom", "user"), "allow", true); ?> value="checked" /></td>

                    </tr>
                    <tr>
                        <td class="classleft">Send File</td>
                        <td><input type="checkbox" name="p_filesend_guest" value="checked" <?php $param->default_param(array("ACL", "filesend", "guest"), "allow", true); ?> /></td>
                        <td><input type="checkbox" name="p_filesend_user" value="checked" <?php $param->default_param(array("ACL", "filesend", "user"), "allow", true); ?> /></td>

                    </tr>
                    <tr>
                        <td class="classleft">Email Conversation</td>
                        <td><input type="checkbox" name="p_mail_guest" value="checked" <?php $param->default_param(array("ACL", "mail", "guest"), "allow", true); ?> /></td>
                        <td><input type="checkbox" name="p_mail_user" value="checked" <?php $param->default_param(array("ACL", "mail", "user"), "allow", true); ?> /></td>

                    </tr>                   <tr>
                        <td class="classleft">Save Conversation</td>
                        <td><input type="checkbox" name="p_save_guest" value="checked" <?php $param->default_param(array("ACL", "save", "guest"), "allow", true); ?> /></td>
                        <td><input type="checkbox" name="p_save_user" value="checked" <?php $param->default_param(array("ACL", "save", "user"), "allow", true); ?> /></td>

                    </tr>                    <tr>
                        <td class="classleft">Smiley</td>
                        <td><input type="checkbox" name="p_smiley_guest" value="checked" <?php $param->default_param(array("ACL", "smiley", "guest"), "allow", true); ?> /></td>
                        <td><input type="checkbox" name="p_smiley_user" value="checked" <?php $param->default_param(array("ACL", "smiley", "user"), "allow", true); ?> /></td>

                    </tr>
                </table>


            </div>

            <!-- third tab -->

            <div id="polling">




                <p>Polling Type</p><br/>
                <select name="polling">
                    <option value="disabled"<?php $param->default_param("polling", "disabled"); ?>>Short Polling</option>
                    <option value="enabled"<?php $param->default_param("polling", "enabled"); ?>>Comet</option>
                </select>
                <br/><br/><hr/>
                <br/>
                Polling Time:(only for comet)<br/><br/>

                <input type="text" name="polling_time" value="<?php echo $param->default_value('polling_time'); ?>"/> seconds

                <br/><br/>
                <hr/>
                <p>
                <h3>What is Comet?</h3>
                Comet is a web application model in which a long-held HTTP request allows a web server to push data to a browser, without the browser explicitly requesting it. Comet is an umbrella term, encompassing multiple techniques for achieving this interaction. All these methods rely on features included by default in browsers, such as JavaScript, rather than on non-default plugins. The Comet approach differs from the original model of the web, in which a browser requests a complete web page at a time.
                The use of Comet techniques in web development predates the use of the word Comet as a neologism for the collective techniques. Comet is known by several other names, including Ajax Push, Reverse Ajax,Two-way-web, HTTP Streaming, and HTTP server push among others

                <br/>
                <br/>
                Pros: you are notified when the server event happens with no delay.<br/> 
                Cons: more complex and more server resources used as the connection is kept alive. 
                </p>
                <hr/>
                <p>
                <h3>What is Short Polling?</h3>
                This is technically not in the same league, but attempts to recreate close to real-time connectivity with the server. In this model, the server is short-polled on a frequent basis (1-7 seconds as specified in the chatspeed settings).<br/>
                As one can imagine, this method is very resource intensive and bandwidth hungry. Even if polling the server returns no data, just the TCP/HTTP overhead will consume a lot of bandwidth.                            

                <br/>
                <br/>
                Pros: simpler, not server consuming(only if the time between requests is <b>long</b>).<br/>
                Cons: bad if you need to be notified when the server event happens with no delay.
                <br/>

                </p>





            </div>



            <!-- Fourth TAB -->



            <div id="client">


                <ol id ="parametejrs" style="list-style-type: upper-roman;">

                    <li>
                        <p>Disable FreiChat ?</p><br/>
                        <select name="BOOT">
                            <option value="no"<?php $param->default_param("BOOT", "no"); ?>>Yes</option>
                            <option value="yes"<?php $param->default_param("BOOT", "yes"); ?>>No</option>
                        </select>
                        <br/><br/><hr/>
                    </li>


                    <li>
                        <p>Do not load FreiChat for guests ?</p><br/>
                        <select name="exit_for_guests">
                            <option value="no"<?php $param->default_param("exit_for_guests", "no"); ?>>No</option>
                            <option value="yes"<?php $param->default_param("exit_for_guests", "yes"); ?>>Yes</option>
                        </select>
                        <br/><br/><hr/>
                    </li>





                    <li>
                        <p>Who should be on the online users list</p><br/>
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
                        <?php if (in_array($param->driver, $param->joomla_drivers) == true) { 
                            echo '<br/><br/>Enter group ids separated by commas [user groups created in joomla user manager]<br/>


                            <br/>                        
                            <a href="admin_files/home/example_ug_ids.png" target="_blank">click here</a>  to see an example of group ids

                            <div id="fade"></div>

                            <input type="text" name="ug_ids" value="'.$param->default_value("ug_ids").'"/>
                            <br/><em>if left empty freichat will fetch users as per the previous option else will override it.</em>'; 

                         } 
                         
                         ?>
                        <br/><br/><hr/>
                    </li>
                    <?php
//echo $param->default_param("link_profile", "enabled");
                    if (in_array($param->driver, $param->link_profile_supported_drivers) == true) {
                        echo '<li>
                        <p>Show link to user\'s profile</p><br/>
                        <select name="link_profile">
                            <option value="enabled" ';
                        $param->default_param("link_profile", "enabled");
                        echo ' >Yes</option>
                            <option value="disabled" ';
                        $param->default_param("link_profile", "disabled");
                        echo '>No</option>
                            

                        </select><br/><br/>';
                        if (in_array($param->driver, $param->sef_rec_supported_drivers) == true) {
                            echo 'SEF support for profile link<br/><br/>
                        <select name="sef_link_profile">
                            <option value="enabled" ';
                            $param->default_param("sef_link_profile", "enabled");
                            echo ' >Yes</option>
                            <option value="disabled" ';
                            $param->default_param("sef_link_profile", "disabled");
                            echo '>No</option>
                            

                        </select>';
                        }

                        echo ' <br/><br/><hr/>
                    </li>';
                    }
                    ?>

                    <li>
                        <p>Show Avatar</p><br/>
                        <select name="show_avatar">
                            <option value="block"<?php $param->default_param("show_avatar", "block"); ?>>Yes</option>
                            <option value="none"<?php $param->default_param("show_avatar", "none"); ?>>No</option>
                        </select>
                        <br/><br/><hr/>
                    </li>

                    <li>
                        <p>The message content box for the chat window should </p><br/>
                        <select name="content_height">
                            <option value="auto"<?php $param->default_param("content_height", "auto"); ?>>resize with content</option>
                            <option value="200px"<?php $param->default_param("content_height", "200px"); ?>>have fixed height</option>
                        </select>
                        <br/><br/><hr/>
                    </li>

                    <li>
                        <p>The options like clear history, save history , send email , send files in the chat window should be by-default </p><br/>
                        <select name="addedoptions_visibility">
                            <option value="HIDDEN"<?php $param->default_param("addedoptions_visibility", "HIDDEN"); ?>>hidden</option>
                            <option value="SHOWN"<?php $param->default_param("addedoptions_visibility", "SHOWN"); ?>>shown</option>
                        </select>
                        <br/><br/><hr/>
                    </li>





                    <?php
                    if (in_array($param->driver, $param->name_supported_drivers) == true) {
                        echo '<li><p>Show Username or Nickname(name)</p><br/><select name="displayname">';
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
                        echo '</select><br/><br/><hr/></li>';
                    }
                    ?>

                    <li>
                        <p>Select a theme for the chat</p><br/>
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
                        <br/><br/><hr/>
                    </li>


                    <li>
                        <p>Draggable chatwindows feature should be </p>
                        <select name="draggable">
                            <option value="enable"<?php $param->default_param("draggable", "enable"); ?>>Enabled</option>
                            <option value="disable"<?php $param->default_param("draggable", "disable"); ?>>Disabled</option>
                        </select>
                        <br/><br/><hr/>
                    </li>

                    <li>
                        <p>ChatBox on load should be</p>
                        <select name="load">
                            <option value="show"<?php $param->default_param("load", "show"); ?>>Maximized</option>
                            <option value="hide"<?php $param->default_param("load", "hide"); ?>>Minimized</option>
                        </select><br/><br/><hr/>
                    </li>

                    <li>
                        <p>ChatBox on load can be set by user</p>
                        <select name="chatbox_status">
                            <option value="true"<?php $param->default_param("chatbox_status", "true"); ?>>Yes</option>
                            <option value="false"<?php $param->default_param("chatbox_status", "false"); ?>>No</option>
                        </select><br/><br/><hr/>
                        Note: Setting this to yes will override your previous setting, thus giving more preference to the user . 
                    </li>

                    <li>
                        <p>Remove Jquery Conflicts <span onmousedown="helpme1()"><img src="<?php echo '../client/jquery/img/about.jpeg' ?>" alt="About"/></a></span></p>
                        <select name="conflict">
                            <option value="true"<?php $param->default_param("conflict", "true"); ?>>Yes</option>
                            <option value=""<?php $param->default_param("conflict", ""); ?>>No</option>
                        </select><br/><br/><hr/>
                    </li>

                    <li>
                        <p>Show Jquery Animations</p><br/>
                        <select name="fxval">
                            <option value="true"<?php $param->default_param("fxval", "true"); ?>>Yes</option>
                            <option value="false"<?php $param->default_param("fxval", "false"); ?>>No</option>
                        </select>
                        <br/><br/><hr/>
                    </li>

                    <li>
                        <p>Play sound on new message </p><br/>
                        <select name="playsound">
                            <option value="true"<?php $param->default_param("playsound", "true"); ?>>Yes</option>
                            <option value="false"<?php $param->default_param("playsound", "false"); ?>>No</option>
                        </select>
                        <br/><br/>
                    </li>

                </ol>

            </div>

            <!-- Second TAB -->
            <div id="server">
                <ol  style="list-style-type: upper-roman;">
                    <li>
                        <p>purge/delete message history</p><br/>
                        No of days : <input type="text" id="purge_mesg_period" value="0"/><br/><br/>
                        Note: The above field specifies the no. of days prior to which all messages should be deleted.<br/>
                        0 days denotes all messages are to be deleted.<br/>
                        <br/>
                        <input type="button" value="purge messages" onclick="purge_mesg_history()" />
                        <br/><br/><hr/>
                    </li>
                    <li>
                        <p>Change Chat Speed to</p><br/>
                        <select name="chatspeed">
                            <option value="7000"<?php $param->default_param("chatspeed", "7000"); ?>>7 seconds</option>
                            <option value="5000"<?php $param->default_param("chatspeed", "5000"); ?>>5 seconds</option>
                            <option value="3000"<?php $param->default_param("chatspeed", "3000"); ?>>3 seconds</option>
                            <option value="1000"<?php $param->default_param("chatspeed", "1000"); ?>>1 second</option>
                        </select><br/><br/>
                        Note:<br/>
                        1. It is the time interval between 2 consecutive requests.<br/>
                        <br/><br/><hr/>
                    </li>

                    <li>
                        <p>Choose any Language</p><br/>
                        <select name="lang">
                            <?php
                            if ($handle = opendir('../lang/')) {
                                while (false !== ($file = readdir($handle))) {
                                    if ($file != "." && $file != ".." && $file != '.svn' && $file != "index.html") {
                                        $file_name = str_replace(".php", "", $file);
                                        echo '<option value=' . "$file_name ";
                                        $param->default_param("lang", $file_name);
                                        echo">$file_name</option>";
                                    }
                                }
                                closedir($handle);
                            } else {
                                echo 'directory open failed';
                            }
                            ?>
                        </select>
                    </li>

                    <li><hr/>
                        <p>Time interval between messages</p><br/>
                        <select name="msgSendSpeed">
                            <option value="500"<?php $param->default_param("msgSendSpeed", "500"); ?>>0.5 second</option>
                            <option value="1000"<?php $param->default_param("msgSendSpeed", "1000"); ?>>1 seconds</option>
                            <option value="1500"<?php $param->default_param("msgSendSpeed", "1500"); ?>>1.5 seconds</option>
                            <option value="2000"<?php $param->default_param("msgSendSpeed", "2000"); ?>>2 seconds</option>
                        </select><br/><br/>
                        Note:<br/>
                        1. This is the time FreiChatX will wait between two requests (messages sent)<br/>
                        2. Increase the time interval if you want to reduce server resource usage<br/>
                        3. 1 second is the default time interval.Do not reduce it further if you <br/>dont
                        know what you are doing.<br/>
                        <br/><br/>
                    </li>


                    <li><hr/>
                        <p>Turn GZIP ob_handler </p><br/>
                        <select name="GZIP_handler">
                            <option value="ON"<?php $param->default_param("GZIP_handler", "ON"); ?>>ON</option>
                            <option value="OFF"<?php $param->default_param("GZIP_handler", "OFF"); ?>>OFF</option>
                        </select><br/><br/>
                        Note:<br/>
                        This handler compresses FreiChatX files for faster load <br/>                      
                        <br/><br/><hr/>
                    </li>

                </ol>
            </div>

            <!-- Third TAB -->



            <!-- Fourth TAB -->
            <div id ="account">
                <ol style="list-style-type: upper-roman;">
                    <!--<li>
                       Change FreiChatX administrator password<br/><br/>

                       A . Enter your old password<br/>
                       <input type="password" name="adminpassold1"/>
                       <br/>
                       B . Enter your old password again<br/>
                       <input type="password" name="adminpassold2"/>
                       <br/>
                       <br/>
                       C. Enter your new password <br/>
                       <input type ="password" name="adminpassnew"/>
                   </li>-->

                    <li>
                        Busy time out<br/><br/>
                        User status will be changed to busy after <br/>

                        <input name="busy_timeOut" value="<?php echo $param->default_value('busy_timeOut'); ?>" type="text"> seconds
                        <br/><br/><hr/>
                    </li>

                    <li>
                        Offline time out<br/><br/>
                        User status will be changed to offline after <br/>

                        <input name="offline_timeOut" value="<?php echo $param->default_value('offline_timeOut'); ?>" type="text"> seconds
                        <br/><br/><hr/>
                    </li>

                    <li>
                        <p>PHP debugging</p><br/>
                        <select name="debug">
                            <option value="true"<?php $param->default_param("debug", "true"); ?>>Yes</option>
                            <option value="false"<?php $param->default_param("debug", "false"); ?>>No</option>
                        </select>
                        <br/><br/><hr/>
                    </li>

                    <li>
                        <p>JavaScript debugging</p><br/>
                        <select name="JSdebug">
                            <option value="true"<?php $param->default_param("JSdebug", "true"); ?>>Yes</option>
                            <option value="false"<?php $param->default_param("JSdebug", "false"); ?>>No</option>
                        </select>
                        <br/><br/>
                    </li>

                </ol>
            </div>
        </div>

    </div>


    <br/>

    <input id="paramsubmit2" type="submit" value="SUBMIT">
</form>