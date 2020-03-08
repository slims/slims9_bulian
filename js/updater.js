/**
 * Arie Nugraha 2009
 * this file need jQuery library to works
 *
 * AJAX related functions
 *
 * Modified by Waris Agung Widodo
 * Last modified: 2020-03-08
 */

jQuery.extend({
    ajaxHistory: [],
    addAjaxHistory: function (strURL, strElement) {
        jQuery.ajaxHistory.unshift({url: strURL, elmt: strElement});
        // delete the last element
        if (jQuery.ajaxHistory.length > 5) {
            jQuery.ajaxHistory.pop();
        }
    },
    ajaxPrevious: function () {
        if (jQuery.ajaxHistory.length < 1) {
            return;
        }
        let moveBack = 1;
        if (arguments[0] !== undefined) {
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
jQuery.fn.simbioAJAX = async function (strURL, params) {
    const options = {
        method: 'get',
        insertMode: 'replace',
        addData: '',
        returnType: 'html',
        loadingMessage: 'LOADING CONTENT... PLEASE WAIT'
    };
    jQuery.extend(options, params);

    const ajaxContainer = $(this);

    let ajaxResponse;
    try {
        ajaxResponse = await $.ajax({
            type: options.method, url: strURL,
            data: options.addData, async: true
        })
    } catch (err) {
        ajaxResponse = `<div class="alert alert-danger error-message">Unknown error</div>`;
        if (err.responseText !== undefined)
            ajaxResponse = `<div class="alert alert-danger error-message">${err.responseText}</div>`;
    }

    // add to elements
    if (options.insertMode === 'before') {
        ajaxContainer.prepend(ajaxResponse);
    } else if (options.insertMode === 'after') {
        ajaxContainer.append(ajaxResponse);
    } else {
        ajaxContainer.html(ajaxResponse).hide().fadeIn('fast');
    }

    ajaxContainer.trigger('simbioAJAXloaded');

    return ajaxContainer;
};

/* invoke UCS upload catalog */
let ucsUpload = function (strUploadHandler, strData) {
    let confUpload = false;
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
        success: function (ajaxRespond) {
            // alert(jsonObj.status + ': ' + jsonObj.message);
            alert(ajaxRespond.message);
        },
        error: function (ajaxRespond) {
            alert('UCS Upload error with message: ' + ajaxRespond.responseText);
        }
    });
};

/* invoke UCS record update */
let ucsUpdate = function (strURLHandler, strData) {
    strData = jQuery.trim(strData);
    jQuery.ajax({
        url: strURLHandler,
        type: 'POST',
        data: strData,
        dataType: 'json',
        error: function (jqXHR, textStatus, errorThrown) {
            alert('Error updating UCS : ' + textStatus + ' (' + errorThrown + ')');
        },
        success: function (data, textStatus, jqXHR) {
            alert('UCS record(s) updated');
        }
    });
};

/**
 * Function to activate loader
 *
 * @return void
 */
jQuery.fn.registerLoader = function () {
    // loader
    const loader = $(".loader");
    const currLoaderMessage = loader.html();
    const doc = this;
    doc.ajaxStart(function () {
        loader.show()
    });
    doc.ajaxSuccess(function () {
        loader.html(currLoaderMessage);
        loader.fadeOut('fast')
    });
    doc.ajaxStop(function () {
        loader.fadeOut('fast')
    });
};
