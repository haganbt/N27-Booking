<?php

//set some additional one-time session variables if they do not already exist
//this saves repeating db queries for what are basically static values
//
//rahter than doing a separate query for each value, we now pull all of the
//values in one go and use a switch statement to follow the correct behaviour
//for the various options


if ( ( !isset($_SESSION['PUBLIC_REGISTER_FLAG']) ) || ( !isset($_SESSION['ADVANCE_BOOKING_LIMIT']) ) || ( !isset($_SESSION['MINIMUM_ADVANCE_BOOKING_LIMIT']) ) || ( !isset($_SESSION['ADVANCE_CANCEL_LIMIT']) ) || ( !isset($_SESSION['SHOW_USER_DETAILS']) ) || ( !isset($_SESSION['MINIMUM_USER_BOOKING_OPIONS']) ) || ( !isset($_SESSION['MINIMUM_ADMIN_BOOKING_OPIONS']) ) || ( !isset( $_SESSION['BOOKING_CONF_EMAILS_SEND'] ) ) || ( !isset( $_SESSION['BOOKING_CONF_EMAILS_FROM_NAME'] ) ) || ( !isset( $_SESSION['BOOKING_CONF_EMAILS_FROM'] ) ) || ( !isset( $_SESSION['BOOKING_CONF_EMAILS_SUBJECT'] ) ) || ( !isset( $_SESSION['BOOKING_CONF_EMAILS_BODY'] ) ) || ( !isset( $_SESSION['BOOKING_CONF_EMAILS_CC'] ) ) || ( !isset( $_SESSION['BUDDY_LIST_EMAILS_SEND'] ) ) || ( !isset( $_SESSION['BUDDY_LIST_EMAILS_FROM_NAME'] ) ) || ( !isset( $_SESSION['BUDDY_LIST_EMAILS_FROM'] ) ) || ( !isset( $_SESSION['BUDDY_LIST_EMAILS_SUBJECT'] ) ) || ( !isset( $_SESSION['BUDDY_LIST_EMAILS_BODY'] ) ) || ( !isset( $_SESSION['PAYMENT_GATEWAY'] ) ) || ( !isset( $_SESSION['PAYPAL_BUSINESS_EMAIL'] ) ) || ( !isset( $_SESSION['PAYPAL_NOTIFICATION_EMAIL'] ) ) || ( !isset( $_SESSION['USER_REGISTER_EMAIL_TO'] ) ) ) {

    $result = wrap_db_query( "SELECT name, function_value FROM " . SETTINGS_TABLE . " ;" ) ;
    if ( $result ) {
        while( $fields = wrap_db_fetch_array( $result ) ) {
            //see which parameter we are dealing with
            switch( $fields['name'] ) {
                case 'public_register':
                    $set_val_to = false ;
                    if ( $fields['function_value'] == '1' ) {
                        //allow new user registrations
                        $set_val_to = true ;
                    }
                    $_SESSION['PUBLIC_REGISTER_FLAG'] = $set_val_to;
                    break;
					
                case 'booking_hours_limit':
                    //a safe default. Also used in case the db query fails for some reason
                    //$set_val_to = 336 ; // 336 = 14 days x 24 hours in a day
                    $_SESSION['ADVANCE_BOOKING_LIMIT'] = $fields['function_value'];
                    break;

                case 'cancellation_hours_limit':
                    //a safe default. Also used in case the db query fails for some reason
                    //$set_val_to = 6 ; // hours
                    $_SESSION['ADVANCE_CANCEL_LIMIT'] = $fields['function_value'];
                    break;

                case 'minimum_booking_hours_limit':
                    //a safe default. Also used in case the db query fails for some reason
                    //$set_val_to = 2 ; // hours
                    $_SESSION['MINIMUM_ADVANCE_BOOKING_LIMIT'] = $fields['function_value'];
                    break;

                case 'public_details_viewing':
                    //a safe default. Also used in case the db query fails for some reason
                    $set_val_to = $fields['function_value'];
                    //change 1's and 0's to true and false
                    if ( $set_val_to == "1" ) {
                        $set_val_to = true ;
                    } else {
                        $set_val_to = false ;
                    }
                    $_SESSION['SHOW_USER_DETAILS'] = $set_val_to;
                    break;

                case 'user_minimum_booking_options':
                    //a safe default. Also used in case the db query fails for some reason
                    //$set_val_to = 0 ; // none required
                    $_SESSION['MINIMUM_USER_BOOKING_OPIONS'] = $fields['function_value'];
                    break;

                case 'admin_minimum_booking_options':
                    //a safe default. Also used in case the db query fails for some reason
                    //$set_val_to = 0 ; // none required
                    $_SESSION['MINIMUM_ADMIN_BOOKING_OPIONS'] = $fields['function_value'];
                    break;

                case 'send_booking_conf_email':
                    //a safe default. Also used in case the db query fails for some reason
                    $set_val_to = false ; // don't email anyone
                    if ( $fields['function_value'] == '1' ) {
                        //send booking confirmation e-mails
                        $set_val_to = true ;
                    }
                    $_SESSION['BOOKING_CONF_EMAILS_SEND'] = $set_val_to;
                    break;

                case 'send_booking_conf_email_from_name':
                    $_SESSION['BOOKING_CONF_EMAILS_FROM_NAME'] = trim( $fields['function_value'] ) ;
                    break;

                case 'send_booking_conf_email_from':
                    $set_val_to = MAIL_MYEMAIL ; //default to the same as the constant used for error messages and lost passwords
                    if ( trim( $fields['function_value'] ) != '' ) {
                        //send booking confirmation e-mails
                        $set_val_to = trim( $fields['function_value'] ) ;
                    }
                    $_SESSION['BOOKING_CONF_EMAILS_FROM'] = $set_val_to ;
                    break;

                case 'send_booking_conf_email_subject':
                    $_SESSION['BOOKING_CONF_EMAILS_SUBJECT'] = trim( $fields['function_value'] ) ;
                    break;

                case 'send_booking_conf_email_body':
                    $_SESSION['BOOKING_CONF_EMAILS_BODY'] = stripslashes( $fields['function_value'] ) ;
                    break;

                case 'send_booking_conf_email_cc':
                    $set_val_to = false ; // don't cc a copy anywhere
                    if ( trim( $fields['function_value'] ) != '' ) {
                        //send booking confirmation e-mails
                        $set_val_to = trim( $fields['function_value'] ) ;
                    }
                    $_SESSION['BOOKING_CONF_EMAILS_CC'] = $set_val_to ;
                    break;
					
					
					// Buddy list notification		
				case 'send_buddy_list_email':
                    //a safe default. Also used in case the db query fails for some reason
                    $set_val_to = false ; // don't email anyone
                    if ( $fields['function_value'] == '1' ) {
                        //send booking confirmation e-mails
                        $set_val_to = true ;
                    }
                    $_SESSION['BUDDY_LIST_EMAILS_SEND'] = $set_val_to;
                    break;
					
			    case 'send_buddy_list_email_from_name':
                    $_SESSION['BUDDY_LIST_EMAILS_FROM_NAME'] = trim( $fields['function_value'] ) ;
                    break;	
					
				case 'send_buddy_list_email_from':
                    $set_val_to = MAIL_MYEMAIL ; //default to the same as the constant used for error messages and lost passwords
                    if ( trim( $fields['function_value'] ) != '' ) {
                        //send booking confirmation e-mails
                        $set_val_to = trim( $fields['function_value'] ) ;
                    }
                    $_SESSION['BUDDY_LIST_EMAILS_FROM'] = $set_val_to ;
                    break;
					
				case 'send_buddy_list_email_subject':
                    $_SESSION['BUDDY_LIST_EMAILS_SUBJECT'] = trim( $fields['function_value'] ) ;
                    break;	
					
				case 'send_buddy_list_email_body':
                    $_SESSION['BUDDY_LIST_EMAILS_BODY'] = stripslashes( $fields['function_value'] ) ;
                    break;		
				
				// Payment Gateway - Check if it is enabled. 
				case 'payment_gateway':   	
					$set_payval_to = false ;
                    if ( $fields['function_value'] == '1' ) {
                        //allow new user registrations
                        $set_payval_to = true ;
                    }
                    $_SESSION['PAYMENT_GATEWAY'] = $set_payval_to;
                    break;
								
				case 'paypal_business_email':
                    $_SESSION['PAYPAL_BUSINESS_EMAIL'] = trim( $fields['function_value'] ) ;
                    break;												
								
				case 'paypal_notification_email':
                    $_SESSION['PAYPAL_NOTIFICATION_EMAIL'] = trim( $fields['function_value'] ) ;
                    break;				

				case 'send_user_register_email_to':
                    $set_val_to = MAIL_MYEMAIL ; //default to the same as the constant used for error messages and lost passwords
                    if ( trim( $fields['function_value'] ) != '' ) {
                        //send booking confirmation e-mails
                        $set_val_to = trim( $fields['function_value'] ) ;
                    }
                    $_SESSION['USER_REGISTER_EMAIL_TO'] = $set_val_to ;
                    break;
			
			}
        }
    }
}

//a set of values specifying the options for min cancellation times
if ( !isset($_SESSION['MINIMUM_CANCELLATION_HOUR_OPTIONS']) ) {
    $_SESSION['MINIMUM_CANCELLATION_HOUR_OPTIONS'] = array( 1, 2, 3, 4, 5, 6, 8, 10, 12, 18, 24, 36, 48, 72, 96, 120, 144, 168 ) ;
}

//a set of values specifying the various credit types
if ( !isset($_SESSION['CREDIT_TYPES']) ) {
    $_SESSION['CREDIT_TYPES'] = get_credit_types() ;
}

//uncomment the following lines to aid testing
//echo "<pre>" ;
//print_r( $_SESSION ) ;
//echo "</pre>" ;
//exit;		

?>