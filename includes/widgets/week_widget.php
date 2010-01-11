<?
// month_widget.php
// Displays the Month View

  // Setup the weekday index array values
  $wdays_ind = array ();
  $wdays_ind = weekday_index_array(WEEK_START);
  // Build the wdays string using the weekday_short_name function.
  $wdays = array ();
  foreach ($wdays_ind as $index) {
	$wdays[] = weekday_name($index);
  }
  reset($wdays_ind);
  // Find the beginning of the Week yyyy-mm-dd
  if (WEEK_START == 1) { // Starts on Monday
	$week_day_start = monday_before_date($_REQUEST['date']);
  } else { // Starts on Sunday
	$week_day_start = sunday_before_date($_REQUEST['date']);
  }
  // Define the 7 dates of the Week yyyy-mm-dd
  $week_dates = array ();
  for ($i=0; $i<=6; $i++) {
		$week_dates[] = add_delta_ymd($week_day_start, 0, 0, $i);
  }
  // Define Previous and Next Week Dates
  $previous_week_date = add_delta_ymd($_REQUEST['date'], 0, 0, -7);
  $next_week_date = add_delta_ymd($_REQUEST['date'], 0, 0, 7);

  // Define the $event_data object.
  $event_data = get_week_view_event_data(SELECTED_DATE, $_REQUEST['loc']);

  // Note $event_row_data is passed globally and contains the
  // 'db_row_id|row_span|start_time|end_time" data.
  // row_span: '' => no data, '1-up' => event, '0' => rowspan of event (no cell)

  // Time Display Cell Width
  $time_cell_width = 75;

  // Changed 'colors' to Style References for Odd and Even Rows
  $colors = array ('BgcolorDull2', 'BgcolorNormal');

  //multiply the allowed advance booking time (stored in the session on hours) by the number of seconds in an hour.
  //add this value to the current time to find the upper bookiung time limit.
  //3600 = 60 mins in an hour  x  60 secs in a min
  $booking_limited_time = time() + ( $_SESSION['ADVANCE_BOOKING_LIMIT'] * 3600 ) ;
?>


<!-- week_widget.php -->
<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
    <td align="left" class="SectionHeaderStyle">
      All Week of <?=SELECTED_DATE_LONGSTR?> Events for <?=$location_display[$_REQUEST['loc']]?>:
    </td>
  </tr>
</table>



<table cellspacing="1" cellpadding="1" width="100%" border="0">

  <tr>
	<td align="center" valign="middle" width="<?=$time_cell_width?>"
		class="BgcolorDull2" nowrap="nowrap"><b>Time Slot</b></td>
<?
	for ($i=0; $i<=6; $i++) {
		$week_date = $week_dates[$i];
		list($year, $month, $day) = explode("-", $week_date);
?>
		<td class="BgcolorBright" align="center" valign="top" nowrap="nowrap"><b><?=$wdays[$i]?><br />
<?
		if ($i == 0) { // Previous Week Link
?>
			<a href="<?=href_link(FILENAME_WEEK_VIEW, 'date='.$previous_week_date.'&view=week&'.make_hidden_fields_workstring(array('loc')), 'NONSSL')?>"><img
			src="<?=DIR_WS_IMAGES?>/prev.gif" alt="Previous Week" align="top" /></a>
<?
		}
?>
			<?=month_short_name($month)?> <?=occurence_name($day+0)?>
<?
		if ($i == 6) { // Next Week Link
?>
			<a href="<?=href_link(FILENAME_WEEK_VIEW, 'date='.$next_week_date.'&view=week&'.make_hidden_fields_workstring(array('loc')), 'NONSSL')?>"><img
			src="<?=DIR_WS_IMAGES?>/next.gif" alt="Next Week" align="top" /></a>
<?
		}
?>
		</b></td>
<?
	} // end of for loop
?>
  </tr>


<?
  $count = 0;
  $width_length = 5;
  $data_display_times = array ();
  $data_display_times = get_times_in_range(MIN_BOOKING_HOUR, MAX_BOOKING_HOUR, BOOKING_TIME_INTERVAL);
  array_pop($data_display_times);

  foreach ($data_display_times as $display_time) {

	//$row_data = $data_sel_day_data[$hour];
	list ($hour, $min, $sec) = explode(":", $display_time);
	$time_str = sprintf("%02d:%02d", $hour, $min);
	$std_time_str = $time_str;

	// To Cater for the AM PM Hour display
	if (DEFINE_AM_PM) {
		// Note that the time placed in the HREF will be in 24 hour
		$time_str = format_time_to_ampm($time_str);
	}

	$count++;
	$color_ind = count % 2;
?>
	<tr>
	<td align="center" valign="top" width="<?=$time_cell_width?> height="<?=$time_cell_height?>"
		class="<?=$colors[$color_ind]?>" nowrap="nowrap">
<?
	// NOTE that the $std_time_str will be in 24 hour
	// regardless if it is  set to or not (DEFINE_AM_PM).

//modified by MJ on 10/03/05. Force user to use the plus links for the relevant day.
//Clicking on a time does not make it sensible to book for the first day in that group when looking at a week at a time.
//	if (ALLOW_ADDITIONS_FLAG) {
//        echo '<a href="' . href_link(FILENAME_ADD_EVENT, 'start_time='.$std_time_str.'&'.make_hidden_fields_workstring(array('date', 'view', 'loc')), 'NONSSL') . '">' . $time_str . '</a>' ;
//	} else {
		echo $time_str ;
//	}
    echo ' &nbsp; <br /> <br />' ;
?>
    </td>
<?
	// Note $event_row_data is passed globally and contains the
	// 'db_row_id|row_span|start_time|end_time" data (pipe delimited).

	reset($week_dates);
	$cnt=0;
	foreach ($week_dates as $week_date) {

		if (strlen($event_row_data[$display_time][$week_date]) > 1) {

			$cnt++;
			@ list ($db_row_id, $row_span, $start_time, $end_time) = explode("|", $event_row_data[$display_time][$week_date]);
			// To Cater for the AM PM Hour display
			if (DEFINE_AM_PM) {
				$start_time = format_time_to_ampm($start_time);
				$end_time = format_time_to_ampm($end_time);
			}
			// Use the $db_row_id to data seek to the data for this event.
			$rv = wrap_db_data_seek($event_data, $db_row_id);
			$this_event = wrap_db_fetch_array($event_data);
    		//is this user allowed to see the booking details?
    		if ( !$_SESSION['SHOW_USER_DETAILS'] && ( $this_event['user_id'] != $currentUsersID ) ) {
    		    //user not allowed to see these details, overwrite the subject string
    		    $this_event['subject'] = 'Booking Confirmed' ;
    		} else {
                //add the booking option data into the event array
                $this_event['booking_options'] = get_booking_options( $this_event['event_id'] ) ;
            }

			$event_url = href_link(FILENAME_DETAILS_VIEW, 'event_id='.$this_event['event_id'].'&'.make_hidden_fields_workstring(array('date', 'view', 'loc')), 'NONSSL');
			$over_text = 'Event ID#: ' . $this_event['event_id'] .
						 '<br />Subject: ' . $this_event['subject'];
            $numBookingOptions = count( $this_event['booking_options'] ) ;
            if ( $numBookingOptions > 0 ) {
                $over_text .= '<br />Options: ' ;
                for ( $o = 0 ; $o < $numBookingOptions ; $o++ ) {
                    //handle commas to separate the list
                    if ( $o != 0 ) {
                        $over_text .= ', ' ;
                    }
                    $over_text .= htmlspecialchars( stripslashes( $this_event['booking_options'][$o]['desc'] ) ) ;
                }
            }
?>
			<td class="BgcolorDull" align="left" width="14%" rowspan="<?=$row_span?>" nowrap="nowrap"><span
			class="FontSoftSmall">&nbsp;<a href="<?=$event_url?>" id="<?=$cnt?>" onmouseover="return overlib('' +
			'<?=overlib_escape(htmlentities($over_text, ENT_QUOTES, 'ISO-8859-1'))?>' +
			' ', CAPTION, 'Event Time: <?=$start_time?>-<?=$end_time?>');"
			onmouseout="nd();"><?=$start_time?>-<?=$end_time?></a>&nbsp;</span></td>
<?
		} elseif ($event_row_data[$display_time][$week_date] == '0') {

			// This is where the cell is already taken from the prev row.

		} else {

            //generate the link to be used for making/preventing bookings. Store for possible re-use later in this table row:
            $make_booking_link = '' ;

            list($year, $month, $day) = explode("-", $week_date);
            if ( wrap_session_is_registered("admin_user") || ( mktime( $hour, $min, $sec, $month, $day, $year ) < $booking_limited_time ) ) {
                $make_booking_link = href_link(FILENAME_ADD_EVENT, 'date='.$week_date.'&start_time='.$std_time_str.'&'.make_hidden_fields_workstring(array('view', 'loc')), 'NONSSL') ;
            } else {
                $make_booking_link = '#" onclick="return outsideBookingLimit();"' ;
            }

?>
			<td align="right" rowspan="1" class="BgcolorNormal" width="14%"><span
			class="FontSoftSmall"><a href="<?= $make_booking_link ; ?>">(+)</a></span></td>
<?
		} // end of if/elseif/else
	} // end of foreach $week_date
?>
  </tr>
<?
  } // end of foreach
?>
</table>


