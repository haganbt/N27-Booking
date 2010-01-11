<?
// month_widget.php
// Displays the Month View

  // Setup the weekday index array values
  $wdays_ind = array ();
  $wdays_ind = weekday_index_array(WEEK_START);

  // Build the wdays string using the weekday_short_name function.
  $wdays = array ();
  foreach ($wdays_ind as $index) {
	$wdays[] = weekday_short_name($index);
  }

  $days_in_the_month = number_of_days_in_month(SELECTED_DATE_YEAR, SELECTED_DATE_MONTH);
  $month_begin_wday = weekday_short_name(beginning_weekday_of_the_month(SELECTED_DATE_YEAR, SELECTED_DATE_MONTH));

  $min_cell_width = 50;

  // Define the $event_data object.
  $event_data = get_month_view_event_data(SELECTED_DATE, $_REQUEST['loc']);

  // Note $event_row_data is passed globally and contains the
  // 'db_row_id|row_span|start_time|end_time" data.
  // row_span: '' => no data, '1-up' => event, '0' => rowspan of event (no cell)
?>


<!-- month_widget.php -->
<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
	<td align="left" class="SectionHeaderStyle">
		All <?=month_name(SELECTED_DATE_MONTH)?> <?=SELECTED_DATE_YEAR?> Events for <?=$location_display[$_REQUEST['loc']]?>:
	</td>
  </tr>
</table>


<table cellspacing="1" cellpadding="1" width="100%" border="0">

<!-- weekdays header -->
<tr>
<?
  foreach ($wdays as $wday) {
?>
  <td width="14%" class="BgcolorBright" align="center"><b><?=$wday?></b></td>
<?
	}
?>
</tr>


<!-- set min cell width for weekdays -->
<tr>
<?
  reset($wdays);
  foreach ($wdays as $wday) {
?>
	<td width="<?=$min_cell_width?>" class="BgcolorBody" align="center"><img
	src="<?=DIR_WS_IMAGES?>/spacer.gif" width="<?=$min_cell_width?>"
	height="1" /></td>
<?
  }
?>
</tr>
  <!-- rows -->
<?
  $count = 0;
  for ($row = 1; $row < 7; $row++) {
?>
	<tr>
<?
	$day_count = $count; // tmp holder

	reset($wdays_ind);
	foreach ($wdays_ind as $wday_ind) {

		$count = ($count > 0 || (weekday_short_name($wday_ind) == $month_begin_wday)) ? $count + 1 : 0;
		if ($count > 0 && $count <= $days_in_the_month) {
			$date = SELECTED_DATE_YEAR.'-'.SELECTED_DATE_MONTH.'-'.sprintf("%02d", $count);

            //generate the link to be used for making/preventing bookings. Store for possible re-use later in this table row:
            $make_booking_link = href_link(FILENAME_DAY_VIEW, 'date='.$date.'&view=day&'.make_hidden_fields_workstring(array('loc')), 'NONSSL') ;
?>
			<td width="14%" class="BgcolorDull2" align="center"><table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td align="center" width="100%" nowrap="nowrap"><span class="FontSoftSmall"><a
				href="<?= $make_booking_link ; ?>"><?=$count?></a></span></td>
<?
			if (weekday_short_name($wday_ind) == $wdays['0']) { // if start of the week, add week link
?>
				<td align="center" nowrap="nowrap" width="100%"><span class="FontSoftSmall"><a href="<?=href_link(FILENAME_WEEK_VIEW, 'date='.$date.'&view=week&'.make_hidden_fields_workstring(array('loc')), 'NONSSL')?>">
				(Week <?=week_number(SELECTED_DATE_YEAR, SELECTED_DATE_MONTH, $count)?>)</a></span></td>
<?
			}
?>
				<td align="right" nowrap="nowrap"><span class="FontSoftSmall"><a href="<?php
				//Modified by MJ on 10/03/05 - commented out the original line below and replaced it with the following one.
				//this change forces the user to go to the day view to select a time slot rather than just take the 7:00am slot on this day as the orig link did
				//echo href_link(FILENAME_ADD_EVENT, 'date='.$date.'&view=week&'.make_hidden_fields_workstring(array('loc')), 'NONSSL') ;
				echo $make_booking_link ;
				?>">(+)</a></span></td>
			</td></tr></table></td>
<?
		} else {
?>
			<td width="14%">&nbsp;</td>
<?
		}
	} // end of foreach
?>
	</tr>


	<tr>
<?
	// restore the count for the data row
	$count = $day_count;

	reset($wdays_ind);
	foreach ($wdays_ind as $wday_ind) {

		$count = ($count > 0 || (weekday_short_name($wday_ind) == $month_begin_wday)) ? $count + 1 : 0;

		if ($count > 0 && $count <= $days_in_the_month) {

			$selected = (SELECTED_DATE_DAY == $count) ? 1 : 0;
			$mday = $count + 1;
			$count_date = SELECTED_DATE_YEAR.'-'.SELECTED_DATE_MONTH.'-'.sprintf("%02d", $count);
?>
			<td align="left" valign="top" width="14%"
				class="<?=$selected ? "BgcolorDull" : "BgcolorNormal"?>" nowrap="nowrap">
<?
			$event_count = 0;
			while (list($event_row_key, $event_row_value) = each($event_row_data[$count_date])) {

				if (strlen($event_row_value) > 1) {

					$event_count++;
					@ list ($db_row_id, $row_span, $start_time, $end_time) = explode("|", $event_row_value);
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
					<span class="FontSoftSmall">&nbsp;<a href="<?=$event_url?>" onmouseover="return overlib('' +
					'<?=overlib_escape(htmlentities($over_text, ENT_QUOTES, 'ISO-8859-1'))?>' +
					' ', CAPTION, 'Event Time: <?=$start_time?>-<?=$end_time?>');"
					onmouseout="nd();"><?=$start_time?>-<?=$end_time?></a>&nbsp;</span><br />
<?
				} // end of if
			} // end of while data for that day

			if ($event_count == 0) {
?>
				<div class="FontSoftSmall"> <br /> <br /> <br /> <br /> </div>
<?
			} // end of if
?>
			</td>
<?
		} else {
?>
		<td width="14%"><div class="FontSoftSmall"> <br /> <br /> <br /> </div></td>
<?
		} // end of if/else
	} // end of foreach
?>
  </tr>
<?
	if ($count >= $days_in_the_month) { break; }
  } // end for loop

?>

</table>



