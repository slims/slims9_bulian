<?php
$inSimbioRequest = $this->withSimbioAJAXRequest(outputWithHeader: false);

defined('SB') or define('SB', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);

if ($inSimbioRequest === false):
?>
<!DOCTYPE Html>
<html>
    <head>
        <title><?= $title??'Oops Error' ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <style><?= file_get_contents(SB . 'css/bootstrap.min.css') ?></style>
        <style>
            * {
                font-family: 'Trebuchet MS', sans-serif;
            }
        </style>
    </head>
    <body style="background-color: #e6e6e6">
<?php endif; ?>
        <section class="container">
            <div class="row">
                <div class="p-5 m-5">
                    <div class="w-full p-5">
                        <div class="col-12">
                            <h1 style="font-size: 30pt">Error</h1>
                            <p style="font-size: 14pt">Please contact system admin or change <strong>system environment to development</strong> at system module for more information about this error.</p>
                            <div>
                                <strong>URL : </strong>
                                <span class="text-muted"><?= $_SERVER['PHP_SELF'] . (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
<?php if ($inSimbioRequest !== false): ?>        
    </body>
</html>
<?php endif; ?>