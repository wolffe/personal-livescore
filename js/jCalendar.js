/*******************************************************************************
Developer    : Jyothis
Email ID     : sastrijajyothis@gmail.com
License Type : GPL (http://www.gnu.org/licenses/gpl-3.0.txt)
*******************************************************************************/
var lastDayOfPrevMonth;
var calendarFrom;
var calendarIter;
var calendarTo;
var today = createDate();
var currentMonth = createDate();
var isVisible = false;
var date_format = 'yyyy-mm-dd HH:MM:ss';
function viewCalendar(parent, destField) {
	parentObj = document.getElementById(parent);
	if(isVisible) {
		parentObj.style.display = 'none';
		isVisible = false;
	} else {
		parentObj.style.display = '';
		if(parentObj.innerHTML == '')
			generateCalendar(parent, destField);
		isVisible = true;
	}
}

function generateCalendar(parent, destField) {
	findFromDate();
	calendarIter = calendarFrom;
	findToDate();
	generateCalendarTable(parent, destField);
}

function findFromDate() {
	var dateObj = createDate();
	dateObj.setFullYear(currentMonth.getFullYear());
	dateObj.setMonth(currentMonth.getMonth());
	dateObj.setDate(1);
	if(dateObj.getDay() > 0){
		dateObj.setDate(0);
		while(dateObj.getDay() != 0) {
			dateObj.setDate(dateObj.getDate() - 1);
		}
	}
	calendarFrom = dateObj;
}

function findToDate() {
	var dateObj = new createDate();
	//Find last day of month
	dateObj.setFullYear(currentMonth.getFullYear());
	dateObj.setMonth(currentMonth.getMonth() + 1);
	dateObj.setDate(0);
	//Set to date to saturday of that last week of month
	while(dateObj.getDay() < 6) {
		dateObj.setDate(dateObj.getDate() + 1);
	}
	calendarTo = dateObj;
}

function generateCalendarTable(parent, destField) {
	var parentObj = document.getElementById(parent);
	var rootTable = document.createElement('table');
	rootTable.border = '0';
	rootTable.cellPadding = '0';
	rootTable.cellSpacing = '0';
	rootTable = addCalendarHeader(rootTable, parent, destField);

	var totalDays = parseInt((calendarTo.getTime() - calendarFrom.getTime() )/(1000*60*60*24)) + 1;
	var totalWeeks = parseInt(totalDays/7);
	for(i=2;i<=totalWeeks+1;i++) {
		var tableRow = rootTable.insertRow(i);
		for(j=0;j<7;j++) {
			var cell = tableRow.insertCell(j);
			cell.innerHTML = calendarIter.getDate();
			if(calendarIter.getMonth() != currentMonth.getMonth())
				cell.className = 'jc_cell_disabled';
			else {
				var destObj = document.getElementById(destField);
				cell.onclick = function() {
					calendarIter = currentMonth;
					calendarIter.setDate(this.innerHTML);
					calendarIter.setHours(document.getElementById('jc_hour').value);
					calendarIter.setMinutes(document.getElementById('jc_minute').value);
					destObj.value = calendarIter.format(date_format);
					parentObj.style.display = 'none';
					isVisible = false;
				};
				if(j == 0 || j == 6)
					cell.className = 'jc_cell_weekend';
				else
					cell.className = 'jc_cell_weekday';
			}

			if(isCurrentDate(calendarIter,today))
                cell.className = 'jc_cell_current';
			calendarIter.setDate(calendarIter.getDate() + 1);
		}
	}
	// add time fields
	addCalendarFooter(rootTable, parent, destField);
	parentObj.appendChild(rootTable);
}

function addCalendarHeader(rootTable, parent, destField) {
	var tableTop = rootTable.insertRow(0);
	tableTop.className = 'jc_cell_title';
	var cell1 = tableTop.insertCell(0);
	cell1.innerHTML = '&raquo;';
	cell1.style.cursor = 'pointer';
	cell1.onclick= function() {
		currentMonth.setMonth(currentMonth.getMonth() + 1);
		document.getElementById(parent).innerHTML = '';
		generateCalendar(parent, destField);
	};
	var cell2 = tableTop.insertCell(0);
	cell2.colSpan = 5;
	month_names = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	cell2.innerHTML = month_names[currentMonth.getMonth()] + ' ' + currentMonth.getFullYear();
	var cell3 = tableTop.insertCell(0);
	cell3.innerHTML = '&laquo;';
	cell3.style.cursor = 'pointer';
	cell3.onclick = function() {
		currentMonth.setMonth(currentMonth.getMonth() - 1);
		document.getElementById(parent).innerHTML = '';
		generateCalendar(parent, destField);
	};

	// add day names
	var tableRow = rootTable.insertRow(1);
	day_names = new Array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
	for(i=0;i<7;i++){
		cell = tableRow.insertCell(i);
		cell.innerHTML = day_names[i];
		cell.className = 'jc_cell_title';
	}
	return rootTable;
}

function addCalendarFooter(rootTable, parent, destField) {
	var tableFooter = rootTable.insertRow(rootTable.rows.length);
	tableFooter.className = 'jc_cell_title';
	var cell1 = tableFooter.insertCell(0);
	cell1.innerHTML = '';
	var cell2 = tableFooter.insertCell(0);
	cell2.colSpan = 2;
	cell2.appendChild(getNumList(0, 59, 'jc_minute'));
	var cell3 = tableFooter.insertCell(0);
	cell3.colSpan = 2;
	cell3.appendChild(getNumList(0, 23, 'jc_hour'));
	var cell4 = tableFooter.insertCell(0);
	cell4.innerHTML = 'Time';
	var cell5 = tableFooter.insertCell(0);
	cell5.innerHTML = '';
}

function getNumList(numFrom, numTo, objId) {
	var listObj = document.createElement('select');
	listObj.id = objId;
	for(i=numFrom;i<=numTo;i++) {
		var option = document.createElement('option');
		var prefix = '';
		if(i < 10) // numFrom was wrong
			prefix = '0';
		else
			prefix = '';
		option.value = prefix + i;
		option.innerHTML = prefix + i;
		listObj.appendChild(option)
	}
	return listObj;
}

function createDate() {
    var dateObj = new Date();
    dateObj.setHours(0);
    dateObj.setMinutes(0);
    dateObj.setSeconds(0);
    dateObj.setMilliseconds(0);
    return dateObj;
}

function isCurrentDate() {
	if(calendarIter.getYear() == today.getYear() && calendarIter.getMonth() == today.getMonth() && calendarIter.getDate() == today.getDate())
		return true;
	else
		return false;
}

//------------------
/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */

var dateFormat = function () {
	var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
    timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
    timezoneClip = /[^-+\dA-Z]/g,
    pad = function (val, len) {
        val = String(val);
        len = len || 2;
        while (val.length < len) val = "0" + val;
        return val;
    };

	// Regexes and supporting functions are cached through closure
	return function (date, mask, utc) {
		var dF = dateFormat;

		// You can't provide utc if you skip other args (use the "UTC:" mask prefix)
		if(arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
			mask = date;
			date = undefined;
		}

		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date(date) : new Date;
		if(isNaN(date)) throw SyntaxError("invalid date");

		mask = String(dF.masks[mask] || mask || dF.masks["default"]);

		// Allow setting the utc argument via the mask
		if(mask.slice(0, 4) == "UTC:") {
			mask = mask.slice(4);
			utc = true;
		}

		var	_ = utc ? "getUTC" : "get",
        d = date[_ + "Date"](),
        D = date[_ + "Day"](),
        m = date[_ + "Month"](),
        y = date[_ + "FullYear"](),
        H = date[_ + "Hours"](),
        M = date[_ + "Minutes"](),
        s = date[_ + "Seconds"](),
        L = date[_ + "Milliseconds"](),
        o = utc ? 0 : date.getTimezoneOffset(),
        flags = {
            d:    d,
            dd:   pad(d),
            ddd:  dF.i18n.dayNames[D],
            dddd: dF.i18n.dayNames[D + 7],
            m:    m + 1,
            mm:   pad(m + 1),
            mmm:  dF.i18n.monthNames[m],
            mmmm: dF.i18n.monthNames[m + 12],
            yy:   String(y).slice(2),
            yyyy: y,
            h:    H % 12 || 12,
            hh:   pad(H % 12 || 12),
            H:    H,
            HH:   pad(H),
            M:    M,
            MM:   pad(M),
            s:    s,
            ss:   pad(s),
            l:    pad(L, 3),
            L:    pad(L > 99 ? Math.round(L / 10) : L),
            t:    H < 12 ? "a"  : "p",
            tt:   H < 12 ? "am" : "pm",
            T:    H < 12 ? "A"  : "P",
            TT:   H < 12 ? "AM" : "PM",
            Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
            o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
            S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
        };

        return mask.replace(token, function ($0) {
            return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
        });
    };
}();

// Some common format strings
dateFormat.masks = {
    "default":      "ddd mmm dd yyyy HH:MM:ss",
    shortDate:      "m/d/yy",
    mediumDate:     "mmm d, yyyy",
    longDate:       "mmmm d, yyyy",
    fullDate:       "dddd, mmmm d, yyyy",
    shortTime:      "h:MM TT",
    mediumTime:     "h:MM:ss TT",
    longTime:       "h:MM:ss TT Z",
    isoDate:        "yyyy-mm-dd",
    isoTime:        "HH:MM:ss",
    isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
    isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
    dayNames: [
    "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
    "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
    ],
    monthNames: [
    "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
    "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
    ]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
	return dateFormat(this, mask, utc);
};
