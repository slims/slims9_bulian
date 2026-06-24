/**
 * SLiMS GUI Functions - Modernized 2025
 * Originally created by Arie Nugraha 2013
 * Modified by Waris Agung Widodo
 * Modernized for better performance, security, and maintainability
 *
 * Requires: jQuery library
 */

'use strict';

/**
 * Modern SimbioTable Plugin - Enhanced with ES6+ features
 * @param {Object} params - Configuration options
 * @returns {jQuery} jQuery object for chaining
 */
jQuery.fn.simbioTable = function(params) {
  const defaultOptions = {
    mouseoverCol: '#6dff77',
    highlightCol: 'yellow'
  };
  
  const options = { ...defaultOptions, ...params };
  const $table = $(this);
  
  // Use more efficient selector and caching
  const tableRows = $table.find('thead tr, tbody tr, tfoot tr').length 
    ? $table.find('thead tr, tbody tr, tfoot tr')
    : $table.children('tr');
  
  // Set row attributes with data- prefix for HTML5 compliance
  tableRows.each((index, element) => {
    $(element).attr('data-row-index', index);
  });

  // Use event delegation for better performance
  $table.off('click.simbioTable').on('click.simbioTable', 'tr', function(evt) {
    const $currentRow = $(this);
    const rowElement = this;
    
    // Initialize original color if needed
    if (!rowElement.dataset.originColor) {
      rowElement.dataset.originColor = $currentRow.css('background-color');
    }
    
    if (rowElement.classList.contains('highlighted')) {
      // Unhighlight row
      rowElement.classList.remove('highlighted', 'last-highlighted');
      $currentRow.css('background-color', rowElement.dataset.originColor);
      
      // Uncheck checkbox if exists and not marked as noAutoFocus
      if (!$currentRow.find('.noAutoFocus').length) {
        $currentRow.find('.selected-row').prop('checked', false);
      }
    } else {
      // Highlight row
      rowElement.classList.add('highlighted');
      
      if (!$currentRow.find('.noAutoFocus').length) {
        // Check checkbox and focus first input
        $currentRow.find('.selected-row').prop('checked', true);
        const $firstInput = $currentRow.find('input:text, textarea, select').first();
        if ($firstInput.length) {
          // Use requestAnimationFrame for smooth focus
          requestAnimationFrame(() => $firstInput.focus());
        }
      }
      
      // Handle shift-click range selection
      const $parentTable = $currentRow.closest('table');
      const currentRowIndex = parseInt($currentRow.attr('data-row-index'), 10);
      
      if (evt.shiftKey) {
        const $lastHighlighted = $parentTable.find('.last-highlighted');
        if ($lastHighlighted.length) {
          const lastRowIndex = parseInt($lastHighlighted.attr('data-row-index'), 10);
          const start = Math.min(currentRowIndex, lastRowIndex);
          const end = Math.max(currentRowIndex, lastRowIndex);
          
          // Use more efficient range selection
          for (let i = start + 1; i < end; i++) {
            $parentTable.find(`tr[data-row-index="${i}"]`).trigger('click.simbioTable');
          }
        }
      }
      
      // Remove previous last-highlighted and set new one
      $parentTable.find('.last-highlighted').removeClass('last-highlighted');
      rowElement.classList.add('last-highlighted');
      $currentRow.css('background-color', options.highlightCol);
    }
  });

  return $table;
};


/**
 * Modern Dynamic Field Plugin
 * @returns {jQuery} jQuery object for chaining
 */
jQuery.fn.dynamicField = function() {
  const $container = $(this);
  
  // Use event delegation for better performance
  $container.off('click.dynamicField').on('click.dynamicField', '.add', function(evt) {
    evt.preventDefault();
    
    const $currentField = $(this).closest('.dynamic-field-container');
    const $clonedField = $currentField.clone(true);
    
    // Clean up cloned field
    $clonedField
      .find('.add').remove().end()
      .append(' <a href="#" class="remove-field" role="button" aria-label="Remove field">Remove</a>');
    
    // Insert after current field
    $currentField.after($clonedField);
  });

  // Handle remove button clicks
  $container.off('click.removeField').on('click.removeField', '.remove-field', function(evt) {
    evt.preventDefault();
    $(this).closest('.dynamic-field-container').remove();
  });

  return $container;
};

/**
 * Modern Form Disable/Enable Functions
 */
jQuery.fn.disableForm = function() {
  return this.each(function() {
    const formElements = this.querySelectorAll('input, select, textarea');
    formElements.forEach(element => {
      element.disabled = true;
    });
  });
};

jQuery.fn.enableForm = function() {
  return this.each(function() {
    const formElements = this.querySelectorAll('input, select, textarea');
    formElements.forEach(element => {
      element.disabled = false;
    });
    
    // Trigger custom event
    $(document).trigger('formEnabled');
  });
};

/**
 * Modern Event Cleanup
 */
jQuery.fn.unRegisterEvents = function() {
  return this.each(function() {
    const $container = $(this);
    // More specific event cleanup
    $container.find('a, table, tr, td, input, textarea, div').off();
  });
};

/**
 * Enhanced utility functions with better performance
 */
const SimbioUtils = {
  unCheckAll(selector) {
    const $container = $(selector);
    $container.find('tr.highlighted').each(function() {
      $(this).trigger('click.simbioTable');
    });
  },

  checkAll(selector) {
    const $container = $(selector);
    $container.find('tr:not(.highlighted)').each(function() {
      $(this).trigger('click.simbioTable');
    });
  }
};

// Add to jQuery namespace for backward compatibility
jQuery.extend({
  unCheckAll: SimbioUtils.unCheckAll,
  checkAll: SimbioUtils.checkAll
});

/**
 * Modern AJAX Admin Events Registration
 */
jQuery.fn.registerAdminEvents = function(params) {
  const defaultOptions = {
    ajaxifyLink: true,
    ajaxifyForm: true
  };
  
  const options = { ...defaultOptions, ...params };
  const $container = $(this);

  // Enhanced download form handler with better error handling
  $container.off('submit.downloadForm').on('submit.downloadForm', 'form[name="downloadForm"]', async function(evt) {
    evt.preventDefault();
    
    const $form = $(this);
    const formData = $form.serialize() + '&doExport=yes';
    const actionUrl = $form.attr('action');
    const filename = $form.find('input[name="doExport"]').data('filename') || 'download.file';
    
    try {
      const response = await fetch(actionUrl, {
        method: 'POST',
        body: formData,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const blob = await response.blob();
      
      // Create download link using modern approach
      const url = URL.createObjectURL(blob);
      const downloadLink = document.createElement('a');
      
      downloadLink.style.display = 'none';
      downloadLink.href = url;
      downloadLink.download = filename;
      
      document.body.appendChild(downloadLink);
      downloadLink.click();
      
      // Cleanup
      document.body.removeChild(downloadLink);
      URL.revokeObjectURL(url);
      
      $('.loader').hide();
      
    } catch (error) {
      console.error('Download error:', error);
      if (window.toastr) {
        window.toastr.error('Download failed. Please try again.', 'Error');
      } else {
        alert('Download failed. Please try again.');
      }
      $('.loader').hide();
    }
  });

  // Enhanced form submission with loader
  $container.off('submit.submitExec').on('submit.submitExec', 'form[target="submitExec"]', function() {
    $('.loader').show();
    return true;
  });

  // Enhanced iframe debug handler
  const iframe = $container.find('iframe#submitExec')[0];
  if (iframe) {
    iframe.addEventListener('load', function() {
      try {
        const iframeDoc = this.contentDocument || this.contentWindow.document;
        if (iframeDoc && iframeDoc.body && iframeDoc.body.innerHTML.trim()) {
          $container.find('details.debug')
            .removeClass('debug-empty')
            .prop('open', true);
          
          // Set iframe height dynamically
          this.style.height = iframeDoc.body.scrollHeight + 'px';
        }
      } catch (error) {
        console.warn('Cannot access iframe content:', error);
      }
    });
  }

  // Enhanced datagrid setup with better performance
  $container.find('table.datagrid, #dataList').each(function() {
    const $datagrid = $(this);
    $datagrid.simbioTable();
    
    // Setup row double-click behavior
    $datagrid.off('dblclick.editLink').on('dblclick.editLink', 'tr', function() {
      const $editLink = $(this).find('.editLink');
      if ($editLink.length) {
        $editLink[0].click();
      }
    });
  });

  // Setup check/uncheck all functionality
  $container.off('click.checkAll').on('click.checkAll', '.check-all', function() {
    SimbioUtils.checkAll('.datagrid, #dataList');
  });
  
  $container.off('click.unCheckAll').on('click.unCheckAll', '.uncheck-all', function() {
    SimbioUtils.unCheckAll('.datagrid, #dataList');
  });

  // Handle disabled forms
  $container.find('form.disabled').each(function() {
    $(this).disableForm()
      .find('.cancelButton')
      .prop('disabled', false)
      .off('click.ajaxPrevious')
      .on('click.ajaxPrevious', function() {
        if (typeof jQuery.ajaxPrevious === 'function') {
          jQuery.ajaxPrevious(0);
        }
      });
  });

  // Enhanced focus management
  const $firstInput = $container.find('input[type="text"]:first');
  if ($firstInput.length) {
    requestAnimationFrame(() => $firstInput.focus());
  }
  
  const $mainForm = $container.find('#mainForm');
  if ($mainForm.length) {
    const $firstFormInput = $mainForm.find('input, textarea').not(':submit, :button').first();
    if ($firstFormInput.length) {
      requestAnimationFrame(() => $firstFormInput.focus());
    }
  }

  return $container;
};

/**
 * Enhanced logging function with better security
 */
const log = (container, type, message) => {
  const $container = $(container);
  
  // Limit log entries for performance
  if ($container.children().length > 9) {
    $container.find('p:first').remove();
  }
  
  // Sanitize message to prevent XSS
  const sanitizedMessage = $('<div>').text(message).html();
  const validTypes = ['success', 'error', 'warning', 'info'];
  const safeType = validTypes.includes(type) ? type : 'info';
  
  const logEntry = $(`
    <p>
      <span class="badge badge-${safeType} text-capitalize text-center" 
            style="width: 50px; font-size: 10px; line-height: 16px; font-weight: bold; border-radius: 2px; display: inline-block">
        ${safeType}
      </span> 
      ${sanitizedMessage}
    </p>
  `);
  
  $container.append(logEntry);
};

/**
 * Modern window opening function with better security
 */
const openWin = (url, winName, width, height, allowScroll = false) => {
  // Validate parameters
  if (!url || typeof url !== 'string') {
    console.error('Invalid URL provided to openWin');
    return null;
  }
  
  const screenWidth = window.screen.availWidth;
  const screenHeight = window.screen.availHeight;
  
  // Calculate center position
  const xPos = Math.max(0, (screenWidth - width) / 2);
  const yPos = Math.max(0, (screenHeight - height) / 2);
  
  const scrollbars = allowScroll ? 'yes' : 'no';
  const features = [
    `height=${height}`,
    `width=${width}`,
    'menubar=no',
    `scrollbars=${scrollbars}`,
    'location=no',
    'toolbar=no',
    'directories=no',
    'resizable=no',
    `screenY=${yPos}`,
    `screenX=${xPos}`,
    `top=${yPos}`,
    `left=${xPos}`
  ].join(',');
  
  try {
    return window.open(url, winName, features);
  } catch (error) {
    console.error('Failed to open window:', error);
    return null;
  }
};

/**
 * Enhanced iframe content setter with cross-frame support and error handling
 */
const setIframeContent = (iframeId, url) => {
  const $ = window.jQuery || window.$ || (window.parent && window.parent.jQuery) || (window.top && window.top.jQuery);
  
  // Helper function to search in a specific document context
  const searchInDocument = (doc, $context) => {
    let element = null;
    
    // Try jQuery first if available
    if ($context && typeof $context.fn !== 'undefined') {
      try {
        let $iframe = $context(`#${iframeId}`);
        if (!$iframe.length) {
          $iframe = $context(`iframe[id="${iframeId}"]`);
        }
        if (!$iframe.length) {
          $iframe = $context('iframe').filter(function() {
            return this.id === iframeId;
          });
        }
        if ($iframe.length) {
          element = $iframe[0];
        }
      } catch (e) {
        // Silent fallback to native methods
      }
    }
    
    // Try native DOM methods
    if (!element && doc) {
      try {
        element = doc.getElementById(iframeId);
      } catch (e) {
        // Silent fallback
      }
      
      if (!element) {
        try {
          element = doc.querySelector(`#${iframeId}`);
        } catch (e) {
          // Silent fallback
        }
      }
      
      // Manual search through all iframes
      if (!element) {
        try {
          const allIframes = doc.querySelectorAll('iframe');
          for (let iframe of allIframes) {
            if (iframe.id === iframeId) {
              element = iframe;
              break;
            }
          }
        } catch (e) {
          // Silent fallback
        }
      }
    }
    
    return element;
  };
  
  // Try to find iframe in multiple document contexts
  let iframeElement = null;
  
  // Context 1: Current window
  iframeElement = searchInDocument(document, $);
  
  // Context 2: Parent window (if different)
  if (!iframeElement && window.parent && window.parent !== window) {
    try {
      const parent$ = window.parent.jQuery || window.parent.$;
      iframeElement = searchInDocument(window.parent.document, parent$);
    } catch (e) {
      // Cross-frame access may be restricted
    }
  }
  
  // Context 3: Top window (if different)
  if (!iframeElement && window.top && window.top !== window && window.top !== window.parent) {
    try {
      const top$ = window.top.jQuery || window.top.$;
      iframeElement = searchInDocument(window.top.document, top$);
    } catch (e) {
      // Cross-frame access may be restricted
    }
  }
  
  if (!iframeElement) {
    return null;
  }
  
  if (iframeElement.tagName !== 'IFRAME') {
    return null;
  }
  
  try {
    // Check if URL is the same and force reload if needed
    if (iframeElement.src === url) {
      // Force reload by clearing src first
      iframeElement.src = 'about:blank';
      setTimeout(() => {
        iframeElement.src = url;
      }, 50);
    } else {
      // Direct assignment for different URL
      iframeElement.src = url;
    }
    
    return $ ? $([iframeElement]) : iframeElement;
  } catch (error) {
    return null;
  }
};

/**
 * Force reload iframe content - utility function for stubborn iframes
 */
const reloadIframe = (iframeId) => {
  const $ = window.jQuery || window.$;
  
  // Try to find iframe
  let iframeElement = null;
  
  if ($ && typeof $.fn !== 'undefined') {
    const $iframe = $(`#${iframeId}`);
    if ($iframe.length) {
      iframeElement = $iframe[0];
    }
  }
  
  if (!iframeElement) {
    iframeElement = document.getElementById(iframeId);
  }
  
  if (!iframeElement || iframeElement.tagName !== 'IFRAME') {
    console.warn(`[reloadIframe] Iframe '${iframeId}' not found`);
    return null;
  }
  
  const currentSrc = iframeElement.src;
  
  try {
    // Try contentWindow reload first (fastest)
    if (iframeElement.contentWindow) {
      iframeElement.contentWindow.location.reload(true);
      return $ ? $([iframeElement]) : iframeElement;
    }
  } catch (error) {
    // Fall back to src manipulation
  }
  
  // Force reload by manipulating src with timestamp
  iframeElement.src = 'about:blank';
  setTimeout(() => {
    const separator = currentSrc.includes('?') ? '&' : '?';
    const timestampedUrl = `${currentSrc}${separator}_t=${Date.now()}`;
    iframeElement.src = timestampedUrl;
  }, 50);
  
  return $ ? $([iframeElement]) : iframeElement;
};

/**
 * Modern table row visibility controls
 */
const TableRowManager = {
  hiddenTables: new Set(),
  
  hideRows(tableId, startRow = 0) {
    const $table = $(`#${CSS.escape(tableId)}`);
    const $rows = $table.find('.divRow');
    
    $rows.each((index, element) => {
      if (index >= startRow) {
        element.style.display = 'none';
      }
    });
    
    this.hiddenTables.add(tableId);
  },
  
  showRows(tableId) {
    const $table = $(`#${CSS.escape(tableId)}`);
    $table.find('.divRow').slideDown();
    this.hiddenTables.delete(tableId);
  },
  
  toggleRows(tableId, startRow, button, hideText, showText) {
    const $button = $(button);
    
    if ($button.hasClass('hideButton')) {
      this.hideRows(tableId, startRow);
      $button.removeClass('hideButton').val(hideText);
    } else {
      this.showRows(tableId);
      $button.addClass('hideButton').val(showText);
    }
  }
};

// Legacy support
const hiddenTables = TableRowManager.hiddenTables;
const hideRows = (tableId, startRow) => TableRowManager.hideRows(tableId, startRow);
const showRows = (tableId) => TableRowManager.showRows(tableId);
const showHideTableRows = (tableId, startRow, button, hideText, showText) => 
  TableRowManager.toggleRows(tableId, startRow, button, hideText, showText);

/**
 * Modern filter functionality with enhanced security
 */
const urls = new URLSearchParams(location.search);

const filter = (clear = false) => {
  const filterData = {};
  const $filterForm = $('#search-filter');
  
  // Collect filter data
  $filterForm.serializeArray().forEach(item => {
    if (clear) {
      // Clear all except current item
      Object.keys(filterData).forEach(key => delete filterData[key]);
      filterData[item.name] = item.value;
      return;
    }
    
    // Clean up existing URL parameters
    const keyName = item.name.split('[')[0];
    if (urls.has(keyName)) {
      urls.delete(keyName);
    }
    
    filterData[item.name] = item.value;
  });
  
  // Update URL parameters
  try {
    const filterJson = JSON.stringify(filterData);
    if (urls.has('filter')) {
      urls.set('filter', filterJson);
    } else {
      urls.append('filter', filterJson);
    }
    
    // Navigate to filtered URL
    const newUrl = `${window.location.pathname}?${urls.toString()}`;
    window.location.href = newUrl;
  } catch (error) {
    console.error('Filter error:', error);
  }
};

/**
 * Enhanced back to list functionality
 */
const backToList = (container = '#mainContent') => {
  const $container = $(container);
  const $form = $container.find('form[method="POST"]');
  
  if (!$form.length) return false;
  
  const formAction = $form.attr('action');
  if (!formAction) return false;
  
  try {
    const [baseUrl, queryString] = formAction.split('?');
    
    if (!queryString) {
      if (typeof $container.simbioAJAX === 'function') {
        $container.simbioAJAX(baseUrl);
      }
      return false;
    }
    
    const params = new URLSearchParams(queryString);
    if (params.has('action')) {
      params.delete('action');
      const newUrl = queryString ? `${baseUrl}?${params.toString()}` : baseUrl;
      
      if (typeof $container.simbioAJAX === 'function') {
        $container.simbioAJAX(newUrl);
      }
    }
  } catch (error) {
    console.error('Back to list error:', error);
  }
};

/**
 * Modern DOM Ready Event Handler with Enhanced Performance
 */
$(() => {
  const $container = $('#mainContent, #pageContent, #sidepan, .ajaxRegister');

  // Enhanced link click handler with better security
  $container.on('click.modernAjax', 'a', function(evt) {
    const $anchor = $(this);
    
    // Skip non-AJAX links
    if ($anchor.hasClass('notAJAX') || 
        $anchor.closest('.cke, .mceEditor, .chzn-container').length ||
        $anchor.hasClass('sf-dump-toggle') || 
        $anchor.hasClass('sf-dump-str-toggle')) {
      return true;
    }
    
    // Check target attribute
    const target = $anchor.attr('target');
    if (target && target !== '_self') {
      return true;
    }
    
    evt.preventDefault();
    
    const href = $anchor.attr('href');
    if (!href || href === '#') return false;
    
    // Enhanced submenu handling
    if ($anchor.hasClass('subMenuItem')) {
      $('.subMenuItem').removeClass('curModuleLink');
      $anchor.addClass('curModuleLink');
    }
    
    // Determine AJAX container
    const loadContainer = $anchor.attr('loadcontainer');
    const $ajaxContainer = loadContainer ? $(`#${CSS.escape(loadContainer)}`) : $('#mainContent, #pageContent');
    
    // Enhanced AJAX loading
    const postData = $anchor.attr('postdata');
    const ajaxOptions = postData 
      ? { method: 'post', addData: postData }
      : { addData: { ajaxload: 1 } };
    
    if (typeof $ajaxContainer.simbioAJAX === 'function') {
      $ajaxContainer.simbioAJAX(href, ajaxOptions);
    }
    
    return false;
  });

  // Enhanced form submission handler
  $container.on('submit.modernAjax', '.menuBox form:not(.notAJAX), .submitViaAJAX', function(evt) {
    const $form = $(this);
    
    // Check if form has target attribute
    if ($form.attr('target')) {
      return true; // Let it submit normally
    }
    
    evt.preventDefault();
    
    const formAction = $form.attr('action');
    const formMethod = $form.attr('method') || 'GET';
    const formData = $form.serialize();
    
    // Determine container
    const loadContainer = $form.attr('loadcontainer');
    const $ajaxContainer = loadContainer ? $(`#${CSS.escape(loadContainer)}`) : $('#mainContent, #pageContent');
    
    if (typeof $ajaxContainer.simbioAJAX === 'function') {
      $ajaxContainer.simbioAJAX(formAction, {
        method: formMethod,
        addData: formData
      });
    }
    
    return false;
  });

  // Enhanced edit form link handler
  $container.on('click.editForm', '.editFormLink', function(evt) {
    evt.preventDefault();
    
    const $form = $(this).closest('form');
    $form.enableForm();
    
    // Focus first input
    const $firstInput = $form.find('input, textarea').not(':submit, :button').first();
    if ($firstInput.length) {
      requestAnimationFrame(() => $firstInput.focus());
    }
    
    // Show hidden elements
    $('.makeHidden').removeClass('makeHidden');
    
    // Enable delete form
    const $deleteForm = $container.find('#deleteForm');
    if ($deleteForm.length) {
      $deleteForm.enableForm();
    }
    
    // Handle select2 elements
    $container.find('.select2').prop('disabled', false).trigger('liszt:updated');
  });

  // Enhanced tab handler
  $container.on('click.tabs', 'a.tab', function(evt) {
    evt.preventDefault();
    
    const $tab = $(this);
    const $parentContainer = $tab.closest('ul');
    
    $parentContainer.find('li').removeClass('active');
    $tab.parent().addClass('active');
  });

  // Enhanced AJAX loaded event handler
  $container.not('#sidepan').on('simbioAJAXloaded', function() {
    const $loaded = $(this);
    
    // Register admin events
    $loaded.registerAdminEvents();
    
    // Enhanced filter form setup
    const $filterForm = $('#filterForm');
    if ($filterForm.length) {
      const $hiddenRows = $filterForm.find('.divRow:gt(0)');
      if ($hiddenRows.length) {
        $hiddenRows.wrapAll('<div class="hiddenFilter"></div>');
        const $hiddenFilter = $('.hiddenFilter').hide();
        
        $('[name="moreFilter"]').off('click.moreFilter').on('click.moreFilter', function(evt) {
          evt.preventDefault();
          $hiddenFilter.toggle('fast');
        });
      }
    }
    
    // Enhanced tooltips
    if ($.fn.tooltipsy) {
      $('input[title], textarea[title]').tooltipsy({
        offset: [-10, 0],
        show: function(e, $el) {
          const leftPos = parseInt($el[0].style.left.replace(/[a-z]/g, ''), 10) || 0;
          $el.css({
            left: `${leftPos - 50}px`,
            opacity: 0,
            display: 'block'
          }).animate({
            left: `${leftPos + 0}px`,
            opacity: 1
          }, 300);
        },
        hide: function(e, $el) {
          $el.slideUp(100);
        }
      });
    }

    // Enhanced Select2 initialization
    $('.select2').each(function() {
      const $select = $(this);
      const ajaxHandler = $select.data('src');
      
      if (ajaxHandler) {
        const dataSourceTable = $select.data('src-table');
        const dataSourceCols = $select.data('src-cols');
        
        if ($.fn.ajaxChosen) {
          $select.ajaxChosen({
            jsonTermKey: 'keywords',
            type: 'POST',
            url: ajaxHandler,
            data: {
              tableName: dataSourceTable,
              tableFields: dataSourceCols
            },
            dataType: 'json',
            contentType: 'application/json'
          }, function(data) {
            return data.map(item => ({
              value: item.id,
              text: item.text
            }));
          });
        }
      } else if ($.fn.chosen) {
        $select.chosen();
      }
    });
  });

  // Handle disabled forms
  $('form.disabled').disableForm();

  // Enhanced colorbox popup handler
  $('body').on('click.colorbox', 'a.openPopUp', function(evt) {
    evt.preventDefault();
    
    const $popUpButton = $(this);
    const href = $popUpButton.attr('href');
    
    if (!href || href === '#') return false;
    
    const isIframe = !$popUpButton.hasClass('notIframe');
    const width = parseInt($popUpButton.attr('width'), 10) || 600;
    const height = parseInt($popUpButton.attr('height'), 10) || 300;
    const title = $popUpButton.attr('title') || '';
    
    if ($.colorbox) {
      top.jQuery.colorbox({
        iframe: isIframe,
        href: href,
        innerWidth: width,
        innerHeight: height,
        title: title
      });
    }
  });

  // Enhanced voice search with better error handling
  const startDictation = () => {
    if (!('webkitSpeechRecognition' in window)) {
      console.warn('Speech recognition not supported');
      return false;
    }
    
    try {
      const recognition = new window.webkitSpeechRecognition();
      recognition.continuous = false;
      recognition.interimResults = false;
      recognition.lang = 'en-US';
      
      recognition.onresult = (event) => {
        const transcript = event.results[0]?.[0]?.transcript;
        if (transcript) {
          const transcriptField = document.getElementById('transcript');
          if (transcriptField) {
            transcriptField.value = transcript;
            recognition.stop();
            const labnolForm = document.getElementById('labnol');
            if (labnolForm) {
              labnolForm.submit();
            }
          }
        }
      };
      
      recognition.onerror = (event) => {
        console.warn('Speech recognition error:', event.error);
        recognition.stop();
      };
      
      recognition.start();
    } catch (error) {
      console.error('Speech recognition failed:', error);
    }
  };

  // Enhanced keyword speech handler
  $('#keyword').on('webkitspeechchange', function() {
    $(this).closest('form').submit();
  });

  // Register loader if available
  if (typeof $.fn.registerLoader === 'function') {
    $(document).registerLoader();
  }

  // Enhanced filter form submission
  $('#search-filter').on('submit.filter', function(evt) {
    evt.preventDefault();
    filter();
  });

  // Enhanced range slider with better error handling
  if ($.fn.ionRangeSlider) {
    const initializeRangeSlider = () => {
      const $inputSlider = $('.input-slider');
      if (!$inputSlider.length) return false;
      
      const $inputFrom = $('.js-input-from');
      const $inputTo = $('.js-input-to');
      
      const min = $inputSlider.data('min') || 0;
      const max = $inputSlider.data('max') || 100;
      let from = min;
      let to = max;
      
      const updateInputs = (data) => {
        from = data.from;
        to = data.to;
        $inputFrom.val(from);
        $inputTo.val(to);
      };
      
      try {
        $inputSlider.ionRangeSlider({
          onStart: updateInputs,
          onChange: updateInputs
        });
        
        const instance = $inputSlider.data('ionRangeSlider');
        
        if (instance) {
          $inputFrom.on('input.rangeSlider', function() {
            let val = parseInt($(this).val(), 10) || min;
            val = Math.max(min, Math.min(val, to));
            instance.update({ from: val });
          });
          
          $inputTo.on('input.rangeSlider', function() {
            let val = parseInt($(this).val(), 10) || max;
            val = Math.max(from, Math.min(val, max));
            instance.update({ to: val });
          });
        }
      } catch (error) {
        console.error('Range slider initialization failed:', error);
      }
    };
    
    initializeRangeSlider();
  }

  // Enhanced filter input handlers
  $('#search-filter input:not(.input-slider)').on('change.filter', function() {
    const shouldClear = $(this).attr('clear') === 'true';
    filter(shouldClear);
  });

  $('#search-order').on('change.sort', function() {
    $('#sort').val($(this).val());
    filter();
  });

  // Enhanced easter egg with better security
  let keydownCount = 0;
  
  $(document).on('keydown.easter', function(event) {
    if (event.which === 17) { // Ctrl key
      const $homeMenu = $('.menu.home');
      
      $homeMenu.off('click.easter').on('click.easter', function(evt) {
        evt.preventDefault();
        
        if (keydownCount < 1) {
          try {
            const currentUrl = window.location.href;
            const adminIndex = currentUrl.indexOf('/admin');
            
            if (adminIndex !== -1) {
              const baseUrl = currentUrl.substring(0, adminIndex);
              window.open(baseUrl, '_blank', 'noopener,noreferrer');
              keydownCount++;
            }
          } catch (error) {
            console.error('Easter egg failed:', error);
          }
        }
      });
    }
  });

  $(document).on('keyup.easter', function() {
    keydownCount = 0;
  });

  // Make startDictation globally available
  window.startDictation = startDictation;
});

/**
 * =============================================================================
 * GLOBAL FUNCTION ASSIGNMENTS FOR BACKWARD COMPATIBILITY
 * =============================================================================
 * These functions need to be available globally for legacy code compatibility
 */

// Make all utility functions globally available
window.setIframeContent = setIframeContent;
window.reloadIframe = reloadIframe;
window.openWin = openWin;
window.log = log;
window.hideRows = hideRows;
window.showRows = showRows;
window.showHideTableRows = showHideTableRows;
window.filter = filter;
window.backToList = backToList;

// Also assign to parent for iframe compatibility
if (window.parent && window.parent !== window) {
  try {
    window.parent.setIframeContent = setIframeContent;
    window.parent.reloadIframe = reloadIframe;
    window.parent.openWin = openWin;
    window.parent.log = log;
    window.parent.hideRows = hideRows;
    window.parent.showRows = showRows;
    window.parent.showHideTableRows = showHideTableRows;
    window.parent.filter = filter;
    window.parent.backToList = backToList;
  } catch (error) {
    // Ignore cross-origin errors
    console.warn('Cannot assign functions to parent window (cross-origin restriction)');
  }
}

/**
 * =============================================================================
 * IFRAME AUTO-RESIZE FUNCTIONALITY
 * =============================================================================
 */

/**
 * Auto-resize iframe manager for dynamic content height
 */
const IframeAutoResize = {
  // Configuration options
  config: {
    minHeight: 80,
    maxHeight: 400,
    padding: 10, // Reduced padding
    resizeDelay: 500, // Increased delay to avoid premature resizing
    observerOptions: {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['style', 'class']
    }
  },

  // Active resize observers
  observers: new Map(),

  /**
   * Initialize auto-resize for iframe with specific class
   * @param {string} selector - CSS selector for iframes
   */
  init: function(selector = '.auto-resize-iframe') {
    const iframes = document.querySelectorAll(selector);
    
    iframes.forEach(iframe => {
      this.setupIframe(iframe);
    });
  },

  /**
   * Setup individual iframe for auto-resize
   * @param {HTMLIFrameElement} iframe - The iframe element
   */
  setupIframe: function(iframe) {
    if (!iframe || iframe.tagName !== 'IFRAME') return false;

    // Get custom min/max heights from style or use defaults
    const style = window.getComputedStyle(iframe);
    const minHeight = this.parseHeight(style.minHeight) || this.config.minHeight;
    const maxHeight = this.parseHeight(style.maxHeight) || this.config.maxHeight;

    // Set initial height to minimum to avoid jumping
    iframe.style.height = minHeight + 'px';

    // Wait for iframe to load
    const handleLoad = () => {
      try {
        // Check if we can access iframe content (same-origin)
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        
        if (iframeDoc) {
          // Wait a bit more for content to be rendered
          setTimeout(() => {
            this.resizeIframe(iframe, minHeight, maxHeight);
            this.observeContent(iframe, minHeight, maxHeight);
          }, 200);
        }
      } catch (error) {
        // Cross-origin iframe - use postMessage if needed
        console.warn('Cannot auto-resize cross-origin iframe:', iframe.name || iframe.id);
      }
    };

    // Handle different loading states
    if (iframe.contentDocument) {
      const doc = iframe.contentDocument;
      if (doc.readyState === 'complete') {
        handleLoad();
      } else {
        doc.addEventListener('DOMContentLoaded', handleLoad);
        iframe.addEventListener('load', handleLoad);
      }
    } else {
      iframe.addEventListener('load', handleLoad);
    }
  },

  /**
   * Parse height value from CSS
   * @param {string} heightStr - CSS height value
   * @returns {number|null} Height in pixels
   */
  parseHeight: function(heightStr) {
    if (!heightStr || heightStr === 'auto' || heightStr === 'none') return null;
    const match = heightStr.match(/^(\d+(?:\.\d+)?)px$/);
    return match ? parseFloat(match[1]) : null;
  },

  /**
   * Resize iframe based on content
   * @param {HTMLIFrameElement} iframe - The iframe element
   * @param {number} minHeight - Minimum height
   * @param {number} maxHeight - Maximum height
   */
  resizeIframe: function(iframe, minHeight, maxHeight) {
    try {
      const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
      if (!iframeDoc) return false;

      // Get content height
      const body = iframeDoc.body;
      const html = iframeDoc.documentElement;
      
      if (!body) return false;

      // Wait for content to be fully loaded
      if (iframeDoc.readyState !== 'complete') {
        setTimeout(() => this.resizeIframe(iframe, minHeight, maxHeight), 100);
        return false;
      }

      // Check if there's actual content
      const hasContent = body.children.length > 0 || 
                        (body.textContent && body.textContent.trim().length > 0) ||
                        body.innerHTML.trim().length > 0;

      // If no content, use minimum height
      if (!hasContent) {
        const currentHeight = iframe.offsetHeight;
        if (currentHeight !== minHeight) {
          iframe.style.height = minHeight + 'px';
        }
        return false;
      }

      // Calculate actual content height more accurately
      let contentHeight = 0;
      
      // Method 1: Use body scrollHeight (most reliable for content)
      if (body.scrollHeight > 0) {
        contentHeight = body.scrollHeight;
      }
      
      // Method 2: Calculate from children if scrollHeight seems wrong
      if (contentHeight < 20 && body.children.length > 0) {
        let totalChildHeight = 0;
        Array.from(body.children).forEach(child => {
          const rect = child.getBoundingClientRect();
          const styles = window.getComputedStyle(child);
          const marginTop = parseFloat(styles.marginTop) || 0;
          const marginBottom = parseFloat(styles.marginBottom) || 0;
          totalChildHeight += rect.height + marginTop + marginBottom;
        });
        if (totalChildHeight > contentHeight) {
          contentHeight = totalChildHeight;
        }
      }

      // Method 3: Fallback to body offsetHeight
      if (contentHeight < 20) {
        contentHeight = Math.max(body.offsetHeight, html.clientHeight);
      }

      // Add padding only if we have real content
      const paddingToAdd = contentHeight > 20 ? this.config.padding : 0;
      
      // Apply min/max constraints
      const newHeight = Math.min(Math.max(contentHeight + paddingToAdd, minHeight), maxHeight);
      
      // Only update if height changed significantly (avoid flickering)
      const currentHeight = iframe.offsetHeight;
      if (Math.abs(newHeight - currentHeight) > 5) {
        iframe.style.height = newHeight + 'px';
        
        // Debug logging
        // console.log(`Iframe resize: ${iframe.id || 'unnamed'} - Content: ${contentHeight}px, New Height: ${newHeight}px, Has Content: ${hasContent}`);
        
        // Trigger custom event for other scripts
        iframe.dispatchEvent(new CustomEvent('iframeResized', {
          detail: { 
            oldHeight: currentHeight, 
            newHeight: newHeight,
            contentHeight: contentHeight,
            hasContent: hasContent
          }
        }));
      }
    } catch (error) {
      console.warn('Error resizing iframe:', error);
    }
  },

  /**
   * Observe content changes in iframe
   * @param {HTMLIFrameElement} iframe - The iframe element
   * @param {number} minHeight - Minimum height
   * @param {number} maxHeight - Maximum height
   */
  observeContent: function(iframe, minHeight, maxHeight) {
    try {
      const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
      if (!iframeDoc || !iframeDoc.body) return false;

      // Remove existing observer
      if (this.observers.has(iframe)) {
        this.observers.get(iframe).disconnect();
      }

      // Create debounced resize function
      const debouncedResize = this.debounce(() => {
        // Only resize if document is complete and has been stable
        if (iframeDoc.readyState === 'complete') {
          this.resizeIframe(iframe, minHeight, maxHeight);
        }
      }, this.config.resizeDelay);

      // Create mutation observer with more specific targeting
      const observer = new MutationObserver((mutations) => {
        // Check if mutations actually affect layout
        const hasLayoutMutation = mutations.some(mutation => {
          // Only trigger on significant changes
          return (
            mutation.type === 'childList' && (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0)
          ) || (
            mutation.type === 'attributes' && 
            ['style', 'class', 'width', 'height'].includes(mutation.attributeName)
          );
        });

        if (hasLayoutMutation) {
          debouncedResize();
        }
      });
      
      // Start observing with more specific options
      observer.observe(iframeDoc.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['style', 'class', 'width', 'height']
      });
      this.observers.set(iframe, observer);

      // Also observe window resize (but debounced)
      const resizeHandler = this.debounce(() => {
        this.resizeIframe(iframe, minHeight, maxHeight);
      }, this.config.resizeDelay);
      
      iframe.contentWindow.addEventListener('resize', resizeHandler);

    } catch (error) {
      console.warn('Error setting up iframe content observer:', error);
    }
  },

  /**
   * Debounce function to limit resize frequency
   * @param {Function} func - Function to debounce
   * @param {number} wait - Wait time in milliseconds
   * @returns {Function} Debounced function
   */
  debounce: function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  /**
   * Cleanup observer for iframe
   * @param {HTMLIFrameElement} iframe - The iframe element
   */
  cleanup: function(iframe) {
    if (this.observers.has(iframe)) {
      this.observers.get(iframe).disconnect();
      this.observers.delete(iframe);
    }
  },

  /**
   * Cleanup all observers
   */
  cleanupAll: function() {
    this.observers.forEach(observer => observer.disconnect());
    this.observers.clear();
  }
};

// Auto-initialize when DOM is ready
$(() => {
  // Initialize auto-resize for existing iframes
  IframeAutoResize.init();

  // Handle dynamically added iframes
  const mainObserver = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (node.nodeType === Node.ELEMENT_NODE) {
          // Check if the added node is an auto-resize iframe
          if (node.matches && node.matches('.auto-resize-iframe')) {
            IframeAutoResize.setupIframe(node);
          }
          
          // Check for auto-resize iframes within the added node
          const iframes = node.querySelectorAll && node.querySelectorAll('.auto-resize-iframe');
          if (iframes) {
            iframes.forEach(iframe => IframeAutoResize.setupIframe(iframe));
          }
        }
      });
    });
  });

  // Start observing document for new iframes
  mainObserver.observe(document.body, {
    childList: true,
    subtree: true
  });

  // Cleanup on page unload
  window.addEventListener('beforeunload', () => {
    IframeAutoResize.cleanupAll();
  });
});

// Make IframeAutoResize globally available
window.IframeAutoResize = IframeAutoResize;

// Also assign to parent for iframe compatibility
if (window.parent && window.parent !== window) {
  try {
    window.parent.IframeAutoResize = IframeAutoResize;
  } catch (error) {
    // Ignore cross-origin errors
    console.warn('Cannot assign IframeAutoResize to parent window (cross-origin restriction)');
  }
}

/**
 * =============================================================================
 * MODERNIZATION IMPROVEMENTS SUMMARY
 * =============================================================================
 * 
 * 1. ES6+ Features:
 *    - Arrow functions for better `this` binding
 *    - const/let instead of var
 *    - Template literals for string interpolation
 *    - Destructuring assignments
 *    - Spread operator for object merging
 * 
 * 2. Performance Enhancements:
 *    - Event delegation instead of individual handlers
 *    - requestAnimationFrame for smooth UI updates
 *    - More efficient DOM queries with CSS.escape()
 *    - Reduced jQuery dependency where possible
 *    - Better caching of jQuery objects
 * 
 * 3. Security Improvements:
 *    - Input sanitization to prevent XSS
 *    - URL validation for window.open
 *    - Safe parameter handling in URLSearchParams
 *    - noopener,noreferrer for external links
 *    - Proper error handling with try-catch blocks
 * 
 * 4. Code Quality:
 *    - Strict mode enabled
 *    - Better error handling and logging
 *    - Consistent naming conventions
 *    - Proper JSDoc documentation
 *    - Separation of concerns
 * 
 * 5. Maintainability:
 *    - Modular structure with clear functions
 *    - Better organization of related functionality
 *    - Consistent event naming with namespaces
 *    - Legacy support maintained for backward compatibility
 * 
 * 6. Browser Compatibility:
 *    - Feature detection instead of browser sniffing
 *    - Graceful degradation for unsupported features
 *    - Modern API usage with fallbacks
 * 
 * 7. Global Compatibility:
 *    - All legacy functions remain accessible globally
 *    - Parent window compatibility for iframe usage
 *    - Backward compatibility with existing SLiMS modules
 * 
 * All original functionality has been preserved while significantly improving
 * performance, security, and maintainability.
 * =============================================================================
 */
