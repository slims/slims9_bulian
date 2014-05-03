<?php
session_start();


//security check
if (!isset($_SESSION['FREIX']) || $_SESSION['FREIX'] != 'authenticated' || !isset($_POST['host'])) {
    header("Location:index.php");
    exit;
}

if (!is_writable("../hardcode.php")) {
//die("arg.php is not writable!<br/>Go back and change ~/freichat/arg.php permisssions");
    $_SESSION['error'] = 'hardcode.php is not writable!<br/>Go back and change ~/freichat/hardcode.php permisssions';
    header('Location: error.php');
}

class Install {

    public function __construct() {
        $this->installed = 'true';
        $this->path_host = str_replace("installation/install.php", "", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']);

        if ($_POST['port'] == '' || $_POST['port'] == null) {
            $this->port = '';
        } else {
            $this->port = 'port=' . $_POST['port'] . ';';
        }
    }

    public function connectDB() {
        require '../hardcode.php';

        try {
            $this->db = new PDO($dsn, $db_user, $db_pass);
        } catch (PDOException $e) {
            $this->installed = 'false';
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

//
// remove_remarks will strip the sql comment lines out of an uploaded sql file
//
    private function remove_remarks($sql) {
        $lines = explode("\n", $sql);

        // try to keep mem. use down
        $sql = "";

        $linecount = count($lines);
        $output = "";

        for ($i = 0; $i < $linecount; $i++) {
            if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0)) {
                if (isset($lines[$i][0]) && $lines[$i][0] != "#") {
                    $output .= $lines[$i] . "\n";
                } else {
                    $output .= "\n";
                }
                // Trading a bit of speed for lower mem. use here.
                $lines[$i] = "";
            }
        }

        return $output;
    }

//
// split_sql_file will split an uploaded sql file into single sql statements.
// Note: expects trim() to have already been run on $sql.
//
    private function split_sql_file($sql, $delimiter) {
        // Split up our string into "possible" SQL statements.
        $tokens = explode($delimiter, $sql);

        // try to save mem.
        $sql = "";
        $output = array();

        // we don't actually care about the matches preg gives us.
        $matches = array();

        // this is faster than calling count($oktens) every time thru the loop.
        $token_count = count($tokens);
        for ($i = 0; $i < $token_count; $i++) {
            // Don't wanna add an empty string as the last thing in the array.
            if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0))) {
                // This is the total number of single quotes in the token.
                $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
                // Counts single quotes that are preceded by an odd number of backslashes,
                // which means they're escaped quotes.
                $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);

                $unescaped_quotes = $total_quotes - $escaped_quotes;

                // If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
                if (($unescaped_quotes % 2) == 0) {
                    // It's a complete sql statement.
                    $output[] = $tokens[$i];
                    // save memory.
                    $tokens[$i] = "";
                } else {
                    // incomplete sql statement. keep adding tokens until we have a complete one.
                    // $temp will hold what we have so far.
                    $temp = $tokens[$i] . $delimiter;
                    // save memory..
                    $tokens[$i] = "";

                    // Do we have a complete statement yet?
                    $complete_stmt = false;

                    for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++) {
                        // This is the total number of single quotes in the token.
                        $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
                        // Counts single quotes that are preceded by an odd number of backslashes,
                        // which means they're escaped quotes.
                        $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);

                        $unescaped_quotes = $total_quotes - $escaped_quotes;

                        if (($unescaped_quotes % 2) == 1) {
                            // odd number of unescaped quotes. In combination with the previous incomplete
                            // statement(s), we now have a complete statement. (2 odds always make an even)
                            $output[] = $temp . $tokens[$j];

                            // save memory.
                            $tokens[$j] = "";
                            $temp = "";

                            // exit the loop.
                            $complete_stmt = true;
                            // make sure the outer loop continues at the right point.
                            $i = $j;
                        } else {
                            // even number of unescaped quotes. We still don't have a complete statement.
                            // (1 odd and 1 even always make an odd)
                            $temp .= $tokens[$j] . $delimiter;
                            // save memory.
                            $tokens[$j] = "";
                        }
                    } // for..
                } // else
            }
        }

        return $output;
    }

    private function get_queries($sql) {

        $sql = $this->remove_remarks($sql);

        $sql = $this->split_sql_file($sql, ';');

        return $sql;
    }

    public function create_tables() {

        $sql = file_get_contents("install.sql");

        $queries = $this->get_queries($sql);

        $res = false;
        //print_r($queries);
        foreach ($queries as $query) {
            //reformat the query
            $query = trim($query) . ";";

            $res = $this->db->query($query);

            if (!$res) {
                //problem;
                break;
            }
        }
    }

    public function init() {

        $this->connectDB();
        $this->create_tables();
        //$this->write_hardcode();

        $cname = $_POST['driver'];
        require 'integ/' . $cname . '.php';
        $cls = new $cname();
        $cls->path_host = $this->path_host;
        $output = $cls->info($this->path_host);
        $output['auto_install'] = $cls->self_install();

        return $output;
    }

}

$install = new Install();
$info = $install->init();


require("header.php");
?>
<script>
    $(document).ready(function() {
        //$('#content_manual').dialog({autoOpen: false, minWidth: 800, title: "Manual Installation"});
    });

    function maximize()
    {
        $('#content_manual').modal();
    }


</script>


<?php
$submit_url = $_SERVER['SERVER_NAME'];
?>
<!---COMMERCIAL_CODE--->




<div class="box span10 centerme" id="content">

    <div id="modal_db"></div>
    <div class="box-content" >
        <span  style="">
            <?php
            if ($info['auto_install'] == true) {
                echo '<h2><br/><br/>' . $_SESSION['cms'] . " has been auto installed</h2>";
            } else {
                ?>
            </span>    



            <?php if ($info['integ_url'] != '') { ?>

                <div class="well">
                    <h4>Download and install the following <?php echo $info['module_type']; ?></h4>
                    <br/><a href="<?php echo $info["integ_url"]; ?>" class="btn" target="_blank"><i class="icon-download-alt"></i> Download</a>
                </div>

                <div style="text-align:center">
                    <h2>
                        <br/>OR
                    </h2>
                </div>
                <br/>
                <?php
            }
            ?>   

            <br/>

            <style>

                textarea {
                    font-size: 12px;
                    text-shadow: 0 1px white;
                    font-family: time new roman;
                    color: #222;
                    width: 98%;
                    height: 100px;
                }
            </style>

            <?php if ($info['manual']) { ?>
                <br/>
                <div id ="manual" class="well">
                    <h4>copy paste the following code</h4><br/>
                    <a style="text-align:center" class="btn" href="#content_manual"  data-toggle="modal"><i class="icon-pencil"></i> get the code</a>
                </div>

                <div id ="content_manual" class="modal hide fade">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3>Manual installation</h3>
                    </div>

                    <div class="modal-body">
                        <?php if ($_SESSION['cms'] == 'se4' || $_SESSION['cms'] == 'etano') { ?>
                            <div class="">
                                <?php echo $info['cookie_where']; ?>

                            </div>
                            <br/>
                            <textarea style="height:30px" onclick="this.focus();this.select();" readonly="readonly"><?php echo $info['cookie_code']; ?></textarea>
                            <br/><br/>  

                        <?php } ?>

                        <div class="">
                            <?php
                            echo $info['addn_info'];
                            echo $info['code_add'];
                            echo $info['jsloc'];
                            echo $info['js_where'];
                            ?>
                        </div>
                        <br/>
                        <div>
                        <textarea onclick="this.focus();this.select();" readonly="readonly"><?php
                            echo $info['phpcode'];
                            echo $info['jscode'];
                            echo $info['csscode'];
                            ?></textarea>
                        </div>
                    </div>
                </div>

            <?php } //show manual install or not ?>


        <?php } ?><br/><br/><div align='center'>
            <a style="width:200px;height:40px;line-height: 40px" class="btn btn-primary" href='../administrator/index.php' target="_blank"><i class="icon-wrench icon-white"></i> Administer</a>
        </div><br/><br/>
    </div>

</div>

<?php
require("footer.php");
//session_destroy();
?>