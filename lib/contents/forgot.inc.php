<?php
/**
 *
 * Forgot Password for Administrator
 * Copyright (C) 2020 Eddy Subratha (eddy.subratha@gmail.com)
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

/*
if (defined('LIGHTWEIGHT_MODE')) {
    header('Location: index.php');
}
*/

// required file
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// https connection (if enabled)
if ($sysconf['https_enable']) {
    simbio_security::doCheckHttps($sysconf['https_port']);
}

// start the output buffering for main content
ob_start();

// if there is login action
$message = __('If you need help resetting your password, we can help by sending you a link to reset it.');
if (isset($_POST['resetPass'])) {
    $email = $dbs->escape_string($_POST['currentmail']);
    if (!$email) {
        echo '<script type="text/javascript">alert(\''.__('Please supply valid username and password').'\');</script>';
    } else {
        # <!-- Captcha form processing - start -->
        if ($sysconf['captcha']['forgot']['enable']) {
            if ($sysconf['captcha']['forgot']['type'] == 'recaptcha') {
                require_once LIB.$sysconf['captcha']['forgot']['folder'].'/'.$sysconf['captcha']['forgot']['incfile'];
                $privatekey = $sysconf['captcha']['forgot']['privatekey'];
                $resp = recaptcha_check_answer ($privatekey,$_SERVER["REMOTE_ADDR"],$_POST["g-recaptcha-response"]);

                if (!$resp->is_valid) {
                    // What happens when the CAPTCHA was entered incorrectly
                    $message = __('Captcha incorrect.');
                } else {
                    // Validate current email
                    $_q = $dbs->query("SELECT user_id, realname FROM user WHERE email='{$email}'");
                    if ($_q->num_rows > 0) {
                        /// Name
                        $file_d = $_q->fetch_assoc();
                        $name = $file_d['realname'];
                        /// Generate a token for forgot password
                        $salt = password_hash($email, PASSWORD_DEFAULT);
                        $_sql_update_salt = sprintf("UPDATE user SET forgot = '{$salt}', last_update = CURDATE() WHERE email = '%s'", $email);
                        // write log
                        utility::writeLogs($dbs, 'staff', $name, 'Forgot Password', $name.' has been requested a new password.', 'Password', 'Request');
                        $_update_q = $dbs->query($_sql_update_salt);
                        // error check
                        if ($dbs->error) {
                            $message = __('Failed to query user data from database with error: ').$dbs->error;
                        } else {
                            $path = 'https://slims.web.id/mailer/forgot.php';
                            $fields = array(
                                'url' => sprintf("%s://%s%s", isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http', $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']),
                                'salt' => $salt,
                                'email' => $email,
                                'name' => $name
                            );
                            
                            $header = array(
                                "X-API-KEY: ".$salt
                            );

                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                                CURLOPT_URL => $path,
                                CURLOPT_POST => true,
                                CURLOPT_HTTPHEADER => $header,
                                CURLOPT_POSTFIELDS => $fields,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_USERAGENT => "Slims"
                            ));

                            $response = curl_exec($curl);
                            $err = curl_error($curl);
                            curl_close($curl);

                            if ($response == 'false') {
                                $message = __('Cannot send the email. Please try again.');
                            } else {
                                $message = __('<strong>Congratulations! </strong>An instruction has been sent to your email. Please check your inbox.');
                            }        
                        }
                    } else {
                        $message = __('Current email not found. Please try again.');
                    }

                }
            }
        }
    }
}
?>
<div id="loginForm">
    <noscript>
        <div style="font-weight: bold; color: #FF0000;"><?php echo __('Your browser does not support Javascript or Javascript is disabled. Application won\'t run without Javascript!'); ?><div>
    </noscript>
    <div class="reset-info mb-3"><?php echo $message ?></div>
    <form action="index.php?p=forgot" method="post" novalidation>
        <div class="heading1"><?php echo __('Your email address'); ?></div>
        <div class="login_input"><input type="email" name="currentmail" id="currentmail" class="login_input" required /></div>
        <div class="captchaAdmin">
        <?php
        require_once LIB.$sysconf['captcha']['forgot']['folder'].'/'.$sysconf['captcha']['forgot']['incfile'];
        $publickey = $sysconf['captcha']['forgot']['publickey'];
        echo recaptcha_get_html($publickey);
        ?>
        </div>
        <div class="marginTop">
        <input type="submit" name="resetPass" value="<?php echo __('Reset my password'); ?>" class="loginButton" />
        <a class="forgotButton" href="index.php?p=login"><?php echo __('Cancel') ?></a>
        </div>
    </form>
</div>
<script type="text/javascript">jQuery('#currentmail').focus();</script>

<?php
// main content
$main_content = ob_get_clean();

// page title
$page_title = __('Forgot My Password').' | '.$sysconf['library_name'];

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
