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
use SLiMS\{Visitor,Json};

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

// Create visitor instance
$visitor = new Visitor($sysconf['allowed_counter_ip'], $sysconf['time_visitor_limitation'], $opac);
$visitor->accessCheck();

if ($sysconf['enable_counter_by_ip'] && !$visitor->isAccessAllow()) {
    header ("location: index.php");
    exit;
}

// start the output buffering for main content
ob_start();

if (isset($_POST['counter'])) {

  if (trim($_POST['memberID']) == '') {
    die(Json::stringify(['message' => __('Member ID can\'t be empty'), 'image' => 'person.png'])->withHeader());
  }
   
  // sleep for a while
  sleep(0);

  // Record visitor data
  $visitor->record(trim($_POST['memberID']));

  $image = 'person.png'; // default image
  if ($visitor->getResult() === true) {
    // Map visitor data into variable list
    list($memberId, $memberName, $institution, $image) = $visitor->getData();

    // default message
    $message = $memberName . __(', thank you for inserting your data to our visitor log');

    // Expire message
    if ($visitor->isMemberExpire()) $message = '<div class="error visitor-error">'.__('Your membership already EXPIRED, please renew/extend your membership immediately').'</div>';

    // already checkin message
    if ($visitor->isAlreadyCheckIn()) $message = __('Welcome back').' '.$memberName.'.';

  // For guest access institution data is required!
  } else if ($visitor->isInstitutionEmpty()) {
    $message = __('Sorry, Please fill institution field if you are not library member');
  } else {
    $message = ENVIRONMENT === 'production' ? __('Error inserting counter data to database!') : $visitor->getError();
  }
  
  // send response
  die(Json::stringify(['message' => $message, 'image' => $image, 'status' => $visitor->getError()])->withHeader());
}

// include visitor form template
require SB.$sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/visitor_template.php';

?>
<div style="display: none !important;">
<input type="text" id="text_voice" value=""></input>
<button type="button" id="speak">Speak</button>
</div>

<script type="text/javascript">
$(document).ready( function() {
  var success_text = '<?php echo __('Welcome to our library.') ?>';
  var empty_text = '<?php echo __('Please fill your member ID or name.') ?>';
  var error_text = '<?php echo __('Error while inserting counter data to database.') ?>';
  // give focus to first field
  $('#memberID').focus();
  var visitorCounterForm = $('#visitorCounterForm');
  var defaultMsg = $('#counterInfo').html();
  var defaultImg = 'photo.png';
  // register event
  visitorCounterForm.on('submit', function(e) {
    e.preventDefault();
    // check member ID or name
    if ($.trim($('#memberID').val()) == '') {
      $('#counterInfo').html(error_text);
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
            $('#counterInfo').html(respond.message);
            $('#text_voice').val(success_text + respond.message); 
            // reset counter
            setTimeout(function() { 
              $('#speak').trigger('click');
              $('#visitorCounterPhoto').attr('src', `./images/persons/${respond.image}`);
              $('#counterInfo').html(defaultMsg); 
              visitorCounterForm.enableForm().find('input[type=text]').val('');
              $('#memberID').focus();
              setTimeout(() => { $('#visitorCounterPhoto').attr('src', `./images/persons/${defaultImg}`); }, 8000);
            }, 1000);
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
            // alert('Error inserting counter data to database!');
            $('#text_voice').val(error_text);            
            $(this).enableForm().find('input[type=text]').val('');
            $('#memberID').focus();
          }
      });
  });

});

$("#speak").on("click", function () {
    var message = new SpeechSynthesisUtterance($("#text_voice").val());
    var voices = speechSynthesis.getVoices();
    // console.log(message);
    message['volume'] = 1;
    message['rate'] = 1;
    message['pitch'] = 1;
    message['lang'] = '<?php echo str_replace('_', '-', $sysconf['default_lang']); ?>';
    message['voice'] = null;
    speechSynthesis.cancel();
    speechSynthesis.speak(message);
});

</script>

<?php
// main content
$main_content = ob_get_clean();
// page title
$page_title = __('Visitor Counter').' | ' . $sysconf['library_name'];
require $main_template_path;
exit();
