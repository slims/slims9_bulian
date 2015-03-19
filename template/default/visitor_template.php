<?php
/**
 *
 * Template for visitor counter
 * name of memberID text field must be: memberID
 * name of institution text field must be: institution
 *
 * Copyright (C) 2015 Arie Nugraha (dicarve@gmail.com)
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
 * 
 */
$main_template_path = $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/login_template.inc.php';
?>
<div id="masking"></div>
    <div class="row">
      <div class="span12">
        <div class="visitor">
          <h4><?php echo __('Visitor Counter'); ?></h4>
          <div class="info"><?php echo __('Please insert your library member ID otherwise your full name instead'); ?></div>
          <img id="visitorCounterPhoto" src="./images/persons/person.png" class="photo img-circle" />
          <div id="counterInfo">&nbsp;</div>
          <form action="index.php?p=visitor" name="visitorCounterForm" id="visitorCounterForm" method="post" class="form-inline">
              <div class="control-group">
                  <label class="control-label"><?php echo __('Member ID'); ?> / <?php echo __('Visitor Name'); ?></label>
                  <div class="controls">
                      <input type="text" name="memberID" id="memberID"  class="input-block-level" />
                  </div>
              </div>
          
              <div class="control-group">
                  <label class="control-label"><?php echo __('Institution'); ?> / <?php echo __('Visitor Name'); ?></label>
                  <div class="controls">
                      <input type="text" name="institution" id="institution"  class="input-block-level" />
                  </div>
              </div>
              <hr size="1"/>
              <div class="control-group">
                  <div class="controls">
                      <input type="submit" id="counter" name="counter" value="<?php echo __('Add'); ?>" class="btn-block btn btn-primary" />
                  </div>
              </div>
          
              <div class="marginTop" ></div>
          </form>
          <small>Powered By <?php echo SENAYAN_VERSION; ?></small>
        </div>
      </div>
    </div>
