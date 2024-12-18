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
<div class="s-visitor container">
  <header>
    <h1><?php echo __('Visitor Counter'); ?></h1>
    <div  id="counterInfo" class="info"><?php echo __('Please insert your library member ID otherwise your full name instead'); ?></div>
    <div class="s-visitor-photo">  
      <img id="visitorCounterPhoto" src="./images/persons/photo.png"/>
    </div>
  </header>
  
  <form action="index.php?p=visitor" name="visitorCounterForm" id="visitorCounterForm" method="post">

    <div class="row"> 

      <div class="col-lg-6 col-sm-6 col-xs-12">
        <div class="form-group"> 
          <input type="text" name="memberID" id="memberID"  class="form-control input-lg" />
          <label for="memberID"><?php echo __('Member ID / Visitor Name'); ?></label>
        </div> 
      </div>

      <div class="col-lg-6 col-sm-6 col-xs-12">
        <div class="form-group"> 
          <input type="text" name="institution" id="institution" class="form-control input-lg" />
          <label for="institution"><?php echo __('Institution'); ?></label>
        </div> 
      </div>
    
      <div class="clearfix"></div>

      <div class="col-lg-12 col-sm-12 col-xs-12">
        <input type="submit" id="counter" name="counter" class="form-control input-lg" value="<?php echo __('Add'); ?>">
      </div>
    </div>

  </form>

</div>
<script>
  $('#login-page, .s-login').attr('style','margin:0;')
  $('.s-login-content').removeClass('animated flipInY').addClass('animated fadeInUp')
</script>
