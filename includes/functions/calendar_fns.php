<?

// Find the Number of Days in a Month
// Month is between 1 and 12
function number_of_days_in_month($year, $month)
{
  $days_in_the_month = array (31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
  if ($month != 2) return $days_in_the_month[$month - 1];
  // or Check for Leap Year (February)
  return (checkdate($month, 29, $year)) ? 29 : 28;
}

function check_valid_date($date)
{
  // Split the date into its components.
  list($year, $month, $day) = explode("-", $date);
  // 4 Digit Year Check
  if ($year < 1900 || $year > 2050) { return false; }
  // Day Check
  $days_in_month = number_of_days_in_month($year, $month);
  if ($day+0 > $days_in_month+0) { return false; }
  // otherwise...
  return true;
}

// Month is between 1 and 12
function beginning_weekday_of_the_month($year, $month)
{
  // Return Values: (0=Sunday, 1=Monday,...,6=Saturday)
  // mktime function expects month as 1-12
  return date("w", mktime(1, 0, 0, $month, 1, $year));
}

function weekday_of_the_month($date)
{
  // Split the date into its components.
  list($year, $month, $day) = explode("-", $date);
  // Return Values: (0=Sunday, 1=Monday,...,6=Saturday)
  return date("w", mktime(1, 0, 0, $month, $day, $year));
}

function weekday_occurrence($date)
{
  // Split the date into its components.
  list($year, $month, $day) = explode("-", $date);
  return floor(($day-1)/7)+1;
}

// $xst - 1=1st, 2=2nd, 3=3rd, 4=4th, etc.
// $weekday - weekday value (0=Sunday, 1=Monday,...,6=Saturday)
// Month is between 1 and 12
function xst_weekday_of_the_month($xst, $weekday, $year, $month)
{
  $days_in_month = number_of_days_in_month($year, $month);
  $beginning_weekday = beginning_weekday_of_the_month($year, $month);

  // Find 1st occurence of the specified weekday.
  $weekday_difference = $weekday - $beginning_weekday;
  // Add 7 if the weekday value is less than the starting weekday value.
  if ($weekday_difference < 0) { $weekday_difference += 7; }

  // Add the number of days, to the 1st, required to move to the specified day.
  $first_date = date('Y-m-d', mktime(1, 0, 0, $month, 1 + $weekday_difference, $year));
  // Add 7 for each week in the month (1=1st, 2=2nd, etc).
  $date = add_delta_ymd($first_date,0,0,($xst-1)*7);

  // Split the date into its components.
  list($new_year, $new_month, $new_day) = explode("-", $date);
  // If the date is beyond the current month then set $date equal to nothing.
  if ($new_month != $month) { $date = ''; }
  return $date;
}

// The date of the Sunday before the specified date.
// Returns the date in 'Y-m-d' format.
function sunday_before_date($date)
{
  // Split the date into its components.
  list($year, $month, $day) = explode("-", $date);
  // Find the current day of the week as a single digit.
  // Range from "0" (Sunday) to "6" (Saturday)
  $day_of_the_week = date("w", mktime(1, 0, 0, $month, $day, $year));
  // Subtract the day of the week for Sunday from the specified
  // day and reformat into YYYY-MM-DD format.
  return date('Y-m-d', mktime(1, 0, 0, $month, $day - $day_of_the_week, $year));
}

// The date of the Monday before the specified date.
// Returns the date in 'Y-m-d' format.
function monday_before_date($date)
{
  // Split the date into its components.
  list($year, $month, $day) = explode("-", $date);
  // Find the current day of the week as a single digit.
  // Range from "0" (Sunday) to "6" (Saturday)
  $day_of_the_week = date("w", mktime(1, 0, 0, $month, $day, $year));
  // If Sunday, subtract 6 days to get to Monday.
  if ($day_of_the_week == 0) {
    return date('Y-m-d', mktime(1, 0, 0, $month, $day - 6, $year));
  // Else If Monday, return that day.
  } elseif ($day_of_the_week == 1) {
    return date('Y-m-d', mktime(1, 0, 0, $month, $day, $year));
  // Else, subtract the day of the week to get to Sunday
  // and then add one to get to Monday.
  } else {
    return date('Y-m-d', mktime(1, 0, 0, $month, $day - $day_of_the_week + 1, $year));
  }
}

function add_delta_ymd($date, $delta_years = 0, $delta_months = 0, $delta_days = 0)
{
  // delta_years adjustment:
  // Use this to adjust for next and previous years.
  // Add the $delta_years to the current year and make the new date.

  if ($delta_years != 0) {
	// Split the date into its components.
	list($year, $month, $day) = explode("-", $date);
	// Careful to check for leap year effects!
	if ($month == 2 && $day == 29) {
		// Check the number of days in the month/year, with the day set to 1.
		$tmp_date = date("Y-m", mktime(1, 0, 0, $month, 1, $year + $delta_years));
		list($new_year, $new_month) = explode("-", $tmp_date);
		$days_in_month = number_of_days_in_month($new_year, $new_month);
		// Lower the day value if it exceeds the number of days in the new month/year.
		if ($days_in_month < $day) { $day = $days_in_month; }
		$date = $new_year . '-' . $month . '-' . $day;
    } else {
		$new_year = $year + $delta_years;
		$date = sprintf("%04d-%02d-%02d", $new_year, $month, $day);
	}
  }

  // delta_months adjustment:
  // Use this to adjust for next and previous months.
  // Note: This DOES NOT subtract 30 days!
  // Use $delta_days for that type of calculation.
  // Add the $delta_months to the current month and make the new date.

  if ($delta_months != 0) {
	// Split the date into its components.
	list($year, $month, $day) = explode("-", $date);
	// Calculate New Month and Year
	$new_year = $year;
	$new_month = $month + $delta_months;
	if ($delta_months < -840 || $delta_months > 840) { $new_month = $month; } // Bad Delta
	if ($delta_months > 0) { // Adding Months
		while ($new_month > 12) { // Adjust so $new_month is between 1 and 12.
			$new_year++;
			$new_month -= 12;
		}
	} elseif ($delta_months < 0) { // Subtracting Months
		while ($new_month < 1) { // Adjust so $new_month is between 1 and 12.
			$new_year--;
			$new_month += 12;
		}
	}
	// Careful to check for number of days in the new month!
	$days_in_month = number_of_days_in_month($new_year, $new_month);
	// Lower the day value if it exceeds the number of days in the new month/year.
	if ($days_in_month < $day) { $day = $days_in_month; }
	$date = sprintf("%04d-%02d-%02d", $new_year, $new_month, $day);
  }

  // delta_days adjustment:
  // Use this to adjust for next and previous days.
  // Add the $delta_days to the current day and make the new date.

  if ($delta_days != 0) {
	// Split the date into its components.
	list($year, $month, $day) = explode("-", $date);
	// Create New Date
	$date = date("Y-m-d", mktime(1, 0, 0, $month, $day, $year) + $delta_days*24*60*60);
  }

  // Check Valid Date, Use for TroubleShooting
  //list($year, $month, $day) = explode("-", $date);
  //$valid = checkdate($month, $day, $year);
  //if (!$valid)  return "Error, function add_delta_ymd: Could not process valid date!";

  return $date;
}

// Returns week number for the specified date,
// depending on the week numbering setting(s).
function week_number($year, $month, $day)
{
// Make Adjustment if Week Starts on Weekday Sunday.
// ISO Weeks Start on Monday. We will consider the
// Sunday before as part of the following ISO week.
  if (WEEK_START == 0) { $day++; } // Add one to get to Monday.

  $timestamp = mktime(1, 0, 0, $month, $day, $year);
  $week = "";
  $week = strftime("%V", $timestamp); // ISO Weeks start on Mondays
  if ($week == "") {
    // %V not implemented on older versions of PHP and on Win32 machines.
    $week = ISOWeek($year, $month, $day);
  }

  return $week + 0;
}

function ISOWeek($y, $m, $d)
{
  $week = strftime("%W", mktime(0, 0, 0, $m, $d, $y));
  $dow0101 = getdate(mktime(0, 0, 0, 1, 1, $y));
  $next0101 = getdate(mktime(0, 0, 0, 1, 1, $y+1));

  if ($dow0101["wday"] > 1 && $dow0101["wday"] < 5) { $week++; }
  if ($next0101["wday"] > 1 && $next0101["wday"] < 5 && $week == 53) { $week = 1; }
  if ($week == 0) { $week = ISOWeek($y-1,12,31); }

  return substr("00" . $week, -2);
}

// Return the full month name
// Month is between 1 and 12
function month_name($month)
{
  switch($month) {
	case 0: return "Month is between 1-12!";
	case 1: return "January";
	case 2: return "February";
	case 3: return "March";
	case 4: return "April";
	case 5: return "May";
	case 6: return "June";
	case 7: return "July";
	case 8: return "August";
	case 9: return "September";
	case 10: return "October";
	case 11: return "November";
	case 12: return "December";
  }
  return "unknown-month($m)";
}

// Return the abbreviated month name
// Month is between 1 and 12
function month_short_name($month)
{
  switch($month) {
	case 0: return "Month is between 1-12!";
	case 1: return "Jan";
	case 2: return "Feb";
	case 3: return "Mar";
	case 4: return "Apr";
	case 5: return "May";
	case 6: return "Jun";
	case 7: return "Jul";
	case 8: return "Aug";
	case 9: return "Sep";
	case 10: return "Oct";
	case 11: return "Nov";
	case 12: return "Dec";
  }
  return "unknown-month($m)";
}

// Return the full weekday name
// $weekday_value - weekday (0=Sunday,...,6=Saturday)
function weekday_name($weekday_value)
{
  switch($weekday_value) {
	case 0: return "Sunday";
	case 1: return "Monday";
	case 2: return "Tuesday";
	case 3: return "Wednesday";
	case 4: return "Thursday";
	case 5: return "Friday";
	case 6: return "Saturday";
  }
  return "unknown-weekday($w)";
}

// Return the abbreviated weekday name
// $weekday_value - weekday (0=Sunday,...,6=Saturday)
function weekday_short_name($weekday_value)
{
  switch($weekday_value) {
	case 0: return "Sun";
	case 1: return "Mon";
	case 2: return "Tue";
	case 3: return "Wed";
	case 4: return "Thu";
	case 5: return "Fri";
	case 6: return "Sat";
  }
  return "unknown-weekday($w)";
}

// Return the occurence name
// $occurence, 0-31
function occurence_name($occurence)
{
  if ($occurence < 0) { return "occurence must be great than zero"; }
  switch($occurence) {
	case 0: return "None";
	case 1: return "1st";
	case 2: return "2nd";
	case 3: return "3rd";
	case 21: return "21st";
	case 22: return "22nd";
	case 23: return "23rd";
	case 31: return "31st";
  }
  return $occurence."th";
}

// Return the recur interval display name
// $recur_interval - none, day, week, day-month, weekday-month, year
function recur_interval_name($recur_interval)
{
  switch($recur_interval) {
	case ''     : return 'None';
	case 'none' : return 'None';
	case 'day'  : return 'Daily';
	case 'week' : return 'Weekly';
	case 'day-month' : return 'Monthly (by day of the month)';
	case 'weekday-month' : return 'Monthly (by occuring weekday of the month)';
	case 'year' : return 'Yearly';
  }
  return 'None';
}

// Return the full weekday index array used to
// determine what day of the week to start with
// to display the month view and month nav view.
// $w - weekday (0=Sunday, 1=Monday)
function weekday_index_array ($w)
{
  switch($w) {
	case 0: return array (0,1,2,3,4,5,6); // Start with Sunday
	case 1: return array (1,2,3,4,5,6,0); // Start with Monday
  }
  return array (0,1,2,3,4,5,6); // Default - Start with Sunday
}

// Get Times in Range Function
// $min_time - minimum time '00:00' to '24:00'
// $max_time - maximum time '00:00' to '24:00'
// $time_inc - time interval in seconds.
// Recommended: 60 % $time_inc = 0
// $include_max_time (true or false)
function get_times_in_range ($min_time = "00:00", $max_time = "24:00",
			$time_inc = 30, $include_max_time = false)
{
  @list ($hour, $min) = explode(":", $min_time);
  $min_time_dec = $hour + $min/60;
  @list ($hour, $min) = explode(":", $max_time);
  $max_time_dec = $hour + $min/60;

  $time_strings = array ();
  $time_dec = $min_time_dec;
  $i = 0;
  while ($time_dec <= $max_time_dec && $i < 10000) {
	$time_string_hour = floor($time_dec);
	$time_string_min = round(($time_dec - $time_string_hour) * 60);
	$time_strings[] = sprintf("%02d:%02d", $time_string_hour, $time_string_min);
	$time_dec += $time_inc/60;
	$i++;
  }
  if (!$include_max_time && $time_strings[count($time_strings)-1] == $max_time) {
	$trash = array_pop($time_strings);
  }
  // returns array values in 24 hour format ("%02d:%02d")
  return $time_strings;
}

// Param: $date format, 'YYYY-MM-DD'
// Returns formatted date string, "January 30, 2005"
//function date_format($date)
//{
//  list($year, $month, $day) = explode("-",$date);
//  return month_name($month) . ' ' . sprintf("%d",$day) . ', ' . $year;
//}

// Param: $date format, 'YYYY-MM-DD'
// Returns formatted date string, "Jan 30, 2005"
function short_date_format($date)
{
  list($year, $month, $day) = explode("-",$date);
  return month_short_name($month) . ' ' . sprintf("%d",$day) . ', ' . $year;
}

// Param: $date format, 'YYYY-MM-DD'
// Returns formatted date string, "Thursday 30th Jan 2005"
function short_date_format_with_day_of_week( $date ) {
  list( $year, $month, $day ) = explode( '-', $date ) ;
  return date( 'l jS M Y', mktime( 11, 0, 0, $month, $day, $year ) ) ;
}

// Param: $time, 'hh:mm' format
function format_time_to_ampm($time, $add_leading_zeros = false)
{
  list ($hour, $min) = explode(":", $time);
  // To Cater for the AM PM Hour display
  if (DEFINE_AM_PM) {
	if ($hour > 12 ) { $hour = $hour - 12; $ampm = "PM";
	} elseif ($hour == 12) { $ampm="PM"; } else { $ampm="AM"; }
  }
  if ($add_leading_zeros) {
		$time = sprintf("%02d:%02d", $hour, $min) . $ampm;
  } else {
		$time = sprintf("%d:%02d", $hour, $min) . $ampm;
  }
  return $time;
}

// Calculate the number of days an event spans.
// This function assumes that the dates do exist!
// $start_date - YYYY-MM-DD
// $end_date - YYYY-MM-DD
function days_span($start_date, $end_date)
{
  list($year, $month, $day) = explode("-", $start_date);
  $start_time_stamp = mktime(1, 0, 0, $month, $day, $year);
  list($year, $month, $day) = explode("-", $end_date);
  $end_time_stamp = mktime(1, 0, 0, $month, $day, $year);
  return round(($end_time_stamp - $start_time_stamp)/(24*60*60))+1;
}

// Determine the time ranges for the spanning days
// $min_time - minimum time in 24 hr format (hh:mm)
// $max_time - maximum time in 24 hr format (hh:mm)
// $start_time - starting time in 24 hr format (hh:mm)
// $end_time - ending time in 24 hr format (hh:mm)
// $days_span - number of days to span
// This function is used to determine the time ranges for each
// day in the day span. Used of display purposes.
// Returns an array with "hh:mm-hh:mm" format (start_time-end_time).
function get_time_ranges_for_spanning_days($min_time = "00:00", $max_time = "24:00",
			$start_time, $end_time, $days_span)
{
  $time_ranges = array();
  if ($days_span > 1) {
	for ($i=1; $i<=$days_span; $i++) {
		if ($i == 1) { // first day
			$time_ranges[] = $start_time."-".$max_time;
		} elseif ($i == $days_span) { // last day
			$time_ranges[] = $min_time."-".$end_time;
		} else { // inbetween day(s)
			$time_ranges[] = $min_time."-".$max_time;
		}
	}
  } else { // one day span
	$time_ranges[] = $start_time."-".$end_time;
  }
  return $time_ranges;
}

// $event_start_date - YYYY-MM-DD
// $event_end_date - YYYY-MM-DD
// $recur_end_date - YYYY-MM-DD
// $recur_frequency - 1,2,3...10
// $recur_interval - none, day, week, day-month, weekday-month, year
function get_recurrence_dates ($event_start_date, $event_end_date,
		$recur_end_date = '', $recur_frequency = '', $recur_interval = '')
{
  $recur_interval = strtolower($recur_interval);
  //echo "<br />Start Date: ".$event_start_date."<br />";
  //echo "End Date: ".$event_end_date."<br />";
  //echo "Recur Date: ".$recur_end_date."<br />";
  //echo "Interval: ".$recur_interval."<br /><br />";

  $days_span = days_span($event_start_date, $event_end_date);
  //echo "Days Span: ".$days_span."<br />";

  // For each day we will attempt to determine the
  // recurrence dates for this event.
  $recur_days = array ();
  $recur_days[] = $event_start_date;

  // Define Date "Values" for Numeric Comparison
  $recur_end_date_value = implode("", explode("-",$recur_end_date))+0;
  $event_end_date_value = implode("", explode("-",$event_end_date))+0;

  // Define Variables for Recur Monthly by Weekday Occurence of the Month
  list($start_year, $start_month, $start_day) = explode("-",$event_start_date);
  $start_weekday = weekday_of_the_month($event_start_date);
  $start_weekday_occurrence = weekday_occurrence($event_start_date);

  $recur_date_value = 0;
  $i = 1;
  if ($recur_end_date_value > $event_end_date_value &&
	$recur_interval != '' && $recur_interval != 'none' && $recur_end_date != '') {
	while ($recur_date_value < $recur_end_date_value && $i < 100) {
		// Recur Daily
		if ($recur_interval == 'day') {
			$recur_date = add_delta_ymd($event_start_date,0,0,$i*$recur_frequency);
		// Recur Weekly
		} elseif ($recur_interval == 'week') {
			$recur_date = add_delta_ymd($event_start_date,0,0,$i*7*$recur_frequency);
		// Recur Monthly by Day of the Month
		} elseif ($recur_interval == 'day-month') {
			$recur_date = add_delta_ymd($event_start_date,0,$i*$recur_frequency,0);
		// Recur Monthly by Weekday Occurence of the Month (The Difficult One!)
		} elseif ($recur_interval == 'weekday-month') {
			$new_month = $start_month+$i*$recur_frequency;
			$new_year = $start_year;
			while ($new_month > 12) { // Adjust so $new_month is between 1 and 12.
				$new_year++;
				$new_month -= 12;
			}
			$recur_date = xst_weekday_of_the_month($start_weekday_occurrence,
								$start_weekday, $new_year, $new_month );
			//echo "Weekday: ".weekday_of_the_month($event_start_date)."<br />";
			//echo "Occurence: ".weekday_occurrence($event_start_date)."<br />";
			//echo "Xst Recur Date: ".$recur_date."<br />";
			//echo "Year: $new_year, Month: $new_month<br />";
		} elseif ($recur_interval == 'year') {
			$recur_date = add_delta_ymd($event_start_date,$i,0,0);
		}
		$recur_date_value = implode("", explode("-",$recur_date))+0;
		if ($recur_date_value > $recur_end_date_value) { break; }
		if ($recur_date != '') {
			$recur_days[] = $recur_date;
		}
		$i++;
	} // end of while
  } // end of if

  // If the event spans more than 1 day then we need to
  // determine the dates of each of those days, for each recurrency.
  // We do this after, because of the last date of the month differences.
  $spanning_days = array();
  if ($days_span > 1 && $recur_interval != 'day') {
   foreach ($recur_days as $recur_day) {
	for ($days=1; $days<$days_span; $days++) {
		$spanning_days[] = add_delta_ymd($recur_day,0,0,$days);
	} // end of for $days_span
   } // end of foreach $recur_day
  }
  reset ($spanning_days);
  // Merge the two arrays together and get the unique dates.
  @ $all_days = array_merge($spanning_days, $recur_days);
  @ $all_days_assoc = array_count_values($all_days); // dates assoc with counts/freq
  @ ksort($all_days_assoc);

  return $all_days_assoc;
}


