<?php

require 'base.php';

class Oxwall extends base {

    public $set_file = 'ow_includes/config.php';
    public $redir = false;

    public function info($path_host) {

        $this->info['code_add'] = 'Add the following code in your ';
        $this->info['jsloc'] = '`custom head code` area  by going to <br/>Oxwall Admin Area -> Settings -> Main Settings -> Page Settings -> Custom head code ';
        $this->info['js_where'] = '';
        $this->info['phpcode'] = '<script type="text/javascript" language="javascipt"src="' . $path_host . 'client/main.php?id=null&xhash=null"></script>
	<link rel="stylesheet" href="' . $path_host . 'client/jquery/freichat_themes/freichatcss.php" type="text/css">';
        $this->info['jscode'] = '';
        $this->info['csscode'] = '';

        $this->info['module_type'] = 'plugin';
        $this->info['manual'] = false; //hide manual install
        $this->info["integ_url"] = $this->get_integ_module_url(); //show module install


        return $this->info;
    }

    function get_integ_module_url() {
        return "http://freichatx-i.googlecode.com/files/mod_freichat_v7.zip";
    }

    function get_config() {

        //somethings needed by Oxwall
        define('DS', DIRECTORY_SEPARATOR);
        define('OW_DIR_ROOT', dirname(dirname(dirname(dirname(__FILE__)))) . DS);


        require $_SESSION['config_path'];


        $conf = array();
        //host
        $conf[0] = OW_DB_HOST;
        //db username
        $conf[1] = OW_DB_USER;
        //db password
        $conf[2] = OW_DB_PASSWORD;
        //db name
        $conf[3] = OW_DB_NAME;
        //db prefix
        $conf[4] = OW_DB_PREFIX;

        return $conf;
    }

}
