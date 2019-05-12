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

// load settings from database
utility::loadSettings($dbs);

$allowed_counter_ip = array('127.0.0.1');
$remote_addr = $_SERVER['REMOTE_ADDR'];
$confirmation = 0;
$limit_time_visit = $sysconf['time_visitor_limitation'];

foreach ($allowed_counter_ip as $ip) {
// change wildcard
    $ip = preg_replace('@\*$@i', '.', $ip);
    if ($ip == $remote_addr || $_SERVER['HTTP_HOST'] == 'localhost' || preg_match("@$ip@i", $remote_addr)) {
        $confirmation = 1;
    }
}

if (!$confirmation) {
    header ("location: index.php");
}

// start the output buffering for main content
ob_start();

define('INSTITUTION_EMPTY', 11);
define('ALREADY_CHECKIN', 12);

if (isset($_POST['counter'])) {
   if (trim($_POST['memberID']) == '') {
     die();
   }
   $member_name = 'Guest';
   $photo = 'person.png';
   $expire = 0;
  // sleep for a while
  sleep(0);

  /**
  * check if already checkin
  */
  function checkVisit($str_member_ID, $ismember = true)
  {
    global $dbs, $limit_time_visit;
    if ($ismember) {
      $criteria = 'member_id';
    } else {
      $criteria = 'member_name';
    }

    $date = date('Y-m-d');

    $_q = $dbs->query('SELECT checkin_date FROM visitor_count WHERE '.$criteria.'=\''.$str_member_ID.'\' ORDER BY checkin_date DESC LIMIT 1');
    if ($_q->num_rows > 0) {
      $_d = $_q->fetch_row();
      $time = new DateTime($_d[0]);
      $time->add(new DateInterval('PT'.$limit_time_visit.'M'));
      $timelimit = $time->format('Y-m-d H:i:s');
      $now = date('Y-m-d H:i:s');
      if ($now < $timelimit) {
        return true;
      }
    }

    return false;
  }
  
  /**
  * Insert counter data to database
  */
  function setCounter($str_member_ID) {
    global $dbs, $member_name, $photo, $expire, $sysconf;
    // check if ID exists
    $str_member_ID = $dbs->escape_string($str_member_ID);
    $_q = $dbs->query("SELECT member_id,member_name,member_image,inst_name, IF(TO_DAYS('".date('Y-m-d')."')>TO_DAYS(expire_date), 1, 0) AS is_expire FROM member WHERE member_id='$str_member_ID'");
    // if member is already registered
    if ($_q->num_rows > 0) {
        $_d = $_q->fetch_assoc();
        if ($_d['is_expire'] == 1) {
            $expire = 1;
        }
        $member_id      = $_d['member_id'];
        $member_name    = $_d['member_name'];
        $member_name    = preg_replace("/'/", "\'", $member_name);
        $photo          = trim($_d['member_image'])?trim($_d['member_image']):'person.png';
        $_institution   = $dbs->escape_string(trim($_d['inst_name']))?$dbs->escape_string(trim($_d['inst_name'])):null;
        
        $_checkin_date  = date('Y-m-d H:i:s');
        $_checkin_sql   = "INSERT INTO visitor_count (member_id, member_name, institution, checkin_date) VALUES ('$member_id', '$member_name', '$_institution', '$_checkin_date')";
        
        // limitation
        if ($sysconf['enable_visitor_limitation']) {
          $already_checkin = checkVisit($member_id, true);
          if ($already_checkin) {
            return ALREADY_CHECKIN;
          } else {
            $_i = $dbs->query($_checkin_sql);
          }
        } else {
          $_i = $dbs->query($_checkin_sql);
        }
    } else {
    // non member
        $_d = $_q->fetch_assoc();
        $member_name = $dbs->escape_string(trim(strip_tags($_POST['memberID'])));
        $_institution = $dbs->escape_string(trim(strip_tags($_POST['institution'])));
        $photo = 'non_member.png';
        $_checkin_date = date('Y-m-d H:i:s');
        if (!$_institution) {
            return INSTITUTION_EMPTY;
        } else {
          $_checkin_sql = "INSERT INTO visitor_count (member_name, institution, checkin_date) VALUES ('$member_name', '$_institution', '$_checkin_date')";
          // limitation
          if ($sysconf['enable_visitor_limitation']) {
            $already_checkin = checkVisit($member_name, false);
            if ($already_checkin) {
              return ALREADY_CHECKIN;
            } else {
              $_i = $dbs->query($_checkin_sql);
            }
          } else {
            $_i = $dbs->query($_checkin_sql);
          }
        }
    }
    return true;
  }
  
  
  $memberID = trim($_POST['memberID']);
  $counter = setCounter($memberID);
  if ($counter === true) {
    echo __($member_name.', thank you for inserting your data to our visitor log').'<div id="memberImage" data-img="./images/persons/'.urlencode($photo).'"></div>';
    if ($expire) {
      echo '<div class="error visitor-error">'.__('Your membership already EXPIRED, please renew/extend your membership immediately').'</div>';
    }
  } else if ($counter === ALREADY_CHECKIN) {
    echo __('Welcome back').' '.$member_name.'.';
  } else if ($counter === INSTITUTION_EMPTY) {
    echo __('Sorry, Please fill institution field if you are not library member');
  } else {
    echo __('Error inserting counter data to database!');
  }
  exit();
}

// include visitor form template
require SB.$sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/visitor_template.php';

?>
<script type="text/javascript">
$(document).ready( function() {
  // give focus to first field
  $('#memberID').focus();
  var visitorCounterForm = $('#visitorCounterForm');
  var defaultMsg = $('#counterInfo').html();
  // register event
  visitorCounterForm.on('submit', function(e) {
    e.preventDefault();
    // check member ID or name
    if ($.trim($('#memberID').val()) == '') {
      $('#counterInfo').html('Please fill your member ID or name');
      return false;
    }
    var theForm     = $(this);
    var formAction  = theForm.attr('action');
    var formData    = theForm.serialize();
    formData       += '&counter=true';
    // block the form
    theForm.disableForm();
    $('#counterInfo').html('Please Wait ...');
    // create AJAX request for submitting form
    $.ajax({ url: formAction,
          type: 'POST',
          async: false,
          data: formData,
          cache: false,
          success: function(respond) {
            $('#counterInfo').html(respond);
            // reset counter
            setTimeout(function() { 
              $('#visitorCounterPhoto').attr('src', './images/persons/photo.png');
              $('#counterInfo').html(defaultMsg); 
              visitorCounterForm.enableForm().find('input[type=text]').val('');
              $('#memberID').focus();
            }, 5000);
          },
          complete: function() {
            $(this).enableForm().find('input[type=text]').val('');
            var memberImage = $('#memberImage');
            if (memberImage) {
              // update visitor photo
              var imageSRC = memberImage.data("img");
              $('#visitorCounterPhoto').attr('src', imageSRC);
            }
            $('#memberID').focus();            
          },
          error: function(){
            alert('Error inserting counter data to database!');
            $(this).enableForm().find('input[type=text]').val('');
            $('#memberID').focus();
          }
      });
  });

});
</script>

<?php
// main content
$main_content = ob_get_clean();
// page title
$page_title = 'Visitor Counter | ' . $sysconf['library_name'];
require $main_template_path;
exit();
