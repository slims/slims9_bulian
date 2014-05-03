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

//--------------------------------------------------------------------------------------------
}

$param = new param();
if (isset($_POST['busy_timeOut']) == true) {
    $config = $param->build_config_array();
    $param->update_config($config);
    $param->build_vars();
}
?>
<div class="row-fluid sortable ui-sortable">
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-plus"></i> Additional</h2>

        </div>
        <div class="box-content">
            <div class="row-fluid">


                <form name="params" action='<?php $_SERVER['PHP_SELF']; ?>' method="POST">

                    <div class="control-group">
                        <label class="control-label" for="appendedInput">Busy time out</label>
                         User status will be changed to busy after <br/><br/>

                        <div class="controls">
                            <div class="input-append">
                                <input name="busy_timeOut" value="<?php echo $param->default_value('busy_timeOut'); ?>" id="appendedInput" size="16" type="text"><span class="add-on">seconds</span>
                            </div>
                        </div>
                    </div>  
                    <br/><br/>

                    <div class="control-group">
                        <label class="control-label" for="appendedInput">Offline time out</label>
                         User status will be changed to offline after  <br/><br/>

                        <div class="controls">
                            <div class="input-append">
                                <input name="offline_timeOut" value="<?php echo $param->default_value('offline_timeOut'); ?>" id="appendedInput" size="16" type="text"><span class="add-on">seconds</span>
                            </div>
                        </div>
                    </div>  


                    <br/><br/>

                    <p>PHP debugging</p><br/>
                    <select name="debug">
                        <option value="true"<?php $param->default_param("debug", "true"); ?>>Yes</option>
                        <option value="false"<?php $param->default_param("debug", "false"); ?>>No</option>
                    </select>

                    <br/><br/>                        <p>JavaScript debugging</p><br/>
                    <select name="JSdebug">
                        <option value="true"<?php $param->default_param("JSdebug", "true"); ?>>Yes</option>
                        <option value="false"<?php $param->default_param("JSdebug", "false"); ?>>No</option>
                    </select>
                    <br/><br/>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>                   
        </div>
    </div><!--/span-->
</div>