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

define('MAX_THUMB_WIDTH', 600);
define('MAX_THUMB_HEIGHT', 600);

try {
    // Fetch filename based on query request
    $filenameInputRaw = isset($_GET['filename']) && !empty($_GET['filename']) ? urldecode($_GET['filename']) : 'notfound.png';
    if (strpos($filenameInputRaw, '..') !== false || strpos($filenameInputRaw, '%2e%2e') !== false) {
        throw new Exception("Path Traversal attempt detected and blocked.");
    }

    $filenameinput = pathinfo($filenameInputRaw);
    $storageName = explode('/', $filenameinput['dirname'])[0] ?? 'uknown';
    $filename = str_replace($storageName, '', $filenameinput['dirname']) . '/' . $filenameinput['basename'];
    $storage = Storage::{$storageName}();
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
    $inputWidth = (isset($_GET['width']) && is_numeric($_GET['width'])) ? (int)trim($_GET['width']) : 120;
    $inputHeight = (isset($_GET['height']) && is_numeric($_GET['height'])) ? (int)trim($_GET['height']) : 0;
    $thumbnail->setWidth(max(0, min($inputWidth, MAX_THUMB_WIDTH)));
    $thumbnail->setHeight(max(0, min($inputHeight, MAX_THUMB_HEIGHT)));

    // Preparing image and generate it
    $thumbnail->prepare()->generate();

} catch (Exception $e) {

    if (!isDev()) Thumb::setError();
    dd($e);
}