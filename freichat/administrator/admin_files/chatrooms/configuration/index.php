<?php
if (!isset($_SESSION['phplogin']) || $_SESSION['phplogin'] !== true) {
    header('Location: ../administrator/index.php'); //Replace that if login.php is somewhere else
    exit;
}

class param extends FC_admin {

    public function __construct() {

        parent::__construct();
        $this->themeray = $this->langray = array();
    }

//--------------------------------------------------------------------------------------------

    public function create_config() {
        $parameters = array();
        $parameters["plugins"]["chatroom"]["location"] = $_POST['location'];
        $parameters["plugins"]["chatroom"]["autoclose"] = $_POST['autoclose'];
        
        if(isset($_POST['offset'])) {

            $parameters["plugins"]["chatroom"]["offset"] = $_POST['offset'];
            $parameters["plugins"]["chatroom"]["label_offset"] = $_POST['label_offset'];            
        }
        $parameters["plugins"]["chatroom"]['override_positions'] = $_POST['override_positions'];
        $parameters["plugins"]["chatroom"]["chatroom_expiry"] = $_POST["chatroom_expiry"];

        return $parameters;
    }

}

$success = false;

$param = new param();
if (isset($_POST['location']) == true) {
    $configs = $param->create_config();
    $param->update_config($configs);
    $success = true;
}
$param->build_vars();
?>


<script type="text/javascript">

    $(window).load(function() {
        if (<?php echo $success; ?>) {
            var noty = $.noty({text: 'Your changes were saved successfully'});
        }

    });
</script>

<div class="row-fluid sortable ui-sortable">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-asterisk"></i> Chatroom configuration</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">



                <form name="params" action='admin.php?freiload=chatrooms/configuration' method="POST">


                    <p>Location of the chat room that will be displayed on the webpage</p>
                    <select name='location'>
                        <option value='left' <?php $param->default_param(array("plugins", "chatroom", "location"), 'left'); ?>>Left</option>
                        <option value='right'  <?php $param->default_param(array("plugins", "chatroom", "location"), 'right'); ?>>Right</option>
                        <option value='top'  <?php $param->default_param(array("plugins", "chatroom", "location"), 'top'); ?>>Top</option>
                        <option value='bottom'  <?php $param->default_param(array("plugins", "chatroom", "location"), 'bottom'); ?>>Bottom</option>

                    </select>
                    <br/><br/>

                    <p>Chat room should auto close itself when mouse is clicked anywhere on the page <b>not</b> on the chatroom</p>
                    <select name='autoclose'>
                        <option value='true' <?php $param->default_param(array("plugins", "chatroom", "autoclose"), 'true'); ?>>Yes</option>
                        <option value='false'  <?php $param->default_param(array("plugins", "chatroom", "autoclose"), 'false'); ?>>No</option>

                    </select>
                    <br/><br/>

                    <div class="control-group">
                        <label class="control-label" for="appendedInput">
                            Temporary chatrooms created by users/guests would be deleted after 
                        </label>

                        <div class="controls">
                            <div class="input-append">
                                <input name="chatroom_expiry" value="<?php echo $param->default_value(array("plugins", "chatroom", 'chatroom_expiry'), 3); ?>" id="appendedInput" size="16" type="text"><span class="add-on">seconds</span>
                            </div>
                        </div>
                    </div>  
                    <br/><br/>

                    <p>Take below values from the theme (defined in client/themes/your_theme/argument.php)</p>
                    <select id="overrid_positions" name='override_positions'>
                        <option value='yes' <?php $param->default_param(array("plugins", "chatroom", "override_positions"), 'yes'); ?>>Yes</option>
                        <option value='no'  <?php $param->default_param(array("plugins", "chatroom", "override_positions"), 'no'); ?>>No</option>

                    </select>
                    <br/><span class="muted">Note: Below values wont take effect if you have set this options to Yes</span>

                    <br/><br/><br/>

                    <div id="arg_independent_inputs">

                        <p>Offset from original position of chatroom</p>
                        <input name="offset" type="text" value="<?php echo $param->default_value(array("plugins", "chatroom", "offset"), 3); ?>"/>
                        <br/><br/>

                        <p>Position of chatroom label along the chatroom</p>
                        <input name="label_offset" type="text" value="<?php echo $param->default_value(array("plugins", "chatroom", "label_offset"), 3); ?>"/>
                        <br/><br/>

                    </div>


                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>                   
        </div>
    </div><!--/span-->
</div>

<script type="text/javascript">

    function change_options_status() {

        var disabled = false;

        if ($('#overrid_positions').val() === "yes") {

            disabled = true;
        }

        $('#arg_independent_inputs input').each(function() {

            $(this).prop('disabled', disabled);
        });

    }

    change_options_status();

    $('#overrid_positions').change(function() {
        change_options_status();
    });
</script>