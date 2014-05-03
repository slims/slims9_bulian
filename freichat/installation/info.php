<?php

session_start();

if (!isset($_SESSION['FREIX']) || $_SESSION['FREIX'] != 'authenticated') {
    header("Location:index.php");
    exit;
}


function is__writable($path) {
//will work in despite of Windows ACLs bug
//NOTE: use a trailing slash for folders!!!
//see http://bugs.php.net/bug.php?id=27609
//see http://bugs.php.net/bug.php?id=30931

    if ($path{strlen($path) - 1} == '/') // recursively return a temporary file path
        return is__writable($path . uniqid(mt_rand()) . '.tmp');
    else if (is_dir($path))
        return is__writable($path . '/' . uniqid(mt_rand()) . '.tmp');
    // check tmp file for read/write capabilities
    $rm = file_exists($path);
    $f = @fopen($path, 'a');
    if ($f === false)
        return false;
    fclose($f);
    if (!$rm)
        unlink($path);
    return true;
}

class Info {

    public function __construct() {
        if (isset($_POST['cms']) == true) {

            if ($_POST['cms'] == "CBE" && $_POST['CBE_ver'] == '2') {
                $_SESSION['cms'] = 'CBE_2';
            }
            else if ($_POST['cms'] == "SMF" && $_POST['SMF_ver'] == '2') {
                $_SESSION['cms'] = 'SMF2';
            }
            else {
                $_SESSION['cms'] = $_POST['cms'];
            }
        }
    }

    public function set_file() {
        $redir = false;


        if (is_file('integ/' . $_SESSION['cms'] . '.php')) {

            $cname = $_SESSION['cms'];
            require 'integ/' . $_SESSION['cms'] . '.php';
            $cls = new $cname();
            $redir = $cls->redir;
            $set_file = $cls->set_file;
        } else {
            $_SESSION['error'] = 'Invalid Integration Driver Selected';
            header("Location: error.php");
            exit(0);
        }

        $this->set_path($set_file);

        if ($redir == true) {
            header('Location: params.php');
            exit(0);
        }

        return $set_file;
    }

    public function set_path($set_file) {

        $freichat_dir = dirname(dirname(__FILE__));
        $arr = explode(DIRECTORY_SEPARATOR, $freichat_dir);

        $freichat_dir = end($arr);
        $_SESSION["freichat_renamed"] = $freichat_dir;


        if (isset($_POST['paths']) == true) {
            $_SESSION['old_config_path'] = $_SESSION['config_path'];
            $_SESSION['config_path'] = $_POST['paths'];
            $_SESSION['config_path'] = str_replace('\\', '/', $_SESSION['config_path']);
            $_SESSION['cms_path'] = str_replace($set_file, "", $_SESSION['config_path']);
        } else {
            $ROOT_path = str_replace('\\', '/', dirname(__FILE__));
            $_SESSION['cms_path'] = str_replace("$freichat_dir/installation", "", $ROOT_path);
            $_SESSION['config_path'] = str_replace("$freichat_dir/installation", "", $ROOT_path) . $set_file;
        }
    }
    
    private function correct_to_freichat_path($set_file) {
        
        if(isset($_POST['paths'])) {
                        
            $o_conf_path = str_replace($set_file, "", $_SESSION['old_config_path']);
            $n_conf_path = str_replace($set_file, "", $_SESSION['config_path']);
            
            $dir = str_replace($n_conf_path, '', $o_conf_path);
            $freichat_renamed = $dir.$_SESSION["freichat_renamed"];
            
            //check if this new path is correct
            if(is_readable($n_conf_path.$freichat_renamed."/hardcode.php")) {
                $_SESSION["freichat_renamed"] = $freichat_renamed;
            }
        }
    }

    public function get_flags($set_file) {
        $flags = Array();

        $flags['flag'] = true;
        $flags['color1'] = $flags['color10'] = $flags['color11'] = $flags['color0'] = $flags['color3'] = $flags['color4'] = $flags['color5'] = "label label-success";
        $flags['text1'] = $flags["text10"] = $flags["text11"] = $flags["text0"] = $flags["text3"] = $flags["text4"] = $flags['text5'] = "is writable";

        if (!is_writable("../hardcode.php")) {
            $flags['flag'] = false;
            $flags['color1'] = "label label-important";
            $flags['text1'] = "is not writable (change file permissions to 0777)";
        }

        if (!is_writable("../cache/perm/request.001")) {
            $flags['flag'] = false;
            $flags['color3'] = "label label-important";
            $flags['text3'] = "is not writable (change file permissions to 0777)";
        }

        if (!is__writable('../client/plugins/upload/upload')) {
            $flags['flag'] = false;
            $flags['color5'] = "label label-important";
            $flags['text5'] = "is not writable (change file permissions to 0777)";
        }

        if (!is__writable("../cache/temp/")) {
            $flags['flag'] = false;
            $flags['color4'] = "label label-important";
            $flags['text4'] = "is not writable (change folder permissions to 0777)";
        }

         if (!is__writable("../client/themes")) {
            $flags['flag'] = false;
            $flags['color10'] = "label label-important";
            $flags['text10'] = "is not writable (change folder permissions to 0777)";
        }
        
        if($_SESSION['cms'] == 'Custom') {
            if (!is_writable("../server/drivers/Custom.php")) {
                $flags['flag'] = false;
                $flags['color11'] = "label label-important";
                $flags['text11'] = "is not writable (change file permissions to 0777)";
            }
        }


        if (isset($_SESSION['config_path']) == true) {
            if (is_readable($_SESSION['config_path'])) {
                
                $this->correct_to_freichat_path($set_file);
                $flags['color2'] = "label label-success";
                $flags['text2'] = "is readable";
            } else {
                $flags['flag'] = false;
                $flags['color2'] = "label label-important";
                $flags['text2'] = "is not readable";
            }
        }
        return $flags;
    }

}

$info = new Info();


$file_name_post = 'params.php';


$set_file = $info->set_file();


$flags = $info->get_flags($set_file);

if ($_SESSION['cms'] == 'Custom') {
    $file_name_post = 'smart.php';
    $flags['color2'] = 'green';
}

require 'header.php';

?>

<div style="text-align: center">
    <br/> <span  style="font-family: 'Sonsie One', cursive;font-size: 18pt;text-align: center"><b>
            <?php
            if ($flags['flag'] == false) {
                echo "Please Correct the following";
            } else {
                echo "Everything Seems Alright!";
            }
            ?>
        </b></span><br/><br/><br/>

    <div class="box-content">
        <table style="max-width: 60%;margin: 0px auto;" class="table table-striped table-bordered" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
            <thead>
                <tr role="row">
                    <th class="center" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1"  aria-label="Date registered: activate to sort column ascending">file path</th>
                    <th class="center" role="columnheader" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1"  aria-label="Role: activate to sort column ascending">status</th>
                </tr>
            </thead>   

            <tbody role="alert" aria-live="polite" aria-relevant="all">

                <tr>
                    <td>freichat/hardcode.php</td><td> <span class='<?php echo $flags['color1']; ?>'><?php echo $flags['text1']; ?> </span></td>
                </tr>

                <tr>
                    <td>freichat/cache/perm/request.001</td><td> <span class='<?php echo $flags['color3']; ?>'><?php echo $flags['text3']; ?> </span></td>
                </tr>

                <tr>
                    <td>freichat/cache/temp</td><td> <span class='<?php echo $flags['color4']; ?>'><?php echo $flags['text4']; ?> </span></td>
                </tr>
                <?php
                if($set_file != '')  { ?>
                <tr>
                    <td><?php echo $set_file; ?></td> <td> <span class='<?php echo $flags['color2']; ?>'><?php echo $flags['text2']; ?></span></td>
                </tr>
                <?php } ?>

                <tr>
                    <td> freichat/client/plugins/upload/upload</td> <td><span class='<?php echo $flags['color5']; ?>'><?php echo $flags['text5']; ?> </span></td>
                </tr>

                <tr>
                    <td> freichat/client/themes</td> <td><span class='<?php echo $flags['color10']; ?>'><?php echo $flags['text10']; ?> </span></td>
                </tr>

                <?php
                if($_SESSION['cms'] == 'Custom')  { ?>
                <tr>
                    <td>freichat/server/drivers/Custom.php</td> <td> <span class='<?php echo $flags['color11']; ?>'><?php echo $flags['text11']; ?></span></td>
                </tr>
                <?php } ?>


            </tbody>

        </table>
        <?php
        if ($flags['flag'] == false) {


            echo " <br/>
                    <br/>
                    <br/><form name='path' action='info.php' id='sameform' method='POST'>
                    ";

            if ($flags['color2'] != "green") {
                echo "<span  style=\"font-family: 'Sonsie One', cursive;font-size: 18pt;text-align: center\">is the path to your <span class=green>$set_file</span> file correct?</span><br/>
                   <br/><input style=\"padding:17px;line-height:12px;font-family: 'Exo', sans-serif;font-weight:600 ;font-style:italic;font-size:16px;width:500px;\" name='paths' type='text' value= " . $_SESSION['config_path'] . " /><br/><br/>";
            }

            echo '<br/><a href="JavaScript:void(0)" class="refreshbutton" onclick="modify()">Refresh</a></form>';
        }

        echo "

            <form name='cms' id='nextform' action='" . $file_name_post . "' method='POST'>
        <br/>

         ";

        if ($flags['flag'] == true) {
            echo '<br/><br/><a href="JavaScript:void(0)" class="nextbutton" onclick="proceed()">Proceed</a>';
        }

        echo " </form>";
        ?>
    </div>                  
    <script type="text/javascript">
    
        function proceed(){
            $('#nextform').submit();
        }
    
        function modify(){
            $('#sameform').submit();
        }
    
    </script>

    <?php
    require 'footer.php';
    ?>
