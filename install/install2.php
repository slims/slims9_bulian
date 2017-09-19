<?php
/**
 * Slims Installer files
 *
 * Copyright © 2006 - 2012 Advanced Power of PHP
 * Some modifications & patches by Eddy Subratha (eddy.subratha@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

  require "settings.php";

	$completed = false;
	$error_mg  = array();
	$indexdbupgrade_max = 18;

	function apphp_db_install_core($link, $database, $sql_file)
	{
		$db_error = false;

		if (!@apphp_db_select_db($link, $database)) {
			if (@apphp_db_query('create database ' . $database)) {
				apphp_db_select_db($link, $database);
			} else {
				$db_error = mysqli_error($link);
				return false;
			}
		}

		if (!$db_error) {
			if (file_exists($sql_file)) {
        include_once $sql_file;
        if(array_key_exists('create', $sql))
        {
          foreach ($sql['create'] as $value) {
            @mysqli_query($link, $value);
          }
        }
        if(array_key_exists('insert', $sql))
        {
          foreach ($sql['insert'] as $value) {
            @mysqli_query($link, $value);
          }
        }
        if(array_key_exists('alter', $sql))
        {
          foreach ($sql['alter'] as $value) {
            @mysqli_query($link, $value);
          }
        }
        if(array_key_exists('update', $sql))
        {
          foreach ($sql['update'] as $value) {
            @mysqli_query($link, $value);
          }
        }
        if(array_key_exists('delete', $sql))
        {
          foreach ($sql['delete'] as $value) {
            @mysqli_query($link, $value);
          }
        }
        if(array_key_exists('truncate', $sql))
        {
          foreach ($sql['truncate'] as $value) {
            @mysqli_query($link, $value);
          }
        }
        if(array_key_exists('drop', $sql))
        {
          foreach ($sql['drop'] as $value) {
            @mysqli_query($link, $value);
          }
        }
        return true;
			} else {
			  $db_error = 'SQL file does not exist: ' . $sql_file;
			  return false;
			}
    }
	}

	function apphp_db_select_db($link, $database) {
		return mysqli_select_db($link, $database);
	}

	function apphp_db_query($query) {
		global $link;
		$res=mysqli_query($link, $query);
		return $res;
	}

	function apphp_db_install($link, $database, $sql_file)
	{
		$db_error = false;

		if (!@apphp_db_select_db($link, $database)) {
			if (@apphp_db_query('create database ' . $database)) {
				apphp_db_select_db($link, $database);
			} else {
				$db_error = mysqli_error($link);
				return false;
			}
		}

		if (!$db_error) {
			if (file_exists($sql_file)) {
				$fd = fopen($sql_file, 'rb');
				$restore_query = fread($fd, filesize($sql_file));
				fclose($fd);
			} else {
				$db_error = 'SQL file does not exist: ' . $sql_file;
			return false;
			}

			$sql_array = array();
			$sql_length = strlen($restore_query);
			$pos = strpos($restore_query, ';');
			for ($i=$pos; $i<$sql_length; $i++) {
				if ($restore_query[0] == '#') {
					$restore_query = ltrim(substr($restore_query, strpos($restore_query, "\n")));
					$sql_length = strlen($restore_query);
					$i = strpos($restore_query, ';')-1;
					continue;
				}
				if (@$restore_query[($i+1)] == "\n") {
					for ($j=($i+2); $j<$sql_length; $j++) {
						if (trim($restore_query[$j]) != '') {
							$next = substr($restore_query, $j, 6);
							if ($next[0] == '#') {
								// find out where the break position is so we can remove this line (#comment line)
								for ($k=$j; $k<$sql_length; $k++) {
									if ($restore_query[$k] == "\n") break;
								}
								$query = substr($restore_query, 0, $i+1);
								$restore_query = substr($restore_query, $k);
								// join the query before the comment appeared, with the rest of the dump
								$restore_query = $query . $restore_query;
								$sql_length = strlen($restore_query);
								$i = strpos($restore_query, ';')-1;
								continue 2;
							}
							break;
						}
					}
					if ($next == '') { // get the last insert query
						$next = 'insert';
					}
					if ( (preg_match('/create/i', $next)) || (preg_match('/insert/i', $next)) || (preg_match('/drop/i', $next)) ) {
						$next = '';
						$sql_array[] = substr($restore_query, 0, $i);
						$restore_query = ltrim(substr($restore_query, $i+1));
						$sql_length = strlen($restore_query);
						$i = strpos($restore_query, ';')-1;
					}
				}
			}

			for ($i=0; $i < sizeof($sql_array); $i++) {
				apphp_db_query($sql_array[$i]);
			}
			return true;
		} else {
			return false;
		}
	}

	if ($_POST['submit'] == "step2") {
		$database_host		= isset($_POST['database_host'])?$_POST['database_host']:"";
		$database_name		= isset($_POST['database_name'])?$_POST['database_name']:"";
		$database_username	= isset($_POST['database_username'])?$_POST['database_username']:"";
		$database_password	= isset($_POST['database_password'])?$_POST['database_password']:"";
		$database_sample	= isset($_POST['install_sample'])?$_POST['install_sample']:"";
		$username		= isset($_POST['username'])?$_POST['username']:"";
		$password		= isset($_POST['password'])?$_POST['password']:"";
		$retype_password	= isset($_POST['retype_password'])?$_POST['retype_password']:"";

		if (empty($database_host)){
			$error_mg[] = "<li>Database host can not be empty </li>";
		}

		if (empty($database_name)){
			$error_mg[] = "<li>Database name can not be empty</li>";
		}

		if (empty($database_username)){
			$error_mg[] = "<li>Database username can not be empty</li>";
		}

		if(trim($username) <> 'admin')
		{
			if (!empty($password)){
				if (empty($retype_password)){
					$error_mg[] = "<li>Please retype your password</li>";
				}

				if ($password <> $retype_password){
					$error_mg[] = "<li>Your password did not match. Please try again</li>";
				}
			} else {
				$retype_password = 'admin';
			}
		} else {
			if (!empty($password)){
				if (empty($retype_password)){
					$error_mg[] = "<li>Please retype your password</li>";
				}

				if ($password <> $retype_password){
					$error_mg[] = "<li>Your password did not match. Please try again</li>";
				}
			} else {
				$retype_password = 'admin';
			}
		}

        // check for write access
        $write_access = substr(sprintf('%o', fileperms($config_file_directory)), -4);
        if($write_access != '0777') {
            $error_mg[] = "<li>Cannot write ".$config_file_path." file. Please check ".$config_file_directory." folder permission.</li>";
        }


		$sql_update = " UPDATE user set
			username = '".$username."',
			passwd = '".password_hash($retype_password, PASSWORD_BCRYPT)."',
			realname = '".ucfirst($username)."',
			last_login = NULL,
			last_login_ip = '127.0.0.1',
			groups = 'a:1:{i:0;s:1:\"1\";}',
			input_date = DATE(NOW()),
			last_update = DATE(NOW())
			WHERE user_id = 1";

		if(empty($error_mg)){
			$config_file = file_get_contents($config_file_default);
			$config_file = str_replace("_DB_HOST_", $database_host, $config_file);
			$config_file = str_replace("_DB_NAME_", $database_name, $config_file);
			$config_file = str_replace("_DB_USER_", $database_username, $config_file);
			$config_file = str_replace("_DB_PASSWORD_", $database_password, $config_file);

			if(!copy('../config/sysconfig.local.inc-sample.php',$config_file_path))
			{
        $error_mg[] = "<li>Could not create file ".$config_file_name."! Please check if the sysconfig.local.inc-sample.php file is exists</li>";
			}
      else {
        @chmod($config_file_path,0777);
        $f = @fopen($config_file_path, "w+");
        if (@fwrite($f, $config_file) > 0) {
          $link = @mysqli_connect($database_host, $database_username, $database_password);
          if($link){
            if (@mysqli_select_db($link, $database_name)) {
              // upgrade db
              if (isset($_POST['indexdbupgrade'])) {
                $indexdbupgrade_start = $_POST['indexdbupgrade'];
                $completed_upgrade = 0;
                for ($i=$indexdbupgrade_start; $i <= $indexdbupgrade_max; $i++) {
                  $v = $i + 1;
                  $file_sql_path = ($i == $indexdbupgrade_max) ? './../upgrade/'.$sql_upgrade[$v] : './../upgrade/old_sql/'.$sql_upgrade[$v];
                  $sql_php_path = 'sql_php_upgrade/'.$sql_upgrade[$v].'.php';
                  if ($v >= 13 || $v == 11 || $v == 9) {
                    if (false == ($db_error = apphp_db_install_core($link, $database_name, $sql_php_path))) {
                    $error_mg[] = "<li>Could not read file ".$sql_php_path."! Please check if the file exists</li>";
                  } else {
                    $completed_upgrade++;
                  }
                  } else {
                    if(false == ($db_error = apphp_db_install($link, $database_name, $file_sql_path))){
                    $error_mg[] = "<li>Could not read file ".$file_sql_path."! Please check if the file exists</li>";
                  }else{
                    $completed_upgrade++;
                  }
                  }
                }
                if ($completed_upgrade != ($indexdbupgrade_max - $indexdbupgrade_start)) {
                  $completed = true;
                } else {
                  $error_mg[] = "<li>".$completed_upgrade." Database imported.</li>";
                  @unlink($config_file_path);
                }
              } else {
                // fresh install db
                if(false == ($db_error = apphp_db_install_core($link, $database_name, $sql_dump))){
                  $error_mg[] = "<li>Could not read file ".$sql_dump."! Please check if the file exists</li>";
                  @unlink($config_file_path);
                }
                else{
                  // install sampel data
                  if($_POST['install_sample'] == 'yes'){
                    if(false == ($db_error = apphp_db_install($link, $database_name, $sql_sample))){
                      $error_mg[] = "<li>Could not read file ".$sql_sample."! Please check if the file exists</li>";
                    }else{
                      $completed = true;
                    }
                  } else {
                    $completed = true;
                  }

                  if(!empty($retype_password))
                  {
                    apphp_db_query($sql_update);
                  }

                }
              }

            }
            else {
              // create database
              $create = @mysqli_query($link, 'CREATE DATABASE '.$database_name.' CHARACTER SET utf8 COLLATE utf8_unicode_ci');
              if ($create) {
                // fresh install db
                if(false == ($db_error = apphp_db_install_core($link, $database_name, $sql_dump))){
                  $error_mg[] = "<li>Could not read file ".$sql_dump."! Please check if the file exists</li>";
                  @unlink($config_file_path);
                }
                else{
                  // install sampel data
                  if($_POST['install_sample'] == 'yes'){
                    if(false == ($db_error = apphp_db_install($link, $database_name, $sql_sample))){
                      $error_mg[] = "<li>Could not read file ".$sql_sample."! Please check if the file exists</li>";
                    }else{
                      $completed = true;
                    }
                  } else {
                    $completed = true;
                  }

                  if(!empty($retype_password))
                  {
                    apphp_db_query($sql_update);
                  }

                }
              }
              else {
                $error_mg[] = "<li>Database connecting error! Check your database exists or make sure you have privileges to create database.</li>";
                @unlink($config_file_path);
              }
            }
          }
          else {
            $error_mg[] = "<li>Database connecting error! Check your connection parameters</li>";
            @unlink($config_file_path);
          }
        }
        else {
          $error_mg[] = "<li>Can not open configuration file ".$config_file_directory.$config_file_name."</li>";
        }
        @fclose($f);
        @chmod($config_file_path,0755);
			}
		}
	}

?>

<!DOCTYPE HTML>
<html>
<head>
	<title>Step 3 | Slims Installer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="styles.css">
	<link rel="shortcut icon" href="img/webicon.ico" type="image/x-icon"/>
</head>
<body>
    <div class="wrapper">
        <div class="content">
	    <?php if(!$completed) : ?>
    	    <div class="title">
    			<h2>Step 3 - Installation Not Completed</h2>
    		</div>
		    <p class="error">Please correct your information according to this message</p>
	        <?php
                foreach($error_mg as $msg) {
		                  echo "<ul class=\"list\">".$msg."</ul>";
	            }
	        ?>
    	    <hr>
    	    <div class="toright">
        		<input type="button" class="button" value="Back" name="submit" onclick="javascript: history.go(-1);">
        		<input type="button" class="button" value="Retry" name="submit" onclick="javascript: location.reload();">
    	    </div>
	    </div>
        <?php else : ?>
    	    <div class="title">
                <h2>Step 3 - Installation Completed</h2>
    	    </div>
            <p class="success">Hooray, the installation was successful</p>
	        <p>The <?php echo $config_file_name;?> file was sucessfully created.</p>
	        <p>For security reasons, please remove <code style="font-weight: bold;">install/</code> folder from your server.</p>
		    <hr>
    		<div class="toright">
    		    <?php if($application_start_file != ""){ ?><a href="<?php echo $application_start_file;?>" class="button">OK, start the SLiMS</a><?php } ?>
    		</div>
	    <?php endif ?>
        <br>
        <?php include_once("footer.php"); ?>
    </div>

</body>
</html>
