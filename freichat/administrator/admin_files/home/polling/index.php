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
        $config['polling'] = $_POST['polling'];
        $config['polling_time'] = $_POST['polling_time'];

        return $config;
    }

//--------------------------------------------------------------------------------------------
}

$param = new param();
if (isset($_POST['polling']) == true) {
    $config = $param->build_config_array();
    $param->update_config($config);
    $param->build_vars();
}
?>


<div class="row-fluid sortable ui-sortable">		
    <div class="box span12">
        <div class="box-header well" data-original-title="">
            <h2><i class="icon-arrow-up"></i> Polling</h2>
        </div>
        <div class="box-content">

            <form name="params" action='<?php $_SERVER['PHP_SELF']; ?>' method="POST">


                <p>Polling Type</p><br/>
                <select name="polling">
                    <option value="disabled"<?php $param->default_param("polling", "disabled"); ?>>Short Polling</option>
                    <option value="enabled"<?php $param->default_param("polling", "enabled"); ?>>Comet</option>
                </select>
                <br/><hr/>
                <br/>

                <div class="control-group">
                    <label class="control-label" for="appendedInput">Polling Time (only for comet)</label><br/>
                    <div class="controls">
                        <div class="input-append">
                            <input name="polling_time" value="<?php echo $param->default_value('polling_time'); ?>" id="appendedInput"  type="text"><span class="add-on">seconds</span>
                        </div>
                    </div>
                </div>  

                <br/>
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

                <br/><br/>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
