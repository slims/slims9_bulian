<?php 
/**
*
* Chat Engine
* Copyright (C) 2015 Arie Nugraha (dicarve@yahoo.com), Eddy Subratha (eddy.subratha@slims.web.id)
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

// Is Chat activated
if($sysconf['chat_system']['enabled'] && $sysconf['chat_system']['opac']) : 

  //Register user name before chat
  if(isset($_SESSION['m_name'])) {
    $_SESSION['userchat'] = $_SESSION['m_name'];     
  } else {
    if(isset($_POST['userchat'])) {
      $_SESSION['userchat'] = $_POST['userchat']; 
   }
 }    

  // Remove any user session
  if(isset($_GET['end'])) {
    session_unset($_SESSION['userchat']);
    session_destroy();
  }

  // write to log
  if(isset($_GET['p']) && $_GET['p'] == 'chat') {
    fwrite(fopen(FLS.'/chat/'.md5(date('Ydm').'randomizeWord123').'.txt', 'a'), $_POST['msg']."\n");    
  }

?>
<?php if(isset($_SESSION['userchat'])): ?>
<aside class="s-chat s-maximize">
<?php else: ?>
<aside class="s-chat">
<?php endif ?>
  <a id="show-pchat" class="s-pchat-toggle animated bounceInRight delay8" role="navigation" >
  <?php if(isset($_SESSION['userchat'])): ?>
  <i class="fa fa-times"></i>
  <?php else: ?>
  <i class="fa fa-comment-o"></i>
  <?php endif ?>  
  </a>
  <div class="s-chat-header">
  <?php if(isset($_SESSION['userchat'])) { 
    $chat_title = __('Chat As ').$_SESSION['userchat'];
  } else {
    $chat_title = __('Chat With Librarian');
  }
  echo $chat_title; ?>
  </div>
  <?php if(isset($_SESSION['userchat'])): ?>
  <div class="s-chat-content">
    <div id="log"></div>
    <label for="message">Message</label>
    <input type="text" id="message" name="message" />
    <?php if(isset($_SESSION['m_name'])) : ?>
      <button type="button" onclick="javascript:window.location='index.php?p=member&amp;logout=1'" class="btn btn-block"><?php echo __('Logout') ?></button>  
    <?php else: ?>
      <button type="button" onclick="javascript:window.location='index.php?p=chat&amp;end=1'" class="btn btn-block"><?php echo __('End Chat') ?></button>  
    <?php endif ?>
  </div>
  <footer>
    <p>
    <?php echo __('Please type and hit Enter button to send your messages'); ?><br>
    [M] <?php echo __('Members') ?> - [L] <?php echo __('Librarian') ?>
    </p>
  </footer>
  <?php else: ?>
  <div class="s-chat-content text-center">
    <form action="" method="post">
      <p><?php echo __('Please type your name before starting in conversations.')?></p>
      <hr>
      <label for="message"><?php echo __('Your Name:') ?></label>
      <input type="text" id="message" name="userchat" />
      <button type="submit" class="btn btn-block">Start Conversation</button>  
    </form>
  </div>
  <footer class="text-center"><?php echo __('You may also hit Enter button to starting in conversation. '); ?></footer>
  <?php endif; ?>
</aside>

<script>
  $.get('chatserver.php', {}, function(){});
  var Server;
  function log( text ) {
    $log = $('#log');
    //Add text to log
    $log.append(($log.html()?'<br>':'') + text);
    //Autoscroll
    //$log[0].scrollTop = $log[0].scrollHeight - $log[0].clientHeight;
  }

  function send( text ) {
    Server.send( 'message', text );
  }

  $(document).ready(function() {
    log('Connecting...');
    Server = new FancyWebSocket('ws://<?php echo $sysconf['chat_system']['server'] ?>:<?php echo $sysconf['chat_system']['server_port'] ?>?u=<?php echo @$_SESSION["userchat"] ?>');
    $('#message').keypress(function(e) {
      if ( e.keyCode == 13 && this.value ) {
        log( 'You: ' + this.value );
        send( '[M] <?php echo @$_SESSION["userchat"] ?>|' + this.value );
        $.ajax({
          type: 'POST',
          url: 'index.php?p=chat',
          data: {msg: '<?php echo date("Y.m.d H:i:s") ?> [M] <?php echo @$_SESSION["userchat"] ?> - ' + this.value}
        });
        $(this).val('');
      }
    });

    //Let the user know we're connected
    Server.bind('open', function() {
      log( "Connected." );
    });

    //OH NOES! Disconnection occurred.
    Server.bind('close', function( data ) {
      log( "Disconnected." );
    });

    //Log any messages sent from server
    Server.bind('message', function( payload ) {
      log( payload );
      $('#log').scrollTop($('#log')[0].scrollHeight);
    });

    Server.connect();
  });

</script>
<?php endif; ?>
