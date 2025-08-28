<?php
use Symfony\Component\Finder\Finder;
$inSimbioRequest = $this->withSimbioAJAXRequest(outputWithHeader: false);

defined('SB') or define('SB', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);

if ($inSimbioRequest === false):
?>
<!DOCTYPE Html>
<html>
    <head>
        <title><?= $title??'' ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <style><?= file_get_contents(SB . 'css/bootstrap.min.css') ?></style>
        <style>
            * {
                font-family: 'Trebuchet MS', sans-serif;
            }
        </style>
    </head>
    <body style="background-color: #ccc">
<?php endif; ?>
        <section class="container-fluid">
            <div class="row bg-white">
                <div class="col-12 p-3">
                    <strong>PHP <?= phpversion() ?> | SLiMS <?= SENAYAN_VERSION_TAG ?></strong>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="rounded d-block bg-danger text-white mx-auto col-11 p-5" style="min-height: 200px">
                    <h4><?= $class??'Exception' ?></h4>
                    <h1><?= $message ?></h1>
                    <span>in <strong><?= $path ?></strong> on line <strong><?= $line ?></strong></span>
                </div>
            </div>
            <div class="row mt-4">
                <div class="rounded bg-white d-block mx-auto col-11 p-5">
                    <h4>Traces</h4>
                    <?php
                    foreach ($traces??[] as $order => $trace) {
                        extract($trace);
                        $type = $function??$class??'Ulknown';
                        $file = $file??'';
                        $line = $line??'?';
                        echo <<<HTML
                        <div class="d-block col-12 my-2">
                            <strong class="d-block mb-2">#{$order} {$type}</strong></td>
                            <div>In <strong>{$file}</strong> on line <strong>{$line}</strong></div>
                        </div>
                        HTML;
                        unset($file);
                        unset($type);
                        unset($line);
                    }
                    ?>
                </div>
            </div>
            <?php if (defined('SLIMS_DEBUG_FULL')): ?>
            <div class="row mt-4">
                <div class="rounded bg-white d-block mx-auto col-11 p-5">
                    <h4>Config</h4>
                    <?php 
                    $finder = new Finder;
                    $scanned_directory = $finder->files()->in(__DIR__ . '/../config/');
                    $scanned_directory->notName(['*.*.php','*_*.php','index.php','env.php'])->name('*.php');
                    $scanned_directory->sortByName();

                    foreach ($scanned_directory as $file) {
                        $content = require $file->getPathname();
                        $content = is_array($content) ? '<pre class="p-3 rounded" style="background-color: #3c3c3c; color:white; width: 100%; overflow: auto">'.htmlentities(json_encode($content, JSON_PRETTY_PRINT)).'</pre>' : $content;
                        echo <<<HTML
                        <div class="d-block col-12">
                            <strong class="d-block mb-2">{$file->getFilename()}</strong></td>
                            <div>{$content}</div>
                        </div>
                        HTML;
                    }
                    ?>
                </div>
            </div>
            <div class="row mt-4">
                <div class="rounded bg-white d-block mx-auto col-11 p-5">
                    <h4>Sysconfig</h4>
                    <table class="table table-striped table-responsive">
                    <?php
                    foreach ($sysconf as $key => $value) {
                        $value = is_array($value) ? '<pre class="text-white p-3" style="background-color: #3c3c3c; max-width: 1000px; overflow: auto">'.htmlentities(json_encode($value, JSON_PRETTY_PRINT)).'</pre>' : $value;
                        echo <<<HTML
                        <tr>
                            <td valign="center"><strong>{$key}</strong></td>
                            <td valign="center">:</td>
                            <td valign="center">{$value}</td>
                        </tr>
                        HTML;
                    }
                    ?>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </section>
<?php if ($inSimbioRequest !== false): ?>        
    </body>
</html>
<?php endif; ?>