<?php
// set page title
$opac->page_title = '';

if (!isset($_SESSION['user']['2fa']) || is_null($_SESSION['user']['2fa'] ?? null)) redirect('index.php?p=login');

if (isset($_POST['code-6'])) {
    $code = '';
    for ($i=1; $i <= 6; $i++) $code .= $_POST['code-' . $i];
    $otp = OTPHP\TOTP::createFromSecret($_SESSION['user']['2fa']);
    if ($otp->verify($code)) {

        $username = $_SESSION['user']['username'];
        $realname = $_SESSION['user']['realname'];
        $user_info = $_SESSION['user'];

        // destroy previous session set in OPAC
        simbio_security::destroySessionCookie(null, MEMBER_COOKIES_NAME, SWB, false);
        require SB . 'admin/default/session.inc.php';

        // regenerate session ID to prevent session hijacking
        session_regenerate_id(true);

        setcookie('admin_logged_in', TRUE, [
            'expires' => time() + 14400,
            'path' => SWB,
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        // write log
        utility::writeLogs($dbs, 'staff', $username, 'Login', 'Login success for user ' . $username . ' from address ' . ip());

        # ADV LOG SYSTEM - STIIL EXPERIMENTAL
        $log = new SLiMS\AlLibrarian('1001', array("username" => $username, "realname" => $realname));

        if ($sysconf['login_message']) utility::jsAlert(__('Welcome to Library Automation, ') . $realname);

        require LIB . 'admin_logon.inc.php';
        $logon = new admin_logon($username, '');
        $logon->setUserInfo($user_info);
        $logon->setupSession($dbs);
        redirect('admin/index.php');
    }
}

?>

<form class="row" method="post" action="<?= $_SERVER['PHP_SELF'] ?>?p=2fa">
    <div class="col-md-6 offset-md-3 card card-body text-center">
        <div>
            <div class="w-24 h-24 bg-blue-lighter rounded-full p-4 mb-4 inline-block">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.10102 10H7V8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8V10H16.899M12 14V16M19 15C19 18.866 15.866 22 12 22C8.13401 22 5 18.866 5 15C5 11.134 8.13401 8 12 8C15.866 8 19 11.134 19 15Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>
        <h1 class="mb-3">Two Factor Authenticator</h1>
        <p class="text-muted">Enter 6-digit code from your two factor authenticator app.</p>
        <div class="my-4">
            <div class="flex justify-content-center">
                <input name="code-1" type="text" class="w-12 text-center mx-1 form-control otp-code" size="1" maxlength="1" autofocus>
                <input name="code-2" type="text" class="w-12 text-center mx-1 form-control otp-code" size="1" maxlength="1">
                <input name="code-3" type="text" class="w-12 text-center mx-1 form-control otp-code mr-3" size="1" maxlength="1">
                <input name="code-4" type="text" class="w-12 text-center mx-1 form-control otp-code" size="1" maxlength="1">
                <input name="code-5" type="text" class="w-12 text-center mx-1 form-control otp-code" size="1" maxlength="1">
                <input name="code-6" type="text" class="w-12 text-center mx-1 form-control otp-code" size="1" maxlength="1">
            </div>
        </div>
        <button class="btn btn-primary" id="submit-code" type="submit" disabled>Verify</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        let filled = 0;
        $('body').on('keyup', 'input.otp-code', function() {
            var key = event.keyCode || event.charCode;
            var inputs = $('input.otp-code');
            if (($(this).val().length === this.size) && key != 32) {
                inputs.eq(inputs.index(this) + 1).focus();
                filled+=1
            }
            if (key == 8 || key == 46) {
                var indexNum = inputs.index(this);
                if (indexNum != 0) {
                    inputs.eq(inputs.index(this) - 1).val('').focus();
                }
                filled-=1
            }

            $('#submit-code').prop('disabled', filled < 6)
        });
    });
</script>