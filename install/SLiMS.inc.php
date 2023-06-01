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
    /**
     * @var mysqli
     */
    private $db = null;
    private $sql_mode = '';

  function getAuthorizationHeader()
  {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
      $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
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

  function getBearerToken()
  {
    $headers = $this->getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
      if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
      }
    }
    return null;
  }

  function phpExtensionCheck($returnType = 'html')
  {
    if ($returnType == 'bool')
    {
      // Minimum SLiMS PHP Extension requirement
      return $this->isGdOk() && $this->isMbStringOk() && $this->isGettextOk() && $this->isPdoOk();
    }

    $message  = '<div class="flex flex-col">';
    $message .= $this->isGdOk() ? '<span>GD : installed</span>' : '<div>GD : <strong class="text-red-500">not installed. PHP GD required for image processing!</strong></div>';
    $message .= $this->isMbStringOk() ? '<span>Mbstring : installed</span>' : '<divMbstring : <strong class="text-red-500">not installed. PHP Mbstring is used to convert strings to different encodings.</strong></div>';
    $message .= $this->isGettextOk() ? '<span>Gettext : installed</span>' : '<div>Gettext : <strong class="text-red-500">not installed. PHP gettext required for translation to other language.</strong></div>';
    $message .= $this->isPdoOk() ? '<span>PDO MySQL : installed</span>' : '<div>PDO : <strong class="text-red-500">not installed. PHP pdo required by some feature in SLiMS.</strong></div>';
    // Yaz is optional
    $message .= $this->isYazOk() ? '<span>YAZ : installed</span>' : '<div>YAZ : <strong class="text-red-500">not installed. It\'s optional, but will be needed if you want to use Z39.50 protocol.</strong></div>';
    $message .= '</div>';

    return $message;
  }

  function isPdoOk()
  {
    return class_exists('PDO') && in_array('mysql', \PDO::getAvailableDrivers());
  }

  function isPhpOk($expectedVersion)
  {
    // Is this version of PHP greater than minimum version required?
    return version_compare(PHP_VERSION, $expectedVersion, '>=');
  }

  function databaseDriverType()
  {
    $mysql = extension_loaded('mysqli') || extension_loaded('nd_mysqli');
    $pdoMySQL = extension_loaded('pdo_mysql') || extension_loaded('nd_pdo_mysql');
    if ($mysql && $pdoMySQL) {
      $type = 'MySQLi & PDO MySQL';  
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
    if (!function_exists('gd_info')) {
      return false;
    }

    $gd_info = gd_info();
    $gd_version = preg_replace('/[^0-9\.]/', '', $gd_info['GD Version']);

    // Image extension Support
    $Need = ['GIF Read Support','GIF Create Support','JPEG Support','PNG Support'];
    $extensionCheck = array_filter($Need, function($Extension) use($gd_info) {
      if (isset($gd_info[$Extension]) && ($gd_info[$Extension]))
      {
          return true;
      }
    });

    // If the GD version is at least 1.0
    return ($gd_version >= 1 && count($extensionCheck) == 4);
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

  function chkDir()
  {
    $result['status'] = true;
    $html = '';
    $config = 'Yes';
    $files = 'Yes';
    $images = 'Yes';
    $repository = 'Yes';

    if(!is_writable(__DIR__ . '/../config/')){
      $result['status'] = false;
      $config = 'NO';
    }

    if(!is_writable(__DIR__ . '/../files/')){
      $result['status'] = false;
      $files = 'NO';
    }

    if(!is_writable(__DIR__ . '/../images/')){
      $result['status'] = false;
      $images = 'NO';
    }
    
    if(!is_writable(__DIR__ . '/../repository/')){
      $result['status'] = false;
      $repository = 'NO';
    }

    $html .= '/config is writable : '.($config).'<br/>';
    $html .= '/files is writable : '.($files).'<br/>';  
    $html .= '/images is writable : '.($images).'<br/>';  
    $html .= '/repository is writable : '.($repository).'<br/>';  

    $result['data'] = $html;
    return $result;
  }

  function filter($mix_input, $type)
  {
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

  function createConnection($host, $port = '3306', $user = 'root', $pass = '', $name = null)
  {
    if (is_null($this->db)) {
        $this->db = @new mysqli($host, $user, $pass, $name, $port);
    }
    if (mysqli_connect_error()) {
      throw new Exception("Error Connecting to Database with message: ".mysqli_connect_error());
    }
    return $this->db;
  }

  function setConnection($db)
  {
    $this->db = $db;
  }

  function isDatabaseExist($database_name)
  {
    $query = $this->db->query(sprintf("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '%s'", $database_name));
    return $query->num_rows > 0;
  }

  function createDatabase($database_name)
  {
    return $this->db->query("CREATE DATABASE IF NOT EXISTS `{$database_name}` character set UTF8mb4 collate utf8mb4_bin");
  }

  public function getDb()
  {
    return $this->db;
  }

  function getStorageEngines()
  {
    $basic_engines = [1 => 'MyISAM','Aria','InnoDB'];
    $state = $this->db->query('SHOW ENGINES');

    $engines = [];
    while ($result = $state->fetch_object()) {
      foreach ($basic_engines as $index => $engine) {
        if ($result->Engine === $engine && in_array($result->Support, ['YES','DEFAULT'])) {
          $engines[$index] = [$result->Engine, $result->Comment];
        }      
      }
    }

    return $engines;
  }

  function updateStorageEngine()
  {
    if (!isset($_POST['engine']) || $_POST['engine'] === 'MyISAM') return;
    
    $state = $this->db->query('SHOW TABLES');

    while ($result = $state->fetch_row()) {
      $tableName = $this->db->escape_string($result[0]);
      $tableEngine = $this->db->escape_string($_POST['engine']);
      $this->db->query('ALTER TABLE `' . $tableName . '` ENGINE=\''.$tableEngine.'\';');
    }
  }

  function createTable($table) {
    try {
      $column_str = '';
      $primaryKey = '';
      foreach ($table['column'] as $column) {
        $null = $column['null'] ? 'NULL' : 'NOT NULL';
        $default = $column['default'] !== '' ? "DEFAULT '" . $column['default'] . "'" : '';
        if (is_null($column['default'])) $default = 'DEFAULT NULL';
        if ($column['default'] === 'AUTO_INCREMENT') {
          $default = 'AUTO_INCREMENT';
          $primaryKey = "PRIMARY KEY (`{$column['field']}`),";
        }
        $column_str .= "`{$column['field']}` {$column['type']} COLLATE 'utf8mb4_unicode_ci' {$null} {$default}, ";
      }

      $column_str .= $primaryKey;

      if ($column_str === '') throw new Exception('Column can not be empty');

      // remove last comma
      $column_str = substr(trim($column_str), 0, -1);

      $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$table['table']}` ({$column_str})
ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
SQL;

      // die($sql);

      $stmt = $this->db->prepare($sql);
      if (!$stmt) return $this->db->error . '. Your syntax: ' . $sql;
      $stmt->execute();
      $stmt->close();
    } catch (Exception $exception) {
      return $exception->getMessage();
    }
  }

  function getTables()
  {
    $r = [];
    $query = $this->db->query("SHOW TABLES");
    if (!$query) {
      throw new Exception($this->db->error);
    }
    while ($data = $query->fetch_row()) {
      $r[] = $data[0];
    }
    return $r;
  }

  function getColumn($table, $all = false)
  {
    $r = [];
    $query = $this->db->query("SHOW COLUMNS FROM {$table}");
    if (!$query) {
      throw new Exception($this->db->error);
    }
    while ($data = $query->fetch_assoc()) {
      if ($all) {
        $r[] = $data;
      } else {
        $r[] = $data['Field'];
      }
    }
    return $r;
  }

  function addColumn($table, $column)
  {
    try {
      $null = $column['null'] ? 'NULL' : 'NOT NULL';
      $default = $column['default'] !== '' ? "DEFAULT '" . $column['default'] . "'" : '';
      if (is_null($column['default'])) $default = 'DEFAULT NULL';
      if ($column['default'] === 'AUTO_INCREMENT') $default = 'AUTO_INCREMENT';
      $sql = <<<SQL
ALTER TABLE `{$table}` ADD `{$column['field']}` {$column['type']} {$null} {$default};
SQL;
      $stmt = $this->db->prepare($sql);
      if (!$stmt) return $this->db->error . '. Your syntax: ' . $sql;
      $stmt->execute();
      $stmt->close();
    } catch (Exception $exception) {
      return $exception->getMessage();
    }
  }

  function changeColumn($table, $column) {
    try {
      $null = $column['null'] ? 'NULL' : 'NOT NULL';
      $default = $column['default'] !== '' ? "DEFAULT '" . $column['default'] . "'" : '';
      if (is_null($column['default'])) $default = 'DEFAULT NULL';
      $sql = <<<SQL
ALTER TABLE `{$table}` 
    CHANGE `{$column['field']}` `{$column['field']}` {$column['type']} COLLATE 'utf8_unicode_ci' {$null} {$default};
SQL;
      $stmt = $this->db->prepare($sql);
      if (!$stmt) return $this->db->error . '. Your syntax: ' . $sql;
      $stmt->execute();
      $stmt->close();
    } catch (Exception $exception) {
      return $exception->getMessage();
    }
  }

  function createConfigFile(array $options)
  {
    $base_config_file = __DIR__ . '/../config/database.sample.php';
    $config_file_path = __DIR__ . '/../config/database.php';

    if (!is_readable($base_config_file)) throw new Exception('File ' . $base_config_file . ' not readable', 5000);

    if (!is_writable(dirname($base_config_file))) throw new Exception('Directory ' . dirname($base_config_file) . ' not writable', 5001);

    $config_content = file_get_contents($base_config_file);
    $config_content = str_replace("_DB_HOST_", $options['db_host'], $config_content);
    $config_content = str_replace("'_DB_PORT_'", (isset($options['db_port']) ? (int)$options['db_port'] : 3306), $config_content);
    $config_content = str_replace("_DB_NAME_", $options['db_name'], $config_content);
    $config_content = str_replace("_DB_USER_", $options['db_user'], $config_content);
    $config_content = str_replace("_DB_PASSWORD_", $options['db_pass'], $config_content);
    if (isset($_POST['engine'])) $config_content = str_replace("_STORAGE_ENGINE_", trim($_POST['engine']), $config_content);

    $config_file = fopen($config_file_path, 'w');
    $write = fwrite($config_file, $config_content);
    return ['status' => $write];
  }

  function createEnvFile()
  {
    $base_env_file = __DIR__ . '/../config/env.sample.php';
    $env_file_path = __DIR__ . '/../config/env.php';

    if (!file_exists($base_env_file)) {
      throw new Exception("File {$base_env_file} not found!", 404);
    }
    
    $sample = file_get_contents($base_env_file);
    $sample = str_replace('<environment>', 'production', $sample);
    $sample = str_replace('<conditional_environment>', 'production', $sample);
    $sample = str_replace('\'<based_on_ip>\'', 'false', $sample);
    $sample = str_replace('<ip_range>', '', $sample);

    $writeEnv = file_put_contents($env_file_path, $sample);

    if ($writeEnv === false) throw new Exception("Cannot write env file. Create it manually in config directory based on env.sample.php", 403);
  }

  function query($array, $types = [])
  {
    $_return = [];
    foreach ($types as $type) {
      if (array_key_exists($type, $array)) {
        foreach ($array[$type] as $item) {
          try {
            if (isset($_POST['engine']) && $_POST['engine'] !== 'MyISAM') 
            {
              $item = str_replace('ENGINE=MyISAM', 'ENGINE=' . trim($_POST['engine']), $item);
            }
            $stmt = $this->db->prepare($item);
            if (!$stmt) throw new Exception($this->db->error . '. Your syntax: ' . $item);
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


  function queryTrigger($array)
  {
    $_return = [];
    foreach ($array as $key => $item) {
      try{
        $sql = $this->db->query($item);
        if(!$sql) throw new Exception($this->db->error . '. Your syntax: ' . $item);
      } catch (Exception $exception) {
        $_return[] = $exception->getMessage();
      }
    }
    return $_return;
  }

  function updateAdmin($username, $password)
  {
    $username = $this->db->escape_string($username);
    $sql_update = " UPDATE user set
			username = '" . $username . "',
			passwd = '" . password_hash($password, PASSWORD_BCRYPT) . "',
			realname = '" . ucfirst($username) . "',
			last_login = NULL,
			last_login_ip = '127.0.0.1',
			`groups` = 'a:1:{i:0;s:1:\"1\";}',
			input_date = DATE(NOW()),
			last_update = DATE(NOW())
			WHERE user_id = 1";

    return $this->db->query($sql_update);
  }

  function updateTheme($theme = 'default', $upgrade_from = '') {
    // get template setting
    $sysconf = [];
    $query = $this->db->query("SELECT setting_name, setting_value 
                               FROM setting 
                               WHERE setting_name IN ('template','admin_template')");
                               
    while ($data = $query->fetch_assoc()) {
      // get value
      $value = @unserialize($data['setting_value']);
      if (is_array($value)) {
        foreach ($value as $k => $v) {
          $sysconf[$data['setting_name']][$k] = $v;
        }

        // update value
        if (isset($sysconf[$data['setting_name']]['theme'])) $sysconf[$data['setting_name']]['theme'] = $theme;
        if (isset($sysconf[$data['setting_name']]['css'])) $sysconf[$data['setting_name']]['css'] = $data['setting_name'].'/'.$theme.'/style.css';

      } else {
        // Default template if unserialize process is failed
        $sysconf[$data['setting_name']]['theme'] = 'default';
        $sysconf[$data['setting_name']]['css'] = $data['setting_name'].'/default/style.css';
      }

      // update admin template per user if SLiMS version start from v9.2.0
      if ($upgrade_from > 22 && $data['setting_name'] == 'admin_template') {
        $this->db->query('UPDATE user SET admin_template = \''.$this->db->escape_string(serialize($sysconf[$data['setting_name']])).'\'');
      }

      // save again
      $this->db->query('UPDATE setting SET setting_value=\''.$this->db->escape_string(serialize($sysconf[$data['setting_name']])).'\' WHERE setting_name=\''.$data['setting_name'].'\'');
    }
  }

  function storeOldSqlMode() {
      $query = $this->db->query("SELECT @@sql_mode");
      if ($query->num_rows > 0) {
          $row = $query->fetch_row();
          $this->sql_mode = $row[0];
      }
      return $this->sql_mode;
  }

  function updateSqlMode($sql_mode = '') {
      return $this->db->query(sprintf("SET @@sql_mode := '%s' ;", $sql_mode));
  }

  function rollbackSqlMode() {
      return $this->db->query(sprintf("SET @@sql_mode := '%s' ;", $this->sql_mode));
  }

}
