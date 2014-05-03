<?php
if (!isset($_SESSION['phplogin'])
        || $_SESSION['phplogin'] !== true) {
    header('Location: ../administrator/index.php'); //Replace that if login.php is somewhere else
    exit;
}

class param extends FC_admin {

    public function __construct() {
        parent::__construct();
    }

//--------------------------------------------------------------------------------------------

    public function create_file() {
        $parameters = array();
        $parameters["plugins"]["file_sender"]["show"] = 'true';
        $parameters["plugins"]["file_sender"]["file_size"] = $_POST['max_file_size'];
        $parameters["plugins"]["file_sender"]["expiry"] = $_POST['max_file_expiry'];
        $parameters["plugins"]["file_sender"]["valid_exts"] = $_POST['valid_exts'];
        return $parameters;
    }

}

$success = false;

$param = new param();
if (isset($_POST['max_file_size']) == true) {
    $configs = $param->create_file();
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
            <h2><i class="icon-file"></i> Send file configuration</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">


                <form class="form-horizontal" name="params" action='admin.php?freiload=_file_send' method="POST">

                    <br/><br/>
                    <div class="parameters">

                        <div id="tabs">

                            <!-- First TAB -->
                            <div id="client">




                                <div class="control-group">
                                    <label class="control-label" for="appendedInput">Maximum file size for uploading</label>
                                    <div class="controls">
                                        <div class="input-append">
                                            <input name="max_file_size" value="<?php echo $param->default_value(array("plugins", "file_sender", "file_size"), 3); ?>" id="appendedInput" size="16" type="text"><span class="add-on">KB</span>
                                        </div>
                                    </div>
                                </div>  

                                <div class="control-group">
                                    <label class="control-label" for="appendedInput">Uploaded files will be deleted after</label>
                                    <div class="controls">
                                        <div class="input-append">
                                            <input name="max_file_expiry" value="<?php echo $param->default_value(array("plugins", "file_sender", "expiry"), 3); ?>" id="appendedInput" size="16" type="text"><span class="add-on">minutes</span>
                                        </div>
                                    </div>
                                </div>  
                                <div class="control-group">
                                    <label class="control-label">Valid file extensions for uploading</label>
                                    <div class="controls">
                                        <div class="input-append">
                                            <input size="60px" name="valid_exts" value="<?php echo $param->default_value(array("plugins", "file_sender", "valid_exts"), 3); ?>" id="" size="16" type="text">
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

