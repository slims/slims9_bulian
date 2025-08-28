  <?php 
  // Chat
  // =============================================
  if($sysconf['chat_system']['enabled'] && $sysconf['chat_system']['librarian']) : ?>
  <aside class="s-chat s-maximize">
    <a href="#" id="hide-pchat" class="s-chat-header"><?php echo __('Chat With Members'); ?></a>
    <div class="s-chat-content">
      <div id="log"></div>
      <label for="message">Message</label>
      <input type="text" id="message" name="message" />
    </div>
    <footer>
      <p>
      <?php echo __('Please type and hit Enter button to send your messages'); ?><br>
      [M] <?php echo __('Members') ?> - [L] <?php echo __('Librarian') ?>
      </p>
    </footer>
  </aside>

  <script>
    var Server;
    function log( text ) {
      $log = $('#log');
      //Add text to log
      $log.append(($log.html()?'<br>':'') + text);
      //Autoscroll
      $log[0].scrollTop = $log[0].scrollHeight - $log[0].clientHeight;
    }

    function send( text ) {
      Server.send( 'message', text );
    }

    $(document).ready(function() { 
     log('Connecting...');
      Server = new FancyWebSocket('ws://<?php echo $sysconf['chat_system']['server'] ?>:<?php echo $sysconf['chat_system']['server_port'] ?>?u=<?php echo $_SESSION["realname"] ?>');
      $('#message').keypress(function(e) {
        if ( e.keyCode == 13 && this.value ) {
          log( 'You: ' + this.value );
          send( '[L] <?php echo $_SESSION["realname"]?>|'+this.value);
          $.ajax({
            type: 'POST',
            url: '../index.php?p=chat',
            data: {msg: '<?php echo date("Y.m.d H:i:s") ?> [L] <?php echo $_SESSION["realname"] ?> - ' + this.value}
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
      });

      Server.connect();
    });

    // Show or hide chat
    // ============================================
    $('.s-chat').toggleClass('s-minimize s-maximize');
    $('#hide-pchat').on('click', function(){
        $('.s-chat').toggleClass('s-minimize s-maximize ');
    });

  </script>
  <?php endif; ?>