/**
 * 
 * Webcam feature
 * 
 * Require : jQuery library
 * 
 * by Indra Sutriadi Pipii 2012-2013
 * 
 */

var slims_hotkey=false;
var input_focus=false;
var data_list=false;
var tr_up=0;
var tr_down=0;
var tr_now=null;

function setPicture(s, t){
	$('textarea#base64picstring').val(s+'#image/type#'+t);
}

var canvas;
var context;
var video;
var localMediaStream = null;
var ratio;
var dataUrl;
var sep;
var pause;

function loadcam(t) {
  video = document.querySelector("#my_vid");
  canvas = document.querySelector('#my_canvas');
  preview = document.querySelector('#my_preview');
  context = canvas.getContext("2d");
  canvas.style.visibility = "hidden";
  video.style.visibility = "visible";
  canvas.height = video.height;
  canvas.width = video.width;
  pause = false;
  var options;
  var onCameraFail = function (e) {
      console.log('Camera did not work.', e);
      alert('Camera did not work.', e);
      $('#btn_pause').attr('disabled', 'disabled');
  };

  window.URL = window.URL || window.webkitURL ||
      window.mozURL || window.msURL;
  if (navigator.getUserMedia) {
      navigator.getUserMedia(
          { "video": true },
          function(stream) {
              video.src = stream;
              video.play();
              localMediaStream = stream;
          },
          onCameraFail
      );
  }
  else {
      navigator.getUserMedia = navigator.webkitGetUserMedia ||
          navigator.mozGetUserMedia || navigator.msGetUserMedia;
      navigator.getUserMedia(
          { "video": true },
          function (stream) {
              video.src = window.URL.createObjectURL(stream);
              video.play();
              localMediaStream = stream;
          },
          onCameraFail
      );
  }
  t.disabled = true;
  $('#btn_pause').removeAttr('disabled');
};

function snapshot(t) {
  if (localMediaStream) {
      context.drawImage(video, 0, 0, canvas.width, canvas.height);
      if (canvas.style.visibility == "visible") {
          t.innerHTML = "Capture";
          canvas.style.visibility = "hidden";
          video.style.visibility = "visible";
          pause = false;
      }
      else {
          t.innerHTML = "Play";
          canvas.style.visibility = "visible";
          video.style.visibility = "hidden";
          pause = true;
          set();
      }
  }
}

function aspect(t) {
  switch(t.value) {
    case "1":
      $('#my_frame').width(240).height(240);
      break;
    case "2":
      $('#my_frame').width(160).height(240);
      break;
    case "3":
      $('#my_frame').width(180).height(240);
      break;
  }
  w = $('#my_frame').width();
  h = $('#my_frame').height();
  preview.width = w;
  preview.height = h;
  if (pause) {
    set();
  }
}

function set() {
  var div = $('#my_frame');
  var parent = div.parent();
  var parent_pos = parent.offset();
  border_left = parseInt(parent.css('borderLeftWidth'));
  border_top = parseInt(parent.css('borderTopWidth'));
  var div_pos = div.offset();
  the_left = div_pos.left - parent_pos.left - border_left;
  the_top = div_pos.top - parent_pos.top - border_top;

  var ctxprv = preview.getContext('2d');
  ctxprv.drawImage(canvas, the_left, the_top, div.width(), div.height(), 0, 0, preview.width, preview.height);
  
  switch ($('#cmb_format').val()) {
    case "png":
      dataUrl = preview.toDataURL();
      dataUrl = dataUrl.split('data:image/png;base64,')[1];
      dataUrl = dataUrl + '#image/type#' + 'png';
      break;
    case "jpg":
      dataUrl = preview.toDataURL("image/jpeg");
      dataUrl = dataUrl.split('data:image/jpeg;base64,')[1];
      dataUrl = dataUrl + '#image/type#' + 'jpg';
      break;
  }
  $('textarea#base64picstring').val(dataUrl);
}



//~ 
//~ $(function() {}).keypress(function(event) {
	//~ /*
	 //~ * global hotkey for navigation module's menu and submodule's menu
	 //~ */
	//~ // control hotkey work not in text area
	//~ input_focus=$(':focus').length>0?true:false;
//~ 
	//~ // hotkey for module's menu (Shift+F[1-12])
	//~ if(event.shiftKey && event.keyCode && !event.ctrlKey){
		//~ mlength=$('div#mainMenu ul#menuList li a').length-1;
		//~ index=event.keyCode-110;
		//~ if(index>=2 && index<mlength)
			//~ window.location=$('div#mainMenu ul#menuList li a').eq(event.keyCode - 110).attr('href');
		//~ else if(event.keyCode == 36 && input_focus===false) // (Shift+Home)
			//~ window.location=$('div#mainMenu ul#menuList li a:first').attr('href');
		//~ else if(event.keyCode == 27 && input_focus===false) // (Shift+Esc)
			//~ window.location=$('div#mainMenu ul#menuList li a:last').attr('href');
		//~ if(event.keyCode>=112 && event.keyCode<=123)
			//~ return false;
	//~ }
	//~ // hotkey for submodule's menu (Ctrl+[0-9] until Ctrl+Alt[0-9])
	//~ else if(event.ctrlKey && event.which && !event.shiftKey){
		//~ slength=$('td#sidepan a').length;
		//~ eWhich=event.which;
		//~ if(!event.altKey){
			//~ if(eWhich-49>=0 && eWhich-49<=9 && eWhich-49<=slength)
				//~ $('td#sidepan a').eq(eWhich-49).click();
			//~ if(eWhich-49==-1 && slength>=9)
				//~ $('td#sidepan a').eq(9).click();
		//~ }else{
			//~ if(eWhich-49>=0 && eWhich-49<=9 && eWhich-39<=slength)
				//~ $('td#sidepan a').eq(eWhich-39).click();
			//~ if(eWhich-49==-1 && slength>=9)
				//~ $('td#sidepan a').eq(19).click();
		//~ }
	//~ }
	//~ /*
	 //~ * SLiMS special hotkey for specific purpose
	 //~ */
	//~ // toggle both enable or disable SLiMS special hotkey
	//~ if(event.ctrlKey && event.which==109 && !event.altKey && !event.shiftKey){
		//~ slims_hotkey=true;
		//~ alert('SLiMS hotkey activated!');
		//~ $(':focus').blur();
		//~ return false;
	//~ }else if(event.ctrlKey && event.shiftKey && event.which==77 && !event.altKey){
		//~ slims_hotkey=false;
		//~ alert('SLiMS hotkey disabled!');
		//~ $(':input:first').focus();
		//~ return false;
	//~ }
	//~ if(slims_hotkey===true){
		//~ // alert('Key: '+event.keyCode+' Which: '+event.which);
		//~ tr_up=$('table#dataList tbody tr').length-2;
		//~ // navigation paging list
		//~ if($('table.datagrid-action-bar span.pagingList a').length>0 && input_focus===false){
			//~ switch(event.keyCode){
				//~ case 39: //next (Right Arrow)
					//~ $('table.datagrid-action-bar span.pagingList a.next_link:first').click();
					//~ break;
				//~ case 37: //prev (Left Arrow)
					//~ $('table.datagrid-action-bar span.pagingList a.prev_link:first').click();
					//~ break;
				//~ case 36: //first (Home)
					//~ $('table.datagrid-action-bar span.pagingList a.first_link:first').click();
					//~ return false;
					//~ break;
				//~ case 35: //last (End)
					//~ $('table.datagrid-action-bar span.pagingList a.last_link:first').click();
					//~ $(':focus').blur();
					//~ return false;
					//~ break;
			//~ }
		//~ }
		//~ var chlist=$('table#dataList tbody :checkbox');
		//~ // select all data from list (Ctrl+A)
		//~ if(event.ctrlKey && event.which==97 && !event.altKey && !event.shiftKey && chlist.length>0){
			//~ chlist.click();
			//~ return false;
		//~ }
		//~ // click row on data list
		//~ // hold-on Ctrl for multiple selection
		//~ if(tr_up>1 && input_focus===false){
			//~ switch(event.keyCode){
				//~ case 38: //up (Up Arrow)
					//~ if(tr_now==null){
						//~ tr_now=tr_up;
						//~ $('table#dataList').append('<a href="#bottomList" id="linkBottomList" class="bottomList"></a>');
						//~ window.location=('#linkBottomList');
					//~ }
					//~ else if(tr_now>tr_down) tr_now--;
					//~ if(tr_now!=tr_up && chlist.eq(tr_now+1).is(':checked') && !event.ctrlKey)
						//~ chlist.eq(tr_now+1).click();
					//~ chlist.eq(tr_now).not(':checked').click();
					//~ break;
				//~ case 40: //down (Down Arrow)
					//~ if(tr_now==null) tr_now=tr_down;
					//~ else if(tr_now<tr_up) tr_now++;
					//~ if(tr_now!=tr_down && chlist.eq(tr_now-1).is(':checked') && !event.ctrlKey)
						//~ chlist.eq(tr_now-1).click();
					//~ chlist.eq(tr_now).not(':checked').click();
					//~ break;
			//~ }
		//~ }
		//~ // edit row on data list (E)
		//~ if(event.which==101 && !event.ctrlKey && !event.altKey && !event.shiftKey && chlist.length>0 && input_focus===false){
			//~ $('table#dataList tbody a.editLink').eq(tr_now).click();
			//~ $(':focus').blur();
		//~ }
		//~ // click submit button first (Enter)
		//~ if(event.which==13 && event.keyCode==13 && !event.ctrlKey && !event.altKey && !event.shiftKey && chlist.length>0 && input_focus===false){
			//~ $('table.datagrid-action-bar :input:first').click();
		//~ }
		//~ // cancel editing (Esc)
		//~ if(event.keyCode==27 && !event.ctrlKey && !event.altKey && !event.shiftKey && $('form#mainForm input.cancelButton').length>0){
			//~ $('form#mainForm input.cancelButton:first').click();
			//~ tr_now=null;
			//~ $(':focus').blur();
		//~ }
	//~ }
//~ }).keydown(function(event) { // show up hotkeys tip
	//~ $('span.keytip').remove();
	//~ if(event.ctrlKey || event.altKey && !event.shiftKey && !event.which){
		//~ for(n=0;n<=9;n++){
			//~ x=n+1;
			//~ if(x==10) x=0;
			//~ $('td#sidepan a').eq(n).append('<span class="keytip" style="font-weight: bold; position: relative; top: -10px; padding: 10px;">Ctrl+'+x+'</span>');
		//~ }
		//~ for(n=0;n<=9;n++){
			//~ x=n+1;
			//~ if(x==10) x=0;
			//~ $('td#sidepan a').eq(n+10).append('<span class="keytip" style="font-weight: bold; position: relative; top: -10px; padding: 10px;">Ctrl+Alt+'+x+'</span>');
		//~ }
	//~ }else if(event.shiftKey && !event.ctrlKey && !event.altKey){
		//~ $('div#mainMenu ul#menuList li a:first').append('<span class="keytip" style="border: none; padding:0; margin: 0; font-weight: bold; position: relative; left: -50px;">&uArr;+Home</span>');
		//~ $('div#mainMenu ul#menuList li a:last').append('<span class="keytip" style="border: none; padding:0; margin: 0; font-weight: bold; position: relative; left: -50px;">&uArr;+Esc</span>');
		//~ for(n=2;n<=$('div#mainMenu ul#menuList li a').length - 2;n++){
			//~ $('div#mainMenu ul#menuList li a').eq(n).append('<span class="keytip" style="border: none; padding:0; margin: 0; font-weight: bold; position: relative; left: -50px;">&uArr;+F'+eval(n-1)+'</span>');
		//~ }
	//~ }
//~ }).keyup(function(event) { // hide hotkeys tip
	//~ if(event.ctrlKey || event.shiftKey){
		//~ $('span.keytip').remove();
	//~ }
//~ });
