<? include_once("./includes/application_top.php"); ?>
<?php
//	$numSelectedOptions = count( $_POST['bookingOptions'] ) ;
//	echo "<hr>num selected = $numSelectedOptions<hr>" . $_SESSION['MINIMUM_ADMIN_BOOKING_OPIONS'] . "<pre>";
//	print_r( $_POST ) ;
//	echo "</pre>" ;
//exit ;
?>
<?
if (REQUIRE_AUTH_FOR_ADDING_FLAG) {
    if (!@wrap_session_is_registered('valid_user')) {
    	header('Location: ' . href_link(FILENAME_LOGIN, 'origin=' . FILENAME_ADD_EVENT . '&' . make_hidden_fields_workstring(), 'NONSSL'));
        wrap_exit();
    }
}

//set a shorter test for admin/regular user
$is_admin = false ;
if ( wrap_session_is_registered("admin_user") ) {
    $is_admin = true ;
}
//set a shorter test for the block booking test
$can_block_book = false ;
if ( $is_admin ) {
    //all admins can block book - no exceptions
    $can_block_book = true ;
} else if ( wrap_session_is_registered("block_book") ) {
    //this is a regular user who is allowed to block book
    $can_block_book = true ;
}
//set some shorter vars for bookings made by admins
$ignoreRules = false ;
$deductCredits = true ;
$bookingByUserID = get_user_id($_SESSION['valid_user']) ;
$bookingForUserID = $bookingByUserID ; // a default that may get overwritten later on in the code
$bookingForUsername = $_SESSION['valid_user'] ;
$bookeeMinimumAdvanceBookingLimit = $_SESSION['MINIMUM_ADVANCE_BOOKING_LIMIT'] ;
$bookeeAdvanceBookingLimit = $_SESSION['ADVANCE_BOOKING_LIMIT'] ;
$bookeeUsesCredits = true ;
if ( $_SESSION['booking_credits'] == 'Not used') {
    $bookeeUsesCredits = false ;
}
$bookeeCreditsRemaining = $_SESSION['booking_credits'] ;
//update details if a booked_by value is passed
if ( isset( $_POST['booked_for'] ) && ( $_POST['booked_for'] != '' ) ) {
    //is this an admin booking for an unspecified person?
    if ( $_POST['booked_for'] == 'NotSet' ) {
        //it is. In this instance no booking rules apply at all
        $deductCredits = false ;
    } else {
        //specify who the admin is booking for
        $bookingForUserID = $_POST['booked_for'] ;
        $userDetails = get_user( $bookingForUserID ) ;
        $bookingForUsername = $userDetails['username'] ;
        $deductCredits = true ; //already set - leave as default
        $bookeeUsesCredits = true ;
        if ( $userDetails['booking_credits'] == 'Not used') {
            $bookeeUsesCredits = false ;
        }
        $bookeeCreditsRemaining = $userDetails['booking_credits'] ;
    }
    $ignoreRules = true ;
    $bookeeAdvanceBookingLimit = 0 ; //no booking limit
}


$page_error_message = '';
$total_bookings_already_made = 0 ;
$remaining_booking_slots_allowed = 0 ;

// ADD EVENT OR CHECK EVENT FORM SUBMIT BUTTONS
if (!empty($_POST['add_event']) || !empty($_POST['check_event'])) {

	// VALIDATE DATA

	// DEFINE DATES (Do not change from YYYY-MM-DD format!)
	$starting_date = sprintf("%04d-%02d-%02d", $_POST['start_year'], $_POST['start_mon'], $_POST['start_day']);
	$ending_date = sprintf("%04d-%02d-%02d", $_POST['end_year'], $_POST['end_mon'], $_POST['end_day']);
	$recur_date = sprintf("%04d-%02d-%02d", $_POST['recur_year'], $_POST['recur_mon'], $_POST['recur_day']);

	$dates_within_limit = true ; // default to everything is okay
	$bookingTooFarAhead = false ;
	$bookingTooSoonAhead = false ;

	$unix_todays_date = date('U') ;
	list ($start_hour, $start_min) = explode(":", $_POST['start_time']);
	$unix_starting_date = mktime( $start_hour, $start_min, 0, $_POST['start_mon'], $_POST['start_day'], $_POST['start_year'] ) ;

    //advance booking limit does not apply to admins
    if ( !$is_admin && !$ignoreRules ) {

    	// override the end and recurrence dates if they are beyond the booking limit
    	$curr_time = date('U') ;
    	$curr_year = date( 'Y', $curr_time ) ;
    	$curr_month = date( 'm', $curr_time ) ;
    	$curr_day = date( 'd', $curr_time ) ;
    	$curr_hour = date( 'H', $curr_time ) ;
    	$curr_min = date( 'i', $curr_time ) ;
    	$curr_sec = date( 's', $curr_time ) ;

    	$unix_ending_date = mktime( $curr_hour, $curr_min, $curr_sec, $_POST['end_mon'], $_POST['end_day'], $_POST['end_year'] ) ;
        $unix_recur_date = mktime( $curr_hour, $curr_min, $curr_sec, $_POST['recur_mon'], $_POST['recur_day'], $_POST['recur_year'] ) ;

        $unix_minimum_date = mktime( ($curr_hour + $bookeeMinimumAdvanceBookingLimit), $curr_min, $curr_sec, $curr_month, $curr_day, $curr_year ) ;
        $unix_limited_date = mktime( ($curr_hour + $bookeeAdvanceBookingLimit), $curr_min, $curr_sec, $curr_month, $curr_day, $curr_year ) ;

        if ( $unix_starting_date < $unix_minimum_date ) {
            //the booking is trying to be made inside the mimumum period (too soon to event start)

            //fail the request
            $dates_within_limit = false ;
            $bookingTooSoonAhead = true ;
        }

        $limited_year = date('Y', $unix_limited_date) ;
        $limited_month = date('m', $unix_limited_date) ;
        $limited_day = date('d', $unix_limited_date) ;

        $limited_ending_date = sprintf("%04d-%02d-%02d", $limited_year, $limited_month, $limited_day) ;

        if ( $unix_ending_date > $unix_limited_date ) {
            // end date is > limit. Replace end date with limited version.
            $ending_date = $limited_ending_date ;
            // update form to show max values
            $_POST['end_year'] = $limited_year ;
            $_POST['end_mon'] = $limited_month ;
            $_POST['end_day'] = $limited_day ;
            $_POST['end_time'] = MIN_BOOKING_HOUR ;
            $_REQUEST['end_year'] = $limited_year ;
            $_REQUEST['end_mon'] = $limited_month ;
            $_REQUEST['end_day'] = $limited_day ;
            $_REQUEST['end_time'] = MIN_BOOKING_HOUR ;

            //fail the request
            $dates_within_limit = false ;
            $bookingTooFarAhead = true ;
        }

        if ( $unix_recur_date > $unix_limited_date ) {
            // end date is > limit. Replace end date with limited version.
            $recur_date = $limited_ending_date ;
            // update form to show max values
            $_POST['recur_year'] = $limited_year ;
            $_POST['recur_mon'] = $limited_month ;
            $_POST['recur_day'] = $limited_day ;
            $_REQUEST['recur_year'] = $limited_year ;
            $_REQUEST['recur_mon'] = $limited_month ;
            $_REQUEST['recur_day'] = $limited_day ;

            //fail the request
            $dates_within_limit = false ;
            $bookingTooFarAhead = true ;
        }
    }

	// DEFINE THE RECURRING DATES, FREQUENCY AND INTERVAL

	$recur_freq = stripslashes($_REQUEST['recur_freq']);
	$recur_interval = stripslashes($_REQUEST['recur_interval']);
	$recurring_dates = array ();
	$recurring_dates = get_recurrence_dates($starting_date, $ending_date, $recur_date, $recur_freq, $recur_interval);

	//NUMBER OF SPANNING DAYS (NOT including the recurrence dates)
	$days_span = days_span($starting_date, $ending_date);
	//echo "<br />Days Span: $days_span<br />";


	// CHECK REQUIRED FIELDS

	if ($_POST['subject'] == "") {
		$page_error_message = "You have not filled out the add form completely. Please type in a subject.";
	}
	// CHECK DATES
    elseif ( $unix_starting_date < $unix_todays_date ) {
        $page_error_message = "Your starting date is in the past! Please enter a start date in the future." ;
    }
	elseif (!(check_valid_date($starting_date))) {
		$page_error_message = "Your starting date does not exist. There are only " .
			number_of_days_in_month($_POST['start_year'], $_POST['start_mon']) . " days in " . month_name($_POST['start_mon']) .
			" " . $_POST['start_year'] . ". Please check the calendar and try again.";
	}
	elseif (!($dates_within_limit)) {
	    if ( $bookingTooSoonAhead ) {
	        $page_error_message = "Your starting time is outside of the minimum booking period of " . $bookeeMinimumAdvanceBookingLimit . " hours.<br><br>" ;
		    $page_error_message .= "Please select a later booking slot and re-submit the form." ;
		} else {
		    //Basically: $bookingTooFarAhead == true
    		$page_error_message = "Your ending date is outside of the booking limit of " . ($bookeeAdvanceBookingLimit / 24) . " days.<br><br>" ;
    		$page_error_message .= "The form has been updated to reflect the maximum end and recurrence dates (where applicable).<br>Please check these dates, select an appropriate start date and re-submit the form." ;
        }
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

	elseif ( ( implode("", explode("-",$ending_date)) . implode("", explode(":",$_POST['end_time'])) )+0 <=
			 ( implode("", explode("-",$starting_date)) . implode("", explode(":",$_POST['start_time'])) )+0 ) {
		$page_error_message = "Your ending date and time must occur after your starting " .
			"date and time. Please check your dates and times and try again.";
	} // end of if/elseif

	// CHECK THAT RECUR DATE > ENDING DATE/TIME

	elseif ( implode("", explode("-",$recur_date))+0 <= implode("", explode("-",$ending_date))+0
			   && !($recur_interval == 'none' || $recur_interval == '') ) {
		$page_error_message = "Your recurring until date must occur after your ending " .
			"date. Please check your dates and try again.";
	} // end of if/elseif

    //if recurrence interval is set to none, overwrite the recur_date with
    //the start date to prevent entry of past recur dates into the db
    if ( ($recur_interval == 'none') || ($recur_interval == '') ) {
        $recur_date = $starting_date ;
    }

	// CHECK FOR OVERLAPPING RECURRENCE PROBLEM

	//echo "Recurrence Dates:<br />";
	reset($recurring_dates);
	while (list($date,$freq) = each($recurring_dates)) {
		//echo "Date: $date, Freq: $freq<br />";
		if ($date != '' && $freq > 1) {
			$page_error_message = 'Recurrency Problem: Your &quot;Recurrency Interval&quot; is '.
				'too small for the spanning days between your starting and ending dates.  This causes '.
				'overlapping of dates to occur.  Try increasing your &quot;Recurrency Interval&quot; '.
				'or change your starting and ending dates.';
		}
	}

	// MAKE SURE THE MINIMUM NUMBER OF REQUIRED BOOKING OPTIONS HAVE BEEN SELECTED
	$numSelectedOptions = count( $_POST['bookingOptions'] ) ;
    $user_info = get_user( $bookingForUserID ) ;
	if ( $user_info['is_admin'] == '1' ) {
	    if ( $numSelectedOptions < $_SESSION['MINIMUM_ADMIN_BOOKING_OPIONS'] ) {
	        $page_error_message = "Please select a minimum of " . $_SESSION['MINIMUM_ADMIN_BOOKING_OPIONS'] . " booking options." ;
	    }
	} else {
	    if ( $numSelectedOptions < $_SESSION['MINIMUM_USER_BOOKING_OPIONS'] ) {
	        $page_error_message = "Please select a minimum of " . $_SESSION['MINIMUM_USER_BOOKING_OPIONS'] . " booking options." ;
	    }
    }

    //check the users allowed booking limit. If it is > 0 (0 being unlimited) check that this is within limit:
    if ( !$ignoreRules ) {
        if (($user_info['max_bookings'] != '') && ($user_info['max_bookings'] > 0)) {

            // Check how many upcoming bookings the user already has reserved in the system
            $user_events_result = get_user_events($bookingForUsername, true) ;
            while ($user_events_row = wrap_db_fetch_array( $user_events_result) ) {
                //echo '<pre>' ;
                //print_r( $user_events_row ) ;
                //echo '</pre>' ;
            	$display_dates_and_time_ranges = get_event_dates_and_time_ranges($user_events_row['event_id'], $user_events_row['location']);
            	if (count($display_dates_and_time_ranges) > 0) {
            		reset ($display_dates_and_time_ranges);
            		foreach ($display_dates_and_time_ranges as $display_date_and_time) {
            			list ($date, $time_range) = explode(" ", $display_date_and_time);

            			list ($from_time, $to_time) = explode("-", $time_range);
            			$time_slots = get_times_in_range($from_time, $to_time, DISPLAY_TIME_INTERVAL);
            			if (count($time_slots)>1) $trash = array_pop($time_slots);
            			foreach ($time_slots as $time_slot) {
                            //make sure the dates and times are today (now) or later (ie. not in the past)
                            
							list($year, $month, $day) = explode("-",$date);
                            list($hour, $min, $sec) = explode(":", $time);
							
							// Hack for Warning: mktime() expects parameter 1 to be long, string given
							$hour = (int) $hour;
							
                            if (mktime($hour, $min, $sec, $month, $day, $year) > date('U')) {
                                $total_bookings_already_made++ ;
                            }
                		} // end foreach time_slot
                	}
                }
            }

            //update the number of booking slots remaining
            $remaining_booking_slots_allowed = $user_info['max_bookings'] - $total_bookings_already_made ;
            //echo "remain = $remaining_booking_slots_allowed, max = " . $user_info['max_bookings'] . ", total used = $total_bookings_already_made<br>" ;

            //make sure that this is not more than the user is allowed to make at any one time
            //and that they therefore have remaining booking slots available to them
            if ($remaining_booking_slots_allowed < 1) {
                //booking limit already reached, do not allow any more to be made
                $page_error_message = 'You have already reached your limit of ' . $user_info['max_bookings'] . ' booking' ;
                if ($user_info['max_bookings'] > 1) {
                    $page_error_message .= 's' ;
                }
                $page_error_message .= '.<br><br>You will be able to make new bookings as your existing bookings pass.' ;
            }
        }
    }


	// DATA HAS PASSED VALIDATION - PROCESS DATE AND TIME DATA

	if ($page_error_message == '') {

		//echo "Start Date: ".$starting_date."<br />";
		//echo "Start Time: ".$_POST['start_time']."<br />";
		//echo "End Date: ".$ending_date."<br />";
		//echo "End Time: ".$_POST['end_time']."<br />";

		// ENSURE THE CREATION OF THE SCHEDULE TABLE DATA FOR THE SPANNING

		// DETERMINE THE TIME RANGES FOR THE SPANNING DAYS

		$time_ranges_for_spanning_days = array (); // "hh:mm-hh:mm" format (min_time-max_time)
		$time_ranges_for_spanning_days = get_time_ranges_for_spanning_days(
				sprintf("%02d", MIN_BOOKING_HOUR).':00', sprintf("%02d", MAX_BOOKING_HOUR).':00',
				$_POST['start_time'], $_POST['end_time'], $days_span);
		//echo "<br />Time Ranges for Spanning Dates:<br />";

		//foreach ($time_ranges_for_spanning_days as $tr) { echo "---$tr<br />"; }
		//reset ($time_ranges_for_spanning_days);
		//echo "<br /><br />";

		// REPEATATIVELY BUILD-OUT THE TIME RANGES FOR SPANNING DAYS
		// OVER THE RECURRING DATES ARRAY. (This array includes the spanning days.)

		$display_dates_and_time_ranges = array (); // FOR HTML DISPLAY
		$scheduled_date_time_data = array (); // FOR SQL DATABASE

		$day = 1; // start on the first span day time range.
		reset($recurring_dates);
		while (list($date,$freq) = each($recurring_dates)) {
			if ($day > $days_span) { $day = 1; }
			$time_range = $time_ranges_for_spanning_days[$day-1];
			//echo "Time Range: $time_range <br />";
			list($range_start_time, $range_end_time) = explode("-", $time_range); // "hh:mm-hh:mm"
			// Define the date/time blocks in SQL format.
			$time_blocks = get_times_in_range ($range_start_time, $range_end_time,
							BOOKING_TIME_INTERVAL, false);
			foreach ($time_blocks as $time_block) {
				//echo "---$time_block<br />";
				$scheduled_date_time_data[] = $date.' '.$time_block.':00'; // SQL
			}
			$display_dates_and_time_ranges[] = $date.' '.$time_range; // Display
			$day++;
		}

		// CHECK AVAILABILITY OF DATE TIME DATA

		$scheduled_slots = count($scheduled_date_time_data);
		//echo "Scheduled Slots: ".$scheduled_slots."<br />";
		$available_date_time_data = array ();
		$unavailable_date_time_data = array ();
		$availability_count = check_schedule_availability(
					$scheduled_date_time_data, $location_db_name[stripslashes($_REQUEST['location'])] );
		//echo "Availability Count: ".$availability_count."<br />";
		// All slots not available condition check.
		if ($scheduled_slots > $availability_count) {
			$page_error_message = 'Availability Problem: Not all of the selected date and time slots ' .
					'are available for booking.  Please check the conflicting date and time slots, ' .
					'make the necessary changes to your booking form and try again.';
			$unavailable_date_time_data = find_schedule_unavailability(
					$scheduled_date_time_data, $location_db_name[stripslashes($_REQUEST['location'])] );
			if (count($unavailable_date_time_data) > 0) {
				$available_date_time_data = array_minus_array($scheduled_date_time_data, $unavailable_date_time_data);
			}
		} else if ( !ignoreRules && ($availability_count > $remaining_booking_slots_allowed) && (($user_info['max_bookings'] == '') || ($user_info['max_bookings'] != 0)) ) {
            //user is attempting to book more slots than they are allowed to (and they are not allowed unlimited bookings).
            //report error to user and do not allow the booking without ammendments
            $page_error_message = 'You are attempting to book ' . $availability_count . ' slot' ;
            if ($availability_count > 1) {
                $page_error_message .= 's' ;
            }
            $page_error_message .= ', however you ' ;
            if ( $total_bookings_already_made > 0 ) {
                $page_error_message .= 'already have ' . $total_bookings_already_made  . ' slot' ;
                if ($total_bookings_already_made > 1) {
                    $page_error_message .= 's' ;
                }
                $page_error_message .= ' booked and ' ;
            }
            $page_error_message .= 'are only allowed to book ' . $user_info['max_bookings'] . ' slot' ;
            if ($user_info['max_bookings'] > 1) {
                $page_error_message .= 's' ;
            }
            $page_error_message .= ' in advance.<br><br>' ;
            if ($availability_count > 1) {
                //more than 1 slot requested, suggest reducing dates or recurrence settings
                $page_error_message .= 'Please reduce the number of slots you are attempting to book by limiting your time and/or recurrence intervals.' ;
            } else {
                //only 1 slot requested, user has no slots left
                //Note: this clause should never get hit as a previous error should have been thrown.
                //      This is here for completeness and in case of other (earlier) failure
                $page_error_message .= 'You will be able to book additional slots as your existing bookings pass.' ;
            }
        } else if ( $deductCredits && $bookeeUsesCredits && ($bookeeCreditsRemaining < $scheduled_slots) ) {
            //if the user uses booking credits, ensure that they have enough
            //left to cover the number of slots that they are trying to book

            //getting into this if block tells us that the user DOES use credits
            //and that they do NOT have ENOUGH CREDITS left to cover the attempted booking
            //Set the right message depending on if we are making the booking for ourselves or not
            if ($bookingForUsername == $_SESSION['valid_user'] ) {
                //booking for self
                $page_error_message = 'You do not have enough credits to make this booking (' . ($scheduled_slots - $bookeeCreditsRemaining) . ' more required).<br>' ;
                $page_error_message .= 'Please purchase additional credits.' ;
            } else {
                //booking for someone else
                $page_error_message = 'User ' . $bookingForUsername . ' does not have any credits, <a href="' . FILENAME_ADMIN_BOOKING_CREDITS . '">ASSIGN</a> some more.' ;
            }
		} else {
		    //all is well, move the slots to the available array ready for insertion to the db
			$available_date_time_data = $scheduled_date_time_data;
		}
	}

	// IF ALL IS WELL WITH THE SCHEDULE DATA AVAILABILITY - ADD EVENT DATA

	//echo "valid user: ".$_SESSION['valid_user']."<br />user_id: ".get_user_id($_SESSION['valid_user'])."<br />";

	if ($page_error_message == '' && $_POST['add_event'] != "") {

        //see if the user wanted their selected options to be saved
        if ( $_POST['saveBookingOptions'] == 'yes' ) {
            if ( ( $bookingByUserID != '' ) && ( $bookingByUserID != '%' ) ) {
                //remove any existing preferences for this user
                $query = 'DELETE FROM ' . BOOKING_USER_OPTIONS_TABLE . ' WHERE user_id="' . $bookingByUserID . '"' ;
                wrap_db_query( $query ) ;

                //save the new preferences to the db for use next time
                $numBookingOptions = count( $_POST['bookingOptions'] ) ;
                for ( $o = 0 ; $o < $numBookingOptions ; $o++ ) {
                    $query = "INSERT INTO " . BOOKING_USER_OPTIONS_TABLE . " SET user_id = " . $bookingByUserID . ", option_id = '" . $_POST['bookingOptions'][$o] . "'" ;
                    wrap_db_query( $query ) ;
                }
            }
        }

		// Attempt to Add the Event to the Database
		$add_event_id = add_event($bookingForUsername, $scheduled_date_time_data, $_REQUEST['subject'], $_REQUEST['location'],
					$starting_date.' '.$_REQUEST['start_time'], $ending_date.' '.$_REQUEST['end_time'],
					$_REQUEST['recur_interval'], $_REQUEST['recur_freq'],
					$recur_date, $_REQUEST['desc'], $_POST['bookingOptions']);
		if (!empty($add_event_id)) {
		    $page_info_message = "Event added successfully!" ;

            //if the user uses booking credits update their remaining credits
		    if ( $deductCredits && $bookeeUsesCredits ) {

		        update_booking_credits( $bookingForUsername, $scheduled_slots, 'dec' ) ;

                //Set the right message depending on if we are making the booking for ourselves or not
                if ($bookingForUsername == $_SESSION['valid_user'] ) {
                    //booking for self
       			    $page_info_message .= '<br><br>' . $scheduled_slots . ' credits have been deducted for this booking. You have ' . remaining_booking_credits( $bookingForUsername ) . ' credits remaining.' ;
                } else {
                    //booking for someone else
                    $page_info_message .= '<br><br>NOTE: User ' . $bookingForUsername . ' has had ' . $scheduled_slots . ' credits deducted. ' . remaining_booking_credits( $bookingForUsername ) . ' credits remaining.' ;
                }
            }

            //see if booking cinfirmation e-mails are to get sent out
            if ( $_SESSION['BOOKING_CONF_EMAILS_SEND'] ) {
                //create an array of the things we are looking to replace in the body and subject of the e-mail
                $mailTags = Array( '%firstname%', '%lastname%', '%sitename%', '%bookingtimes%', '%bookingtimesvertical%', '%period%', '%location%', '%slots%', '%briefdesc%', '%fulldesc%', '%options%' ) ;

                //figure out the variables that might be reuired in the message
                $mailVars['firstname'] = $user_info['firstname'] ;
                $mailVars['lastname'] = $user_info['lastname'] ;
                $mailVars['sitename'] = SITE_NAME ;
                $mailVars['bookingtimes'] = '' ;
                $mailVars['bookingtimesvertical'] = '' ;

        		$horizSeparator = '' ;
        		$vertSeparator = '' ;
        		foreach( $scheduled_date_time_data as $display_date_and_time ) {
        			list($slot_date, $slot_time) = explode(" ", $display_date_and_time);
                    list($year, $month, $day) = explode("-",$slot_date);
                    list($hour, $min, $sec) = explode(":", $slot_time);
                    $niceTime = $day . '/' . $month . '/' . $year . ' at ' . $hour . ':' . $min ;
                    $mailVars['bookingtimes'] .= $horizSeparator . $niceTime ;
                    $mailVars['bookingtimesvertical'] .= $vertSeparator . $niceTime ;
                    //update the separators
            		$horizSeparator = ', ' ;
            		$vertSeparator = "\n" ;
            	}

                $mailVars['period'] = BOOKING_TIME_INTERVAL . ' minutes' ;
                $mailVars['location'] = $location_display[($_REQUEST['location'])] ;
                $mailVars['slots'] = $scheduled_slots ;
                $mailVars['briefdesc'] = $_REQUEST['subject'] ;
                $mailVars['fulldesc'] = $_REQUEST['desc'] ;

                $mailVars['options'] = '' ;
                $optionsSeparator = '' ;
                $numSelectedOptions = count( $_POST['bookingOptions'] ) ;
                for ( $o = 0 ; $o < $numSelectedOptions ; $o++ ) {
                    $result = wrap_db_query("SELECT description FROM " . BOOKING_OPTIONS_TABLE . " WHERE option_id='" . $_POST['bookingOptions'][$o] . "' LIMIT 0,1") ;
                    if ( $result && ( wrap_db_num_rows( $result ) > 0 ) ) {
                        if ( $fields = wrap_db_fetch_array($result) ) {
                            $mailVars['options'] .= $optionsSeparator . $fields['description'] ;
                            $optionsSeparator = ', ' ;
                        }
                    }
                }
                if ( strlen( $mailVars['options'] ) < 1 ) {
                    $mailVars['options'] = 'none' ;
                }


                //send a booking confirmation
                $mailSubject = str_replace( $mailTags, $mailVars, $_SESSION['BOOKING_CONF_EMAILS_SUBJECT'] ) ;
                $mailBody = str_replace( $mailTags, $mailVars, $_SESSION['BOOKING_CONF_EMAILS_BODY'] ) ;
                send_mail( $_SESSION['BOOKING_CONF_EMAILS_FROM_NAME'], $_SESSION['BOOKING_CONF_EMAILS_FROM'], ( $user_info['firstname'] . ' ' . $user_info['lastname'] ), $user_info['email'], $mailSubject, $mailBody ) ;
                //see if the user wants a copy CC'd to themselves
                if ( $_SESSION['BOOKING_CONF_EMAILS_CC'] !== false ) {
                    send_mail( $_SESSION['BOOKING_CONF_EMAILS_FROM_NAME'], $_SESSION['BOOKING_CONF_EMAILS_FROM'], $_SESSION['valid_user'], $_SESSION['BOOKING_CONF_EMAILS_CC'], $mailSubject, $mailBody ) ;
                }
            
			}
			
			
			 //see if buddy list notification emails are to be sent out
            if ( $_SESSION['BUDDY_LIST_EMAILS_SEND'] && !$is_admin && $notify_buddies=='1' ) {	
				// here i have to make 2 calls to the db beacuse running and old version of mysql, we cant run nested queries.
				// get the buddyid of all buddies			
				$Buddies = wrap_db_query( "SELECT buddy_id FROM " . BOOKING_BUDDIES . " where user_id = '" . $bookingByUserID . "'" ) ;
				// we only want to run the rest if the user has buddies (could possibly put in a session variable
				// which is cheched on login so we dont have to run the above query to improve performance
				if ( $Buddies && ( wrap_db_num_rows( $Buddies ) > 0 ) ) {
					
						while ( $myBuddies = wrap_db_fetch_array( $Buddies ) ) {
      						  $myBuddiesIDs[] = $myBuddies['buddy_id']; 
    						
							}
							
							if(!empty($myBuddiesIDs)) {
  							$allUsers = wrap_db_query( "SELECT user_id, firstname, lastname, email FROM " . BOOKING_USER_TABLE . " where user_id IN ( " . implode(",", $myBuddiesIDs) . ")" );
							
								while ( $myUsers = wrap_db_fetch_array( $allUsers ) ) {

									foreach ($myUsers as $item) {
										$my_users[$myUsers['user_id']]['firstname'] = $myUsers['firstname'];
										$my_users[$myUsers['user_id']]['lastname'] = $myUsers['lastname'];
										$my_users[$myUsers['user_id']]['email'] = $myUsers['email'];
									}  
								}
							}  							
						}
			
					
                	//create an array of the things we are looking to replace in the body and subject of the e-mail
                	$mailTags = Array( '%firstname%', '%lastname%', '%sitename%', '%bookingtimes%', '%bookingtimesvertical%', '%period%', '%location%', '%slots%', '%briefdesc%', '%fulldesc%', '%options%' ) ;

                	//figure out the variables that might be reuired in the message
               		$mailVars['firstname'] = $user_info['firstname'] ;
                	$mailVars['lastname'] = $user_info['lastname'] ;
                	$mailVars['sitename'] = SITE_NAME ;
                	$mailVars['bookingtimes'] = '' ;
                	$mailVars['bookingtimesvertical'] = '' ;

        			$horizSeparator = '' ;
        			$vertSeparator = '' ;
        			foreach( $scheduled_date_time_data as $display_date_and_time ) {
        				list($slot_date, $slot_time) = explode(" ", $display_date_and_time);
                    	list($year, $month, $day) = explode("-",$slot_date);
                    	list($hour, $min, $sec) = explode(":", $slot_time);
                    	$niceTime = $day . '/' . $month . '/' . $year . ' at ' . $hour . ':' . $min ;
                    	$mailVars['bookingtimes'] .= $horizSeparator . $niceTime ;
                    	$mailVars['bookingtimesvertical'] .= $vertSeparator . $niceTime ;
                    	//update the separators
            			$horizSeparator = ', ' ;
            			$vertSeparator = "\n" ;
            		}

                	$mailVars['period'] = BOOKING_TIME_INTERVAL . ' minutes' ;
                	$mailVars['location'] = $location_display[($_REQUEST['location'])] ;
                	$mailVars['slots'] = $scheduled_slots ;
                	$mailVars['briefdesc'] = $_REQUEST['subject'] ;
                	$mailVars['fulldesc'] = $_REQUEST['desc'] ;

                	$mailVars['options'] = '' ;
                	$optionsSeparator = '' ;
                	$numSelectedOptions = count( $_POST['bookingOptions'] ) ;
                	for ( $o = 0 ; $o < $numSelectedOptions ; $o++ ) {
                    	$result = wrap_db_query("SELECT description FROM " . BOOKING_OPTIONS_TABLE . " WHERE option_id='" . $_POST['bookingOptions'][$o] . "' LIMIT 0,1") ;
                    	if ( $result && ( wrap_db_num_rows( $result ) > 0 ) ) {
                        	if ( $fields = wrap_db_fetch_array($result) ) {
                            	$mailVars['options'] .= $optionsSeparator . $fields['description'] ;
                            	$optionsSeparator = ', ' ;
                        	}
                    	}
                	}
                	if ( strlen( $mailVars['options'] ) < 1 ) {
                    	$mailVars['options'] = 'none' ;
                	}

                	//send buddy email
                	$mailSubject = str_replace( $mailTags, $mailVars, $_SESSION['BUDDY_LIST_EMAILS_SUBJECT'] ) ;
                	$mailBody = str_replace( $mailTags, $mailVars, $_SESSION['BUDDY_LIST_EMAILS_BODY'] ) ;
                	
					// Added by BenH 03/12/06 - we only want to send if we have users
					if ( $my_users ) { 
					
							foreach($my_users as $d => $buddy_details){	
								
								send_mail( $_SESSION['BUDDY_LIST_EMAILS_FROM_NAME'], $_SESSION['BUDDY_LIST_EMAILS_FROM'], ( $buddy_details['firstname'] . ' ' . $buddy_details['lastname'] ), $buddy_details['email'], $mailSubject, $mailBody ) ;
							}
					}									
				}// end if user has no buddies
			  

			// Redirect to display page for user options (edit/delete).
            header('Location: ' . href_link(FILENAME_DETAILS_VIEW, 'event_id=' . $add_event_id . '&origin=' . FILENAME_ADD_EVENT . '&' . 'date='.$starting_date.'&' . make_hidden_fields_workstring() . '&page_info_message=' . urlencode( $page_info_message ), 'NONSSL'));
            wrap_exit();
        } else {
            $page_error_message = "We could not add your event. Please check your information and try again.";
        }

	} // end of if ($page_error_message == '')

	//add a BACK link for non admin users if there has been any kind of error.
	if ( ($page_error_message != '') && (!$is_admin) ) {
	    $page_error_message .= '<br><br><a href="javascript:history.go(-2);">Go back</a>' ;
	}

} // end of if ($_POST['add_want'] != "" || $_POST['check_event'] != "")


$page_title = "Booking Calendar - Add Booking Event";
$page_title_bar = "Add Booking Event:";
include_once("header.php");

//get some basic info about the user making the booking
$user_info = get_user($bookingForUserID) ;

/**
    //if ( $is_admin && empty($_POST['add_event']) && empty($_POST['check_event']) && ($page_error_message == '') ) {
    //    ?>
    //    <br>
    //    <b>Please Note:<br>
    //    <br>
    //    The maximum advance booking period is currently <?= ($_SESSION['ADVANCE_BOOKING_LIMIT'] / 24); ?> day<?= ($_SESSION['ADVANCE_BOOKING_LIMIT'] != 24) ? 's' : '' ; ?>.<br>
    //    Bookings (including recurring bookings) will not be accepted beyond this period.</b><br>
    //    <?php
    //} else if ( !$is_admin && empty($_POST['add_event']) && empty($_POST['check_event']) && ($page_error_message == '') ) {
*/
if ( !$is_admin && empty($_POST['add_event']) && empty($_POST['check_event']) && ($page_error_message == '') ) {
    ?>
    <br>
    <b>Please check your details</b><br>
    <br>
    Your booking will not be made until you press the 'Add Booking Event' button.<br>
    You will then receive your booking confirmation on the following page (if the slot is still available).<br>
    <br>
    Please check the details of your booking below:<br>
    <br>
    <?php
}

// Define some arrays for the Input Form below.
// Valid booking times in 24 hour format; includes max and min hours.
$valid_booking_times = get_times_in_range(MIN_BOOKING_HOUR, MAX_BOOKING_HOUR, BOOKING_TIME_INTERVAL, true);

  // display the form, new want or problem
?>

<!-- add_event.php -->


<br />
<div align="center">


<!-- Table for Right Border Section -->
<table cellspacing="0" cellpadding="0" border="0">
<tr><td align="right" valign="top">



<form id="add_event_table" name="add_event_table" action="<?=FILENAME_ADD_EVENT?>" method="post">
<table border="0" cellpadding="2" cellspacing="0">
<?php
if ( $is_admin ) {
?>
<tr>
<td align="right">Booking For: </td>
<td align="left">
    <select name="booked_for" size="4" onChange="document.forms.add_event_table.subject.value=this.options[this.selectedIndex].id;">
        <option value="NotSet" selected="true">Not Set - enter subject below</option>
        <?php
        //get a list of users
        $result = wrap_db_query("SELECT user_id, username, firstname, lastname, email FROM " . BOOKING_USER_TABLE . " ORDER BY lastname, firstname, username");
        if ($result) {
            while ( $fields = wrap_db_fetch_array($result) ) {
                ////check if this is the main admin account
                //if ($fields['username'] == 'admin') {
                //    //it is so skip it and move on to the next one, ie don't display the admin account
                //    continue ;
                //}
//                echo '<option value="' . $fields['firstname'] . ' ' . $fields['lastname'] . '"' ;
                echo '<option value="' . $fields['user_id'] . '" id="' . $fields['firstname'] . ' ' . $fields['lastname'] . '"' ;
                if ( $_POST['booked_for'] == $fields['user_id'] ) {
                    echo ' selected="true"' ;
                }
                echo '>' . $fields['lastname'] . ', ' . $fields['firstname'] . ' - ' . $fields['username'] . '</option>' . "\n\t\t" ;
            }
        }
        ?>
    </select>
</td>
</tr>
<?php
}
?>
<tr>
<td align="right">Subject: <?php
    if ( $is_admin ) {
        ?><br /><em class="FontSoftSmall">(Brief Description)</em><?php
    }
    ?></td>
<td align="left"><?php
    if ( $is_admin ) {
        $subjectFieldValue = htmlentities(stripslashes($_REQUEST['subject'])) ;
    } else {
        $subjectFieldValue =  'Booking by ' . $user_info['firstname'] . ' ' . $user_info['lastname'] ;
        echo $subjectFieldValue ;
    }
    ?><input type="<?= ($is_admin) ? 'text' : 'hidden' ; ?>" name="subject" value="<?= $subjectFieldValue ; ?>" size="35" maxlength="150" /></td>
</tr>


<tr>
<td align="right">Location: </td>
<td align="left">
<?php
if ($_REQUEST['location'] == '' && $_REQUEST['loc'] != '') { $_REQUEST['location'] = $_REQUEST['loc']; }
    reset($location_display);
if ( $is_admin ) {
?>
<select name="location">
<?
    while (list ($location_id, $location_display_name)= each($location_display)) { ?>
    <option value="<?=$location_id?>"<?=($_REQUEST['location'] == $location_id) ? ' selected="selected"' : ''?>><?=$location_display_name?></option>
<? } ?>
</select>
<?php
} else {
    //for non admin users, display the location name and output a hidden field containing the value
    while (list ($location_id, $location_display_name)= each($location_display)) {
        if ($_REQUEST['location'] == $location_id) {
            echo '<input type="hidden" name="location" value="' . $location_id . '">' ;
            echo $location_display_name ;
            break ;
        }
    }
}
?></td></tr>


<tr>
<td align="right">Starting Date/Time: </td>
<td align="left">
<?php
if ( $is_admin || $can_block_book ) {
?>
<select name="start_mon">
<? if ($_REQUEST['start_mon'] == '') { $_REQUEST['start_mon'] = SELECTED_DATE_MONTH; }
   for ($i=1; $i<=12; $i++) { // Defined 1-12 ?>
<option value="<?=$i?>"<?=($_REQUEST['start_mon']+0 == $i) ? ' selected="selected"' : ''?>><?=month_name($i)?></option>
<? } ?>
</select>
<select name="start_day">
<? if ($_REQUEST['start_day'] == '') { $_REQUEST['start_day'] = SELECTED_DATE_DAY; }
   for ($i=1; $i<=31; $i++) { ?>
<option value="<?=$i?>"<?=($_REQUEST['start_day']+0 == $i) ? ' selected="selected"' : ''?>><?=$i?></option>
<? } ?>
</select>,
<select name="start_year">
<? if ($_REQUEST['start_year'] == '') { $_REQUEST['start_year'] = SELECTED_DATE_YEAR; }
   for ($i=TODAYS_DATE_YEAR-1; $i <= TODAYS_DATE_YEAR+11; $i++) { ?>
<option value="<?=$i?>"<?=($_REQUEST['start_year']+0 == $i) ? ' selected="selected"' : ''?>><?=$i?></option>
<? } ?>
</select>
 at
<select name="start_time">
<? $index_cnt = 0;
   $start_time_index = 0;
   reset ($valid_booking_times);
   foreach ($valid_booking_times as $time) {
      if ($time != $valid_booking_times[count($valid_booking_times)-1]) {
?>
<option value="<?=$time?>" <?=($_REQUEST['start_time'] == $time) ? ' selected="selected"' : ''?>><?=(DEFINE_AM_PM == true) ? format_time_to_ampm($time) : $time?></option>
<?    }
      if ($_REQUEST['start_time'] == $time) { $start_time_index = $index_cnt; }
      $index_cnt++;
   } ?>
</select>
<?php
} else {
    //non admin users see non-editable values displayed on screen.
    //hidden form fields are used to post the values back to the page in case of errors.
    if ($_REQUEST['start_mon'] == '') { $_REQUEST['start_mon'] = SELECTED_DATE_MONTH; }
    echo month_name($_REQUEST['start_mon']) ;
    echo '<input type="hidden" name="start_mon" value="' . $_REQUEST['start_mon'] . '"> ' ;

    if ($_REQUEST['start_day'] == '') { $_REQUEST['start_day'] = SELECTED_DATE_DAY; }
    echo $_REQUEST['start_day'] ;
    echo '<input type="hidden" name="start_day" value="' . $_REQUEST['start_day'] . '">, ' ;

    if ($_REQUEST['start_year'] == '') { $_REQUEST['start_year'] = SELECTED_DATE_YEAR; }
    echo $_REQUEST['start_year'] ;
    echo '<input type="hidden" name="start_year" value="' . $_REQUEST['start_year'] . '"> ' ;

    echo 'at ' ;

    $index_cnt = 0;
    $start_time_index = 0;
    reset ($valid_booking_times);
    foreach ($valid_booking_times as $time) {
        if ($time != $valid_booking_times[count($valid_booking_times)-1]) {
            //set a default time in case the user has not selected one yet...
            //...this was not previously required as a drop down defaults to the top value if none is marked as the default.
            if ( ($_REQUEST['start_time'] == '') || ($_REQUEST['start_time'] == $time) ) {
                if (DEFINE_AM_PM == true) {
                    echo format_time_to_ampm($time) ;
                } else {
                    echo $time ;
                }
                echo '<input type="hidden" name="start_time" value="' . $time . '">' ;
                $start_time_index = $index_cnt ;
                break ; //no point looping further
            }
        }
        $index_cnt++;
    }
}
?>
</td>
</tr>


<tr>
<td align="right">Ending Date/Time: </td>
<td align="left">
<?php
if ( $is_admin || $can_block_book ) {
?>
<select name="end_mon">
<? if ($_REQUEST['start_mon'] != '' && $_REQUEST['end_mon'] == '') { $_REQUEST['end_mon'] = $_REQUEST['start_mon']; }
   elseif ($_REQUEST['end_mon'] == '') { $_REQUEST['end_mon'] = SELECTED_DATE_MONTH; }
   for ($i=1; $i<=12; $i++) { // Defined 1-12 ?>
<option value="<?=$i?>"<?=($_REQUEST['end_mon']+0 == $i) ? ' selected="selected"' : ''?>><?=month_name($i)?></option>
<? } ?>
</select>
<select name="end_day">
<? if ($_REQUEST['start_day'] != '' && $_REQUEST['end_day'] == '') { $_REQUEST['end_day'] = $_REQUEST['start_day']; }
   elseif ($_REQUEST['end_day'] == '') { $_REQUEST['end_day'] = SELECTED_DATE_DAY; }
   for ($i=1; $i<=31; $i++) { ?>
<option value="<?=$i?>"<?=($_REQUEST['end_day']+0 == $i) ? ' selected="selected"' : ''?>><?=$i?></option>
<? } ?>
</select>,
<select name="end_year">
<? if ($_REQUEST['start_year'] != '' && $_REQUEST['end_year'] == '') { $_REQUEST['end_year'] = $_REQUEST['start_year']; }
   elseif ($_REQUEST['end_year'] == '') { $_REQUEST['end_year'] = SELECTED_DATE_YEAR; }
   for ($i=TODAYS_DATE_YEAR-1; $i <= TODAYS_DATE_YEAR+11; $i++) { ?>
<option value="<?=$i?>"<?=($_REQUEST['end_year']+0 == $i) ? ' selected="selected"' : ''?>><?=$i?></option>
<? } ?>
</select>
 at
<select name="end_time">
<? reset ($valid_booking_times);
   if ($_REQUEST['end_time'] == '') {
      $_REQUEST['end_time'] = $valid_booking_times[$start_time_index+1];
   }
   foreach ($valid_booking_times as $time) {
      if ($time != $valid_booking_times[0]) {
?>
<option value="<?=$time?>" <?=($_REQUEST['end_time'] == $time) ? ' selected="selected"' : ''?>><?=(DEFINE_AM_PM == true) ? format_time_to_ampm($time) : $time?></option>
<?    }
   } ?>
</select>
<?php
} else {
    //non admin users see non-editable values displayed on screen.
    //hidden form fields are used to post the values back to the page in case of errors.
    if ($_REQUEST['start_mon'] != '' && $_REQUEST['end_mon'] == '') { $_REQUEST['end_mon'] = $_REQUEST['start_mon']; }
    elseif ($_REQUEST['end_mon'] == '') { $_REQUEST['end_mon'] = SELECTED_DATE_MONTH; }
    echo month_name($_REQUEST['end_mon']) ;
    echo '<input type="hidden" name="end_mon" value="' . $_REQUEST['end_mon'] . '"> ' ;

    if ($_REQUEST['start_day'] != '' && $_REQUEST['end_day'] == '') { $_REQUEST['end_day'] = $_REQUEST['start_day']; }
    elseif ($_REQUEST['end_day'] == '') { $_REQUEST['end_day'] = SELECTED_DATE_DAY; }
    echo $_REQUEST['end_day'] ;
    echo '<input type="hidden" name="end_day" value="' . $_REQUEST['end_day'] . '">, ' ;

    if ($_REQUEST['start_year'] != '' && $_REQUEST['end_year'] == '') { $_REQUEST['end_year'] = $_REQUEST['start_year']; }
    elseif ($_REQUEST['end_year'] == '') { $_REQUEST['end_year'] = SELECTED_DATE_YEAR; }
    echo $_REQUEST['end_year'] ;
    echo '<input type="hidden" name="end_year" value="' . $_REQUEST['end_year'] . '"> ' ;

    echo 'at ' ;

    reset ($valid_booking_times);
    if ($_REQUEST['end_time'] == '') {
        $_REQUEST['end_time'] = $valid_booking_times[$start_time_index+1];
    }
    foreach ($valid_booking_times as $time) {
        if ($time != $valid_booking_times[0]) {
            if ($_REQUEST['end_time'] == $time) {
                if (DEFINE_AM_PM == true) {
                    echo format_time_to_ampm($time) ;
                } else {
                    echo $time ;
                }
                echo '<input type="hidden" name="end_time" value="' . $time . '">' ;
                break ; //no point looping further
            }
        }
    }
}
?>
</td>
</tr>

<?php
if ( $is_admin ) {
    //if not, jump to line 665 just before the description values are set
?>
<script language="javascript">
<!--
function setRecurrenceVisibility( setTo ) {
    ro_i = document.getElementById( 'recurrenceOptions_interval' ) ;
    ro_i.style.display = setTo ;
    ro_f = document.getElementById( 'recurrenceOptions_frequency' ) ;
    ro_f.style.display = setTo ;
    ro_ud = document.getElementById( 'recurrenceOptions_untilDate' ) ;
    ro_ud.style.display = setTo ;
}
// -->
</script>
<tr>
<td align="right" valign="top" nowrap="nowrap">Show Recurrence Options: </td>
<td align="left" width="400"><input type="radio" name="showRecurrence" value="no" onClick="setRecurrenceVisibility('none');"<?= ( $_POST['showRecurrence'] == 'yes' ) ? '' : ' checked="checked"' ; ?>> No &nbsp;&nbsp; <input type="radio" name="showRecurrence" value="yes" onClick="setRecurrenceVisibility('inline');"<?= ( $_POST['showRecurrence'] == 'yes' ) ? ' checked="checked"' : '' ; ?>> Yes</tr>

<tr id="recurrenceOptions_interval" style="display: <?= ( $_POST['showRecurrence'] == 'yes' ) ? 'inline' : 'none' ; ?>;">
<td class="BgcolorNormal" align="right" valign="top" nowrap="nowrap"><br />Recurrence Interval:
<br /><em class="FontSoftSmall">(Optional)</em> </td>
<td class="BgcolorNormal" align="left">
<input type="radio" name="recur_interval" value="none"<?=($_REQUEST['recur_interval'] == 'none' || $_REQUEST['recur_interval'] == '') ? ' checked="checked"' : ''?> />None
<br /><em class="FontSoftSmall">No recurrency.</em><br />
<input type="radio" name="recur_interval" value="day"<?=($_REQUEST['recur_interval'] == 'day') ? ' checked="checked"' : ''?> />Daily
<br /><em class="FontSoftSmall">Recur daily can be used to span even more days.</em><br />
<input type="radio" name="recur_interval" value="week"<?=($_REQUEST['recur_interval'] == 'week') ? ' checked="checked"' : ''?> />Weekly
<br /><em class="FontSoftSmall">Recur every week.</em><br />
<input type="radio" name="recur_interval" value="day-month"<?=($_REQUEST['recur_interval'] == 'day-month') ? ' checked="checked"' : ''?> />Monthly (by day of the month)
<br /><em class="FontSoftSmall">Recur based on day of the month; 3rd, 14th or 20th. </em><br />
<input type="radio" name="recur_interval" value="weekday-month"<?=($_REQUEST['recur_interval'] == 'weekday-month') ? ' checked="checked"' : ''?> />Monthly (by occuring weekday of the month)
<br /><em class="FontSoftSmall">Recur based on the occuring weekday;1st Thursday or 3rd Monday.</em><br />
<input type="radio" name="recur_interval" value="year"<?=($_REQUEST['recur_interval'] == 'year') ? ' checked="checked"' : ''?> />Yearly
</td>
</tr>


<tr id="recurrenceOptions_frequency" style="display: <?= ( $_POST['showRecurrence'] == 'yes' ) ? 'inline' : 'none' ; ?>;">
<td class="BgcolorNormal" align="right" valign="top" nowrap="nowrap">Recurrence Frequency:
<br /><em class="FontSoftSmall">(Optional)</em> </td>
<td class="BgcolorNormal" nowrap="nowrap" align="left">
<select name="recur_freq">
<option value="1"<?=($_REQUEST['recur_freq'] == "1") ? ' selected="selected"' : ''?>>1 (Normal)</option>
<? for ($i=2; $i<10; $i++) { ?>
<option value="<?=$i?>"<?=($_REQUEST['recur_freq'] == $i) ? ' selected="selected"' : ''?>><?=$i?></option>
<? } ?>
</select>
<br /><em class="FontSoftSmall">
For example, if the recurrence interval is set to &quot;weekly&quot;, this<br />
setting can be used to recur on every 2 weeks, 3 weeks,<br />
5 weeks, etc. until the recur until date.</em><br />
</td></tr>


<tr id="recurrenceOptions_untilDate" style="display: <?= ( $_POST['showRecurrence'] == 'yes' ) ? 'inline' : 'none' ; ?>;">
<td class="BgcolorNormal" align="right" valign="top" nowrap="nowrap">Recur Until Date:
<br /><em class="FontSoftSmall">(Optional)</em> </td>
<td class="BgcolorNormal" align="left">
<select name="recur_mon">
<? if ($_REQUEST['start_mon'] != '' && $_REQUEST['recur_mon'] == '') { $_REQUEST['recur_mon'] = $_REQUEST['start_mon']; }
   elseif ($_REQUEST['recur_mon'] == '') { $_REQUEST['recur_mon'] = SELECTED_DATE_MONTH; }
   for ($i=1; $i<=12; $i++) { // Defined 1-12 ?>
<option value="<?=$i?>"<?=($_REQUEST['recur_mon']+0 == $i) ? ' selected="selected"' : ''?>><?=month_name($i)?></option>
<? } ?>
</select>
<select name="recur_day">
<? if ($_REQUEST['start_day'] != '' && $_REQUEST['recur_day'] == '') { $_REQUEST['recur_day'] = $_REQUEST['start_day']; }
   elseif ($_REQUEST['recur_day'] == '') { $_REQUEST['recur_day'] = SELECTED_DATE_DAY; }
   for ($i=1; $i<=31; $i++) { ?>
<option value="<?=$i?>"<?=($_REQUEST['recur_day']+0 == $i) ? ' selected="selected"' : ''?>><?=$i?></option>
<? } ?>
</select>,
<select name="recur_year">
<? if ($_REQUEST['start_year'] != '' && $_REQUEST['recur_year'] == '') { $_REQUEST['recur_year'] = $_REQUEST['start_year']; }
   elseif ($_REQUEST['recur_year'] == '') { $_REQUEST['recur_year'] = SELECTED_DATE_YEAR; }
   for ($i=TODAYS_DATE_YEAR-1; $i <= TODAYS_DATE_YEAR+11; $i++) { ?>
<option value="<?=$i?>"<?=($_REQUEST['recur_year']+0 == $i) ? ' selected="selected"' : ''?>><?=$i?></option>
<? } ?>
</select>
</td>
</tr>


<tr>
<td align="center" colspan="2">
<br />It is highly recommended to check availability before writing your description!<br />
<input type="submit" name="check_event" value="Check Schedule Availability" class="ButtonStyle" />
<br /><img src="<?=DIR_WS_IMAGES?>spacer.gif" width="500" height="1" alt="" /></td>
</tr>

<?php
}
?>

<tr>
<td align="right" valign="top">Detailed Description: </td><td align="left">&nbsp;</td>
</tr>

<tr>
<td align="center" colspan="2">
<textarea name="desc" rows="5" cols="60"><?php
if ( isset( $_REQUEST['desc'] ) ) {
    echo (stripslashes($_REQUEST['desc'])) ;
}
?></textarea><br>
</td></tr>

<?php
$result = wrap_db_query("SELECT option_id, description FROM " . BOOKING_OPTIONS_TABLE . " ORDER BY description ASC");
if ( $result && ( wrap_db_num_rows( $result ) > 0 ) ) {
    $showingBookingOptions = true ;
?>
    <tr>
    <td align="right" valign="top">Booking Options: </td><td align="left">&nbsp;</td>
    </tr>

    <tr>
    <td align="center" colspan="2">
    <script language="javascript">
    <!--
    function updateBookingOptions() {
        numCheckBoxes = document.forms.add_event_table.length ;
        document.forms.add_event_table.bookingOptionsDesc.value = '' ;
        for ( i = 0; i < numCheckBoxes ; i++ ) {
    		if (document.forms.add_event_table[i].id.substr(0, 5) == "bocb-") {
    			if ( document.forms.add_event_table[i].checked == true ) {
    			    document.forms.add_event_table.bookingOptionsDesc.value += ( '- ' + document.forms.add_event_table[i].id.substr(5) + '\n' ) ;
    			}
    		}
    	}

    }
    // -->
    </script>
    <textarea name="bookingOptionsDesc" rows="5" cols="60" readonly="true"><?php
    if ( isset( $_REQUEST['bookingOptionsDesc'] ) ) {
        echo (stripslashes($_REQUEST['bookingOptionsDesc'])) ;
    }
    ?></textarea><br>

    <table border="0" cellpadding="0" cellspacing="2">
    <?php
    //load any saved booking option preferences this user may have
    $savedUserPrefOptions = null ;
    //only non-admins can save their preferences, admins should have to tick them each time
    if ( !$is_admin ) {
        $userPrefResult = wrap_db_query("SELECT option_id FROM " . BOOKING_USER_OPTIONS_TABLE . " WHERE user_id='" . $bookingByUserID . "'");
        if ( $userPrefResult && ( wrap_db_num_rows( $userPrefResult ) > 0 ) ) {
            while ( $userPrefFields = wrap_db_fetch_array($userPrefResult) ) {
                $savedUserPrefOptions[] = $userPrefFields['option_id'] ;
            }
        }
    }

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
        echo '<input type="checkbox" name="bookingOptions[]" id="bocb-' . str_replace( '"', "'", $fields['description'] ) . '" value="' . $fields['option_id'] . '" onclick="updateBookingOptions();"' ;
        //check if returning from a post (eg, after error from missing a field)
        if ( isset( $_POST['bookingOptions'] ) && ( count( $_POST['bookingOptions'] ) > 0 ) ) {
            if ( in_array( $fields['option_id'], $_POST['bookingOptions'] ) ) {
                echo ' checked="checked"' ;
            }
        } else if ( count( $savedUserPrefOptions ) > 0 ) {
            //see if this value matches one stored in the db from this users saved preferences
            if ( in_array( $fields['option_id'], $savedUserPrefOptions ) ) {
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
    </td></tr>

    <?php
}
?>

<tr>
<td align="center" colspan="2">
<br />

<?php
//output hidden recurrence values for non admins to keep things working smoothly
if ( !$is_admin ) {
    ?>
    <input type="hidden" name="recur_interval" value="none">
    <input type="hidden" name="recur_freq" value="1">
    <input type="hidden" name="recur_mon" value="<?= $_REQUEST['end_mon'] ; ?>">
    <input type="hidden" name="recur_day" value="<?= $_REQUEST['end_day'] ; ?>">
    <input type="hidden" name="recur_year" value="<?= $_REQUEST['end_year'] ; ?>">
    <?php
}
?>

<?=make_hidden_fields(array('date', 'view', 'loc'))?>
<input type="hidden" name="check_event" value="yes" />
<input type="submit" name="add_event" value="Add Booking Event" class="ButtonStyle" />

<?php
if ( $showingBookingOptions && !$is_admin ) {
?>
<br>
<br>
<i>Remember these options next time I make a booking: </i><input type="checkbox" name="saveBookingOptions" value="yes"<?= ( ( count( $_POST ) > 0 ) && !isset( $_POST['saveBookingOptions'] ) ) ? '' : 'checked="checked"' ; ?>>
<?php
}


if ( $_SESSION['BUDDY_LIST_EMAILS_SEND'] && !$is_admin ) {	
?>
<br />
<i>Notify my buddies when I make this booking: </i>
<input name="notify_buddies" type="checkbox" id="notify_buddies" value="1" checked="checked" /></td></tr>
<?  } ?>

</table>

</form>


</td>

<td align="right" valign="top"><img
src="<?=DIR_WS_IMAGES?>spacer.gif" width="20" height="1" alt="" /></td>
<td align="right" valign="top">



<?

// RIGHT BAR/BORDER SCHEDULE SECTION

if (($_POST['add_event'] != "" || $_POST['check_event'] != "") && $scheduled_slots > 0) {

	do_html_right_nav_bar_top(200);
	if ($_POST['add_event']) {
		echo "<strong>Event Booking:</strong><br />";
	} else {
		echo "<strong>Schedule Check:</strong><br />";
	}
?>
<?=$location_display[$_POST['location']]?><br />
Total Booking Slots: <?=$scheduled_slots?><br />
Slot Duration: <?=BOOKING_TIME_INTERVAL?> min.<br /><br />
<strong>Requested Dates<br />and Time Ranges:</strong><br />
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
	} else {
		echo "<strong>None</strong><br />";
	}

	if (count($unavailable_date_time_data) > 0) {
?>
<strong>Unavailable Time Slots:</strong><br />
<table cellspacing="1" cellpadding="0" border="0" width="1%">

<?
		reset ($unavailable_date_time_data);
		foreach ($unavailable_date_time_data as $unavailable_slot) {
			list ($date, $time) = explode(" ", $unavailable_slot);
?>
<tr><td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall"><?=short_date_format($date);?> &nbsp; </td>
<td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall"><?=format_time_to_ampm($time)?></td></tr>
<?
		}
?>
</table><br />
<?
	}

	$show_available = true;
	if (count($available_date_time_data) > 0 && count($available_date_time_data) < 50 && $show_available) {
?>
<strong>Available Time Slots:</strong><br />
<table cellspacing="1" cellpadding="0" border="0" width="1%">

<?
		reset ($available_date_time_data);
		foreach ($available_date_time_data as $available_slot) {
			list ($date, $time) = explode(" ", $available_slot);
?>
<tr><td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall"><?=short_date_format($date);?> &nbsp; </td>
<td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall"><?=format_time_to_ampm($time)?></td></tr>
<?
		}
?>
</table><br />
<?
	} elseif (count($available_date_time_data) > 0 && $show_available)  {
?>
<strong>Available Time Slots:</strong>
<table cellspacing="1" cellpadding="0" border="0" width="1%">
<tr><td align="left" valign="top" nowrap="nowrap" class="FontSoftSmall">
<?=count($available_date_time_data)?><br />
</td></tr></table>
<?
	}

	do_html_right_nav_bar_bottom(200);
}
?>

</td></tr></table>

</div>

<?php
if ( $showingBookingOptions ) {
?>
<script language="javascript">
<!--
updateBookingOptions();
// -->
</script>
<?
}

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>