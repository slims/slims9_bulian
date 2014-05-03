<?php
if (!isset($_SESSION['phplogin'])
        || $_SESSION['phplogin'] !== true) {
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
        $parameters["plugins"]["send_conv"]["show"] = 'true';
        $parameters["plugins"]["send_conv"]["mailtype"] = $_POST['mailtype'];
        $parameters["plugins"]["send_conv"]["smtp_server"] = $_POST['smtp_server'];
        $parameters["plugins"]["send_conv"]["smtp_port"] = $_POST['smtp_port'];
        $parameters["plugins"]["send_conv"]["smtp_protocol"] = $_POST['smtp_protocol'];
        $parameters["plugins"]["send_conv"]["from_address"] = $_POST['from_address'];
        $parameters["plugins"]["send_conv"]["from_name"] = $_POST['from_name'];
        return $parameters;
    }

}

$success = false;

$param = new param();
if (isset($_POST['mailtype']) == true) {
    $configs = $param->create_config();
    $param->update_config($configs);
    $success = true;
    
}
$param->build_vars();
?>

<style>
    .control-label {
        width: 205px !important;
        padding-right: 15px;
        text-align: left !important;
    }
</style>
<script>

    $(window).load(function(){
    if(<?php echo $success; ?>) {
        var noty = $.noty({text: 'Your changes were saved successfully'});
    }
        
    });

</script>


<div class="row-fluid sortable ui-sortable">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-folder-close"></i> Email settings</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">



                <form class="form-horizontal" name="params" action='admin.php?freiload=_send_conv' method="POST">

                    <br/><br/>
                    <div class="parameters">

                        <div id="tabs">
                            <!-- First TAB -->
                            <div id="client">



                                <div class="control-group">
                                    <label class="control-label" for="appendedInput">Mail() or SMTP</label>
                                    <div class="controls">
                                        <select name='mailtype'>
                                            <option value='mail' <?php $param->default_param(array("plugins", "send_conv", "mailtype"), 'mail'); ?>>mail()</option>
                                            <option value='smtp'  <?php $param->default_param(array("plugins", "send_conv", "mailtype"), 'smtp'); ?>>SMTP</option>

                                        </select>
                                    </div>
                                </div>  
                                
                                <hr/>
                                
                                <p style="text-align:left"><b>SMTP Related Settings(Required if you choose SMTP)</b></p><br/>



                                <div class="control-group">
                                    <label class="control-label" for="appendedInput">SMTP server</label>
                                    <div class="controls">
                                        <div>
                                            <input name="smtp_server" value="<?php echo $param->default_value(array("plugins", "send_conv", "smtp_server"), 3); ?>" type="text">
                                        </div>
                                    </div>
                                </div>  



                                <div class="control-group">
                                    <label class="control-label" for="appendedInput">SMTP port</label>
                                    <div class="controls">
                                        <div>
                                            <input size="60px" name="smtp_port" value="<?php echo $param->default_value(array("plugins", "send_conv", "smtp_port"), 3); ?>" type="text">
                                        </div>
                                    </div>
                                </div>  



                                <div class="control-group">
                                    <label class="control-label" for="appendedInput">Use encrypted protocol</label>
                                    <div class="controls">
                                        <div>
                                            <select name='smtp_protocol'>
                                                <option value='none' <?php $param->default_param(array("plugins", "send_conv", "smtp_protocol"), 'none'); ?>>none</option>
                                                <option value='ssl'  <?php $param->default_param(array("plugins", "send_conv", "smtp_protocol"), 'ssl'); ?>>SSL</option>
                                                <option value='tls'  <?php $param->default_param(array("plugins", "send_conv", "smtp_protocol"), 'tls'); ?>>TLS</option>
                                            </select>
                                        </div>
                                    </div>
                                </div><br/>  
                                <hr/>
                                <br/>

                                <p style="font-weight:bold;color:green;">Username and password for SMTP must be defined in freichat/hardcode.php</p>

                                <br/>
                                <div class="control-group">
                                    <label class="control-label" for="appendedInput">E-mail from address</label>
                                    <div class="controls">
                                        <div>
                                            <input size="60px" name="from_address" value="<?php echo $param->default_value(array("plugins", "send_conv", "from_address"), 3); ?>" type="text">
                                        </div>
                                    </div>
                                </div>  


                                <div class="control-group">
                                    <label class="control-label" for="appendedInput">E-mail from name</label>
                                    <div class="controls">
                                        <div>
                                            <input size="60px" name="from_name" value="<?php echo $param->default_value(array("plugins", "send_conv", "from_name"), 3); ?>" type="text">
                                        </div>
                                    </div>
                                </div>  


                            </div>


                        </div>

                    </div>


                    <br/>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>                   
        </div>
    </div><!--/span-->
</div>
