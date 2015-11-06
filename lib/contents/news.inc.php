<?php
/**
 * Copyright (C) 2015, Arie Nugraha (dicarve@gmail.com)
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

require_once LIB.'content.inc.php';

$page_title = __('Library News');

$keywords = null;
if (isset($_GET['keywords'])) {
  $keywords = trim($_GET['keywords']);
}

$content = new Content();
$content_list = $content->getContents($dbs, 10, $keywords);

foreach ($content_list as $c) {
    echo '<div class="row">';
    echo '<div class="col-md-12">';
    echo '<h3>'.$c['content_title'].'</h3>';
    echo '<div class="content-date">'.$c['last_update'].'</div>';
    echo '<p>'.$c['content_desc'].'</p>';
    echo '</div>';
    echo '</div>'."\n";
}