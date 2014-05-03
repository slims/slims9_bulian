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
    }

    //------------------------------------------------------------------
    public function build_config_array() {
        $config = array();
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
        $config['ACL']['video']['user'] = $this->return_checked_value('p_video_user');
        $config['ACL']['video']['guest'] = $this->return_checked_value('p_video_guest');
        $config['ACL']['chatroom_crt']['user'] = $this->return_checked_value('p_chatroom_crt_user');
        $config['ACL']['chatroom_crt']['guest'] = $this->return_checked_value('p_chatroom_crt_guest');
        
        return $config;
    }


//--------------------------------------------------------------------------------------------
}

$param = new param();
if (isset($_POST['p_chat_guest']) || isset($_POST['p_chat_user'])) {

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



<form name="params" action='<?php $_SERVER['PHP_SELF']; ?>' method="POST">

<div class="row-fluid sortable ui-sortable">		
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-lock"></i> Manage ACL</h2>
        </div>
        <div class="box-content">
            <table class="table table-striped table-bordered" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
                <thead>
                    <tr role="row">
                        <th class="sorting_asc">Plugin</th>
                        <th class="sorting_asc">Guest Access</th>
                        <th class="sorting_asc">User Access</th>
                    </tr>
                </thead>
                <tbody>
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

                    </tr>                    
                    <tr>
                        <td class="classleft">Smiley</td>
                        <td><input type="checkbox" name="p_smiley_guest" value="checked" <?php $param->default_param(array("ACL", "smiley", "guest"), "allow", true); ?> /></td>
                        <td><input type="checkbox" name="p_smiley_user" value="checked" <?php $param->default_param(array("ACL", "smiley", "user"), "allow", true); ?> /></td>

                    </tr>
                    <tr>
                        <td class="classleft">Create temporary chatrooms</td>
                        <td><input type="checkbox" name="p_chatroom_crt_guest" value="checked" <?php $param->default_param(array("ACL", "chatroom_crt", "guest"), "allow", true); ?> /></td>
                        <td><input type="checkbox" name="p_chatroom_crt_user" value="checked" <?php $param->default_param(array("ACL", "chatroom_crt", "user"), "allow", true); ?> /></td>

                    </tr>
                    
                    <?php if($param->show_videochat_plugin == "enabled") { ?>
                    <tr>
                        <td class="classleft">Video chat</td>
                        <td><input type="checkbox" name="p_video_guest" value="checked" <?php $param->default_param(array("ACL", "video", "guest"), "allow", true); ?> /></td>
                        <td><input type="checkbox" name="p_video_user" value="checked" <?php $param->default_param(array("ACL", "video", "user"), "allow", true); ?> /></td>

                    </tr>
                    
                    <?php } ?>

                    <input type="hidden" value="posted" name="posted"/>
                   
                      </tbody>
            </table>
                        <button id="paramsubmit3" class="btn btn-primary" type="submit"> Modify</button>

        </div>
    </div><!--/span-->
</div>
