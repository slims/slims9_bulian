/**
* Arie Nugraha 2009
* this file need prototype. js
* library to works
*
* Modification of phpMyAdmin's calendar library
*/

var calendarPop;
var dateField;
var calContainer;
var dateType = 'datetime';
var day;
var month;
var year;
var hour;
var minute;
var second;
var month_names = new Array('January', 'February', 'March', 'April',
  'May', 'June', 'July', 'August', 'September',
  'October', 'November', 'December');
var day_names = new Array('Sun', 'Mon', 'Tue', 'Wed',
  'Thu', 'Fri', 'Sat');

/**
 * Open Calendar Window
 */
function openCalendar(strDatefieldID) {
  if (calendarPop) {
    calendarPop.show();
  } else {
    // inject calendar container to body
    $(document.body).append('<div style="width: 300px;" id="calendarPop">'
      + '<div style="float: left; width: 70%">Calendar</div>'
      + '<div style="float: right; width: 20%; text-align: right;">'
      + '<a style="color: red; font-weight: bold; cursor: pointer;" onclick="calendarPop.hide()">Close</a>'
      + '</div>'
      + '<div id="calendarContainer">&nbsp;</div>'
      + '<div id="clockContainer">&nbsp;</div>'
      + '</div>');
    // positionize
    calendarPop = $('#calendarPop');
  }
  // date input field
  dateField = $('#'+strDatefieldID);
  // get date input position
  var dateFieldPos = dateField.offset();
  calendarPop.css({'position': 'absolute', 'left': (dateFieldPos.left-2)+'px', 'top': (dateFieldPos.top-2)+'px'});
  // reset all time value
  day = 0; month = 0; year = 0;
  // initialize calendar
  initCalendar();
}

/**
 * Formats number to two digits.
 *
 * @param   int number to format.
 * @param   string type of number
 */
var formatNum2 = function(i, valtype) {
  f = (i < 10 ? '0' : '') + i;
  if (valtype && valtype != '') {
    switch(valtype) {
      case 'month':
        f = (f > 12 ? 12 : f);
        break;
      case 'day':
        f = (f > 31 ? 31 : f);
        break;
      case 'hour':
        f = (f > 24 ? 24 : f);
        break;
      default:
      case 'second':
      case 'minute':
        f = (f > 59 ? 59 : f);
        break;
    }
  }
  return f;
}

/**
 * Formats number to two digits.
 *
 * @param   int number to format.
 * @param   int default value
 * @param   string type of number
 */
var formatNum2d = function(i, default_v, valtype) {
  i = parseInt(i, 10);
  if (isNaN(i)) return default_v;
  return formatNum2(i, valtype)
}

/**
 * Formats number to four digits.
 *
 * @param   int number to format.
 */
var formatNum4 = function(i) {
  i = parseInt(i, 10)
  return (i < 1000 ? i < 100 ? i < 10 ? '000' : '00' : '0' : '') + i;
}

/**
 * open calendar window.
 *
 */
var initCalendar = function() {
  var dateFieldValue = dateField.val();
  /* Called for first time */
  if (!year && !month && !day) {
    if (dateFieldValue) {
      if (dateType == 'datetime' || dateType == 'date') {
        if (dateType == 'datetime') {
          var parts = dateFieldValue.split(' ');
          dateFieldValue = parts[0];
          if (parts[1]) {
            time  = parts[1].split(':');
            hour  = parseInt(time[0],10);
            minute  = parseInt(time[1],10);
            second  = parseInt(time[2],10);
          }
        }
        date   = dateFieldValue.split("-");
        day  = parseInt(date[2],10);
        month  = parseInt(date[1],10) - 1;
        year   = parseInt(date[0],10);
      } else {
        year   = parseInt(dateFieldValue.substr(0,4),10);
        month  = parseInt(dateFieldValue.substr(4,2),10) - 1;
        day  = parseInt(dateFieldValue.substr(6,2),10);
        hour   = parseInt(dateFieldValue.substr(8,2),10);
        minute = parseInt(dateFieldValue.substr(10,2),10);
        second = parseInt(dateFieldValue.substr(12,2),10);
      }
    }
    if (isNaN(year) || isNaN(month) || isNaN(day) || day == 0) {
      dt    = new Date();
      year  = dt.getFullYear();
      month   = dt.getMonth();
      day   = dt.getDate();
    }
    if (isNaN(hour) || isNaN(minute) || isNaN(second)) {
      dt    = new Date();
      hour  = dt.getHours();
      minute  = dt.getMinutes();
      second  = dt.getSeconds();
    }
  }

  /* Moving in calendar */
  if (month > 11) {
    month = 0;
    year++;
  }
  if (month < 0) {
    month = 11;
    year--;
  }

  // calendar container
  calContainer = $('#calendarContainer');
  var strTable = ""

  //heading table
  strTable += '<table class="calendar monthyearselect"><tr><th width="50%">';
  strTable += '<form method="NONE" onsubmit="return 0">';
  strTable += '<a href="javascript:month--; initCalendar();">&laquo;</a> ';
  strTable += '<select id="select_month" name="monthsel" onchange="month = parseInt($(\'#select_month\').val()); initCalendar();">';
  for (i =0; i < 12; i++) {
    if (i == month) selected = ' selected="selected"';
    else selected = '';
    strTable += '<option value="' + i + '" ' + selected + '>' + month_names[i] + '</option>';
  }
  strTable += '</select>';
  strTable += ' <a href="javascript:month++; initCalendar();">&raquo;</a>';
  strTable += '</form>';
  strTable += '</th><th width="50%">';
  strTable += '<form method="none" onsubmit="return 0">';
  strTable += '<a href="javascript:year--; initCalendar();">&laquo;</a> ';
  strTable += '<select id="select_year" name="yearsel" onchange="year = parseInt($(\'#select_year\').val()); initCalendar();">';
  for (i = year - 25; i < year + 25; i++) {
    if (i == year) selected = ' selected="selected"';
    else selected = '';
    strTable += '<option value="' + i + '" ' + selected + '>' + i + '</option>';
  }
  strTable += '</select>';
  strTable += ' <a href="javascript:year++; initCalendar();">&raquo;</a>';
  strTable += '</form>';
  strTable += '</th></tr></table>';

  strTable += '<table class="calendar"><tr>';
  for (i = 0; i < 7; i++) {
    strTable += "<th>" + day_names[i] + "</th>";
  }
  strTable += "</tr>";

  var firstDay = new Date(year, month, 1).getDay();
  var lastDay = new Date(year, month + 1, 0).getDate();

  strTable += "<tr>";

  dayInWeek = 0;
  for (i = 0; i < firstDay; i++) {
    strTable += "<td>&nbsp;</td>";
    dayInWeek++;
  }
  for (i = 1; i <= lastDay; i++) {
    if (dayInWeek == 7) {
      strTable += "</tr><tr>";
      dayInWeek = 0;
    }

    dispmonth = 1 + month;

    if (dateType == 'datetime' || dateType == 'date') {
      actVal = "" + formatNum4(year) + "-" + formatNum2(dispmonth, 'month') + "-" + formatNum2(i, 'day');
    } else {
      actVal = "" + formatNum4(year) + formatNum2(dispmonth, 'month') + formatNum2(i, 'day');
    }
    if (i == day) {
      style = ' class="selected"';
      current_date = actVal;
    } else {
      style = '';
    }
    strTable += "<td" + style + "><a href=\"javascript:returnDate('" + actVal + "');\">" + i + "</a></td>"
    dayInWeek++;
  }
  for (i = dayInWeek; i < 7; i++) {
    strTable += "<td>&nbsp;</td>";
  }

  strTable += "</tr></table>";

  calContainer.html(strTable);

  if (dateType == 'datetime') {
    // clock
    var clockContainer = $('#clockContainer');
    strTable = '';
    init_hour = hour;
    init_minute = minute;
    init_second = second;
    strTable += '<form method="none" class="clock" onsubmit="returnDate(\'' + current_date + '\')">';
    strTable += '<input id="hour"  type="text" size="2" maxlength="2" onblur="this.value=formatNum2d(this.value, init_hour, \'hour\'); init_hour = this.value;" value="' + formatNum2(hour, 'hour') + '" />:';
    strTable += '<input id="minute"  type="text" size="2" maxlength="2" onblur="this.value=formatNum2d(this.value, init_minute, \'minute\'); init_minute = this.value;" value="' + formatNum2(minute, 'minute') + '" />:';
    strTable += '<input id="second"  type="text" size="2" maxlength="2" onblur="this.value=formatNum2d(this.value, init_second, \'second\'); init_second = this.value;" value="' + formatNum2(second, 'second') + '" />';
    strTable += '</form>';
    clockContainer.html(strTable);
  }
}

/**
 * Returns date from calendar.
 *
 * @param   string   date text
 */
function returnDate(d) {
  txt = d;
  if (dateType != 'date') {
    // need to get time
    h = parseInt($('#hour').val(),10);
    m = parseInt($('#minute').val(),10);
    s = parseInt($('#second').val(),10);
    if (dateType == 'datetime') {
      txt += ' ' + formatNum2(h, 'hour') + ':' + formatNum2(m, 'minute') + ':' + formatNum2(s, 'second');
    } else {
      // timestamp
      txt += formatNum2(h, 'hour') + formatNum2(m, 'minute') + formatNum2(s, 'second');
    }
  }

  dateField.val(txt);
  // close calendar window
  calendarPop.hide();
}
