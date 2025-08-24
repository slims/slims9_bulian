<?php
/**
 * Copyright (C) 2025  Ari Nugraha (dicarve@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) { 
    die("can not access this file directly");
}

/* Common Variables */

/* Location list */
ob_start();
echo '<option value="0">'.__('All Locations').'</option>';
$loc_q = $dbs->query('SELECT location_name FROM mst_location LIMIT 50');
while ($loc_d = $loc_q->fetch_row()) {
    echo '<option value="'.$loc_d[0].'">'.$loc_d[0].'</option>';
}
$location_list = ob_get_clean();

/* Collection type List */
ob_start();
echo '<option value="0">'.__('All Collections').'</option>';
$colltype_q = $dbs->query('SELECT coll_type_name FROM mst_coll_type LIMIT 50');
while ($colltype_d = $colltype_q->fetch_row()) {
    echo '<option value="'.$colltype_d[0].'">'.$colltype_d[0].'</option>';
}
$colltype_list = ob_get_clean();

/* GMD List */
ob_start();
echo '<option value="0">'.__('All GMD/Media').'</option>';
$gmd_q = $dbs->query('SELECT gmd_name FROM mst_gmd LIMIT 50');
while ($gmd_d = $gmd_q->fetch_row()) {
    echo '<option value="'.$gmd_d[0].'">'.$gmd_d[0].'</option>';
}
$gmd_list = ob_get_clean();

/* Language selection list */
ob_start();
// require_once(LANG.'localisation.php');
$select_lang = isset($_COOKIE['select_lang'])?$_COOKIE['select_lang']:$sysconf['default_lang'];
foreach ($available_languages AS $lang_index) {
    $selected = null;
    $lang_code = $lang_index[0];
    $lang_name = $lang_index[1];
    if ($lang_code == $select_lang) {
        $selected = 'selected';
    }
    echo '<option value="'.$lang_code.'" '.$selected.'>'.$lang_name.'</option>';
}
$language_select = ob_get_clean();

/* Sort order selection list */
ob_start();
$sorts = [
    ['most-relevant', __('Most relevant')],
    ['recently-added', __('Recently Added')],
    ['last-update', __('Last Update')],
    ['most-loaned', __('Most Loaned')],
    ['publish-year-newest', __('Publication Year [newest]')],
    ['publish-year-oldest', __('Publication Year [oldest]')],
    ['title-asc', __('Title Ascending')],
    ['title-desc', __('Title Descending')],
];
foreach ($sorts as $sort) {
    $selected = null;
    $filterStr = \utility::filterData('filter', 'get', false, true, true);
    $filterArr = json_decode($filterStr??'', true);
    if ($sort[0] === ($filterArr['sort']??'')) $selected = 'selected';
    echo '<option value="'.$sort[0].'" '.$selected.'>'.$sort[1].'</option>';
}
$sort_select = ob_get_clean();

/* include simbio form element library */
require_once SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
/* Advanced Search Author AJAX field */
ob_start();
// create AJAX drop down
$ajaxDD = new simbio_fe_AJAX_select();
$ajaxDD->element_name = 'author';
$ajaxDD->element_css_class = 'ajaxInputField';
$ajaxDD->additional_params = 'type=author';
$ajaxDD->handler_URL = 'lib/contents/advsearch_AJAX_response.php';
echo $ajaxDD->out();
$advsearch_author = ob_get_clean();

/* Advanced Search Topic/Subject AJAX field */
ob_start();
// create AJAX drop down
$ajaxDD = new simbio_fe_AJAX_select();
$ajaxDD->element_name = 'subject';
$ajaxDD->element_css_class = 'ajaxInputField';
$ajaxDD->additional_params = 'type=topic';
$ajaxDD->handler_URL = 'lib/contents/advsearch_AJAX_response.php';
echo $ajaxDD->out();
$advsearch_topic = ob_get_clean();
