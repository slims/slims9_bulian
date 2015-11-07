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
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require_once SB.$sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/news_template.php';

$page_title = __('Library News');

$keywords = null;
if (isset($_GET['keywords'])) {
  $keywords = trim($_GET['keywords']);
}

$content = new Content();
$total = 0;
$content_list = $content->getContents($dbs, 10, $total, $keywords);
if ($total > 0) {
  echo '<div class="alert alert-info">'.__(sprintf('We have %d news for you!', $total)).'</div>';  
} else {
  echo '<div class="alert alert-warning">'.__('Sorry, we don\'t have any news for you yet.').'</div>';  
}


foreach ($content_list as $c) {
    $summary = Content::createSummary($c['content_desc'], 300);
    echo news_list_tpl($c['content_title'], $c['content_path'], $c['last_update'], $summary);
}

echo simbio_paging::paging($total, $sysconf['news']['num_each_page'], 5);