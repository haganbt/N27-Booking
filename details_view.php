<? include_once("./includes/application_top.php"); ?>
<?
  if (REQUIRE_AUTH_FOR_VIEWING_DETAILS_FLAG) {
	if (!@wrap_session_is_registered('valid_user')) {
		header('Location: ' . href_link(FILENAME_LOGIN, 'origin=' . FILENAME_DETAILS_VIEW . '&' . make_hidden_fields_workstring(), 'NONSSL'));
	    wrap_exit();
	}
  }
?>
<?

$page_error_message = '';
$showEventDetails = true ;
// EVENT DETAILS FOR EVENT_ID

if (!empty($_REQUEST['event_id'])) {

	// GET THE EVENT DATA
	$event = get_event_details($_REQUEST['event_id']);
	if (!($event)) {
		$page_error_message = "I'm sorry, but that Event ID does not exist in the event records.";
	}

	// VALIDATE DATA

	// DEFINE DATES (Do not change from YYYY-MM-DD format!)
	list($starting_date, $starting_time) = explode(" ", $event['starting_date_time']);
	$starting_time = substr($starting_time, 0, strlen($starting_time)-3);
	list($ending_date, $ending_time) = explode(" ", $event['ending_date_time']);
	$ending_time = substr($ending_time, 0, strlen($ending_time)-3);
	$recur_date = $event['recur_until_date'];
	// DEFINE THE RECURRING FREQUENCY AND INTERVAL
	$recur_freq = $event['recur_freq'];
	$recur_interval = strtolower($event['recur_interval']);
	//echo "Start Date: ".$starting_date."<br />";
	//echo "Start Time: ".$starting_time."<br />";
	//echo "End Date: ".$ending_date."<br />";
	//echo "End Time: ".$ending_time."<br />";

	// CHECK REQUIRED FIELDS

	// CHECK DATES

	if (!(check_valid_date($starting_date))) {
		$page_error_message = "Your starting date does not exist. There are only " .
			number_of_days_in_month($_POST['start_year'], $_POST['start_mon']) . " days in " . month_name($_POST['start_mon']) .
			" " . $_POST['start_year'] . ". Please check the calendar and try again.";
	}
	elseif (!(check_valid_date($ending_date))) {
		$page_error_message = "Your ending date does not exist. There are only " .
			number_of_days_in_month($_POST['end_year'], $_POST['end_mon']) . " days in " . month_name($_POST['end_mon']) .
			" " . $_POST['end_year'] . ". Please check the calendar and try again.";
	}
	elseif (!(check_valid_date($recur_date)) && $recur_interval != '') {
		$page_error_message = "Your recurring date does not exist. There are only " .
			number_of_days_in_month($_POST['recur_year'], $_POST['recur_mon']) . " days in " . month_name($_POST['recur_mon']) .
			" " . $_POST['recur_year'] . ". Please check the calendar and try again.";
	}

	// CHECK THAT ENDING DATE/TIME > STARTING DATE/TIME

	elseif ( ( implode("", explode("-",$ending_date)) . implode("", explode(":",$ending_time)) )+0 <=
			 ( implode("", explode("-",$starting_date)) . implode("", explode(":",$starting_time)) )+0 ) {
		$page_error_message = "There is a problem with this event! The ending date and time must occur after the starting " .
			"date and time. Please notify the calendar adminstrator of this problem.";
	} // end of if/elseif

	// CHECK THAT RECUR DATE > ENDING DATE/TIME

	elseif ( implode("", explode("-",$recur_date))+0 <= implode("", explode("-",$ending_date))+0
			   && !($recur_interval == 'none' || $recur_interval == '') ) {
		$page_error_message = "There is a problem with this event! The recurring until date must occur after your ending " .
			"date. Please notify the calendar adminstrator of this problem.";
	} // end of if/elseif


	// ACTION HANDLER
	// CHECK AUTHENTICATION/USERNAME/GROUP FOR MODIFY OR DELETE ACTIONS
	$user_id = get_user_id($_SESSION['valid_user']); // Current Session User ID
	$event_user = get_user($event['user_id']); // Define Event User Information
	$event_username = $event_user['username'] ; //username of the event booker
	$valid_session = wrap_session_is_registered('valid_user');
	$display_modify_trigger = true;
	$display_delete_trigger = true;

	if (REQUIRE_AUTH_FOR_MODIFYING_FLAG && !$valid_session &&
		($_REQUEST['action'] == 'submit_modify' || $_REQUEST['action'] == 'modify')) {
//echo "1" ;
			$_REQUEST['action'] = "";
			$display_modify_trigger = false;
	}
	if (REQUIRE_AUTH_FOR_DELETING_FLAG && !$valid_session &&
		($_REQUEST['action'] == 'submit_delete' || $_REQUEST['action'] == 'delete' || $_REQUEST['action'] == 'delete_event')) {
//echo "2" ;
			$_REQUEST['action'] = "";
			$display_delete_trigger = false;
	}
	if (REQUIRE_MATCHING_USERNAME_FOR_MODIFICATIONS_FLAG && $event['user_id'] != $user_id) {
//echo "3" ;
		if ($_REQUEST['action'] == 'submit_modify' || $_REQUEST['action'] == 'modify') {
			$_REQUEST['action'] = "";
		}
		$display_modify_trigger = false;
	}
	if (REQUIRE_MATCHING_USERNAME_FOR_DELETIONS_FLAG && $event['user_id'] != $user_id) {
//echo "4" ;
		if ($_REQUEST['action'] == 'submit_delete' || $_REQUEST['action'] == 'delete' || $_REQUEST['action'] == 'delete_event') {
//echo "ha" ;
			$_REQUEST['action'] = "";
		}
		$display_delete_trigger = false;
	}

	if ($_REQUEST['action'] == 'submit_modify') {
//echo "5" ;
		if ($_POST['subject'] == "") {
			$page_error_message = "You have not filled out the add form completely. Please type in a subject.";
			$event['description'] = stripslashes($_POST['desc']);
			$event['subject'] = stripslashes($_POST['subject']);
			$_REQUEST['action'] = 'modify';
		} else if ( ( ( $event_user['is_admin'] == '1' ) && ( count( $_POST['bookingOptions'] ) < $_SESSION['MINIMUM_ADMIN_BOOKING_OPIONS'] ) ) || ( ( $event_user['is_admin'] == '0' ) && ( count( $_POST['bookingOptions'] ) < $_SESSION['MINIMUM_USER_BOOKING_OPIONS'] ) ) ) {
		    //not enough options have been selected
        	if ( $event_user['is_admin'] == '1' ) {
       	        $page_error_message = "Please select a minimum of " . $_SESSION['MINIMUM_ADMIN_BOOKING_OPIONS'] . " booking options." ;
        	} else {
       	        $page_error_message = "Please select a minimum of " . $_SESSION['MINIMUM_USER_BOOKING_OPIONS'] . " booking options." ;
            }
			$event['description'] = stripslashes($_POST['desc']);
			$event['subject'] = stripslashes($_POST['subject']);
			$_REQUEST['action'] = 'modify';
		} else {
			if (modify_event($event_username, $event['event_id'], $_POST['subject'], $_POST['desc'], $_POST['bookingOptions'] )) {
				$page_info_message = "Event details modified successfully!";
				$event = get_event_details($_REQUEST['event_id']);
			} else {
				$page_error_message = "Event details could not be modified. Please try again.";
				$event['description'] = $_POST['desc'];
				$event['subject'] = $_POST['subject'];
				$_REQUEST['action'] = 'modify';
			}
		}
	} else if ($_REQUEST['action'] == 'submit_delete') {
		if (delete_event_slot($event_username, $event['event_id'], $_REQUEST['date_time'])) {
			$page_info_message = "Time slot deleted successfully!";
			$showEventDetails = false ;
		} else {
			$page_error_message = "Event time slot could not be deleted. Please try again.";
		}
		$_REQUEST['action'] = 'delete';
	} else if ($_REQUEST['action'] == 'delete_event') {
	    //make sure the user is within the time limit for a cancellation
        list($year, $month, $day) = explode("-",$starting_date);
        list($hour, $min, $sec) = explode(":", $starting_time);
        $secsUntilSlotStart = mktime($hour, $min, $sec, $month, $day, $year) - time() ;
        $secsForMinimumAllowedCancellation = $_SESSION['ADVANCE_CANCEL_LIMIT'] * 3600 ; //60 sec  x  60 mins  =  secs in an hour

        //echo "times: " . $secsUntilSlotStart . ' > ' . $secsForMinimumAllowedCancellation ;

        //make sure slot is not in the past
        if ( $secsUntilSlotStart < 0 ) {
            $page_error_message = 'This event has already been. You are not allowed to delete items in the past.' ;
        } else if ( wrap_session_is_registered("admin_user") || ($secsUntilSlotStart > $secsForMinimumAllowedCancellation) ) {
    		//check if we should refund the credits, we only do this if the start time is later than the minimum cancellation period
    		$refundUsersCredits = true ; //assume we do (will only apply if they use credits)
    		if ( wrap_session_is_registered("admin_user") && ($secsUntilSlotStart < $secsForMinimumAllowedCancellation) ) {
    		    //an admin is deleting this event for the user outside of the minimum cancellation period.
    		    //do the delete but don't give the user their credits back
    		    $refundUsersCredits = false ;
    		}
    		if (delete_event($event_username, $event['event_id'], $refundUsersCredits)) {
    			$page_info_message = "Event deleted successfully!";
    			if ( wrap_session_is_registered("admin_user") ) {
    			    if ( $refundUsersCredits ) {
    			        $page_info_message .= '<br><br>Booking credits have been returned to the user (where applicable).' ;
    			    } else {
    			        $page_info_message .= '<br><br>Any booking credits used have not been refunded as this booking falls inside of the minimum cancellation period.' ;
    			    }
    			}
    			$page_error_message = "No event to display.";
    			$showEventDetails = false ;
    		} else {
    			$page_error_message = "Event could not be deleted. Please try again.";
    			$_REQUEST['action'] = 'delete';
    		}
        } else {
            $page_error_message = 'You cannot delete this event. All deletions must be made at least ' . $_SESSION['ADVANCE_CANCEL_LIMIT'] . ' hour' ;
            if ( $_SESSION['ADVANCE_CANCEL_LIMIT'] != 1 ) {
                $page_error_message .= 's' ;
            }
            $page_error_message .= ' in advance of the event start time.' ;
        }
	}

} // end of if event_id

?>
<?
if (!empty($_REQUEST['page_info_message'])) $page_info_message = $_REQUEST['page_info_message'];
$page_title = 'Booking Calendar - Event Details';
$page_title_bar = "Event Details:";
//hide the title bar when in print view
if ( $_GET['print_view'] ) { $page_title_bar = '' ; }

include_once("header.php");

  // display details view
?>


<!-- add_event.php -->

<p align="center">


<!-- Table for Right Border Section -->
<table cellspacing="0" cellpadding="0" border="0">
<tr><td align="right" valign="top">


<?

// DISPLAY DETAILS
if ( $showEventDetails ) {
?>

<?	if ($_REQUEST['action'] == 'modify') { ?>
<form id="modify_event" action="<?=FILENAME_DETAILS_VIEW?>" method="post">
<?	} ?>

<table border="0" cellpadding="4" cellspacing="0">

<tr><td align="left" colspan="2"><strong>Location:</strong>  <?=htmlentities($location_display[stripslashes($event['location'])])?></td></tr>

<?
//display this table inline on the left when in print view
if ( $_GET['print_view'] ) {

    // RIGHT BAR SCHEDULE SECTION - on the LEFT

	if ($_REQUEST['event_id'] > 0) {
?>
<tr><td align="left" colspan="2">
<?
		$display_dates_and_time_ranges = get_event_dates_and_time_ranges($event['event_id'], $event['location']);
		//do_html_right_nav_bar_top(200);
?>
<strong>Booked Dates<br />and Time Ranges:</strong><br /><br />
<?
		if (count($display_dates_and_time_ranges) > 0) {
?>
<table cellspacing="1" cellpadding="0" border="0">
<?
			reset ($display_dates_and_time_ranges);
			foreach ($display_dates_and_time_ranges as $display_date_and_time) {
				list ($date, $time_range) = explode(" ", $display_date_and_time);
				list ($from_time, $to_time) = explode("-", $time_range);
?>
<tr><td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall"><?=short_date_format($date);?> &nbsp; </td>
<td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall"><?=format_time_to_ampm($from_time)?>-<?=format_time_to_ampm($to_time)?></td></tr>
<?
			}
?>
</table><br />
<?
		}
		//do_html_right_nav_bar_bottom(200);
	}
}
?>
</td></tr>


<tr><td align="left" colspan="2"><strong>Subject:</strong>
<?
	if ($_REQUEST['action'] == 'modify') {
		echo '<input type="text" name="subject" value="' . htmlentities(stripslashes($event['subject'])) . '" size="35" maxlength="150" />';
	} else {
	    if ( $_SESSION['SHOW_USER_DETAILS'] ) {
		    echo htmlentities(stripslashes($event['subject']));
		} else {
		    echo 'Booking Confirmed' ;
		}
	}
?></td></tr>

<?php
//is this user allowed to see the booking details?
if ( $_SESSION['SHOW_USER_DETAILS'] ) {
?>
    <tr><td colspan="2" align="left" valign="top"><strong>Event Description:</strong></td></tr>

    <tr><td colspan="2" align="left">
    <?
	if ($_REQUEST['action'] == 'modify') {
    ?>
        <img src="<?=DIR_WS_IMAGES?>spacer.gif" width="500" height="1" alt="" /><br />
        <textarea name="desc" rows="5" cols="60"><?= (stripslashes($event['description'])) ; ?></textarea><br>
<?
	} else {
		echo stripslashes($event['description']);
	}
?>
    </td></tr>

<?php
    //does this site use booking options?
    $result = wrap_db_query("SELECT option_id, description FROM " . BOOKING_OPTIONS_TABLE . " ORDER BY description ASC");
    if ( $result && ( wrap_db_num_rows( $result ) > 0 ) ) {

        //get the id's and descriptions for options chosen by the user
        $savedUserBookingOptionIDs = null ;
        $savedUserBookingOptionDescriptions = null ;
        $userBookingResult = wrap_db_query("SELECT e.option_id, o.description FROM " . BOOKING_EVENT_OPTIONS_TABLE . " AS e, " . BOOKING_OPTIONS_TABLE . " AS o WHERE e.event_id='" . $_REQUEST['event_id'] . "' AND e.option_id=o.option_id");
        if ( $userBookingResult && ( wrap_db_num_rows( $userBookingResult ) > 0 ) ) {
            while ( $userBookingFields = wrap_db_fetch_array($userBookingResult) ) {
                $savedUserBookingOptionsIDs[] = $userBookingFields['option_id'] ;
                $savedUserBookingOptionDescriptions[] = $userBookingFields['description'] ;
            }
        }
        $numBookingOptions = count( $savedUserBookingOptionDescriptions ) ;
        ?>
        <tr><td colspan="2" align="left" valign="top"><strong>Booking Options:</strong></td></tr>

        <tr><td colspan="2" align="left">
        <?php
    	if ($_REQUEST['action'] == 'modify') {
    	    //show tickable checkboxes
            ?>
            <table border="0" cellpadding="0" cellspacing="2">
            <?php

            $rightCol = false ;
            for ( $r = 0 ; $fields = wrap_db_fetch_array($result) ; $r++ ) {
                //is this a left or right column?
                if ( ( $r % 2 ) == 0 ) {
                    //left column
                    echo '<tr align="left"><td>' ;
                    $rightCol = false ;
                } else {
                    //right column
                    echo '<td width="20">&nbsp;</td><td>' ;
                    $rightCol = true ;
                }
                echo '<input type="checkbox" name="bookingOptions[]" value="' . $fields['option_id'] . '"' ;
                //check if returning from a post (eg, after error from missing a field)
                if ( $_POST['action'] == 'submit_modify' ) {
                    //make sure at least one booking option was selected before calling in_array
                    if ( count( $_POST['bookingOptions'] ) > 0 ) {
                        if ( in_array( $fields['option_id'], $_POST['bookingOptions'] ) ) {
                            echo ' checked="checked"' ;
                        }
                    }
                } else if ( $numBookingOptions > 0 ) {
                    //see if this value matches one stored in the db from this users saved options
                    if ( in_array( $fields['option_id'], $savedUserBookingOptionsIDs ) ) {
                        echo ' checked="checked"' ;
                    }
                }
                echo '>' . htmlspecialchars( stripslashes( $fields['description'] ) ) ;
                echo '</td>' ;
                if ( $rightCol ) {
                    echo '</tr>' ;
                }
            }
            //ensure the right column is properly closed if we have an odd number of options
            if ( !$rightCol ) {
                echo '<td>&nbsp;</td><td>&nbsp;</td></tr>' ;
            }
            ?>
            </table>
            <?php
        } else {
            //just display the options the user currently has
            for ( $o = 0 ; $o < $numBookingOptions ; $o++ ) {
                //handle commas to separate the list
                if ( $o != 0 ) {
                    echo ', ' ;
                }
                echo htmlspecialchars( stripslashes( $savedUserBookingOptionDescriptions[$o] ) ) ;
            }
        }
        ?>
        </td></tr>
        <?php
    }
    ?>
</td></tr>

    <?php
    if ($_REQUEST['action'] == 'modify') {
        ?>
        <tr><td colspan="2" align="center" valign="top">
        <?=make_hidden_fields(array('date', 'view', 'loc'))?>
        <input type="hidden" name="action" value="submit_modify" />
        <input type="hidden" name="event_id" value="<?=$event['event_id']?>" />
        <input type="submit" name="submit_button" value="Submit Event Changes" class="ButtonStyle" />
        <?php
    }

    //END - is this user allowed to see the booking details?
}
?>

<tr><td align="left" colspan="2"><span class="FontSoftSmall">
<br />Posted by: <?php
if ( $_SESSION['SHOW_USER_DETAILS'] ) {
    echo htmlentities(stripslashes($event_user['firstname'])) . ' ' . htmlentities(stripslashes($event_user['lastname'])) ;
} else {
    echo 'n/a' ;
}
?><br />
Date Posted: <? list($posted_date, $posted_time) = explode(" ",$event['date_time_added']); ?><?=short_date_format($posted_date);?> <?=format_time_to_ampm($posted_time)?><br />
<?
	if (!empty($event['last_mod_date_time']) && $event['last_mod_date_time'] != "0000-00-00 00:00:00") {
?>
Last Modified: <?=htmlentities(stripslashes($event['last_mod_date_time']))?><br />
<?
	}
?>
</span></td>

<?
	if ($display_modify_trigger || $display_delete_trigger) {
?>
<tr class="DoNotPrint"><td align="left" valign="top" nowrap="nowrap" colspan="2">
<br /><span class="FontSoftSmall">User Options:
<a href="<?=href_link(FILENAME_DETAILS_VIEW, 'event_id=' . $event['event_id'] . '&action=view&' . make_hidden_fields_workstring(), 'NONSSL')?>"><strong>View</strong></a>
<?
		//if ($display_modify_trigger) {
		if ( wrap_session_is_registered("admin_user") ) {
?>
| <a href="<?=href_link(FILENAME_DETAILS_VIEW, 'event_id=' . $event['event_id'] . '&action=modify&' . make_hidden_fields_workstring(), 'NONSSL')?>"><strong>Modify</strong></a>
<?
		}
		if ($display_delete_trigger) {
?>
| <a href="<?=href_link(FILENAME_DETAILS_VIEW, 'event_id=' . $event['event_id'] . '&action=delete_event&' . make_hidden_fields_workstring(), 'NONSSL')?>" onClick="return confirm('Are you sure you want to delete this entire event?');"><strong>Delete Event</strong></a>
<?php
        if ( wrap_session_is_registered("admin_user") ) {
            ?>
| <a href="<?=href_link(FILENAME_DETAILS_VIEW, 'event_id=' . $event['event_id'] . '&action=delete&' . make_hidden_fields_workstring(), 'NONSSL')?>"><strong>Delete Time Slots</strong></a>
| <a href="<?=href_link(FILENAME_DETAILS_VIEW, 'event_id=' . $event['event_id'] . '&print_view=1&action=view&' . make_hidden_fields_workstring(), 'NONSSL')?>" target="_blank"><strong>Print Ticket</strong></a>

<?
        }
	}
?>
</span></td></tr>
<?
	}
?>
</table>

<?	if ($_REQUEST['action'] == 'modify') { ?>
</form>
<?	} ?>


<?
// END OF DISPLAY DETAILS
?>





</td>
<td align="right" valign="top"><img
src="<?=DIR_WS_IMAGES?>spacer.gif" width="20" height="1" alt="" /></td>
<td align="right" valign="top">


<?
if ($_REQUEST['action'] == 'delete') {

// RIGHT BAR SCHEDULE SECTION - DELETE OPTIONS

	if ($_REQUEST['event_id'] > 0) {

		$display_dates_and_time_ranges = get_event_dates_and_time_ranges($event['event_id'], $event['location']);
		do_html_right_nav_bar_top(200);
?>
<strong>Booked Dates<br />and Time Slots:</strong><br />
Slot Duration: <?=BOOKING_TIME_INTERVAL?> min.<br /><br />

<?
		if (count($display_dates_and_time_ranges) > 0) {
?>
<table cellspacing="1" cellpadding="0" border="0">
<?
			reset ($display_dates_and_time_ranges);
			foreach ($display_dates_and_time_ranges as $display_date_and_time) {
				list ($date, $time_range) = explode(" ", $display_date_and_time);
				list ($from_time, $to_time) = explode("-", $time_range);
				$time_slots = get_times_in_range($from_time, $to_time, DISPLAY_TIME_INTERVAL);
				if (count($time_slots)>1) $trash = array_pop($time_slots);
				foreach ($time_slots as $time_slot) {
?>
<tr><td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall"><?=short_date_format($date);?> &nbsp; </td>
<td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall"><?=format_time_to_ampm($time_slot)?></td>
<td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall"> &nbsp;
	<a href="<?=FILENAME_DETAILS_VIEW?>?event_id=<?=$event['event_id']?>&date_time=<?=urlencode($date.' '.$time_slot.':00')?>&action=submit_delete"><strong>Delete</strong></a></td></tr>
<?
				} // end foreach time_slot
			}
?>
</table><br />
<?
		}
		do_html_right_nav_bar_bottom(200);
	}

} else {
?>
&nbsp;
<?

  //do not display this table on the right when in print view
  if ( !$_GET['print_view'] ) {

    // RIGHT BAR SCHEDULE SECTION - REGULAR

	if ($_REQUEST['event_id'] > 0) {

		$display_dates_and_time_ranges = get_event_dates_and_time_ranges($event['event_id'], $event['location']);
		do_html_right_nav_bar_top(200);
?>
<strong>Booked Dates<br />and Time Ranges:</strong><br /><br />
<?
		if (count($display_dates_and_time_ranges) > 0) {
?>
<table cellspacing="1" cellpadding="0" border="0">
<?
			reset ($display_dates_and_time_ranges);
			foreach ($display_dates_and_time_ranges as $display_date_and_time) {
				list ($date, $time_range) = explode(" ", $display_date_and_time);
				list ($from_time, $to_time) = explode("-", $time_range);
?>
<tr><td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall"><?=short_date_format($date);?> &nbsp; </td>
<td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall"><?=format_time_to_ampm($from_time)?>-<?=format_time_to_ampm($to_time)?></td></tr>
<?
			}
?>
</table><br />
<?
		}
		do_html_right_nav_bar_bottom(200);
	}
  }
?>

<?
}
?>

</td></tr></table>


<?
}

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>