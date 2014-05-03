<?php

class base {

    public $info = array();
    public $path_host;

    public function __construct() {
        $this->info['addn_info'] = '';
        $this->info['jscode'] = '';
        $this->info['csscode'] = '';
        $this->info['integ_url'] = '';
        $this->info['js_where'] = "in the header(before &lt;/head&gt; tag)";
        $this->info['code_add'] = 'Add the following code in your ';
        $this->info['manual'] = true; //show manual copy-paste code div or not
        $this->info['module_type'] = 'module'; //whether a module, plugin , widget 
    }

    public function self_install() {
        return false;
    }

    public function tokenise($identifiers, $variables, $str) {

        $tokens = token_get_all($str); //tokenize
        $valid_file = true;

        if ($identifiers == null) {
            $valid_identifiers = array();
        } else {
            $valid_identifiers = $identifiers;
        }
        if ($variables == null) {
            $valid_variables = array();
        } else {
            $valid_variables = $variables;
        }

        $nextIsValue = false;
        $values = array(); //the resultant array
        $previous_index = ''; //the index name

        foreach ($tokens as $token) {
            if ($token[0] == 366 || $token[0] == 365 || $token[0] == 370) //we dont need comments and whitespaces
                continue;


            $tn = token_name((int) $token[0]);



            if (isset($token[1])) {
                $token[1] = str_replace("'", "", $token[1]);
            }

            if (isset($token[1]) &&
                    ($tn == 'T_CONSTANT_ENCAPSED_STRING' && in_array($token[1], $valid_identifiers) || //If encapsed in string
                    $tn == 'T_VARIABLE' && in_array($token[1], $valid_variables) )) {      //OR If variable		
                $nextIsValue = true;
                $previous_index = $token[1];
            } else if ($tn == 'T_CONSTANT_ENCAPSED_STRING' && $nextIsValue == true) {
                $nextIsValue = false;
                $values[str_replace("'", "", $previous_index)] = str_replace("'", "", $token[1]);
            }
        }

        if ($valid_file === false) {
            return false;
        }

        return $values;
    }

}

?>
