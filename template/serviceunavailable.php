<?php
$serverContent = isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json';
$headerContent = isset(getallheaders()['Content-Type']) && getallheaders()['Content-Type'] == 'application/json';
if ($serverContent || $headerContent) exit(json_encode(['status' => false, 'message' => 'The Application Environment is not set correctly.']));
$baseUrl = strip_tags(str_replace(['/index.php','admin'],'', $_SERVER['PHP_SELF']));
?>
<!DOCTYPE Html>
<html>
    <head>
        <title>Service Unvailable</title>
        <link href="<?= $baseUrl ?>/css/bootstrap.min.css" rel="stylesheet"/>
    </head>
    <body>
        <div class="alert alert-danger text-left d-flex align-items-center">
            <div>
                <h4 class="alert-heading">The Application Environment is not set correctly.</h4>
                <p>
                    <?php $isIp = function_exists('filter_var') ? filter_var($_SERVER['SERVER_NAME'], FILTER_VALIDATE_IP) : preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $_SERVER['SERVER_NAME']) ?>
                    <?php if ($_SERVER['SERVER_NAME'] === 'localhost' || $isIp): ?>
                        Please run command via your favorite console (in SLiMS Root directory) : <br/>
                        <strong><code>php index.php env</code></strong>
                    <?php else: ?>
                        Call system administrator to fix it.
                    <?php endif;?>
                </p>
            </div>
        </div>
    </body>
</html>