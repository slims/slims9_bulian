<?php
/**
*
* Visitor Counter
* Copyright (C) 2010 Arie Nugraha (dicarve@yahoo.com)
* Modified By Eddy Subratha (eddy.subratha@gmail.com)
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

$allowed_counter_ip = array('127.0.0.1');
$remote_addr = $_SERVER['REMOTE_ADDR'];
$confirmation = 0;

foreach ($allowed_counter_ip as $ip) {
// change wildcard
    $ip = preg_replace('@\*$@i', '.', $ip);
    if ($ip == $remote_addr || $_SERVER['HTTP_HOST'] == 'localhost' || preg_match("@$ip@i", $ip)) {
        $confirmation = 1;
    }
}

if (!$confirmation) {
    header ("location: index.php");
}

// start the output buffering for main content
ob_start();

define('INSTITUTION_EMPTY', 11);

if (isset($_POST['counter'])) {
    if (trim($_POST['memberID']) == '') {
        die();
    }
    $member_name = 'Guest';
    $photo = 'person.png';
    $expire = 0;
// sleep for a while
    sleep(2);
/**
* Insert counter data to database
*/
function setCounter($str_member_ID) {
  global $dbs, $member_name, $photo, $expire;
  // check if ID exists
  $str_member_ID = $dbs->escape_string($str_member_ID);
  $_q = $dbs->query("SELECT member_id,member_name,member_image,inst_name, IF(TO_DAYS('".date('Y-m-d')."')>TO_DAYS(expire_date), 1, 0) AS is_expire FROM member WHERE member_id='$str_member_ID'");
  // if member is already registered
  if ($_q->num_rows > 0) {
      $_d = $_q->fetch_assoc();
      if ($_d['is_expire'] == 1) {
          $expire = 1;
      }
      $member_id = $_d['member_id'];
      $member_name = $_d['member_name'];
      $member_name = preg_replace("/'/", "\'", $member_name);
      $photo = trim($_d['member_image'])?trim($_d['member_image']):'person.png';
      $_institution = $dbs->escape_string(trim($_d['inst_name']))?$dbs->escape_string(trim($_d['inst_name'])):null;
      
      $_checkin_date = date('Y-m-d H:i:s');
      $_i = $dbs->query("INSERT INTO visitor_count (member_id, member_name, institution, checkin_date) VALUES ('$member_id', '$member_name', '$_institution', '$_checkin_date')");
  } else {
  // non member
      $_d = $_q->fetch_assoc();
      $member_name = $dbs->escape_string(trim($_POST['memberID']));
      $_institution = $dbs->escape_string(trim($_POST['institution']));
      $_checkin_date = date('Y-m-d H:i:s');
      if (!$_institution) {
          return INSTITUTION_EMPTY;
      } else {
          $_i = $dbs->query("INSERT INTO visitor_count (member_name, institution, checkin_date) VALUES ('$member_name', '$_institution', '$_checkin_date')");
      }
  }
  return true;
}


$memberID = trim($_POST['memberID']);
$counter = setCounter($memberID);
if ($counter === true) {
  echo __($member_name.', thank you for inserting your data to our visitor log').'<span id="memberImage" src="images/persons/'.urlencode($photo).'"></span>';
  if ($expire) {
    echo '<div class="error">'.__('Your membership already EXPIRED, please renew/extend your membership immediately').'</div>';
  }
} else if ($counter === INSTITUTION_EMPTY) {
  echo __('Sorry, Please fill institution field if you are not library member');
} else {
  echo __('Error inserting counter data to database!');
}
exit();
}

?>
<div id="masking"></div>
    <div class="container">
        <div class="row">
            <div class="span4 offset4">
                <div class="visitor">
                    <h4><?php echo __('Visitor Counter'); ?></h4>
                    <hr/>
                    <div class="info"><?php echo __('Please insert your library member ID otherwise your full name instead'); ?></div>
                    <hr/>
                    <img id="visitorCounterPhoto" src="./images/persons/person.png" class="photo img-circle" />
                    <hr/>
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

                        <div class="control-group">
                            <div class="controls">
                                <input type="submit" id="counter" name="counter" value="<?php echo __('Add'); ?>" class="btn-block btn btn-primary" />
                            </div>
                        </div>

                        <div class="marginTop" ></div>
                    </form>
                    <hr/>
                    <small>Powered By <?php echo SENAYAN_VERSION; ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready( function() {

// give focus to first field
jQuery('#memberID').focus();

var visitorCounterForm = jQuery('#visitorCounterForm');

// AJAX counter error handler
visitorCounterForm.ajaxError( function() {
    alert('Error inserting counter data to database!');
    jQuery(this).enableForm().find('input[type=text]').val('');
    jQuery('#memberID').focus();
});

// AJAX counter complete handler
visitorCounterForm.ajaxComplete( function() {
    jQuery(this).enableForm().find('input[type=text]').val('');
    var memberImage = jQuery('#memberImage');
    if (memberImage) {
// update visitor photo
var imageSRC = memberImage.attr('src'); memberImage.remove();
jQuery('#visitorCounterPhoto')[0].src = imageSRC;
}
jQuery('#memberID').focus();
});

// register event
visitorCounterForm.submit(function(evt) {
    evt.preventDefault();
// check member ID or name
if (jQuery.trim(jQuery('#memberID').val()) == '') {
    jQuery('#counterInfo').html('Please fill your member ID or name');
    return false;
}
var theForm = jQuery(this);
var formAction = theForm.attr('action');
var formData = theForm.serialize();
formData += '&counter=true';
// block the form
theForm.disableForm();
jQuery('#counterInfo').css({'display': 'block'}).html('PLEASE WAIT...');
// create AJAX request for submitting form
jQuery.ajax(
    { url: formAction,
        type: 'POST',
        async: false,
        data: formData,
        cache: false,
        success: function(respond) {
          jQuery('#counterInfo').html(respond);
          // reset counter
          setTimeout(function() { jQuery('#visitorCounterPhoto').attr('src', './images/persons/photo.png');
            jQuery('#counterInfo').html('&nbsp;'); }, 5000);
        }
    });
});

});
</script>

<?php
// main content
$main_content = ob_get_clean();
// page title
$page_title = $sysconf['library_name'].' :: Visitor Counter';
require_once $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/login_template.inc.php';
exit();
