<?php
/**
 *
 * Librarian login page
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com), Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) { 
    die("can not access this file directly");
}

// required file
require LIB.'admin_logon.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// https connection (if enabled)
if ($sysconf['https_enable']) {
    simbio_security::doCheckHttps($sysconf['https_port']);
}

$email = $dbs->escape_string($_GET['email']);
$salt = $dbs->escape_string($_GET['salt']);
$url = $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];

// validate current salt and email
$query = sprintf("SELECT user_id,realname FROM user WHERE email='%s' AND forgot='%s'", $email, $salt);
$_q = $dbs->query($query);
$file_d = $_q->fetch_assoc();
$_uname = $file_d['realname'];


// update password
if (isset($_POST['updatePassword'])) {
  $passwd = trim($_POST['newPasswd']);
  $passwd2 = trim($_POST['newPasswd2']);
  if (empty($passwd)) {
    utility::jsAlert(__('Current password can not be empty!'));
  } else if (($passwd AND $passwd2) AND ($passwd !== $passwd2)) {
    utility::jsAlert(__('Password confirmation does not match. See if your Caps Lock key is on!'));
  } else {
    // Confirm about email and salt again
    if ($_q->num_rows > 0) {
      $_sql_update_password = sprintf("UPDATE user SET passwd = '%s', last_update = CURDATE(), forgot='' WHERE email = '%s' AND forgot= '%s'", password_hash($passwd2, PASSWORD_BCRYPT), $email, $salt);
      $_update_q = $dbs->query($_sql_update_password);
      // error check
      if ($dbs->error) {
        echo __('Failed to query user data from database with error: '.$dbs->error);
      }
      // write log
      utility::writeLogs($dbs, 'staff', $_uname, 'Login', 'Change password SUCCESS for user '.$_uname.' from address '.$_SERVER['REMOTE_ADDR'], 'Password', 'Update');

      // clear cookie
      #setcookie('token', '', time()-3600, SWB);
      #setcookie('token', '', time()-3600, SWB, "", FALSE, TRUE);

      setcookie('token', '', [
        'expires' => time()-3600,
        'path' => SWB,
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);



      #setcookie('uname', '', time()-3600, SWB);
      #setcookie('uname', '', time()-3600, SWB, "", FALSE, TRUE);

      setcookie('uname', '', [
        'expires' => time()-3600,
        'path' => SWB,
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);


      echo '<script type="text/javascript">';
      echo 'alert("'.__("Password has been updated successfully.").'");';
      echo 'location.href = "index.php?p=login";';
      echo '</script>';
    } else {
      echo '<script type="text/javascript">';
      echo 'alert("'.__("Salt key has been expired.").'");';
      echo '</script>';
    }
  }
}

// start the output buffering for main content
ob_start();

// if not valid
?>
<div id="loginForm">
    <noscript>
        <div style="font-weight: bold; color: #FF0000;"><?php echo __('Your browser does not support Javascript or Javascript is disabled. Application won\'t run without Javascript!'); ?><div>
    </noscript>
    <?php if ($_q->num_rows <= 0): ?>
      <?php echo __('Current email not found or salt key has been expired'); ?>
    <?php else: ?>    
    <form action="<?php echo $url ?>" method="post">
        <div class="heading1"><?php echo __('New Password'); ?></div>
        <div class="login_input"><input type="password" name="newPasswd" class="login_input" /></div>
        <div class="heading1"><?php echo __('Confirm New Password'); ?></div>
        <div class="login_input"><input type="password" name="newPasswd2" class="login_input" /></div>
        <div class="marginTop">
        <input type="submit" name="updatePassword" value="<?php echo __('Update'); ?>" class="loginButton" />
        </div>
    </form>
    <?php endif ?>
</div>
<script type="text/javascript">jQuery('#userName').focus();</script>

<?php

// main content
$main_content = ob_get_clean();

// page title
$page_title = __('Create new password').' | '.$sysconf['library_name'];

if ($sysconf['template']['base'] == 'html') {
    // create the template object
    $template = new simbio_template_parser($sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/login_template.html');
    // assign content to markers
    $template->assign('<!--PAGE_TITLE-->', $page_title);
    $template->assign('<!--CSS-->', $sysconf['template']['css']);
    $template->assign('<!--MAIN_CONTENT-->', $main_content);
    // print out the template
    $template->printOut();
} else if ($sysconf['template']['base'] == 'php') {
    require_once $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/login_template.inc.php';
}

exit();

