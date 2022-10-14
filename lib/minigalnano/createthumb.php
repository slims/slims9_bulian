<?php
/**
 * Read lib/minigalnano/Thumb.php for more
 * information
 * 
 * Original source by Hendro Wicaksono
 * delivered from Minigal Nano
 */
use Minigalnano\Thumb;

define('INDEX_AUTH', '1');
include __DIR__ . '/../../sysconfig.inc.php';

$filename = isset($_GET['filename']) && !empty($_GET['filename']) ? urldecode($_GET['filename']) : 'notfound.png';

$thumbnail = new Thumb('../../' . $filename);
$thumbnail->setCacheOption('enable', true);
$thumbnail->setCacheOption('folder', SB . 'images/cache/');
$thumbnail->setCacheOption('file', 
    $thumbnail->getCacheOption('folder') . 
    $thumbnail->getCacheOption('prefix') . 
    basename($thumbnail->getFilePath())
);

// check image attribution
$thumbnail->isFileAllow()->orError();
$thumbnail->isFileExists()->orError();
$thumbnail->isReadable()->orError();

// set measurement
$thumbnail->setWidth(( (isset($_GET['width']) AND trim($_GET['width']) != '') ?  trim($_GET['width']) : 120));
$thumbnail->setHeight(( (isset($_GET['height']) AND trim($_GET['height']) != '') ?  trim($_GET['height']) : 0));

// Preparing image
$thumbnail->prepare()->generate();