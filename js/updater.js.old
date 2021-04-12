/**
* Arie Nugraha 2009
* this file need jQuery library
* library to works
*
* AJAX related functions
*/

jQuery.extend({
    ajaxHistory: new Array(),
    addAjaxHistory: function(strURL, strElement) {
        jQuery.ajaxHistory.unshift({url: strURL, elmt: strElement});
        // delete the last element
        if (jQuery.ajaxHistory.length > 5) {
            jQuery.ajaxHistory.pop();
        }
    },
    ajaxPrevious: function() {
        if (jQuery.ajaxHistory.length < 1) {
            return;
        }
        var moveBack = 1;
        if (arguments[0] != undefined) {
            moveBack = arguments[0];
        }
        if (moveBack >= jQuery.ajaxHistory.length) {
            moveBack -= 1;
        }
        if (jQuery.ajaxHistory.length <= 1) {
            top.location.href = location.pathname + location.search;
            return;
        }
        $(jQuery.ajaxHistory[moveBack].elmt).simbioAJAX(jQuery.ajaxHistory[moveBack].url, {method: 'get'});
    }
});

/**
 * Function to Set AJAX content
 *
 * @param       string      strSelector : string of CSS and XPATH selector
 * @param       string      strURL : URL of AJAX request
 * @return      void
 */
jQuery.fn.simbioAJAX = async function(strURL, params)
{
  var options = {
      method: 'get',
      insertMode: 'replace',
      addData: '',
      returnType: 'html',
      loadingMessage: 'LOADING CONTENT... PLEASE WAIT' };
  jQuery.extend(options, params);

  var ajaxContainer = $(this);
  var loader = $(".loader");
  var currLoaderMessage = loader.html();
  var doc = $(document);
  loader.html(options.loadingMessage);
  doc.ajaxStart(function(){ $(this).addClass('loadingImage'); });
  doc.ajaxSuccess(function(){
      loader.html(currLoaderMessage);
      // no history on post AJAX request
      if (options.method != 'post') {
          var historyURL = strURL;
          if (options.addData.length > 0) {
              var addParam = options.addData;
              if (Array.prototype.isPrototypeOf(options.addData)) {
                  addParam = jQuery.param(options.addData);
              }
              if (historyURL.indexOf('?', 0) > -1) {
                  historyURL += '&' + addParam;
              } else {
                  historyURL += '?' + addParam;
              }
          }
          jQuery.addAjaxHistory(historyURL, ajaxContainer[0]);
      }
  });
  doc.ajaxStop(function(){ loader.removeClass('loadingImage'); });
  doc.ajaxError(function(event, request, settings) { 
      // Fixing error message position
      /* Modified by Drajat Hasan */
      loader.attr('style', 'background: #D9534F;color: white;position: relative !important;font-weight: bold;'),
      loader.html("<div class=\"error\">Error requesting page : <strong>" + settings.url + "</strong>" + request.responseText + " <br> Press F5 to hide this error message.</div>");
      /* End */
  })

    var ajaxResponse;
    try {
        ajaxResponse = await $.ajax({
            type : options.method, url : strURL,
            data : options.addData, async: false })

        // clear error message
        if(loader.html()) {
            if (loader.html().indexOf('Error') > -1) {
                loader.html('&nbsp;')
            }
        }
        loader.removeAttr('style')
    } catch (err) {
        console.error(err)
    }

  // add to elements
  if (options.insertMode == 'before') {
      ajaxContainer.prepend(ajaxResponse);
  } else if (options.insertMode == 'after') {
      ajaxContainer.append(ajaxResponse);
  } else { ajaxContainer.html(ajaxResponse).hide().fadeIn('fast'); }

  ajaxContainer.trigger('simbioAJAXloaded');

  return ajaxContainer;
}

/* invoke UCS upload catalog */
var ucsUpload = function(strUploadHandler, strData) {
    var confUpload = false;
    strData = jQuery.trim(strData);
    if (strData) {
        confUpload = confirm('Are you sure to upload selected data to Union Catalog Server?');
    } else {
        alert('Please select bibliographic data to upload!');
        return;
    }
    if (!confUpload) {
        return;
    }
    jQuery.ajax({
      url: strUploadHandler,
      type: 'POST',
      data: strData,
      dataType: 'json',
      success: function(ajaxRespond) {
              var jsonObj = ajaxRespond;
              // alert(jsonObj.status + ': ' + jsonObj.message);
              alert(jsonObj.message);
          },
      error: function(ajaxRespond) {
          alert('UCS Upload error with message: ' + ajaxRespond.responseText);
          }
      });
}

/* invoke UCS record update */
var ucsUpdate = function(strURLHandler, strData) {
    strData = jQuery.trim(strData);
    jQuery.ajax({
      url: strURLHandler,
      type: 'POST',
      data: strData,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {  alert('Error updating UCS : ' + textStatus + ' (' + errorThrown + ')'); },
      success: function(data, textStatus, jqXHR) {  alert('UCS record(s) updated'); }
      });
}
