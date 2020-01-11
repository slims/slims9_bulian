<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2020-01-10 15:14
 * @File name           : SLiMS.inc.php
 */

namespace Install;


use Exception;
use mysqli;

class SLiMS
{
  private $db = null;

  function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
      $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
      $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
      $requestHeaders = apache_request_headers();
      // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
      $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
      //print_r($requestHeaders);
      if (isset($requestHeaders['Authorization'])) {
        $headers = trim($requestHeaders['Authorization']);
      }
    }
    return $headers;
  }

  function getBearerToken() {
    $headers = $this->getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
      if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
      }
    }
    return null;
  }

  function isPhpOk($expectedVersion)
  {
    // Is this version of PHP greater than minimum version required?
    return version_compare(PHP_VERSION, $expectedVersion, '>=');
  }

  function databaseDriverType()
  {
    if (extension_loaded('mysql')) {
      $type = 'mysql';
    } else if (extension_loaded('mysqli')) {
      $type = 'mysqli';
    } else {
      $type = null;
    }

    return $type;
  }

  function isZlibOk()
  {
    return extension_loaded('zlib');
  }

  function isCurlOk()
  {
    return extension_loaded('curl');
  }

  function isMcryptOk()
  {
    return extension_loaded('mcrypt');
  }

  function isGdOk()
  {
    // Homeboy is not rockin GD at all
    if (! function_exists('gd_info')) {
      return false;
    }

    $gd_info = gd_info();
    $gd_version = preg_replace('/[^0-9\.]/', '', $gd_info['GD Version']);

    // If the GD version is at least 1.0
    return ($gd_version >= 1);
  }

  function isYazOk()
  {
    return extension_loaded('yaz');
  }

  function isGettextOk()
  {
    return extension_loaded('gettext');
  }

  function isMbStringOk()
  {
    return extension_loaded('mbstring');
  }

  function filter($mix_input, $type) {
    if (extension_loaded('filter')) {
      switch ($type) {
        case 'get':
          $mix_input = filter_input(INPUT_GET, $mix_input);
          break;
        case 'post':
          $mix_input = filter_input(INPUT_POST, $mix_input);
          break;
      }
    } else {
      switch ($type) {
        case 'get':
          $mix_input = $_GET[$mix_input];
          break;
        case 'post':
          $mix_input = $_POST[$mix_input];
          break;
      }
    }

    // trim whitespace on string
    $mix_input = trim($mix_input);
    // strip html
    $mix_input = strip_tags($mix_input);

    return $mix_input;
  }

  function createConnection($host, $user, $pass = '') {
    if (is_null($this->db)) $this->db = @new mysqli($host, $user, $pass);
    return $this->db;
  }

  function isDatabaseExist($database_name) {
    $stmt = $this->db->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
    $stmt->bind_param('s', $database_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->num_rows > 0;
  }

  function createDatabase($database_name) {
    return $this->db->query("CREATE DATABASE IF NOT EXISTS `{$database_name}` character set UTF8mb4 collate utf8mb4_bin");
  }

  public function getDb()
  {
    return $this->db;
  }

  function getTables() {
    $r = [];
    $query = $this->db->query("SHOW TABLES");
    while ($data = $query->fetch_row()) {
      $r[] = $data[0];
    }
    return $r;
  }

  function createConfigFile(array $options) {
    $base_config_file = './../config/sysconfig.local.inc-sample.php';
    $config_file_path = './../config/sysconfig.local.inc.php';

    if (!is_readable($base_config_file)) {
      throw new Exception('File '.$base_config_file.' not readable', 5000);
    }
    if (!is_writable(dirname($base_config_file))) {
      throw new Exception('Directory '.dirname($base_config_file).' not writable', 5001);
    }

    $config_content = file_get_contents($base_config_file);
    $config_content = str_replace("_DB_HOST_", $options['db_host'], $config_content);
    $config_content = str_replace("_DB_PORT_", ($options['db_port'] ?? 3306), $config_content);
    $config_content = str_replace("_DB_NAME_", $options['db_name'], $config_content);
    $config_content = str_replace("_DB_USER_", $options['db_user'], $config_content);
    $config_content = str_replace("_DB_PASSWORD_", $options['db_pass'], $config_content);

    $config_file = fopen($config_file_path, 'w');
    $write = fwrite($config_file, $config_content);
    return ['status' => $write];
  }

  function query($array, $types = []) {
    $_return = [];
    foreach ($types as $type) {
      if (array_key_exists($type, $array)) {
        foreach ($array[$type] as $item) {
          try {
            $stmt = $this->db->prepare($item);
            $stmt->execute();
            $stmt->close();
          } catch (Exception $exception) {
            $_return[] = $exception->getMessage();
          }
        }
      }
    }
    return $_return;
  }

  function updateAdmin($username, $password) {
    $sql_update = " UPDATE user set
			username = '".$username."',
			passwd = '".password_hash($password, PASSWORD_BCRYPT)."',
			realname = '".ucfirst($username)."',
			last_login = NULL,
			last_login_ip = '127.0.0.1',
			groups = 'a:1:{i:0;s:1:\"1\";}',
			input_date = DATE(NOW()),
			last_update = DATE(NOW())
			WHERE user_id = 1";

    return $this->db->query($sql_update);
  }

}