<?

// Define Current & Selected Date Constants

// Today's Date Data

  $tmp_todays_dates = date("Y-m-d|j M Y|l F j, Y");
  list($tmp_today, $tmp_shortstr, $tmp_longstr) = explode("|", $tmp_todays_dates);
  define('TODAYS_DATE', $tmp_today);       // YYYY-MM-DD
  list($tmp_year, $tmp_month, $tmp_day) = explode("-", $tmp_today);
  define('TODAYS_DATE_YEAR', $tmp_year);    // 4 Digit
  define('TODAYS_DATE_MONTH', $tmp_month); // 2 Digit
  define('TODAYS_DATE_DAY', $tmp_day);    // 2 Digit
  define('TODAYS_DATE_SHORTSTR', $tmp_shortstr);  // 21 Mar 2003
  define('TODAYS_DATE_LONGSTR', $tmp_longstr);    // Saturday, January 25, 2003

// Selected Date Data

  @list($sel_year, $sel_month, $sel_day) = explode("-", $_REQUEST['date']);
  if (!checkdate($sel_month+0, $sel_day+0, $sel_year+0)) {
		$_REQUEST['date'] = TODAYS_DATE;
		list($sel_year, $sel_month, $sel_day) = explode("-", $_REQUEST['date']);
  }
  if (strlen($sel_year) == 2 && $sel_year <= 69) { $sel_year += 2000; }
  define('SELECTED_DATE_YEAR', sprintf("%04d", $sel_year));
  define('SELECTED_DATE_MONTH', sprintf("%02d", $sel_month));
  define('SELECTED_DATE_DAY', sprintf("%02d", $sel_day));
  define('SELECTED_DATE', SELECTED_DATE_YEAR . '-' . SELECTED_DATE_MONTH . '-' . SELECTED_DATE_DAY);
  $_REQUEST['date'] = SELECTED_DATE;
  $tmp_todays_dates = date("j M Y|l F j, Y", mktime(1, 0, 0, SELECTED_DATE_MONTH, SELECTED_DATE_DAY, SELECTED_DATE_YEAR));
  list($tmp_shortstr, $tmp_longstr) = explode("|", $tmp_todays_dates);
  define('SELECTED_DATE_SHORTSTR', $tmp_shortstr);  // 21 Mar 2003
  define('SELECTED_DATE_LONGSTR', $tmp_longstr);    // Saturday, January 25, 2003

// Selected Date Data - Previous/Next Day, Month, & Year Data

  define('PREVIOUS_DAY_DATE', add_delta_ymd(SELECTED_DATE, 0, 0, -1));
  define('NEXT_DAY_DATE', add_delta_ymd(SELECTED_DATE, 0, 0, 1));
  define('PREVIOUS_MONTH_DATE', add_delta_ymd(SELECTED_DATE, 0, -1, 0));
  define('NEXT_MONTH_DATE', add_delta_ymd(SELECTED_DATE, 0, 1, 0));
  define('PREVIOUS_YEAR_DATE', add_delta_ymd(SELECTED_DATE, -1, 0, 0));
  define('NEXT_YEAR_DATE', add_delta_ymd(SELECTED_DATE, 1, 0, 0));

// Create the schedule table data for the selected month date (year and month).
  include_once("booking_db_fns.php");
  $res = create_date_time_schedule_data(SELECTED_DATE, $_REQUEST['loc']);

?>