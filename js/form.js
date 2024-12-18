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
    $('#'+str_container_ID).html('<strong class="text-danger;">Please supply valid ID</strong>');
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
var chboxFormSubmit = function(strFormID, strMessage, withConfirm) {
  var formObj = $('#'+strFormID);

  if (!withConfirm)
  {
    // submit the form
    formObj[0].submit();
    return;
  }

  // get all checkbox element
  var chkBoxs = formObj.find('input[type=checkbox]:checked');

  if (chkBoxs.length < 1) {
    alert('No Data Selected!');
    return;
  } else {
    var confirmMsg;
    if (arguments[1] != undefined) {
      confirmMsg = arguments[1];
    } else { confirmMsg = 'Are you sure to do this action?'; }
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
var confSubmit = function(strFormID, strMsg, withConfirm) {
  if (!withConfirm)
  {
    $('#'+strFormID).submit();
    return;
  }
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

/**
 * Debouncing method to limit the rate at which a function is executed.
 * 
 * @param {*} func 
 * @param {*} wait 
 * @returns 
 */
function debounce(func, wait) {
  let timeout;

  return function executedFunction(...args) {
      const later = () => {
          clearTimeout(timeout);
          func(...args);
      };

      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
  };
}

// Implement debouncing technique to jsonToList method
const debouncedFetchSuggestions = debounce(jsonToList, 800)

/**
 * Populate AJAX drop down list and show the list
 * 
 * @param {*} strURL 
 * @param {*} strElmntID 
 * @param {*} strAddParams 
 */
function showDropDown(strURL, strElmntID, strAddParams) {
  const element = document.querySelector(`#${strElmntID}`)
  const value = element.value.replace(/<[^<]+>/i, '')
  const elementWidth = element.offsetWidth // Use offsetWidth for width
  const elementRect = element.getBoundingClientRect() // Get position

  // List element
  const listElement = document.querySelector(`#${strElmntID}List`)

  // Populate list (implementation for jsonToList omitted for brevity)
  debouncedFetchSuggestions(strURL, strElmntID, `inputSearchVal=${encodeURIComponent(value)}&${strAddParams}`)
  
  // Show list
  listElement.style.left = elementRect.left + 'px';
  listElement.style.width = elementWidth + 'px';
  listElement.style.display = 'block';

  // Add click event listener to document for closing the list
  document.addEventListener('click', (event) => {
    if (!event.target.matches(`#${strElmntID}List`)) {
      listElement.style.display = 'none'
    }
  })
}
