/**
 * 
 * Webcam feature
 * 
 * Require : jQuery library
 * 
 * by Indra Sutriadi Pipii 2013
 * 
 */

var scan_available = false;
var socket = null;
var part = 0;
var dataUrl2;
var imgArea;
var scan_host;
var scan_port;
var scan_res_x;
var scan_res_y;
var scan_capture_w;
var scan_capture_h;
var scan_max_w;
var scan_max_h;

function preview(img, _sel) {
  if (!_sel.width || !_sel.height)
    return;

  scanvas.width = _sel.width;
  scanvas.height = _sel.height;
  scontext.drawImage(sdata, _sel.x1, _sel.y1, _sel.width, _sel.height, 0, 0, _sel.width, _sel.height);
  scan_type();
}

function checkws() {
  if (window.WebSocket) {
    console.log('WebSocket is supported by your browser.');
    scan_available = true;
  }
  else {
    console.log('Websocket is not supported by your browser.');
    scan_available = false;
    $('#scan_dialog').empty();
    $('#scan_dialog').text('This feature not supported by your browser.');
  }
}

function openws(msg, opts) {
  if (scan_available) {
    //~ var serviceUrl = 'ws://localhost:8100/';
    //~ var serviceUrl = $('#url').val();
    var serviceUrl = 'ws://' + $('#scan_host').val() + ':' + $('#scan_port').val();
    socket = new WebSocket(serviceUrl);
    socket.binaryType = "blob";

    socket.onopen = function () {
      console.log('Connection established!');
      switch (msg) {
        case 'init':
          console.log('Get available machines...');
          break;
        case 'scan':
          console.log('Scan with machine: '+ $('#scan_machine').text());
          break;
        case 'recall':
          console.log('Recall scanned image...');
          break;
        default:
          console.log('Unknown command!');
      }
      sendmsg(msg + '#' + opts);
      //~ closews();
    };

    socket.onclose = function () {
      console.log('Connection closed!');
    };

    socket.onerror = function (error) {
      console.log('Error occured: ' + error);
    };

    socket.onmessage = function (e) {
      if (typeof e.data === "string") {
        var rec = e.data;
        switch (rec) {
          case "880":
          case "881":
          case "882":
          case "883":
          case "884":
          case "885":
          case "886":
          case "887":
          case "888":
          case "889":
            console.log('Scanner is being used by another process or client.');
            return;
          default:
            var n = rec.split('#');
        }
      }
      else if (e.data instanceof ArrayBuffer) {
        console.log('ArrayBuffer received: ' + e.data);
      }
      else if (e.data instanceof Blob) {
        console.log('Blob received: ' + e.data.bytes + ' bytes');
      }
      switch (msg) {
        case 'init':
          $('#scan_machine').empty();
          if (n.length > 0) {
            for (var i = 0; i < n.length; i++) {
              $('#scan_machine').append('<option value=' + i + '>' + n[i] + '</option>');
            }
            console.log('Init finished!');
          }
          else {
            console.log('Machine not found!');
          }
          break;
        case 'recall':
          if (!rec) {
            console.log('Recall failed!');
          }
          else {
            console.log('Recall finished!');
            sdata.src = rec;
          }
        case 'scan':
          if (!rec) {
            console.log('Scan failed!');
          }
          else {
            console.log('Scan finished!');
            sdata.src = rec;
          }
          break;
        default:
          console.log('Unknown result!');
      }
      closews();
    };
  }
}

function sendmsg(msg) {
  if (socket != null) {
    socket.send(msg);
  }
};

function closews() {
  socket.close();
}

function less_select() {
  imgArea = $('img#my_imgdata').imgAreaSelect({ instance: true });
  imgArea.cancelSelection();
}

function scan_reset() {
  less_select();
  sdata.src = "";
  scanvas.width = 0;
  scanvas.height = 0;
  dataUrl2 = '';
  $('textarea#base64picstring').empty();
}

function scan_init() {
  scan_reset();
  openws('init');
}

function scan() {
  scan_reset();
  var options = new Array(
    $('#scan_machine').val(),
    $('#scan_res_x').val() + ',' + $('#scan_res_y').val(),
    $('#scan_capture_w').val() + ',' + $('#scan_capture_h').val(),
    $('#scan_max_w').val() + ',' + $('#scan_max_h').val(),
    $('#scan_type').val()
  );
  openws('scan', options.join('#'));
}

function scan_recall() {
  scan_reset();
  index = $('#scan_history').val();
  openws('recall', (Math.floor(index) == index && $.isNumeric(index)) ? index : 1);
}

function scan_type() {
  if (scanvas.width > 0 && scanvas.height > 0) {
    switch ($('#scan_type').val()) {
      case "png":
        dataUrl2 = scanvas.toDataURL();
        dataUrl2 = dataUrl2.split('data:image/png;base64,')[1];
        dataUrl2 = dataUrl2 + '#image/type#' + 'png';
        break;
      case "jpg":
        dataUrl2 = scanvas.toDataURL("image/jpeg");
        dataUrl2 = dataUrl2.split('data:image/jpeg;base64,')[1];
        dataUrl2 = dataUrl2 + '#image/type#' + 'jpg';
        break;
    }
    $('textarea#base64picstring').text(dataUrl2);
  }
}

function scan_rotate(type) {
  var srotate = scanvas.getContext('2d');
  var cw = scanvas.width;
  var ch = scanvas.height;
  
  var tmpcanvas = document.createElement("canvas");
  var tmpctx = tmpcanvas.getContext("2d");
  if (cw > ch) {
    ch = cw;
  }
  else {
    cw = ch;
  }
  tmpcanvas.width = cw;
  tmpcanvas.height = ch;
  tmpctx.drawImage(scanvas, 0, 0);
  if (type == "right") {
    tmpctx.translate(scanvas.height, 0);
    tmpctx.rotate(90 * Math.PI / 180);
  }
  else {
    tmpctx.translate(0, scanvas.width);
    tmpctx.rotate(-90 * Math.PI / 180);
  }
  tmpctx.drawImage(tmpcanvas, 0, 0);
  cw = scanvas.width;
  ch = scanvas.height;
  scanvas.width = ch;
  scanvas.height = cw;
  srotate.drawImage(tmpcanvas, 0, 0);
  scan_type();
  delete tmpctx;
  delete tmpcanvas;
}

function toggle_options() {
  $('#scan_options').toggleClass('makeHidden');
  $('#scan_container').toggleClass('makeHidden');
  less_select();
}

function toggle_dialog() {
  var $scanDialog = $('#scan_dialog');
  top.$.colorbox({
    inline: true,
    href: $scanDialog,
    title: $scanDialog.attr('title'),
    onOpen: function() {
      $('#scan_options').addClass('makeHidden');
      $('#scan_container').removeClass('makeHidden');
    },
    onComplete: function() {
      checkws();
      sdata = document.querySelector('#my_imgdata');
      scanvas = document.querySelector('#my_selected');
      scontext = scanvas.getContext('2d');
      scanvas.width = 0;
      scanvas.height = 0;
      $('img#my_imgdata').imgAreaSelect({
        handles: true,
        onSelectEnd: preview,
      });
    },
    onCleanup: function() {
      less_select();
    },
  });
}

function toggle_search(query) {
  let win = window;
  let h = 640;
  let w = 800;
  const y = win.top.outerHeight / 2 + win.top.screenY - ( h / 2);
  const x = win.top.outerWidth / 2 + win.top.screenX - ( w / 2);
  let url = 'https://duckduckgo.com/?q='+query+'+book&t=h_&ia=images&iax=images';
  let title = 'DuckduckGo Search Result';
  if(query !== '') {
    win.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+y+', left='+x);
    win.focus();
  } else {
    parent.toastr.error("No title available", "Bibliography", {"closeButton":true,"debug":false,"newestOnTop":false,"progressBar":false,"positionClass":"toast-top-right","preventDuplicates":false,"onclick":null,"showDuration":300,"hideDuration":1000,"timeOut":5000,"extendedTimeOut":1000,"showEasing":"swing","hideEasing":"linear","showMethod":"fadeIn","hideMethod":"fadeOut"})
  }
}