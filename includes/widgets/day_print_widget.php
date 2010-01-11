<?
// day_widget.php
// Displays the Day View

  // Changed 'colors' to Style References for Odd and Even Rows
  $colors = array ('BgcolorDull2', 'BgcolorNormal');

  // Time Display Cell Width
  $time_cell_width = 75;

  // Define the $event_data object.
  $event_data = get_day_view_event_data(SELECTED_DATE, $_REQUEST['loc']);

  // Note $event_row_data is passed globally and contains the
  // 'db_row_id|row_span|start_time|end_time" data.
  // row_span: '' => no data, '1-up' => event, '0' => rowspan of event (no cell)
?>


<!-- day_widget.php -->
<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
    <td align="left" class="SectionHeaderStyle">
      All <?=SELECTED_DATE_LONGSTR?> Events for <?=$location_display[$_REQUEST['loc']]?>:
    </td>
  </tr>
</table>



<table cellspacing="0" cellpadding="0" width="100%" border="0" class="ShowBorders">

  <tr>
	<td align="center" valign="middle" width="<?=$time_cell_width?>"
		class="BgcolorDull2" nowrap="nowrap"><b>Time Slot</b></td>
	<td class="BgcolorBright" align="center" valign="middle">
		<a href="<?=href_link(FILENAME_DAY_VIEW, 'date='.PREVIOUS_DAY_DATE.'&print_view=1&'.make_hidden_fields_workstring(array('view', 'loc')), 'NONSSL')?>"><img
		src="<?=DIR_WS_IMAGES?>/prev.gif"
		alt="Previous Day" /></a><b><?=SELECTED_DATE_LONGSTR?></b><a
		href="<?=href_link(FILENAME_DAY_VIEW, 'date='.NEXT_DAY_DATE.'&print_view=1&'.make_hidden_fields_workstring(array('view', 'loc')), 'NONSSL')?>"><img
		src="<?=DIR_WS_IMAGES?>/next.gif" alt="Next Day" /></a></td>
  </tr>


<?
  $count = 0;
  $width_length = 5;
  $data_display_times = array ();
  $data_display_times = get_times_in_range(MIN_BOOKING_HOUR, MAX_BOOKING_HOUR, BOOKING_TIME_INTERVAL);
  array_pop($data_display_times);

  list ($year, $month, $day) = explode("-", SELECTED_DATE);

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
	<td align="center" width="<?=$time_cell_width?>"
		class="<?=$colors[$color_ind]?>" nowrap="nowrap">
		<?=$time_str?>
    </td>
<?
	// Note $event_row_data is passed globally and contains the
	// 'db_row_id|row_span|start_time|end_time" data (pipe delimited).

	if (strlen($event_row_data[$display_time]) > 1) {

		@ list ($db_row_id, $row_span, $start_time, $end_time) = explode("|", $event_row_data[$display_time]);
		// To Cater for the AM PM Hour display
		if (DEFINE_AM_PM) {
			$start_time = format_time_to_ampm($start_time);
			$end_time = format_time_to_ampm($end_time);
		}
		// Use the $db_row_id to data seek to the data for this event.
		$rv = wrap_db_data_seek($event_data, $db_row_id);
		$this_event = wrap_db_fetch_array($event_data);
		//is this user allowed to see the booking details?
		if ( !$_SESSION['SHOW_USER_DETAILS'] ) {
		    //user not allowed to see these details, overwrite the subject string
		    $this_event['subject'] = 'Booking Confirmed' ;
		    $this_event['description'] = '' ;
		} else {
            //add the booking option data into the event array
            $this_event['booking_options'] = get_booking_options( $this_event['event_id'] ) ;
        }
?>
		<td class="BgcolorDull" align="left" rowspan="<?=$row_span?>"><span
		class="FontSoftSmall">&nbsp;<?=$start_time?>-<?=$end_time?> - <?=$this_event['subject']; ?>
		<?= ($this_event['description'] != '') ? '<br>&nbsp;'.$this_event['description'] : '' ; ?>
		<?php
		$numBookingOptions = count( $this_event['booking_options'] ) ;
		if ( $numBookingOptions > 0 ) {
            echo '<br>(' ;
            for ( $o = 0 ; $o < $numBookingOptions ; $o++ ) {
                //handle commas to separate the list
                if ( $o != 0 ) {
                    echo ', ' ;
                }
                echo htmlspecialchars( stripslashes( $this_event['booking_options'][$o]['desc'] ) ) ;
            }
            echo ')' ;
        }
        ?></td>
<?
	} elseif ($event_row_data[$display_time] == '0') {

		// This is where the cell is already taken from the prev row.

	} else {
?>
		<td align="right" rowspan="1" class="BgcolorNormal">&nbsp;</td>
<?
	} // end of if/elseif/else
?>
  </tr>
<?
  } // end of foreach
?>
</table>

