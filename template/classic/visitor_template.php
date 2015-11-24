<?php
/**
 * Template for visitor counter
 * name of memberID text field must be: memberID
 * name of institution text field must be: institution
 *
 * Copyright (C) 2015 Arie Nugraha (dicarve@gmail.com)
 * Create by Eddy Subratha (eddy.subratha@slims.web.id)
 * 
 * Slims 8 (Akasia)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 */

$main_template_path = $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/login_template.inc.php';

?>

<div class="slims-container slims-visitor slims-vertical">
  <div class="slims-row">
    <div class="slims-2"></div>
    <div class="slims-8">

      <div class="slims-card slims-card--default slims-card--expand">
        
        <div class="slims-card--header beach">
          <h1><?php echo __('Visitor Counter'); ?></h1>

          <div class="slims-visitor--photo">
            <img id="visitorCounterPhoto" src="./images/persons/photo.png"/>
          </div>
        </div>

        <div class="slims-card--body">
          <div  id="counterInfo" class="info"><?php echo __('Please insert your library member ID otherwise your full name instead'); ?></div>
          <form action="index.php?p=visitor" name="visitorCounterForm" id="visitorCounterForm" method="post">
            <div>
              <label for="memberID"><?php echo __('Member ID / Visitor Name'); ?></label>
              <input type="text" name="memberID" id="memberID"  class="" placeholder="Enter your Member ID" />
            </div>
            <div>
              <label for="institution"><?php echo __('Institution'); ?></label>
              <input type="text" name="institution" id="institution" class="" placeholder="Enter your institution" />
            </div>
            <input type="submit" id="counter" name="counter" class="slims-button slims-button--blue" value="<?php echo __('Add'); ?>">
          </form>
        </div>

      </div>

    </div>
  </div>
</div>
