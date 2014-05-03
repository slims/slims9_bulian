<?php

session_start();


/* Make me secure */

if (!isset($_SESSION['FREIX']) || $_SESSION['FREIX'] != 'authenticated') {
    header("Location:index.php");
    exit;
}

/* Now i am secure */

require_once '../hardcode.php';

class Smart {

    public static $connected = false;
    public static $connection;
    public static $driver;
    public static $debug;
    private $db;
    private $db_prefix;

    public function __construct() {

        global $connected;

        global $db_prefix;
        $this->db_prefix = $db_prefix;

        if ($connected == 'YES') {
            global $dsn, $db_user, $db_pass;
            $this->db = self::get_connection($dsn, $db_user, $db_pass);
            //will return false on failed connection
        } else {
            $this->db = false; //not yet connected
        }
    }

    function connect($host, $client_db_name, $username, $password, $port, $driver = 'mysql') {

        if (!extension_loaded('PDO') || !extension_loaded('pdo_'.$driver)) {
            return array("pdo_".$driver." driver is not installed or enabled", false);
        }
        
        $keys = array(
            'mysql' => array(
                "host" => "host",
                "dbname" => "dbname",
				"port" => ";port="
            ),
            'sqlsrv' => array(
                "host" => "Server",
                "dbname" => "Database",
				"port" => ","
            )
        );


        if (strpos($host, "/") !== FALSE) {

            if (strpos($host, ":") !== FALSE) {
                //localhost:socket_dir
                $parts = explode(":", $host);
				$host = $keys[$driver]["host"] . "=" . $parts[0];
                $unix_socket = ";unix_socket=" . $parts[1];
            } else {
                //socket_dir
                $unix_socket = "unix_socket=$host";
                $host = '';
            }
        } else {
            //clean host
            $unix_socket = '';
            $host = $keys[$driver]["host"] . "=$host";
        }

        $error = false;

        if ($port != '') {
            $port = $keys[$driver]["port"]."$port;";
        } else {
            $port = ";";
        }



        $dbname = $keys[$driver]["dbname"] . "=";

        $dsn = "$driver:$host$unix_socket$port$dbname$client_db_name";

        try {

            $dbh = new PDO($dsn, $username, $password, array(
                        PDO::ATTR_PERSISTENT => false
                    ));
        } catch (PDOException $e) {

            //self::freichat_debug("unable to connect to database. Error : " . $e->getMessage());
            $error = $e->getMessage();
        }

        if (!$error) {
            return array($dsn, true);
        }
        $dbh = null; //reset connection;
        //if in localhost , host cannot be localhost for unix 

        $_error = false;

        $host = $keys[$driver]["host"] . "=127.0.0.1";

        $dsn = "$driver:$host$unix_socket$port$dbname$client_db_name";

        try {
            $dbh = new PDO($dsn, $username, $password, array(
                        PDO::ATTR_PERSISTENT => false
                    ));
        } catch (PDOException $e) {

            //self::freichat_debug("unable to connect to database. Error : " . $e->getMessage());
            $_error = $e->getMessage();
        }

        if (!$_error) {
            return array($dsn, true);
        }

        return array($error, false);
    }

    public static function get_connection($dsn, $db_user, $db_pass) {

        if (self::$connected == true) {
            return self::$connection;
        }


        try {
            self::$connection = new PDO($dsn, $db_user, $db_pass, array(
                //check for side effects
                //changed to false in v8.7 
                //due to inconsistencies while testing in IIS 8.0
                //problem: 500 internal server error 
                        PDO::ATTR_PERSISTENT => false //make a persistent connection
                    ));
        } catch (PDOException $e) {
            self::freichat_debug("unable to connect to database. Error : " . $e->getMessage());
            //die(); //do not die
            return false; //instead return false
        }

        self::freichat_debug("connected to database successfully");
        self::$connection->exec("SET CHARACTER SET utf8");
        self::$connection->exec("SET NAMES utf8");

        self::$connected = true;
        return self::$connection;
    }

    private static function freichat_debug($message) {
        if (self::$debug == true) {
            $dbgfile = fopen("../freixlog.log", "a");
            fwrite($dbgfile, "\n" . date("F j, Y, g:i a") . ": " . $message . "\n");
        }
    }

    public function update_db() {


        $db = $_POST['db'];


        //format port acc to PDO
        if ($db['port'] == '' || $db['port'] == null) {
            $db['port'] = '';
        } else {
            $db['port'] = 'port=' . $db['port'] . ';';
        }

        $conn = $this->connect($db['host'], $db['name'], $db['user'], $db['pass'], $db['port'], $db['driver']);

        if (!$conn[1]) {
            echo 'database connection failed ERROR: ' . $conn[0]; // :(
            exit;
        }

        if (!is_writable("../hardcode.php")) {
            echo 'freichat/hardcode.php not writable'; // :(
            exit;
        }

        if (isset($_POST['lang']) && $_POST['lang'] == "asp") {
            $use_cookie = 'freichat_user';
        } else {
            $use_cookie = 'false';
        }
        //Evertything is fine :)

        @file_put_contents("../cache/perm/request.001", "0");

        $data = "<?php
/* Data base details */
\$dsn='$conn[0]'; //DSN
\$db_user='" . $db['user'] . "'; //DB username 
\$db_pass='" . $db['pass'] . "'; //DB password 
\$driver='Custom'; //Integration driver
\$db_prefix='" . $db['prefix'] . "'; //prefix used for tables in database
\$uid='" . uniqid() . "'; //Any random unique number

\$connected='YES'; //only for custom installation

\$PATH = '" . $_POST['PATH'] . "/'; // Use this only if you have placed the freichat folder somewhere else
\$installed=false; //make it false if you want to reinstall freichat
\$admin_pswd='" . $_POST['admin_pass'] . "'; //backend password 

\$debug = false;
\$custom_error_handling='YES'; // used during custom installation

\$use_cookie='" . $use_cookie . "';

/* email plugin */
\$smtp_username = '';
\$smtp_password = '';

\$force_load_jquery = 'NO';

/* Custom driver */
\$usertable='login'; //specifies the name of the table in which your user information is stored.
\$row_username='root'; //specifies the name of the field in which the user's name/display name is stored.
\$row_userid='loginid'; //specifies the name of the field in which the user's id is stored (usually id or userid)


\$avatar_table_name='members'; //specifies the table where avatar information is stored
\$avatar_column_name='avatar'; //specifies the column name where the avatar url is stored
\$avatar_userid='id'; //specifies the userid  to the user to get the user's avatar
\$avatar_reference_user='id'; //specifies the reference to the user to get the user's avatar in user table 
\$avatar_reference_avatar='id'; //specifies the reference to the user to get the user's avatar in avatar
\$avatar_field_name=\$avatar_column_name; //to avoid unnecessary file changes , *do not change
";

        @file_put_contents('../hardcode.php', $data);

        echo 'written'; //mission successfull :)
    }

    public function generate_report() {

        //generate 4 digit unique number
        $digits = 4;
        $random = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);

        $filename = '../client/plugins/upload/upload/report' . $random . '.html';

        $data = $_GET['error_report'];

        @file_put_contents($filename, $data);
        echo $filename;
    }

    /*
      Removes table prefix if present
     */

    private function tolerate_prefix($tbl_name) {
        //make it tolerant
        if ($this->db_prefix != "") {
            //dont want a missing delimiter error
            if (strpos($tbl_name, $this->db_prefix) !== FALSE) {
                //not present
                $tbl_name = str_replace($this->db_prefix, "", $tbl_name);
            }
        }

        return $tbl_name;
    }

    public function test_table_info() {

        $tbl_name = $_POST['table'];
        $tbl_user = $_POST['name'];
        $tbl_id = $_POST['id'];

        global $db_prefix;

        $tbl_name = $this->tolerate_prefix($tbl_name);

        //limit 1 to save resources
        $query = "SELECT $tbl_user,$tbl_id FROM $db_prefix$tbl_name LIMIT 1";
        $res = $this->db->query($query);

        //false on failure
        if ($res) {
            $cnts = file_get_contents("../hardcode.php");

            //store table name without prefix
            $cnts = str_replace("\$usertable='login';", "\$usertable='" . $tbl_name . "';", $cnts);
            $cnts = str_replace("\$row_username='root';", "\$row_username='" . $tbl_user . "';", $cnts);
            $cnts = str_replace("\$row_userid='loginid';", "\$row_userid='" . $tbl_id . "';", $cnts);
            $cnts = str_replace("\$custom_error_handling='YES';", "\$custom_error_handling='NO';", $cnts);

            @file_put_contents("../hardcode.php", $cnts);

            echo 'correct';
        }
    }

    /*
     * 
     * Below three functions taken directly from phpBB :)
     * 
     */

    // remove_comments will strip the sql comment lines out of an uploaded sql file
    // specifically for mssql and postgres type files in the install....

    private function remove_comments(&$output) {
        $lines = explode("\n", $output);
        $output = "";

        // try to keep mem. use down
        $linecount = count($lines);

        $in_comment = false;
        for ($i = 0; $i < $linecount; $i++) {
            if (preg_match("/^\/\*/", preg_quote($lines[$i]))) {
                $in_comment = true;
            }

            if (!$in_comment) {
                $output .= $lines[$i] . "\n";
            }

            if (preg_match("/\*\/$/", preg_quote($lines[$i]))) {
                $in_comment = false;
            }
        }

        unset($lines);
        return $output;
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

        $driver = $_POST['driver'];
       
        

        if($driver == "sqlsrv") {
            $filename = 'install_mssql.sql';
        }else{
            $filename = 'install.sql';
        }

        $sql = file_get_contents($filename);

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

        $cnts = file_get_contents("../hardcode.php");

        $cnts = str_replace("\$installed=false;", "\$installed=true;", $cnts);

        @file_put_contents("../hardcode.php", $cnts);

        if ($res) {

            echo "created";
        }
    }

    public function test_avatar_details() {

        $avatar_table = $_GET['avatar_table'];
        $avatar_column_name = $_GET['avatar_column'];
        $avatar_userid = $_GET['userid_column'];
        $avatar_reference_user = $_GET['reference_column_user'];
        $avatar_reference_avatar = $_GET['reference_column_avatar'];

        global $usertable, $db_prefix;

        $avatar_table_name = $this->tolerate_prefix($avatar_table);
        $usertable = $this->tolerate_prefix($usertable);


        if ($avatar_reference_user != '' && $avatar_reference_avatar != '') {
            //this is for systems that use double linked tables to store avatars such as Drupal 
            //time for complicated queries

            $query = "SELECT a.$avatar_column_name,a.$avatar_userid,u.$avatar_reference_user,a.$avatar_reference_avatar  
			FROM $db_prefix$avatar_table_name AS a,$db_prefix$usertable AS u LIMIT 1";
        } else {
            $query = "SELECT $avatar_column_name,$avatar_userid FROM $db_prefix$avatar_table_name LIMIT 1";
        }

        $res = $this->db->query($query);

        if ($res) {
            //query worked

            $cnts = file_get_contents("../hardcode.php");

            //store table name without prefix
            $cnts = str_replace("\$avatar_table_name='members'", "\$avatar_table_name='" . $avatar_table_name . "'", $cnts);
            $cnts = str_replace("\$avatar_column_name='avatar';", "\$avatar_column_name='" . $avatar_column_name . "';", $cnts);
            $cnts = str_replace("\$avatar_userid='id';", "\$avatar_userid='" . $avatar_userid . "';", $cnts);
            $cnts = str_replace("\$avatar_reference_avatar='id';", "\$avatar_reference_avatar='" . $avatar_reference_avatar . "';", $cnts);
            $cnts = str_replace("\$avatar_reference_user='id';", "\$avatar_reference_user='" . $avatar_reference_user . "';", $cnts);

            //@-> do not corrupt the response 
            @file_put_contents("../hardcode.php", $cnts);

            echo "correct";
        }
    }

    private function remove_forward_slash($url) {
        if ($url[strlen($url) - 1] == "/") {
            $url = rtrim($url, "/");
        }
        return $url;
    }

    private function test_avatar_url($avatar, $site_url, $primary, $correction) {


        if (!$primary) {
            //here user may or may not have any avatar

            if ($avatar == NULL || $avatar == "") {
                return array(1, "");
            }
        } else {
            //here user must have an avatar
            //raise error

            if ($avatar == NULL || $avatar == "") {
                return array(0, "");
            }
        }

        $url = false;
        $site_url = $this->remove_forward_slash($site_url);

        if (strpos($avatar, "http://") === FALSE && strpos($avatar, "https://") === FALSE) {

            if ($avatar[0] != "/") {
                $slash = "/";
            } else {
                $slash = "";
            }


            if ($correction == "") {
                global $PATH;

                if (@$_SERVER["HTTPS"] == "on") {
                    $protocol = "https://";
                } else {
                    $protocol = "http://";
                }
                $address = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

                //http address to main folder
                $root = str_replace($PATH . "installation/smart_ajax.php", "", $address);

                $root = $this->remove_forward_slash($root);
            } else {
                if ($this->diff) {
                    $avatar = '';
                }
                $root = $this->remove_forward_slash($correction);
            }

            $url = $root . $slash . $avatar;


            $try = @file_get_contents($url);

            if ($try) {
                return array(1, $url);
            } else {
                return array(0, $url);
            }
        } else {
            return array(1, $avatar);
            //assume it is correct , not a issue to be handles by freichat
        }


        //returns url of avatar false on failure 
        return array(0, $url);
    }

    public function test_avatar() {

        /*
         * correct => no  problems
         * wrong => give path to correct after testing site.com/avatar client side
         */



        $site_url = $_GET['site_url'];
        $id = $_GET['id'];

        $correction = $_GET['correction'];
        $new_rule = $_GET['new_rule'];
        $orig = $_GET['original'];

        $this->diff = false;

        $this->path_vars = false;

        if (strpos($correction, "}") !== FALSE) {

            $vars = explode("}", $correction);
            $path_vars = array();

            foreach ($vars as $var) {
                $s = explode("{", $var);

                $path_vars[] = $s[1];
            }



            $this->path_vars = $path_vars;
        }

        print_r($this->path_vars);

        if ($new_rule == "name_changed") {

            //other possibilities can exist 
            //but here only smf like url is concerned for now
            $correct_arr = explode("/", $correction);
            $correct = end($correct_arr);

            if ($correct == '') {
                end($correct_arr);
                $correct = prev($correct_arr);
            }

            $pos = strpos($orig, $correct);
            if ($pos !== FALSE) {
                $this->diff = substr($orig, 0, $pos);
            }
        }

        /* preserved for future reference
          if($correction != "") {
          //correct the avatar_url();
          } */

        $changed = 0;


        if (isset($_SESSION['correction'])) {
            if ($correction != $_SESSION['correction']) {
                $_SESSION['correction'] = $correction;
                $changed = 1;
            }
        } else {
            $_SESSION['correction'] = $correction;
            $changed = 1;
        }


        //get avatar table details
        global $avatar_table_name, $avatar_column_name, $avatar_reference_avatar, $avatar_reference_user, $avatar_userid;

        //get user table details
        global $usertable;

        //get DB prefix
        global $db_prefix;




        $double_linked = false;

        if ($avatar_reference_user != '' && $avatar_reference_avatar != '') {
            //this is for systems that use double linked tables to store avatars such as Drupal 
            //time for complicated queries
            $double_linked = true;
            $query = "SELECT a.$avatar_column_name FROM $db_prefix$avatar_table_name AS a,$db_prefix$usertable AS u 
                       WHERE u.$avatar_reference_user = a.$avatar_reference_avatar AND u.$avatar_userid = $id";
        } else {
            $query = "SELECT a.$avatar_column_name FROM $db_prefix$avatar_table_name AS a WHERE a.$avatar_userid = $id";
        }

        //get current logged in users avatar
        $res = $this->db->query($query);

        if (!$res) {
            //wrong userid has been passed to freichat
            echo json_encode(array('exit', "wrong userid is passed or your above details are improper"));
            exit(0);
        }



        $res = $res->fetchAll();

        if (empty($res)) {
            //wrong userid has been passed to freichat
            echo json_encode(array('exit', "you have either not logged in or userid passed is wrong !"));
            exit(0);
        }

        //primary check -> true
        $avatar_url = $res[0][0];
        $avatar_url = str_replace(" ", "%20", $avatar_url);

        $res = $this->test_avatar_url($avatar_url, $site_url, true, $correction);

        if ($res[0]) {

            //now safe to REPLACE avatar fetch query
            $this->replace_fetch_queries();


            //do some further testing
            //get avatars of 100 users
            if ($double_linked) {
                $query = "SELECT a.$avatar_column_name AS avatar_url FROM $db_prefix$usertable AS u, $db_prefix$avatar_table_name AS a WHERE u.$avatar_reference_user = a.$avatar_reference_avatar LIMIT 100";
            } else {
                $query = "SELECT a.$avatar_column_name AS avatar_url FROM $db_prefix$avatar_table_name AS a LIMIT 100";
            }


            $avatars = $this->db->query($query)->fetchAll();
            foreach ($avatars as $avatar) {
                $avatar['avatar_url'] = str_replace(" ", "%20", $avatar['avatar_url']);
                $res = $this->test_avatar_url($avatar['avatar_url'], $site_url, false, $correction);

                if (!$res[0]) {

                    echo json_encode(array("wrong", $res[1], $avatar['avatar_url'], $changed));
                    //url needs to be corrected
                    exit;
                }
            }

            echo json_encode(array('correct', $res[1], $avatar_url));
            $this->replace_avatar_url();
            //now safe to replace avatar_url()
            //everything is perfect 
        } else {
            echo json_encode(array('wrong', $res[1], $avatar_url, $changed)); //echo url to be corrected
        }
    }

    private function preg_replace_pt($startPoint, $endPoint, $newText, $source) {
        return preg_replace('#(' . preg_quote($startPoint) . ')(.*)(' . preg_quote($endPoint) . ')#si', '$1' . $newText . '$3', $source);
    }

    private function replace_avatar_url() {

        $filename = "../server/drivers/Custom.php";
        $cnts = file_get_contents($filename);


        $root = $this->remove_forward_slash($_SESSION['correction']);



        $start = "//AVATAR_URL_START";
        $end = "//AVATAR_URL_END";

        $diff = $this->diff;

        $func = "
        public function avatar_url(\$res) {
            \$root = '$root';
            \$avatar = \$res[\$this->avatar_field_name];
            \$avatar = str_replace(' ','%20',\$avatar);
        ";


        if ($diff) {

            $func .= "
                if(strpos(\$avatar,'$diff') !== FALSE) {
                    \$avatar = str_replace('$diff','',\$avatar);
                }
                ";
        }

        $func .= "if (strpos(\$avatar, 'http://') === FALSE && strpos(\$avatar, 'https://') === FALSE) {
                \$slash = '/';
                if(\$avatar[0] == '/') \$slash = '';
        
                return \$root.\$slash.\$avatar;
            }else{
                return \$avatar;
            }
        }
        ";

        $cnts = $this->preg_replace_pt($start, $end, $func, $cnts);

        file_put_contents($filename, $cnts);
    }

    private function replace_fetch_queries() {

        //get avatar table details
        global $avatar_table_name, $avatar_column_name, $avatar_reference_avatar, $avatar_reference_user, $avatar_userid;

        //get user table details
        global $usertable, $row_userid;

        //get DB prefix
        global $db_prefix;

        $filename = "../server/drivers/Custom.php";
        $cnts = file_get_contents($filename);

        $g_start = "//CUSTOM_GUESTS_QUERY_START";
        $g_end = "//CUSTOM_GUESTS_QUERY_END";

        $double_linked = false;

        if ($avatar_reference_avatar != '' && $avatar_reference_user != '') {
            $double_linked = true;
        }

        //No need to do AS avatar in any of the queries


        if ($double_linked) {
            $guests = "
            \$query = \"SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,f.in_room,a.$avatar_column_name 
                   FROM frei_session AS f LEFT JOIN $db_prefix$usertable AS u ON u.$row_userid = f.session_id
                    LEFT JOIN $db_prefix$avatar_table_name AS a ON u.$avatar_reference_user = a.$avatar_reference_avatar
                  WHERE f.time>\" . \$this->online_time2 . \"
                   AND f.session_id!=\" . \$_SESSION[\$this->uid . 'usr_ses_id'] . \"
                   AND f.status!=2
                   AND f.status!=0\";\n";
        } else {
            $guests = "
            \$query = \"SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,f.in_room,a.$avatar_column_name
                   FROM frei_session AS f LEFT JOIN $db_prefix$avatar_table_name AS a ON f.session_id = a.$avatar_userid
                  WHERE f.time>\" . \$this->online_time2 . \"
                   AND f.session_id!=\" . \$_SESSION[\$this->uid . 'usr_ses_id'] . \"
                   AND f.status!=2
                   AND f.status!=0\";\n";
        }

        $u_start = "//CUSTOM_USERS_QUERY_START";
        $u_end = "//CUSTOM_USERS_QUERY_END";

        if ($double_linked) {
            $users = "
            \$query = \"SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,f.in_room,a.$avatar_column_name 
                   FROM frei_session AS f LEFT JOIN $db_prefix$usertable AS u ON u.$row_userid = f.session_id
                    LEFT JOIN $db_prefix$avatar_table_name AS a ON u.$avatar_reference_user = a.$avatar_reference_avatar
                  WHERE f.time>\" . \$this->online_time2 . \"
                   AND f.session_id!=\" . \$_SESSION[\$this->uid . 'usr_ses_id'] . \"
                   AND f.status!=2
                   AND f.status!=0
                   AND f.guest=0\";\n";
        } else {
            $users = "
            \$query = \"SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,f.in_room,a.$avatar_column_name
                   FROM frei_session AS f LEFT JOIN $db_prefix$avatar_table_name AS a ON f.session_id = a.$avatar_userid
                  WHERE f.time>\" . \$this->online_time2 . \"
                   AND f.session_id!=\" . \$_SESSION[\$this->uid . 'usr_ses_id'] . \"
                   AND f.status!=2
                   AND f.status!=0
                   AND f.guest=0\";\n";
        }

        $b_start = "//CUSTOM_BUDDIES_QUERY_START";
        $b_end = "//CUSTOM_BUDDIES_QUERY_END";

        if ($double_linked) {
            $buddies = "
            \$query = \"SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,f.in_room,a.$avatar_column_name 
                   FROM frei_session AS f LEFT JOIN $db_prefix$usertable AS u ON u.$row_userid = f.session_id
                    LEFT JOIN $db_prefix$avatar_table_name AS a ON u.$avatar_reference_user = a.$avatar_reference_avatar
                  WHERE f.time>\" . \$this->online_time2 . \"
                   AND f.session_id!=\" . \$_SESSION[\$this->uid . 'usr_ses_id'] . \"
                   AND f.status!=2
                   AND f.status!=0
                   AND f.guest=0\";\n";
        } else {
            $buddies = "
            \$query = \"SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,f.in_room,a.$avatar_column_name
                   FROM frei_session AS f LEFT JOIN $db_prefix$avatar_table_name AS a ON f.session_id = a.$avatar_userid
                  WHERE f.time>\" . \$this->online_time2 . \"
                   AND f.session_id!=\" . \$_SESSION[\$this->uid . 'usr_ses_id'] . \"
                   AND f.status!=2
                   AND f.status!=0
                   AND f.guest=0\";\n";
        }

        //mem eater
        $cnts = $this->preg_replace_pt($g_start, $g_end, $guests, $cnts);
        $cnts = $this->preg_replace_pt($u_start, $u_end, $users, $cnts);
        $cnts = $this->preg_replace_pt($b_start, $b_end, $buddies, $cnts);

        file_put_contents($filename, $cnts);
    }

    //no no no -> i simply don't like break n;
    private function chk_possibility($possible_arr, $fixes, $val, $mids) {

        $p_val = $break = false;

        foreach ($possible_arr as $possible) {

            if (strcasecmp($possible, $val) == 0) {
                $p_val = $val;
                break;
            }

            foreach ($fixes as $fix) {

                foreach ($mids as $mid) {
                    if (strcasecmp($possible . $mid . $fix, $val) == 0) {
                        $p_val = $val;
                        $break = true;
                    }
                }

                if ($break)
                    break;
            }

            //try it the other way round too 
            //different styles of different people ;)
            foreach ($fixes as $fix) {

                foreach ($mids as $mid) {
                    if (strcasecmp($fix . $mid . $possible, $val) == 0) {
                        $p_val = $val;
                        $break = true;
                    }
                }

                if ($break)
                    break;
            }


            if ($break)
                break;
        }

        return $p_val;
    }

    private function chk_table_possibility($values, $possible_arr, $fixes) {

        $p_val = $break = false;


        //exchanging below foreach can reduce memusage by 1/nth the size of array 
        //but this will disrupt the order of that array thus giving us the 
        //wrong result in certain cases

        foreach ($possible_arr as $possible) {

            foreach ($values as $val) {

                $val = $this->tolerate_prefix($val[0]);

                if (strcasecmp($possible, $val) == 0) {
                    $p_val = $val;
                    $break = true;
                    break;
                }


                foreach ($fixes as $fix) {

                    if (strcasecmp($possible . $fix, $val) == 0) {
                        $p_val = $val;
                        $break = true;
                        break;
                    }
                }

                if ($break)
                    break;
            }

            if ($break)
                break;
        }

        return $p_val;
    }

    private function get_usercolumn($column) {

        $possible_columns = array("", "user", "member", "screen");
        $fixes = array("base", "name", "login");
        $mids = array("", "_");

        return $this->chk_possibility($possible_columns, $fixes, $column, $mids);
    }

    private function get_idcolumn($column) {

        $possible_columns = array("", "u", "g", "user", "member");
        $fixes = array("base", "id", "uid");
        $mids = array("", "_");

        return $this->chk_possibility($possible_columns, $fixes, $column, $mids);
    }

    public function get_tables_smartly() {

        $query = "SHOW tables";
        $q = $this->db->query($query);

        if (!$q) {
            echo json_encode(0);
            exit;
        }

        $tables = $q->fetchAll();

        //in descending order of importance
        $possible_tables = array("user", "member", "session");
        $fixes = array("s");

        $usertable = false;

        $usertable = $this->chk_table_possibility($tables, $possible_tables, $fixes);

        if ($usertable) {

            global $db_prefix;

            //1 row is enough to retrieve all column names
            $query = "SELECT * FROM $db_prefix$usertable LIMIT 1";
            $q = $this->db->query($query);

            if (!$q) {
                echo json_encode(array("usertable" => $usertable));
                exit;
            }

            $columns = $q->fetchObject();
            $usercolumn = false;
            $idcolumn = false;


            $do_user = true;
            $do_id = true;

            foreach ($columns as $column => $val) {
                $val = "you are useless here!";

                if ($do_user) {
                    $usercolumn = $this->get_usercolumn($column);
                }

                if ($do_id) {
                    $idcolumn = $this->get_idcolumn($column);
                }

                if ($usercolumn) {
                    $do_user = false;
                }

                if ($idcolumn) {
                    $do_id = false;
                }

                if (!$do_user && !$do_id) {
                    break;
                }
            }

            if ($usercolumn && $idcolumn) {
                echo json_encode(array(
                    "usertable" => $usertable,
                    "usercolumn" => $usercolumn,
                    "idcolumn" => $idcolumn
                ));
            } else {
                echo json_encode(array("usertable" => $usertable));
            }
        } else {
            echo json_encode(0);
        }
    }

}

if (isset($_REQUEST["action"])) {
    $smart = new Smart();
    $smart->$_REQUEST['action']();
}
