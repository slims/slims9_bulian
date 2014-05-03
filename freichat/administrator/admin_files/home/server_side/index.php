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
        foreach ($_POST as $post_key => $post_value) {

            $config[$post_key] = $post_value;
        }

        return $config;
    }

    public function purge_mesg_history($days) {
        $time = $days * 24 * 60 * 60 * 10;
        $delete_mesg_query = "DELETE FROM frei_chat  WHERE recd =1 AND sent < ".$this->mysql_now."-" . $time;
        $this->db->query($delete_mesg_query);
    }

//--------------------------------------------------------------------------------------------
}

$param = new param();
if (isset($_POST['chatspeed']) == true) {
    $config = $param->build_config_array();
    $param->update_config($config);
    $param->build_vars();
}

if (isset($_REQUEST['purge'])) {
    $param->purge_mesg_history($_GET['days']);
    die('Messages Purged successfully.');
}
?>
<style>
    p{
        display: inline-block;
    }

    .info_text {
        color: #999;
    }
</style>

<div class="row-fluid sortable ui-sortable">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-wrench"></i> Server side Configuration</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">

                <form name="params" action='<?php $_SERVER['PHP_SELF']; ?>' method="POST">

                    <br/>



                    <div class="control-group">
                        <label class="control-label" for="appendedInput">purge/delete message history</label>
                        <div class="controls">
                            <div class="input-append">
                                <input id="purge_mesg_period" value="0" id="appendedInput" size="16" type="text"><span class="add-on">days</span>
                            </div>
                        </div>
                    </div>  

                    <br/>


                    <span class="info_text">The above field specifies the no. of days prior to which all messages should be deleted.<br/>
                        0 days denotes all messages are to be deleted.</span><br/>
                    <br/>
                    <input type="button" value="purge messages" class="btn" onclick="purge_mesg_history()" />
                    <br/><br/><hr/>

                    <p>Change Chat Speed to</p><br/>
                    <select name="chatspeed">
                        <option value="7000"<?php $param->default_param("chatspeed", "7000"); ?>>7 seconds</option>
                        <option value="5000"<?php $param->default_param("chatspeed", "5000"); ?>>5 seconds</option>
                        <option value="3000"<?php $param->default_param("chatspeed", "3000"); ?>>3 seconds</option>
                        <option value="1000"<?php $param->default_param("chatspeed", "1000"); ?>>1 second</option>
                    </select><br/>
                    <span class="info_text">It is the time interval between 2 consecutive requests.</span><br/>
                    <br/><br/>

                    <p>Select a Language</p><br/>
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
                    <br/><br/>
                    <p>Time interval between messages</p>
                    <button class="btn btn-primary noty" data-noty-options="{&quot;text&quot;:&quot;<span style='float:left; text-align:left;'> This is the time FreiChat will wait between two requests (messages sent)<br/> Increase the time interval if you want to reduce server resource usage<br/> 1 second is the default time interval.</span>&quot;,&quot;layout&quot;:&quot;center&quot;,&quot;type&quot;:&quot;success&quot;}"><i class="icon-question-sign icon-white"></i></button>
                    <br/><br/>
                    <select name="msgSendSpeed">
                        <option value="0"<?php $param->default_param("msgSendSpeed", "0"); ?>>0 seconds (message is sent instantly)</option>
                        <option value="500"<?php $param->default_param("msgSendSpeed", "500"); ?>>0.5 second</option>
                        <option value="1000"<?php $param->default_param("msgSendSpeed", "1000"); ?>>1 second</option>
                        <option value="1500"<?php $param->default_param("msgSendSpeed", "1500"); ?>>1.5 seconds</option>
                        <option value="2000"<?php $param->default_param("msgSendSpeed", "2000"); ?>>2 seconds</option>
                    </select><br/>

                    <br/><br/>

                    <p>Turn GZIP ob_handler </p><br/>
                    <select name="GZIP_handler">
                        <option value="ON"<?php $param->default_param("GZIP_handler", "ON"); ?>>ON</option>
                        <option value="OFF"<?php $param->default_param("GZIP_handler", "OFF"); ?>>OFF</option>
                    </select><br/>
                    <span class="info_text">Turning this on compresses FreiChat files for faster load </span><br/>                      
                    <br/><br/>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>                   
        </div>
    </div><!--/span-->
</div>