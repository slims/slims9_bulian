<?php
/**
 * Read lib/minigalnano/Thumb.php for more
 * information
 * 
 * Original source by Hendro Wicaksono
 * delivered from Minigal Nano
 * 
 * - 2022 modified by Drajat Hasan (drajathasan20@gmail.com)
 */
use Minigalnano\Thumb;
use SLiMS\Filesystems\Storage;

define('INDEX_AUTH', '1');
include __DIR__ . '/../../sysconfig.inc.php';

try {
    // Fetch filename based on query request
    $filenameinput = isset($_GET['filename']) && !empty($_GET['filename']) ? urldecode($_GET['filename']) : 'notfound.png';
    $filename = trim(substr($filenameinput, strpos($filenameinput, '/', 1)), '/');
    $storage = Storage::{dirname($filenameinput, 2)}();

    // thumb instance need parameter 1st as path to image file
    $thumbnail = new Thumb($storage, $filename);

    /** Thumb option **/
    // Turn on image caching
    $thumbnail->setCacheOption('enable', true);

    // Set cache destination
    $thumbnail->setCacheOption('folder', SB . 'images/cache/');

    // Set cache file path
    $thumbnail->setCacheOption('file', 
        $thumbnail->getCacheOption('folder') . 
        $thumbnail->getCacheOption('prefix') . 
        basename($thumbnail->getFilePath())
    );

    // check image permission and attribution
    $thumbnail->isFileAllow()->orError();
    $thumbnail->isFileExists()->orError();
    $thumbnail->isReadable()->orError();

    // set measurement
    $thumbnail->setWidth(( (isset($_GET['width']) AND trim($_GET['width']) != '') ?  trim($_GET['width']) : 120));
    $thumbnail->setHeight(( (isset($_GET['height']) AND trim($_GET['height']) != '') ?  trim($_GET['height']) : 0));

    // Preparing image and generate it
    $thumbnail->prepare()->generate();
} catch (Exception $e) {
    if (!isDev()) Thumb::setError();
    dd($e);
}
