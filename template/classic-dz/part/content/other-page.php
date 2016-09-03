<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-15 16:35:18
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-21 15:03:50
 */


/* 
=========================
Exeption path slims-card class wraper
=========================
*/
$except = array('news', 'librarian', 'show_detail');

$current_path = $_GET['p'];

if (in_array($current_path, $except)) {
    if ($current_path == 'librarian') {
        echo '<div class="librarian-list">';
        echo $main_content;
        echo '</div>';
    } else {
        echo $main_content;
    }
} else {
    echo '<div class="slims-card slims-card--default">';
    // echo '<div class="slims-card--header">';
    // echo $page_title;
    // echo '</div>';
    echo $main_content;
    echo '</div>';
}