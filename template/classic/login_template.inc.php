<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-12 18:45:37
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-19 14:15:29
 */

/*
=========================
Be sure that this file not accessed directly
=========================
*/
if (!defined('INDEX_AUTH')) {
  die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
  die("can not access this file directly");
} 

/* 
=========================
Define current public template directory
=========================
*/
define('CURRENT_TEMPLATE_DIR', $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/');

/* 
=========================
Load header
- open html tag
- head tag
- open body tag
=========================
*/
include 'part/header.php';

/* 
=========================
Load content
=========================
| You can change the layout of template part
| by change/move/remove structure of load content part
*/

// visitor page
if ($_GET['p'] == 'visitor') {
    echo $main_content;
} else {

// login page
?>

<div class="slims-container">
    <div class="slims-row">
        <div class="slims-4"></div>
        <div class="slims-4">
            <div class="slims-card slims-card--default slims-vertical">
                <div class="slims-card--header">
                    <?php echo __('Librarian LOGIN') ?>
                </div>
                <?php echo $main_content; ?>
            </div>
        </div>
    </div>
</div>

<?php

}
// include 'part/content/footer.php';

/* 
=========================
Load footer
- close body tag
- close html tag
=========================
*/
include 'part/footer.php';