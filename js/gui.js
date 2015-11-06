/**
 * Arie Nugraha 2013
 * Simbio GUI related functions
 *
 * Require : jQuery library
 **/

/**
 * jQuery Plugins function to set row event on datagrid table
 *
 * @param     object    an additional option for table
 * @return    jQuery
 *
 * @example usage
 * $('.datagrid').simbioTable();
 * or
 * $('.datagrid').simbioTable({ mouseoverCol: '#bcd4ec', highlightCol: 'yellow' });
 */
jQuery.fn.simbioTable = function(params) {
  // set some options
  var options = {
    mouseoverCol: '#6dff77',
    highlightCol: 'yellow'
  };
  jQuery.extend(options, params);

  var tableRows = $(this).children('thead,tbody,tfoot').children('tr');
  // try straight search to TR
  if (tableRows.length < 1) { tableRows = $(this).children('tr'); }
  // add non-standar 'row' attribute to indicate row position
  tableRows.each(function(i) {
      $(this).attr('row', i);
    });

  // event register
  tableRows.mouseover(function() {
    // on mouse over change background color
    if (!this.highlighted) {
      this.originColor = $(this).css('background-color');
      $(this).css('background-color', options.mouseoverCol);
    }
  }).mouseout(function() {
    // on mouse over revert background color to original
    if (!this.highlighted) {
      $(this).css('background-color', this.originColor);
    }
  }).click(function(evt) {
    var currRow = $(this);
    if (!this.originColor) {
      this.originColor = currRow.css('background-color');
    }
    // on click highlight row with new background color
    if (this.highlighted) {
      this.highlighted = false;
      currRow.removeClass('highlighted last-highlighted').css({'background-color': this.originColor}).find('td');
      // uncheck the checkbox on row if exists
      if (currRow.find('.noAutoFocus').length < 1) {
	    currRow.find('input:checkbox:first').removeAttr('checked');
	  }
    } else {
      // set highlighted flag
      this.highlighted = true;
	  if (currRow.find('.noAutoFocus').length < 1) {
		// check the checkbox on row if exists
		currRow.find('input:checkbox:first').attr('checked', 'checked');
	    currRow.find('input:text,textarea,select').first().focus();
	  }
      // get parent table of row
      var parentTable = $( currRow.parents('table')[0] );

      // get last highlighted row index
      var lastRow = parseInt(parentTable.find('.last-highlighted').attr('row'));
      // get current row index
      var currentRow = parseInt(currRow.attr('row'));

      if (evt.shiftKey) {
        var start = Math.min(currentRow, lastRow);
        var end = Math.max(currentRow, lastRow);
        for (var r = start+1; r <= end-1; r++) {
          parentTable.find('tr[row=' + r + ']').trigger('click');
        }
      }

      // remove all last-highlighted row class
      parentTable.find('.last-highlighted').removeClass('last-highlighted');
      // highlight current clicked row
      currRow.addClass('highlighted last-highlighted').css({'background-color': options.highlightCol}).find('td');
    }
  });

  return $(this);
};


/**
 * jQuery Plugins function to make dynamic addition form field
 *
 *
 * @return    jQuery
 */
jQuery.fn.dynamicField = function() {
  var dynFieldClass = this.attr('class');
  this.find('.add').click(function() {
    // get div parent element
    var currentField = $(this).parent();
    var addField = currentField.clone(true);
    // append remove button and remove ".add" button for additional field
    addField.append(' <a href="#" class="remove-field">Remove</a>').children().remove('.add');
    // add cloned field after
    currentField.after(addField[0]);
    // register event for remove button
    $(document).ready(function() {
      $('.remove-field', this).click(function() {
        // remove field
        var toRemove = $(this).parent().remove();
      });
    });
  });

  return $(this);
}


/**
 * jQuery plugins to disable all input field inside form
 *
 *
 * @return    jQuery
 */
jQuery.fn.disableForm = function() {
  var disabledForm = $(this);
  disabledForm.find('input,select,textarea').each(function() {
    this.disabled = true;
  });
  return disabledForm;
}


/**
 * jQuery plugins to enable all input field inside form
 *
 *
 * @return    jQuery
 */
jQuery.fn.enableForm = function() {
  var enabledForm = $(this);
  enabledForm.find('input,select,textarea').each(function() {
    this.disabled = false;
  });
  $(document).trigger('formEnabled');
  return enabledForm;
}


/**
 * JQuery method to unbind all related event on specified selector
 */
jQuery.fn.unRegisterEvents = function() {
  var container = $(this);
  // unbind all event handlers
  container.find('a,table,tr,td,input,textarea,div').unbind();
  return container;
}


/**
 * Add some utilities function to jQuery namespace
 */
jQuery.extend({
  unCheckAll: function(strSelector) {
    $(strSelector).find('tr').each(function() {
      if ($(this).hasClass('highlighted')) {
        $(this).trigger('click');
      }
    });
  },
  checkAll: function(strSelector) {
    $(strSelector).find('tr').each(function() {
      if (!$(this).hasClass('highlighted')) {
        $(this).trigger('click');
      }
    });
  }
});

/* AJAX plugins for SLiMS */
jQuery.fn.registerAdminEvents = function(params) {
  // set some options
  var options = {
    ajaxifyLink: true,
    ajaxifyForm: true
  };
  jQuery.extend(options, params);

  // cache AJAX container
  var container = $(this);

  // set all table with class datagrid
  container.find('table.datagrid,#dataList').each(function() {
    var datagrid = $(this);
    datagrid.simbioTable();
    // register uncheck click event
    $('.uncheck-all').click(function() {
      jQuery.unCheckAll('.datagrid,#dataList');
    });
    // register check click event
    $('.check-all').click(function() {
      jQuery.checkAll('.datagrid,#dataList');
    });
    // set all row to show detail when double clicked
    datagrid.children('thead,tbody,tfoot').children('tr').each( function() {
      var tRow = $(this);
      var rowLink = tRow.css({'cursor' : 'pointer'}).find('.editLink');
      if (rowLink[0] != undefined) {
        tRow.dblclick(function() {$(rowLink[0]).trigger('click')});
      }
    });
    // unregister event for table-header
    $('.table-header', datagrid).parent().unbind();
  });

  // disable form with class "disabled"
  var disabledForm = container.find('form.disabled');
  if (disabledForm.length > 0) {  disabledForm.disableForm(); }

  // focus first element
  container.find('input[type=text]:first').focus();
  // focus first form element
  var mainForm = container.find('#mainForm'); if (mainForm.length > 0) { mainForm.find('input,textarea').not(':submit,:button').first().focus(); }
  // disable form marked with disabled attribute
  container.find('form.disabled').disableForm().find('.cancelButton').removeAttr('disabled').click(function() {
    jQuery.ajaxPrevious(0);
  });

  return container;
}

/* Javasript function to open new window  */
var openWin = function(strURL, strWinName, intWidth, intHeight, boolScroll) {
  // variables to determine the center position of window
  var xPos = (screen.width - intWidth)/2;
  var yPos = (screen.height - intHeight)/2;

  var withScrollbar = 'no';
  // if scrollbar allowed
  if (boolScroll) { withScrollbar = 'yes'; }

  window.open(strURL, strWinName, "height=" + intHeight + ",width=" + intWidth +
  ",menubar=no, scrollbars=" + withScrollbar + ", location=no, toolbar=no," +
  "directories=no,resizable=no,screenY=" + yPos + ",screenX=" + xPos + ",top=" + yPos + ",left=" + xPos);
}

/* set iframe content */
var setIframeContent = function(strIframeID, strUrl) {
  var iframeObj = $('#'+strIframeID);
  if (iframeObj.length > 0) { iframeObj[0].src = strUrl; }
  return iframeObj;
}

/* hide table rows */
var hiddenTables = new Array();
var hideRows = function(str_table_id, int_start_row) {
  var rows = $('#'+str_table_id).find('.divRow');
  var skip = 0;
  rows.each(function() {
    if (skip < int_start_row) {
      skip++; return;
    } else {
      $(this).css('display','none');
    }
  });
  // add table id to hidden table array
  hiddenTables.push(str_table_id);
}

/* show table rows */
var showRows = function(str_table_id) {
  $('#'+str_table_id).find('.divRow').slideDown();
}

/* toogle show/hide table rows */
var showHideTableRows = function(str_table_id, int_start_row, obj_button, str_hide_text, str_show_text) {
  obj_button = $(obj_button);
  if (obj_button.hasClass('hideButton')) {
    hideRows(str_table_id, int_start_row);
    obj_button.removeClass('hideButton').val(str_hide_text);
  } else {
    showRows(str_table_id);
    obj_button.addClass('hideButton').val(str_show_text);
  }
}

/**
 * Register all events
 */
$('document').ready(function() {
  var container = $('#mainContent,#pageContent,#sidepan');

  // change all anchor behaviour to AJAX in main content
  container.on('click', 'a', function(evt) {
    // avoid conflict with tinyMCE and other non-AJAX anchor
	container.find('.cke a, .mceEditor a, .chzn-container a').addClass('notAJAX');
    var anchor = $(this);
	if (anchor.hasClass('notAJAX')) {
      return true;
	}
	if (anchor.attr('target') && anchor.attr('target') != '_self') {
	  return true;
	}
	evt.preventDefault();
	var ajaxContainer = $('#mainContent,#pageContent');
    // for submenu
    // remove other menu class
    $('.subMenuItem').removeClass('curModuleLink');
    var subMenu = anchor.addClass('curModuleLink');
    // get anchor href
    var url = anchor.attr('href');
    var postData = anchor.attr('postdata');
    var loadContainer = anchor.attr('loadcontainer');
    if (loadContainer) {
      ajaxContainer = $('#'+loadContainer);
    }
    // set ajax
    if (postData) {
      ajaxContainer.simbioAJAX(url, {method: 'post', addData: postData});
    } else {
      ajaxContainer.simbioAJAX(url, {addData: {ajaxload: 1}});
    }
  });

  // change all search form submit behaviour to AJAX
  container.on('submit', '.menuBox form:not(.notAJAX), .submitViaAJAX', function(evt) {
    var ajaxContainer = $('#mainContent,#pageContent');
    var theForm = $(this);
    if (theForm.attr('target')) {
      theForm[0].submit();
      return;
    }
    evt.preventDefault();
    var formAction = theForm.attr('action');
    var formMethod = theForm.attr('method');
    var formData = theForm.serialize();
    var loadContainer = theForm.attr('loadcontainer');
    if (loadContainer) {
      ajaxContainer = jQuery('#'+loadContainer);
    }
    ajaxContainer.simbioAJAX(formAction, {method: formMethod, addData: formData});
  });

	// form EDIT link behaviour
    container.on('click', '.editFormLink', function(evt) {
    evt.preventDefault();
    var theForm = $(this).parents('form').enableForm().find('input,textarea').not(':submit,:button').first().focus();
    $('.makeHidden').removeClass('makeHidden');
    // enable hidden delete form
    container.find('#deleteForm').enableForm();
	  container.find('.select2').attr('disabled', false).trigger("liszt:updated");
  });

	// register event for tab buttons
  container.on('click', 'a.tab', function() {
    var tab = $(this);
		var parentContainer = tab.parents('ul');
		parentContainer.find('li').removeClass('active');
    tab.parent().addClass('active');
  });

  // Register admin event for AJAX event
  container.not('#sidepan').on('simbioAJAXloaded', function(evt) {
    $(this).registerAdminEvents();
    // report filter
    $('#filterForm').children('.divRow:gt(0)').wrapAll('<div class="hiddenFilter"></div>');
    var hiddenFilter = $('.hiddenFilter').hide();
    $('[name=moreFilter]').toggle(function() { hiddenFilter.fadeIn(); }, function() { hiddenFilter.hide(); });
    // tooltip
		if ($.tooltipsy) {
      $('input[title], textarea[title]').tooltipsy({
        offset: [-10, 0],
        show: function (e, $el) {
          $el.css({
            'left': parseInt($el[0].style.left.replace(/[a-z]/g, '')) - 50 + 'px',
            'opacity': '0.0',
            'display': 'block'
          }).animate({
            'left': parseInt($el[0].style.left.replace(/[a-z]/g, '')) + 50 + 'px',
            'opacity': '1.0'
          }, 300);
        },
        hide: function (e, $el) { $el.slideUp(100); }
      });
		}

    // select 2
    $('.select2').each( function(idx) {
      var selectObj = $(this);
      var ajaxHandler = selectObj.attr('data-src')
      if (ajaxHandler) {
        var dataSourceTable = selectObj.attr('data-src-table');
        var dataSourceCols = selectObj.attr('data-src-cols');
        selectObj.ajaxChosen({
          jsonTermKey: 'keywords',
          type: 'POST',
          url: ajaxHandler,
          // data: 'tableName='+dataSourceTable+'&tableFields='+dataSourceCols,
          data: {tableName:dataSourceTable, tableFields:dataSourceCols},
          dataType: 'json' },
          function (data) {
            var results = [];
            $.each(data, function (i, val) {
              results.push({ value: val.id, text: val.text });
            });
            return results;
          });
      } else {
        selectObj.chosen();
      }
    });
  });

  // disable form with class "disabled"
	var disabledForm = $('form.disabled');
	if (disabledForm.length > 0) {  disabledForm.disableForm(); }

  // jquery colorbox
  $('body').on('click', 'a.openPopUp', function(evt) {
    evt.preventDefault();
    var popUpButton = $(this);
    top.jQuery.colorbox({iframe:true,
    href: popUpButton.attr('href'),
    innerWidth: function() {
      var width = parseInt(popUpButton.attr('width'));
      if (width) { return width; } else { return 600; } },
    innerHeight: function() {
      var height = parseInt(popUpButton.attr('height'));
      if (height) { return height; } else { return 300; } },
    title: function() { return popUpButton.attr('title'); } });
  });

  // Google Voice Search
  $('#keyword').bind('webkitspeechchange', function() {
    $(this).closest('form').submit();
  });
});
