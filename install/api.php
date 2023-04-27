<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2020-01-10 14:30
 * @File name           : api.php
 */

use Install\SLiMS;

// key to authenticate
define('INDEX_AUTH', '1');

session_start();
@ini_set('display_errors', false);

require_once 'SLiMS.inc.php';

$slims = new SLiMS();

// switch request
$_POST = json_decode(file_get_contents('php://input'), true);
$action = isset($_POST['action']) ? $_POST['action'] : '';
if ($action === 're-install' || $action === 're-upgrade') {
  if (file_exists($databaseConfigPath = __DIR__ . '/../config/database.php')) {
    $profile = require_once $databaseConfigPath;
    extract($profile['nodes'][$profile['default']]);
    $_SESSION['db_host'] = $host;
    $_SESSION['db_name'] = $database;
    $_SESSION['db_user'] = $username;
    $_SESSION['db_pass'] = $password;
    $_SESSION['db_port'] = $port;
  } else {
    die(json_encode(['status' => false, 'message' => ['Config file not exist. Please, create it first!']]));
  }
}

if (isset($_GET['storeage_engines'])) {
  $isProfileExists = isset($_SESSION['db_host']) && 
                     isset($_SESSION['db_name']) && 
                     isset($_SESSION['db_user']) && 
                     isset($_SESSION['db_pass']) && 
                     isset($_SESSION['db_port']);

  if (!$isProfileExists) die(json_encode(['status' => false]));

  try {
    $slims->createConnection($_SESSION['db_host'], $_SESSION['db_port']??'3306', $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);
    $engines = $slims->getStorageEngines();
    ksort($engines);
    die(json_encode(['status' => true, 'data' => array_values($engines)]));
  } catch (Exception $e) {
    die(json_encode(['status' => false, 'message' => $e->getMessage()]));
  }
  exit;
}

switch ($action) {
  case 'system-requirement':
    $php_minimum_version = '8.1';
    $check_dir = $slims->chkDir();
    $data = [
      'is_pass' => $slims->isPhpOk($php_minimum_version) &&
        $slims->databaseDriverType() &&
        $slims->phpExtensionCheck('bool') &&
        $check_dir['status'],
      'data' => [
        'php' => [
          'title' => 'PHP Version',
          'status' => $slims->isPhpOk($php_minimum_version),
          'version' => phpversion(),
          'message' => 'Minimum PHP version to install SLiMS is ' . $php_minimum_version . '. Please upgrade it first!'
        ],
        'database' => [
          'title' => 'Database driver',
          'status' => $slims->databaseDriverType(),
          'version' => $slims->databaseDriverType(),
          'message' => 'SLiMS required MySQLi and PDO MySQL extension for database management. Please install it first!'
        ],
        'phpextension' => [
          'title' => 'PHP Extension',
          'status' => '',
          'version' => '*',
          'data' => $slims->phpExtensionCheck()
        ],
        'chkdir' => [
          'title' => 'Pre-Installation Step',
          'status' => $check_dir['status'],
          'data' => $check_dir['data'],
          'message' => 'Make the following files and directories (and their contents) writeable (i.e., by changing the owner or permissions with chown or chmod)'
        ]        
      ]
    ];
    sleep(1);
    die(json_encode($data));
    break;

  case 'test-connection':
  case 'test-connection-upgrade':

    $_SESSION['db_host'] = isset($_POST['host']) ? $_POST['host'] : 'localhost';
    $_SESSION['db_port'] = isset($_POST['port']) ? $_POST['port'] : '3306';
    $_SESSION['db_name'] = isset($_POST['name']) ? $_POST['name'] : '';
    $_SESSION['db_user'] = isset($_POST['user']) ? $_POST['user'] : '';
    $_SESSION['db_pass'] = isset($_POST['pass']) ? $_POST['pass'] : '';

    if (empty($_SESSION['db_name'])) die(json_encode(array('status' => false, 'field' => 'name', 'message' => 'Database name is required.')));
    if (empty($_SESSION['db_user'])) die(json_encode(array('status' => false, 'field' => 'user', 'message' => 'Database username is required.')));

    try {
      if ($action === 'test-connection-upgrade') {
        $slims->createConnection($_SESSION['db_host'], $_SESSION['db_port'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);
      } else {
        $slims->createConnection($_SESSION['db_host'], $_SESSION['db_port'], $_SESSION['db_user'], $_SESSION['db_pass']);
      }

      if (mysqli_connect_error()) {
        die(json_encode(array('status' => false, 'message' => mysqli_connect_error())));
      }
      die(json_encode(array('status' => true)));
    } catch (Exception $ex) {
      die(json_encode(array('status' => false, 'message' => $ex->getMessage())));
    }
    break;

  case 'do-install':
  case 're-install':
    try {
      $slims->createConnection($_SESSION['db_host'], $_SESSION['db_port'], $_SESSION['db_user'], $_SESSION['db_pass']);
      // create if not exist
      if (!$slims->isDatabaseExist($_SESSION['db_name'])) $slims->createDatabase($_SESSION['db_name']);
      // use database
      $slims->getDb()->query("USE `{$_SESSION['db_name']}`");
      // write configuration file
      $slims->createConfigFile($_SESSION);
      // write environment file
      $slims->createEnvFile();
      // check if database already have table for make sure this database is empty
      foreach ($slims->getTables() as $table) {
        if ($table === 'biblio') {
          throw new Exception('Database ' . $_SESSION['db_name'] . ' not empty. You appear to have already installed SLiMS. To reinstall please clear your old database tables first.', 5003);
          break;
        }
      }
      // run query
      require_once 'install.sql.php';
      $query_type = ['create', 'insert', 'alter', 'update', 'delete', 'truncate', 'drop'];
      $error = $slims->query($sql, $query_type);

      // create trigger
      $error_trigger = $slims->queryTrigger($query_trigger);
      $error = array_merge($error, $error_trigger);

      if ($_POST['sampleData']) {
        require_once 'install_sample_data.sql.php';
        $error_sample = $slims->query($sample_sql, $query_type);
        $error = array_merge($error, $error_sample);
        if(empty($error_sample)) {
          // run indexer
          $sysconf['index']['type'] = 'index';
          require_once __DIR__ . '/../simbio2/simbio.inc.php';
          require_once __DIR__ . '/../simbio2/simbio_DB/simbio_dbop.inc.php';
          require_once __DIR__ . '/../admin/modules/system/biblio_indexer.inc.php';
          $indexer = new biblio_indexer($slims->getDb());
          $indexer->createFullIndex();
        }
      }
      // update account administrator
      if ($action === 're-install') {
        $_POST['username'] = $_SESSION['admin_username'];
        $_POST['confirmPasswd'] = $_SESSION['admin_password'];
      }
      if (!$slims->updateAdmin($_POST['username'], $_POST['confirmPasswd'])) $error[] = $slims->getDb()->error;

      if (count($error) > 0) die(json_encode(['status' => false, 'message' => $error, 'code' => 5005]));
      // success
      die(json_encode(['status' => true, 'message' => 'SLiMS Successful be installed']));
    } catch (Exception $exception) {
      if ($exception->getCode() === 5000 || $exception->getCode() === 5001) {
        $_SESSION['admin_username'] = $_POST['username'];
        $_SESSION['admin_password'] = $_POST['confirmPasswd'];
      }
      die(json_encode(['status' => false, 'message' => [$exception->getMessage()], 'code' => $exception->getCode()]));
    }
    break;

  case 'do-upgrade':
  case 're-upgrade':
    sleep(1);
    try {
      $slims->createConnection($_SESSION['db_host'], $_SESSION['db_port'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);
      // write configuration file
      $slims->createConfigFile($_SESSION);
      // write environment file
      $slims->createEnvFile();

      if ($action === 're-upgrade') {
        $_POST['oldVersion'] = $_SESSION['oldVersion'];
      }
      require_once 'Upgrade.inc.php';
      $upgrade = Install\Upgrade::init($slims)->from($_POST['oldVersion']);

      if (count($upgrade) > 0) {
        die(json_encode(['status' => false, 'message' => $upgrade, 'code' => 5006]));
      }

      die(json_encode(['status' => true]));
    } catch (Exception $exception) {
      if ($exception->getCode() === 5000 || $exception->getCode() === 5001) {
        $_SESSION['oldVersion'] = $_POST['oldVersion'];
      }
      die(json_encode(['status' => false, 'message' => [$exception->getMessage()], 'code' => $exception->getCode()]));
    }
    break;
}