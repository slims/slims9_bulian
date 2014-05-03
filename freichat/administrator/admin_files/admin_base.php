<?php

class FC_admin extends FreiChat {

    public function __construct() {
        parent::__construct();
    }

    public function return_checked_value($index) {

        if (isset($_POST[$index])) {
            return 'allow';
        } else {
            return 'noallow';
        }
    }

    public function get_dim($array) {
        if (!is_array($array))
            return 0;

        else {
            $dim = 1;
            foreach ($array as $arr) {
                if (is_array($arr)) {
                    $dim = 2;
                }
            }
        }

        return $dim;
    }

    public function update_config($configs) {

        $query = "UPDATE frei_config SET frei_config.val=? WHERE frei_config.\"key\"=? AND frei_config.cat=? AND frei_config.subcat=?";
        $config = $this->db->prepare($query);

        foreach ($configs as $c_key => $c_val) {
            $dim = $this->get_dim($c_val);

            $key = $c_key;
            if ($dim == 0) {
                $cat = $subcat = 'NULL';
                $val = $c_val;
                $up_array = array($val, $key, $cat, $subcat);
                $config->execute($up_array);
            } else if ($dim == 1) {
                foreach ($c_val as $c_s_key => $c_s_val) {
                    $cat = $c_sub_key;
                    $subcat = null;
                    $val = $c_s_val;
                    $up_array = array($val, $key, $cat, $subcat);
                    $config->execute($up_array);
                }
            } else if ($dim == 2) {
                foreach ($c_val as $c_s_key => $c_s_val) {
                    $cat = $c_s_key;
                    foreach ($c_s_val as $c_s_s_key => $c_s_s_val) {
                        $subcat = $c_s_s_key;
                        $val = $c_s_s_val;
                        $up_array = array($val, $key, $cat, $subcat);
                        $config->execute($up_array);
                    }
                }
            } else {
                echo "out of dimension definition";
            }
        }
    }

    public function default_param($name, $given_value, $checked = false) {
        $parameters = $this->db_vars;

        $dim = count($name);

        if ($dim == 0 || $dim == 1) { // if it is 1 dim array or string
            $passed_value = $parameters[$name];
        } else if ($dim == 2) {
            $passed_value = $parameters[$name[0]][$name[1]];
        } else if ($dim == 3) {
            $passed_value = $parameters[$name[0]][$name[1]][$name[2]];
        } else {
            echo "ERR[1100] : dimension not defined"; //$passed_value = $dim;echo $dim;
        }

        if ($passed_value == $given_value) {

            if ($checked == true) {
                echo ' checked="checked" ';
            } else {
                echo ' selected="selected" ';
            }
        }
    }

    public function default_value($name, $dim = 1) {
        //require $this->configpath;
        $parameters = $this->db_vars;

        if ($dim == 1) {
            return $parameters[$name];
        } else if ($dim == 2) {
            return $parameters[$name[0]][$name[1]];
        } else if ($dim == 3) {
            return $parameters[$name[0]][$name[1]][$name[2]];
        } else {
            echo "ERR[1101]: Out of bounds!";
        }
    }

}