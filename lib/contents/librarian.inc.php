<?php
/**
 *
 * Member Area/Information
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
 * Patched by Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

// $info = __('Profile of our Librarian');
$opac->page_title = __('Profile of our Librarian') ;

// query librarian data
$librarian_q = $dbs->query('SELECT * FROM user WHERE user_type IN (1,2) ORDER BY user_type DESC LIMIT 20');
if ($librarian_q->num_rows > 0) {
  while ($librarian = $librarian_q->fetch_assoc()) {
    echo '<div class="row-fluid librarian">';
    echo '<div class="span2">';
    if ($librarian['user_image']) {
      echo '<div class="librarian-image"><img src="'.SWB.'images/persons/'.$librarian['user_image'].'" alt="'.$librarian['realname'].'" /></div>';
    } else {
      echo '<div><img src="'.SWB.'images/persons/person.png" alt="'.$librarian['realname'].'" /></div>';
    }
    echo '</div>';
    echo '<div class="span8">';
    echo '<div class="row-fluid"><div class="span3 key">'.__('Name').'</div><div class="span7">'.$librarian['realname'].'</div></div>';
    echo '<div class="row-fluid"><div class="span3 key">'.__('Position').'</div><div class="span7">'.$sysconf['system_user_type'][$librarian['user_type']].'</div></div>';
    echo '<div class="row-fluid"><div class="span3 key">'.__('E-Mail').'</div><div class="span7">'.$librarian['email'].'</div></div>';
    echo '<div class="row-fluid"><div class="span3 key">'.__('Social').'</div><div class="span9">';
    $social = array();
    if ($librarian['social_media']){
      $social = @unserialize($librarian['social_media']);
      echo '<ul class="librarian-social">';
      foreach ($sysconf['social'] as $id => $social_media) {
        if (isset($social[$id])) {
          echo '<li>'.$sysconf['social'][$id].': &nbsp; '.$social[$id].'</li>';  
        }
      }
      echo '</ul>';
    }
    echo '</div></div>';
    echo '</div>';
    echo '</div>';
  }
} else {
  echo '<p>'.__('No librarian data yet').'</p>';
}
