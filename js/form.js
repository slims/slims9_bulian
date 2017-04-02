/**
* Arie Nugraha 2009
* this file need jQuery library
* library to works
*
* Form related functions
*/

/* function to fill select list with AJAX */
var ajaxFillSelect = function(str_handler_file, str_table_name, str_table_fields, str_container_ID) {
  var additionalParams = '';
  var result = '';
  if (arguments[4] != undefined) {
    additionalParams = '&keywords=' + arguments[4];
  }
  // fill the select list
  jQuery.ajax({ url: str_handler_file, type: 'POST',
    data: 'tableName=' + str_table_name + '&tableFields=' + str_table_fields + additionalParams,
    success: function(ajaxRespond) { result = jQuery.trim(ajaxRespond); $('#'+str_container_ID).html(ajaxRespond); } });

  if (result) {
    return true;
  }
  return false;
}

/* AJAX check ID */
var ajaxCheckID = function(str_handler_file, str_table_name, str_ID_fields, str_container_ID, str_input_obj_ID) {
  var inputEl = $('#'+str_input_obj_ID);
  var inputVal = inputEl.val();
  if (inputVal) {
    additionalParams = '&id=' + inputVal;
    inputEl.css('background-color','#fff');
    inputEl.css('color','#000');
    inputEl.css('border-color','#ddd');
  } else {
    $('#'+str_container_ID).html('<strong style="color: #D9534F;">Please supply valid ID!</strong>');
    inputEl.css('background-color','#D9534F');
    inputEl.css('color','#fff');
    inputEl.css('border-color','#D9534F');
    return;
  }
  // fill the select list
  jQuery.ajax({url: str_handler_file, type: 'POST',
    data: 'tableName=' + str_table_name + '&tableFields=' + str_ID_fields + additionalParams,
    success: function(ajaxRespond) { $('#'+str_container_ID).html(ajaxRespond); } });
}

/* Javasript function to check or uncheck all checkbox element */
var checkAll = function(strFormID, boolUncheck) {
  var formObj = $('#'+strFormID);
  // get all checkbox element
  var chkBoxs = formObj.find('input[type=checkbox]');

  if (!boolUncheck) {
    // check all these checkbox
    chkBoxs.trigger('click');
  } else {
    // check all these checkbox
    chkBoxs.trigger('click');
  }
}

/* function to collect checkbox data and submit form */
var chboxFormSubmit = function(strFormID, strMessage) {
  var formObj = $('#'+strFormID);
  // get all checkbox element
  var chkBoxs = formObj.find('input[type=checkbox]:checked');

  if (chkBoxs.length < 1) {
    alert('No Data Selected!');
    return;
  } else {
    var confirmMsg;
    if (arguments[1] != undefined) {
      confirmMsg = arguments[1];
    } else { confirmMsg = 'Are You SURE to do this action?'; }
    var isConfirm = confirm(confirmMsg);
    if (isConfirm) {
      // submit the form
      formObj[0].submit();
    }
  }
}

/* function to serialize all checkbox element in form */
var serializeChbox = function(strParentID) {
  var serialized = '';
  $('#'+strParentID).find('input[type=checkbox]:checked').each(function() {
    var cbData = this.value;
    if (cbData) { serialized += 'itemID[]='+cbData+'&'; }
  })
  return serialized;
}

/* form submit confirmation */
var confSubmit = function(strFormID, strMsg) {
  strMsg = strMsg.replace(/\'/i, "\'");
  var yesno = confirm(strMsg);
  if (yesno) { $('#'+strFormID).submit(); }
}

/* AJAX drop down */
/* catch JSON response and populate it to list */
var listID = '';
var noResult = true;
var jsonToList = function(strURL, strElmntID) {
  var addParams = '';
  if (arguments[2] != undefined) {
    addParams = arguments[2];
  }
  // escape single quotes
  strURL = strURL.replace(/\'/i, "\'");
  jQuery.ajax({ url: strURL,
    type: 'POST', data: addParams, dataType: 'json',
    success: function(ajaxRespond) {
        listID = strElmntID + 'List';
        if (!ajaxRespond) {
          noResult = true; return;
        }
        noResult = false;
        // evaluate json respons
        var strListVal = '';
        jQuery.each(ajaxRespond, function(i) {
          vals = this;
          strListVal += '<li><a class="DDlink notAJAX" onclick="setInputValue(\'' + strElmntID + '\', \'' + vals.replace(/\'/i, "\'") + '\')">' + vals + '</a></li>';
        });
        // update the list content
        $('#'+listID).html(strListVal);
      }
    });
}

/* set drop down input value */
var setInputValue = function(strElmntID, strValue) {
  $('#'+strElmntID).val(strValue);
  $('#'+strElmntID + 'List').hide();
}

/* populate AJAX drop down list and show the list */
var showDropDown = function(strURL, strElmntID, strAddParams) {
  var inputObj = $('#'+strElmntID);
  var inputVal = inputObj.val().replace(/<[^<]+>/i, '');
  var inputObjWidth = inputObj.width();
  var inputObjXY = inputObj.offset();
  // List ID
  var listObj = $('#'+strElmntID + 'List');
  if (inputVal.length < 4) { listObj.hide(); return; }
  // populate list ID
  jsonToList(strURL, strElmntID, 'inputSearchVal=' + encodeURIComponent(inputVal) + '&' + strAddParams);
  if (noResult) { return; }
  // show list
  listObj.css({'left': inputObjXY.left+'px', 'width': inputObjWidth+'px', 'display': 'block'});
  // observe click
  $(document).click(function(event) {
    var clickedElement = $(this);
    if (!clickedElement.is('#' + strElmntID + 'List')) { listObj.hide(); }
  });
}
