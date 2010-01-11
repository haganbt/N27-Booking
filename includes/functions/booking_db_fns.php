<?
require_once(DIR_WS_FUNCTIONS . 'database.php');

// $date - YYYY-MM-DD format
// $location - location name
function create_date_time_schedule_data($date, $location = DEFAULT_LOCATION_NAME)
{
  //echo "<p>create_date_time_schedule_data function: $date</p>";
  // create the date time schedule data for the specified year and month.

  global $location_db_name;
  list ($year, $month, $day) = explode("-", $date);
  // query for the date time data for the 1st of the month, limit only 1
  $result = wrap_db_query("SELECT * FROM ". DATE_TIME_SCHEDULE_TABLE. " WHERE
						" . $location_db_name[$location] . " >= 0 AND
						schedule_date_time = '" . $year . "-" . $month . "-01 " . MIN_BOOKING_HOUR . ":00:00'
						LIMIT 0,1");
  if (!defined(BOOKING_TIME_INTERVAL)) { define(BOOKING_TIME_INTERVAL, 30); }
  // If there are no result(s) then we need to create the data for this month.
  if (!(wrap_db_num_rows($result) >= 1) || !$result) {
	// Define Valid Times (hh:mm format)
	$valid_times = get_times_in_range (sprintf("%02d", MIN_BOOKING_HOUR).':00',
				sprintf("%02d", MAX_BOOKING_HOUR).':00', BOOKING_TIME_INTERVAL, false);
	// Define Starting Weekday Value (0=Sunday, 1=Monday,...,6=Saturday)
	$dayoftheweekid = beginning_weekday_of_the_month($year, $month);
	for ($i = 1; $i <= 31; $i++) {  // For Each Day in the Month.
		if (check_valid_date($year.'-'.$month.'-'.$i)) {
			//echo "Loop ID: $i<br />";
			if ($dayoftheweekid == 7) { $dayoftheweekid = 0; }
			//echo "Day of the Week ID: $dayoftheweekid <br />";
			foreach ($valid_times as $valid_time) {
				$result = wrap_db_query( "INSERT INTO " . DATE_TIME_SCHEDULE_TABLE . " 
						SET schedule_date_time = '" . $year . "-" . $month . "-" . sprintf("%02d",$i) . 
						" " . $valid_time . "', day_of_the_week_id = " . $dayoftheweekid );
			}
			$dayoftheweekid++;
		} // end of if
	} // end of for loop
	
	// Order the table if modified.
	$result = wrap_db_query( "ALTER TABLE " . DATE_TIME_SCHEDULE_TABLE . " ORDER BY schedule_date_time");
	// Optimize all of the main tables.
	$result = wrap_db_query( "OPTIMIZE TABLE " . DATE_TIME_SCHEDULE_TABLE);
	$result = wrap_db_query( "OPTIMIZE TABLE " . BOOKING_EVENT_TABLE);
	$result = wrap_db_query( "OPTIMIZE TABLE " . BOOKING_USER_TABLE);
  } // end of if
  return true;
};


// Check AVAILABILITY of the Schedule Data (Schedule Table)
// $scheduled_date_time_data is an array containing the date and
// time slots for the schedule.  SQL format: 'YYYY-MM-DD hh:mm:ss'
// $location_field_name - location's SQL column/field name.
function check_schedule_availability($scheduled_date_time_data, $location_field_name)
{
  // Check to see if schedule dates and times are available.
  $query = "SELECT COUNT(*) FROM " . DATE_TIME_SCHEDULE_TABLE . " WHERE (";
  foreach ($scheduled_date_time_data as $sql_data) {
	// First check to see if the data for the month(s) have been created yet.
	// Do this only for each unique "YYYYMM" in the date time data array.
	list ($date, $time) = explode(" ", $sql_data);
	list ($year, $month, $day) = explode("-", $date);
	$yearmonth = $year.$month;
	if ($yearmonth != $previous_yearmonth) { $result = create_date_time_schedule_data($date); }
	$previous_yearmonth = $yearmonth;
	// then...resume building the query
	$query .= "schedule_date_time = '". $sql_data . "' OR ";
  }
  $query = substr($query,0,strlen($query)-4);
  $query .= ") AND " . $location_field_name . " = 0";
  //echo $query."<br /><br />";
  $result = wrap_db_query($query);
  $db_row_values = wrap_db_fetch_array($result);
  // Return the number of available date time schedule blocks
  $availability = $db_row_values[0];
  //echo 'Schedule Availability Count: '.$availability.'<br /><br />';
  return $availability;
}


// Find UN-AVAILABILITY of the Schedule Data (Schedule Table)
// $scheduled_date_time_data is an array containing the dates and
// times for the schedule data.  SQL Date Format: 'YYYY-MM-DD hh:mm:ss'
function find_schedule_unavailability($scheduled_date_time_data, $sql_location_field_name)
{
  // Find the schedule date and time slots that are "unavailable".
  $available = true;
  $query = "SELECT schedule_date_time FROM " . DATE_TIME_SCHEDULE_TABLE . " WHERE (";
  foreach ($scheduled_date_time_data as $sql_data) {
	$query .= "schedule_date_time = '". $sql_data . "' OR ";
  }
  $query = substr($query,0,strlen($query)-4);
  $query .= ") AND " . $sql_location_field_name . " != 0";
  //echo $query."<br /><br />";
  $result = wrap_db_query($query);
  while($db_row_values = wrap_db_fetch_array($result)) {
	$schedule_date_time = $db_row_values['schedule_date_time'];
	$unavailable[] = $schedule_date_time;
  }
  // Return array of the unavailable - "schedule_date_time" field - date and time slots.
  // (SQL Date Format: 'YYYY-MM-DD hh:mm:ss')
  return $unavailable;
}


//function get_data_display_hours($date)
//{
//  // This function is rarely used.  Use 'get_times_in_range' function instead!
//  //echo "<p>Testing the get_data_display_hours function.</p>";
//  // Get the display hours for the selected day.
//  list ($year, $month, $day) = explode("-", $date);
//  $result = wrap_db_query("SELECT * FROM " . DATE_TIME_SCHEDULE_TABLE . " WHERE
//						schedule_date_time >= '" . $date . " 00:00:01' AND
//						schedule_date_time <= '" . $date . " 23:59:59'");
//  $db_row_values = array ();
//  $data_display_hours = array ();
//  while($db_row_values = wrap_db_fetch_array($result)) {
//	$data_date_time = $db_row_values['schedule_date_time'];
//	list ($data_date, $data_time) = explode(" ", $data_date_time);
//	$data_display_hours[] = $data_time;
//  }
//  return $data_display_hours;
//}



// Get the Event Data query object for the Month View
// $date - YYYY-MM-DD
// $location - Location ID
function get_month_view_event_data($date, $location = DEFAULT_LOCATION_NAME)
{
  // Get the event data for the selected month, year and location.

  global $location_db_name;
  list ($year, $month, $day) = explode("-", $date);
  $query = "SELECT *
						FROM " . DATE_TIME_SCHEDULE_TABLE . ", " . BOOKING_EVENT_TABLE . " WHERE
						" . DATE_TIME_SCHEDULE_TABLE . "." . $location_db_name[$location] . " != 0 AND
						" . DATE_TIME_SCHEDULE_TABLE . "." . $location_db_name[$location] . " = " . BOOKING_EVENT_TABLE . ".event_id AND
						" . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time >= '" . $year . "-" . $month . "-01 00:00:01' AND
						" . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time < '" . $year . "-" . sprintf("%02d",($month)) . "-31 23:59:59'  
						ORDER BY " . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time";
  //echo $query."<br /><br />";
  $result = wrap_db_query($query);
  $db_num_rows = wrap_db_num_rows($result);

  // Event Row Data Assoc. Array
  //    $event_row_data['date']['event_id'] = 'db_row_id|row_span|start_time|end_time';
  $event_row_data = array(array ());
  global $event_row_data;

  // Get the Display Times and Number of Rows
  $data_display_times = get_times_in_range(MIN_BOOKING_HOUR, MAX_BOOKING_HOUR, BOOKING_TIME_INTERVAL, true);
  $number_of_display_time_rows = count($data_display_times);

  // Get Month Information
  $number_of_days_in_the_month = number_of_days_in_month($year, $month);

  // Create an Assoc. Time array for index lookup.
  $display_time_lookup = array ();
  for ($i=0; $i<$number_of_display_time_rows; $i++) {
  	$display_time_lookup[$data_display_times[$i]] = $i;
  }

  // $event_row_data array - build out the schedule date blocks
  for ($day=1; $day<=$number_of_days_in_the_month; $day++) {
	$for_date = $year."-".$month."-".sprintf("%02d", $day);
	$event_row_data[$for_date][0] = '';
  }

  if (!$result) { return false; } // no database events

  // Go thru the database $result data and fill out the $event_row_data array.
  $previous_event_id = 0;
  $row_span = 0;
  $row = 0;
  $event = array();
  //echo "<h1>TESTING</h1>";

  for ($row=0; $row<=$db_num_rows; $row++) {

	// define db variables
	$event = wrap_db_fetch_array($result);
	$db_event_id = $event['event_id'];
	//echo "ID: $db_event_id<br />";
	list ($db_starting_date, $db_starting_time) = explode(" ", $event['schedule_date_time']);
	list ($db_hr, $db_min, $db_sec) = explode(":", $db_starting_time);
	$db_starting_time = sprintf("%02d", $db_hr).':'.sprintf("%02d", $db_min);

	if ($previous_event_id != $db_event_id || $previous_event_date != $db_starting_date ||
		$previous_event_id == 0) { // event_id has changed / or first event_id

		if ($previous_event_id != 0) { // if not first id, then define $event_row_data array

			// place the event data into $event_row_data: 'db_row_id|row_span|start_time|end_time'
			$event_row_data[$event_start_date][$previous_event_id] = $event_start_db_row_id."|".$row_span."|".$event_start_time."|".
							$data_display_times[($display_time_lookup[$event_start_time]+$row_span)];
			// echo values for testing
			//echo "Define Event -> " . $event_start_date ."/" . $previous_event_id . " => " . $event_row_data[$event_start_date][$previous_event_id] . "<br />";
			// initialize the row_span for the new event
			$row_span = 1;
		}
		// Mark the event starting time and db row id to be used to data_seeking
		//echo "<strong>Mark Start:</strong> ".$db_starting_date.", ".$row.", ".$db_event_id."<br />";
		$event_start_time = $db_starting_time; // mark the starting time
		$event_start_date = $db_starting_date; // mark the starting date
		$event_start_db_row_id = $row; // mark the starting db row
		$row_span = 1;

	} else { // same event_id
		//echo "<strong>Same Event ID:</strong> ".$db_starting_time.", ".$row.", ".$db_event_id."<br />";
		$row_span++;
	}
	$previous_event_id = $db_event_id;
	$previous_event_date = $db_starting_date;

  } // end of while

  // Display/Check the $event_row_data for errors
  //echo "<br />";
  //for ($day=1; $day<=$number_of_days_in_the_month; $day++) {
	//$test_date = $year."-".$month."-".sprintf("%02d", $day);
	//echo "Test Date: ".$test_date."<br />";
	//while (list($key, $value) = each($event_row_data[$test_date])) {
		//echo "Event ID: ".$key." Value: ".$value."<br />";
	//}
  //}

  // return the resulting data object
  return $result;
}


// Get the Event Data query object for the Week View
// $date - YYYY-MM-DD
// $location - Location ID
function get_week_view_event_data($date, $location = DEFAULT_LOCATION_NAME)
{
  // Get the event data for the selected week, month, year and location.

  // Use several of the already created arrays from week_widget.php as global variables.
  global $wdays_ind;
  global $wdays;
  global $week_day_start;
  global $week_dates;

  global $location_db_name;
  list ($year, $month, $day) = explode("-", $date);
  $query = "SELECT *
						FROM " . DATE_TIME_SCHEDULE_TABLE . ", " . BOOKING_EVENT_TABLE . " WHERE
						" . DATE_TIME_SCHEDULE_TABLE . "." . $location_db_name[$location] . " != 0 AND
						" . DATE_TIME_SCHEDULE_TABLE . "." . $location_db_name[$location] . " = " . BOOKING_EVENT_TABLE . ".event_id AND
						" . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time >= '" . $week_dates[0] . " 00:00:00' AND
						" . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time <= '" . $week_dates[6] . " 23:59:59'
						ORDER BY " . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time";
  //echo $query."<br /><br />";
  $result = wrap_db_query($query);
  $db_num_rows = wrap_db_num_rows($result);

  // Event Row Data Assoc. Array
  //    $event_row_data['display_time']['date'] = 'db_row_id|row_span|start_time|end_time';
  $event_row_data = array();
  global $event_row_data;

  // Get the Display Times and Number of Rows
  $data_display_times = get_times_in_range(MIN_BOOKING_HOUR, MAX_BOOKING_HOUR, BOOKING_TIME_INTERVAL, true);
  $number_of_display_time_rows = count($data_display_times);

  // Create an Assoc. Date array for index lookup.
  $display_time_lookup = array ();
  for ($i=0; $i<$number_of_display_time_rows; $i++) {
  	$display_time_lookup[$data_display_times[$i]] = $i;
  }

  // $event_row_data array - build out the schedule time blocks
  foreach ($week_dates as $week_date) {
	foreach ($data_display_times as $display_time) {
		$event_row_data[$display_time][$week_date] = '';
	}
	reset($data_display_times);
  }
  reset($week_dates);

  if (!$result) {
	//echo "No Database Events / Results<br />";
	return false;
  }
  // Go thru the database $result data and fill out the $event_row_data array.
  $previous_event_id = 0;
  $row_span = 0;
  $row = 0;
  $event = array();
  //echo "<h1>TESTING</h1>";

  for ($row=0; $row<=$db_num_rows; $row++) {

	// define db variables
	$event = wrap_db_fetch_array($result);
	$db_event_id = $event['event_id'];
	//echo "ID: $db_event_id<br />";
	list ($db_starting_date, $db_starting_time) = explode(" ", $event['schedule_date_time']);
	list ($db_hr, $db_min, $db_sec) = explode(":", $db_starting_time);
	$db_starting_time = sprintf("%02d", $db_hr).':'.sprintf("%02d", $db_min);

	if ($previous_event_id != $db_event_id || $previous_event_date != $db_starting_date ||
		$previous_event_id == 0) { // event_id has changed / or first event_id

		if ($previous_event_id != 0) { // if not first id, then define $event_row_data array

			// place the event data into $event_row_data: 'db_row_id|row_span|start_time|end_time'
			$event_row_data[$event_start_time][$event_start_date] = $event_start_db_row_id."|".$row_span."|".$event_start_time."|".
							$data_display_times[($display_time_lookup[$event_start_time]+$row_span)];
			// echo values for testing
			//echo "Define Event -> " . $event_row_data[$event_start_time][$event_start_date] . "<br />";
			// initialize the row_span for the new event
			$row_span = 1;
		}
		// Mark the event starting time and db row id to be used to data_seeking
		//echo "<strong>Mark Start:</strong> ".$db_starting_date." ".$db_starting_time.", ".$row.", ".$db_event_id."<br />";
		$event_start_time = $db_starting_time; // mark the starting time
		$event_start_date = $db_starting_date; // mark the starting date
		$event_start_db_row_id = $row; // mark the starting db row
		$row_span = 1;

	} else { // same event_id
		// Set the 'row_span' for the spanning cells of the event to zero ('row_span' = 0)
		$event_row_data[$db_starting_time][$db_starting_date] = 0;
		//echo "<strong>Same Event ID:</strong> ".$db_starting_time.", ".$row.", ".$db_event_id."<br />";
		$row_span++;
	}
	$previous_event_id = $db_event_id;
	$previous_event_date = $db_starting_date;

  } // end of while

  // Display/Check the $event_row_data for errors
  //echo "<br />";
  //foreach ($week_dates as $week_date) {
	//echo "Test Date: ".$week_date."<br />";
	//foreach ($data_display_times as $display_time) {
		//echo "Time: ".$display_time.", Value: ".$event_row_data[$display_time][$week_date]."<br />";
	//}
  //}

  // return the resulting data object
  return $result;
}


// Get the Event Data query object for the Day View
// $date - YYYY-MM-DD
// $location - Location ID
function get_day_view_event_data($date, $location = DEFAULT_LOCATION_NAME)
{
  // Get the event data for the selected day, month, year and location.

  global $location_db_name;
  list ($year, $month, $day) = explode("-", $date);
  $query = "SELECT *
						FROM " . DATE_TIME_SCHEDULE_TABLE . ", " . BOOKING_EVENT_TABLE . " WHERE
						" . DATE_TIME_SCHEDULE_TABLE . "." . $location_db_name[$location] . " != 0 AND
						" . DATE_TIME_SCHEDULE_TABLE . "." . $location_db_name[$location] . " = " . BOOKING_EVENT_TABLE . ".event_id AND
						" . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time >= '" . $date . " 00:00:00' AND
						" . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time <= '" . $date . " 23:59:59'
						ORDER BY " . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time";
  //echo $query."<br /><br />";
  $result = wrap_db_query($query);
  $db_num_rows = wrap_db_num_rows($result);

  // Event Row Data Assoc. Array
  //    $event_row_data['display_time'] = 'db_row_id|row_span|start_time|end_time';
  $event_row_data = array();
  global $event_row_data;

  // Get the Display Times and Number of Rows
  $data_display_times = get_times_in_range(MIN_BOOKING_HOUR, MAX_BOOKING_HOUR, BOOKING_TIME_INTERVAL, true);
  $number_of_display_time_rows = count($data_display_times);

  // Create an Assoc. Date array for index lookup.
  $display_time_lookup = array ();
  for ($i=0; $i<$number_of_display_time_rows; $i++) {
  	$display_time_lookup[$data_display_times[$i]] = $i;
  }

  // $event_row_data array - build out the schedule time blocks
  foreach ($data_display_times as $display_time) {
	$event_row_data[$display_time] = '';
  }
  reset($data_display_times);

  if (!$result) {
	//echo "No Database Events / Results<br />";
	return false;
  }
  // Go thru the database $result data and fill out the $event_row_data array.
  $previous_event_id = 0;
  $row_span = 0;
  $row = 0;
  $event = array();
  //echo "<h1>TESTING</h1>";

  for ($row=0; $row<=$db_num_rows; $row++) {

	// define db variables
	$event = wrap_db_fetch_array($result);
	$db_event_id = $event['event_id'];
	//echo "ID: $db_event_id<br />";
	list ($db_starting_date, $db_starting_time) = explode(" ", $event['schedule_date_time']);
	list ($db_hr, $db_min, $db_sec) = explode(":", $db_starting_time);
	$db_starting_time = sprintf("%02d", $db_hr).':'.sprintf("%02d", $db_min);

	if ($previous_event_id != $db_event_id || $previous_event_id == 0) { // event_id has changed / or first event_id

		if ($previous_event_id != 0) { // if not first id, then define $event_row_data array

			// place the event data into $event_row_data: 'db_row_id|row_span|start_time|end_time'
			$event_row_data[$event_start_time] = $event_start_db_row_id."|".$row_span."|".$event_start_time."|".
							$data_display_times[($display_time_lookup[$event_start_time]+$row_span)];
			// echo values for testing
			//echo "Define Event -> " . $event_row_data[$event_start_time] . "<br />";
			// initialize the row_span for the new event
			$row_span = 1;
		}
		// Mark the event starting time and db row id to be used to data_seeking
		//echo "<strong>Mark Start:</strong> ".$db_starting_time.", ".$row.", ".$db_event_id."<br />";
		$event_start_time = $db_starting_time; // mark the starting time
		$event_start_db_row_id = $row; // mark the starting db row
		$row_span = 1;

	} else { // same event_id
		// Set the 'row_span' for the spanning cells of the event to zero ('row_span' = 0)
		$event_row_data[$db_starting_time] = 0;
		//echo "<strong>Same Event ID:</strong> ".$db_starting_time.", ".$row.", ".$db_event_id."<br />";
		$row_span++;
	}
	$previous_event_id = $db_event_id;

  } // end of while

  // Display/Check the $event_row_data for errors
  //echo "<br />";
  //foreach ($data_display_times as $display_time) {
  //		echo $display_time." Event Row Data: ".$event_row_data[$display_time]."<br />";
  //}

  // return the resulting data object
  return $result;
}


// Get the event details from the database.
function get_event_details($event_id)
{
  // Get the requested event bases on event_id
  $result = wrap_db_query("SELECT * FROM " . BOOKING_EVENT_TABLE . " WHERE
						event_id = " . $event_id . "");
  if (!$result) { return false; }
  $event = wrap_db_fetch_array($result);
  // return the event data
  return $event;
};


// Get the Event Dates and Time Ranges from the database.
// $event_id - Event ID#
// returns $dates_and_time_ranges, format: 'start_date start_time-end_time'
function get_event_dates_and_time_ranges($event_id, $location = DEFAULT_LOCATION_NAME)
{
  // Get the Date & Time data for the event_id
  global $location_db_name;

  $result = wrap_db_query("SELECT schedule_date_time FROM " . DATE_TIME_SCHEDULE_TABLE . " WHERE
						" . $location_db_name[$location] . " = '" . $event_id . "'
						ORDER BY " . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time ASC LIMIT 1");
  $row = wrap_db_fetch_array($result);
  $min_schedule_date_time = $row['schedule_date_time'];
  list ($min_schedule_date, $min_schedule_time) = explode(" ", $row['schedule_date_time']);

  $result = wrap_db_query("SELECT schedule_date_time FROM " . DATE_TIME_SCHEDULE_TABLE . " WHERE
						" . $location_db_name[$location] . " = '" . $event_id . "'
						ORDER BY " . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time DESC LIMIT 1");
  $row = wrap_db_fetch_array($result);
  $max_schedule_date_time = $row['schedule_date_time'];
  list ($max_schedule_date, $max_schedule_time) = explode(" ", $row['schedule_date_time']);

  // Select the Date Range using the min and max date
//  $query = "SELECT * FROM " . DATE_TIME_SCHEDULE_TABLE . " WHERE
//						(schedule_date_time > '" . $min_schedule_date_time . "' AND schedule_date_time < '" . $min_schedule_date_time . "') OR
//						schedule_date_time = '" . $min_schedule_date_time . "' OR schedule_date_time = '" . $max_schedule_date_time . "'
//						ORDER BY " . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time ASC";
  $query = "SELECT * FROM " . DATE_TIME_SCHEDULE_TABLE . " WHERE
						(schedule_date_time > '" . $min_schedule_date . " ".MIN_BOOKING_HOUR.":00:00' AND schedule_date_time < '" . $max_schedule_date . " ".MAX_BOOKING_HOUR.":00:00') OR
						schedule_date_time = '" . $min_schedule_date . " ".MIN_BOOKING_HOUR.":00:00' OR schedule_date_time = '" . $max_schedule_date . " ".MAX_BOOKING_HOUR.":00:00'
						ORDER BY " . DATE_TIME_SCHEDULE_TABLE . ".schedule_date_time ASC";
  $result = wrap_db_query($query);
  if (!$result) { return false; } // no database dates and times
  $db_num_rows = wrap_db_num_rows($result);

  // Event Dates and Time Ranges Array
  //    $dates_and_time_ranges[] = 'start_date start_time-end_time';
  $dates_and_time_ranges = array();

  // Go thru the database $result data and fill out the $dates_and_time_ranges array.
  $previous_event_id = 0;
  $row_span = 0;
  $row = 0;
  $event = array();

  for ($row=0; $row<=$db_num_rows; $row++) {

	// define db variables
	$event = wrap_db_fetch_array($result);
	$db_event_id = $event[$location_db_name[$location]];
	//echo "ID: $db_event_id<br />";
	list ($db_date, $db_time) = explode(" ", $event['schedule_date_time']);
	list ($db_hr, $db_min, $db_sec) = explode(":", $db_starting_time);
	if ($row > 0 && empty($db_time)) $db_time = MAX_BOOKING_HOUR.":00";

	if ($event_id == $db_event_id && $db_event_id != $previous_event_id ) {
		// Start of Event Range
		$event_start_time = $db_time; // mark the starting time
		$event_start_date = $db_date; // mark the starting date

	} else if ( $event_id == $previous_event_id &&
		($db_event_id != $event_id || $db_date != $previous_event_date)	) {
		// End of Event Range
		// place the event data into $event_row_data: 'start_date start_time-end_time'
		$new_event_range = $event_start_date." ".$event_start_time."-".
						"".$db_time;
		$dates_and_time_ranges[] = $new_event_range;
		// echo values for testing
		//echo "Define Event -> " . $event_start_date ."/" . $previous_event_id . " => " . $new_event_range . "<br />";
	}
	$previous_event_id = $db_event_id;
	$previous_event_date = $db_date;
	$previous_event_time = $db_time;

  } // end for loop

  // Display/Check the $dates_and_time_ranges for errors
  //echo "<br />";
  //foreach ($dates_and_time_ranges as $date_time) {
	//echo "Event: $date_time <br />";
  //}

  // return the resulting dates_and_time_ranges string
  // format: 'start_date start_time-end_time'
  return $dates_and_time_ranges;
}


function get_user_events($username, $future_only=false, $resultLimit=false)
{
  //extract from the database all the events for this user
  //use of optional future_only parameter will only pull events in the future (ignoring past data)
  $query = "SELECT * FROM " . BOOKING_EVENT_TABLE . ", " . BOOKING_USER_TABLE . " WHERE " ;
  if ($future_only) {
    $query .= BOOKING_EVENT_TABLE . ".recur_until_date >= NOW() AND " ;
  }
  $query .= BOOKING_USER_TABLE . ".user_id = " . BOOKING_EVENT_TABLE . ".user_id AND " . BOOKING_USER_TABLE . ".username = '" . $username . "' ORDER BY " . BOOKING_EVENT_TABLE . ".starting_date_time" ;
  if ( $resultLimit !== false ) {
    $query .= " LIMIT 0," . $resultLimit ;
  }
  $result = wrap_db_query($query);
  if (!$result) {
	    return false;
  }
  // return the result reference
  return $result;
};


function add_event($username, $scheduled_date_time_data,
				$subject, $location, $starting_date_time, $ending_date_time,
				$recur_interval, $recur_freq, $recur_until_date, $description, $bookingOptions)
{
  // Add new want to the database

  // Use global $location_db_name
  global $location_db_name;

  // Check for repeat event; 'double click'
  // This might be removed in the future due to a future JavaScript function.
  $result = wrap_db_query("SELECT event_id FROM " . BOOKING_USER_TABLE . ", " . BOOKING_EVENT_TABLE . "
						WHERE " . BOOKING_USER_TABLE . ".username='" . $username . "' AND
						" . BOOKING_USER_TABLE . ".user_id = " . BOOKING_EVENT_TABLE . ".user_id AND
						" . BOOKING_EVENT_TABLE . ".subject = '" . $subject . "' AND
						" . BOOKING_EVENT_TABLE . ".location = '" . $location . "' AND
						" . BOOKING_EVENT_TABLE . ".starting_date_time = '" . $starting_date_time . "' AND
						" . BOOKING_EVENT_TABLE . ".ending_date_time = '" . $ending_date_time . "' AND
						" . BOOKING_EVENT_TABLE . ".recur_interval = '" . $recur_interval . "' AND
						" . BOOKING_EVENT_TABLE . ".recur_freq = " . $recur_freq . " AND
						" . BOOKING_EVENT_TABLE . ".recur_until_date = '" . $recur_until_date . "' AND
						" . BOOKING_EVENT_TABLE . ".description = '" . $description . "'");

  //echo "Duplicate Rows: " . wrap_db_num_rows($result) . "<br />";
  if ($result && (wrap_db_num_rows($result)>0)) {
		return false;
  }
  // get user_id based on current $username
  $user_id = get_user_id($username);
  if (empty($user_id)) {
     return false;
  }

  // insert the new bookmark
  $result = wrap_db_query("INSERT INTO " . BOOKING_EVENT_TABLE . " SET
						user_id = " . $user_id . ",
						subject = '" . $subject . "',
						location = '" . $location . "',
						starting_date_time = '" . $starting_date_time . "',
						ending_date_time = '" . $ending_date_time . "',
						recur_interval = '" . $recur_interval . "',
						recur_freq = " . $recur_freq . ",
						recur_until_date = '" . $recur_until_date . "',
						description = '" . $description . "',
						date_time_added = NOW(),
						last_mod_by_id = '". $user_id . "',
						last_mod_date_time = NOW()");
  if (!$result) {
		return false;
  }
  // Get the event_id (auto) for the event just added to the event table.
  $event_id = wrap_db_insert_id();

  // Insert the event_id into the schedule table at the appropriate date-time slots.
  $add_date_time_error = false;
  foreach ($scheduled_date_time_data as $date_time) {
		$result = wrap_db_query( "UPDATE " . DATE_TIME_SCHEDULE_TABLE . "
						SET " . $location_db_name[$location] . " = " . $event_id . "
						WHERE schedule_date_time = '" . $date_time . "' AND
						" . $location_db_name[$location] . " = 0");
		//echo "location: $location, event_id: $event_id <br />";
		if (!$result) { $add_date_time_error = true; }
  }
  if ($add_date_time_error == true) {
		// Delete Event Info Function needs to be added here!
		echo "ERROR! A date and time slot could not be filled properly!<br />";
		return false;
  }

  //add the option id's chosen to go with this booking
  $numBookingOptions = count( $bookingOptions ) ;
  for ( $o = 0 ; $o < $numBookingOptions ; $o++ ) {
        $query = "INSERT INTO " . BOOKING_EVENT_OPTIONS_TABLE . " SET event_id = " . $event_id . ", option_id = '" . $bookingOptions[$o] . "'" ;
        wrap_db_query( $query ) ;
  }


	// Table maintenance
	
	if (!defined(PURGE_TABLE_SCHEDULE_DAYS)) { define(PURGE_TABLE_SCHEDULE_DAYS, 365); }
	$result = wrap_db_query( "DELETE FROM " . DATE_TIME_SCHEDULE_TABLE . " WHERE schedule_date_time < DATE_SUB(CURDATE(), INTERVAL ". PURGE_TABLE_SCHEDULE_DAYS . " DAY )");
	$result = wrap_db_query( "ALTER TABLE " . DATE_TIME_SCHEDULE_TABLE . " ORDER BY schedule_date_time");
	$result = wrap_db_query( "OPTIMIZE TABLE " . DATE_TIME_SCHEDULE_TABLE);

  return $event_id;
}


function delete_event($username, $event_id, $refundCredits=true)
{
  // delete one event (and event schedule) from the database
  // Use global $location_db_name
  global $location_db_name;

  // get user_id based on current $username
  $user_id = get_user_id($username);
  if (empty($user_id)) {
     return false;
  }
  // get event data: location id
  $event = get_event_details($event_id);
  //echo "location: $event['location'], event_id: $event['event_id'] <br />";

  // delete any options set for this event_id
  $query = 'DELETE FROM ' . BOOKING_EVENT_OPTIONS_TABLE . ' WHERE event_id="' . $event_id . '"' ;
  wrap_db_query( $query ) ;

  // delete the event_id
  $result = wrap_db_query( "DELETE FROM " . BOOKING_EVENT_TABLE . "
		WHERE location = '" . $event['location'] . "' AND event_id = ".$event_id." LIMIT 1");

  // delete the event schedule, set back to zero
  $result = wrap_db_query( "UPDATE " . DATE_TIME_SCHEDULE_TABLE . "
		SET " . $location_db_name[$event['location']] . " = 0
		WHERE " . $location_db_name[$event['location']] . " = ".$event_id);

  //store the number of slots deleted in case we need to refund credits
  $creditsToRefund = wrap_affected_rows() ;

  if ( $refundCredits ) {
      //if the user uses credits, refund used credits
      $userDetails = get_user( $user_id ) ;
      if ( $userDetails['booking_credits'] == 'Not used') {
          //nothing to do, the user does not use credits
          return true ;
      } else {
          //refund a credit for each booking slot deleted
          update_booking_credits( $username, $creditsToRefund, 'inc' ) ;
      }
  }

  return true;
}


function delete_event_slot($username, $event_id, $schedule_date_time)
{
  // delete one event date/time slot based on "schedule_date_time"

  // Use global $location_db_name
  global $location_db_name;

  // get user_id based on current $username
  $user_id = get_user_id($username);
  if (empty($user_id)) {
     return false;
  }
  // get event data: location id
  $event = get_event_details($event_id);
  //echo "location: $event['location'], event_id: $event['event_id'] <br />";

  // get user_id based on current $username
  $user_id = get_user_id($username);

  // delete any options set for this event_id
  $query = 'DELETE FROM ' . BOOKING_EVENT_OPTIONS_TABLE . ' WHERE event_id="' . $event_id . '"' ;
  wrap_db_query( $query ) ;

  // delete the event date/time slot, set back to zero
  $result = wrap_db_query( "UPDATE " . DATE_TIME_SCHEDULE_TABLE . "
		SET " . $location_db_name[$event['location']] . " = 0
		WHERE " . $location_db_name[$event['location']] . " = ".$event_id."
		AND schedule_date_time = '".$schedule_date_time."' LIMIT 1");
  return $result;
}


function modify_event($username, $event_id, $subject, $description, $optionsArray=null )
{
  // modify an event from the database; subject and/or description + options only

  // get user_id based on current $username
  $user_id = get_user_id($username);
  if (empty($user_id)) {
     return false;
  }
   // modify/update the want
  if (!wrap_db_query("UPDATE " . BOOKING_EVENT_TABLE . " SET
					subject = '$subject',
					description =  '$description'
					WHERE user_id = ".$user_id." AND event_id = ".$event_id." LIMIT 1")) {
		return false;
  }

  //remove any existing options for this event and replace with the supplied array of options
  $query = 'DELETE FROM ' . BOOKING_EVENT_OPTIONS_TABLE . ' WHERE event_id="' . $event_id . '"' ;
  wrap_db_query( $query ) ;

  //save the new booking options to the db
  $numBookingOptions = count( $optionsArray ) ;
  for ( $o = 0 ; $o < $numBookingOptions ; $o++ ) {
      $query = "INSERT INTO " . BOOKING_EVENT_OPTIONS_TABLE . " SET event_id = " . $event_id . ", option_id = '" . $optionsArray[$o] . "'" ;
      if ( !wrap_db_query( $query ) ) {
          return false ;
      }
  }

  return true ;
}


function get_user_id($username)
{
  // get user_id based on current $valid_user
  $result = wrap_db_query("SELECT user_id FROM " . BOOKING_USER_TABLE . "
						WHERE username = '" . $username . "'");
  $fields = array ();
  $fields = wrap_db_fetch_array($result);
  $user_id = $fields{'user_id'};
  return $user_id;
}

function get_user($user_id)
{
  // get user_id based on $id
  $result = wrap_db_query("SELECT * FROM " . BOOKING_USER_TABLE . "
						WHERE user_id = '" . $user_id . "' LIMIT 1");
  return wrap_db_fetch_array($result);
}

function get_booking_options( $event_id ) {
    //get the id's and descriptions for options chosen by the user
    $savedUserBookingOptions = null ;
    $userBookingResult = wrap_db_query("SELECT e.option_id, o.description FROM " . BOOKING_EVENT_OPTIONS_TABLE . " AS e, " . BOOKING_OPTIONS_TABLE . " AS o WHERE e.event_id='" . $event_id . "' AND e.option_id=o.option_id");
    if ( $userBookingResult && ( wrap_db_num_rows( $userBookingResult ) > 0 ) ) {
        while ( $userBookingFields = wrap_db_fetch_array($userBookingResult) ) {
            $savedUserBookingOptions[] = array( 'id'=>$userBookingFields['option_id'], 'desc'=>$userBookingFields['description'] ) ;
        }
    }
    return $savedUserBookingOptions ;
}
?>