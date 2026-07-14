<?php
/**
 * Plugin Name: Dual Label Print - Apu
 * Plugin URI: https://github.com/nalamapu/dual_label_print_apu/
 * Description: Print spine label + barcode label together (per item) from a single accession number search. Each item produces two 38×25 mm stickers: spine label first, then barcode label.
 * Version: 1.0.0
 * Author: Nurul Alam Apu
 * Author URI: https://www.slimsbd.com
 * WhatsApp: +8801674066064
 * Email: slimsbd@gmail.com
 */

$plugin = \SLiMS\Plugins::getInstance();
$plugin->registerMenu('bibliography', 'Dual Label Print - Apu', __DIR__ . '/index.php');
