<?

include_once(DIR_WS_FUNCTIONS . 'password_funcs.php');


function login($username, $passwd)
// check username and passwd with db
// if yes, return true else return false
{
  // check if username is unique
  $result = wrap_db_query("SELECT user_id, passwd FROM " . BOOKING_USER_TABLE . "
						WHERE username = '" . $username . "' AND login_enabled = '1'");
  if (!$result) { return false; }
  $fields = wrap_db_fetch_array($result);

  # check to see if username was found
  # also to prevent username = "" sql default
  if ($fields[0] == "") { return false; }

  # check for admin login, passwd = NULL
  if ($passwd == "" && $result && $fields[1] == NULL)
     $passwd = NULL;

  #echo "username: $username<br />";
  #echo "password: $passwd<br />";
  #echo "db field: $fields[1]<br />";
  #if ($fields[1] == NULL) { echo "NULL db passwd<br />"; }

  if (validate_password($passwd, $fields[1]))
     return true;

  return false;
}


// register new person with db
// optional parameter 'is_member' accepts values of '0' or '1' specifying user or admin level respectively
// return false or error
function register($username, $passwd, $firstname, $lastname, $groups, $email, $admin_privileges='0', $can_login='1', $address_l1='', $address_l2='', $town='', $county='', $postcode='', $tel_home='', $tel_work='', $tel_mobile='', $dob='0000-00-00', $is_member='0', $gender='', $mail_opt_out='0', $credit_type_id='1' ) {

  // crypt user password entry
  $crypted_passwd = crypt_password($passwd);

  // check if username is unique
  $result = wrap_db_query("SELECT username FROM " . BOOKING_USER_TABLE . " WHERE username='" . $username . "' LIMIT 1");
  if (!$result)
     return "Could not register you in the database! Please try again.";
  if (wrap_db_num_rows($result)>0)
     return "Sorry, that username is taken.  Please choose another one.";

  // ensure a valid option for the admin_privileges value.
  // limit to regular user if invalid value

  if ( ($admin_privileges != '0') && ($admin_privileges != '1') ) {
    $admin_privileges = '0' ;
  }

  // if ok, put in db
  $result = wrap_db_query( "INSERT " . BOOKING_USER_TABLE . " (username, credit_type_id, passwd, firstname, lastname, groups, email, is_admin, login_enabled, address_l1, address_l2, address_town, address_county, address_postcode, phone_home, phone_work, phone_mobile, dob, gender, is_member, mail_opt_out ) VALUES ('" . $username . "', '" . $credit_type_id . "', '" . $crypted_passwd . "', '" . $firstname . "', '" . $lastname . "', '" . $groups . "', '" . $email . "', '" . $admin_privileges . "', '" . $can_login . "',  '" . $address_l1 . "', '" . $address_l2 . "', '" . $town . "', '" . $county . "', '" . $postcode . "', '" . $tel_home . "', '" . $tel_work . "', '" . $tel_mobile . "', '" .$dob . "', '" . $gender . "', '" . $is_member . "', '" . $mail_opt_out . "')" ) ;
  if (!$result)
    return false;
  else
    return true;
}


function change_password($username, $old_passwd, $new_passwd, $email, $admin_override=false)
// change password for username/old_passwd to new_passwd
// admin_override allows an administrator to change a users current password without knowing the previous password
// return true or false
{
  // if the old password and email are correct!
  // change their password to new_passwd and return true
  // else return false
  if ($admin_override || login($username, $old_passwd))
  {
    // crypt user password entry
    $crypted_new_passwd = crypt_password($new_passwd);

    $result = wrap_db_query("UPDATE " . BOOKING_USER_TABLE . " SET passwd = '" . $crypted_new_passwd . "' " .
					"WHERE username = '" . $username . "' AND email = '" . $email . "'");
    if (!$result)
      return false;  // not changed
    else
      return true;  // changed successfully
  }
  else
    return false; // old password was wrong and no admin_override was specified
}


function reset_password($username, $email)
// set password for username to a random value
// return the new password or false on failure
{
  $result = wrap_db_query("SELECT email FROM " . BOOKING_USER_TABLE . " WHERE username='" . $username . "'");
  if (!$result) {
		return false;  // no result
  } else if (wrap_db_num_rows($result)==0) {
		return false; // username not in db
  } else {
		$fields = wrap_db_fetch_array($result);
		if ($email != $fields['email']) {
			return false; // emails do not match
		}
  }
  $new_passwd = random_password(6);
  // crypt user password entry
  $crypted_new_passwd = crypt_password($new_passwd);

  // set user's password to this in database or return false
  $result = wrap_db_query("UPDATE " . BOOKING_USER_TABLE . " SET passwd = '" . $crypted_new_passwd . "' " .
					"WHERE username = '" . $username . "' AND email = '" . $email . "'");
  if (!$result) {
    return false;  // not changed
  } else {
    return $new_passwd;  // changed successfully
  }
}

function get_username($email)
// Forgot Username Function
// Get username based on email entered
{
  $result = wrap_db_query("SELECT username FROM " . BOOKING_USER_TABLE . " WHERE email='" . $email . "'");
  if (!$result) {
		return false;
  } else if (wrap_db_num_rows($result)==0) {
		return false; // email not in db
  } else {
		$fields = wrap_db_fetch_array($result);
		$username = $fields['username'];
  }
  return $username; // return valid username
}


function get_user_information($username)
// return the user information array or false on failure
{
  $result = wrap_db_query("SELECT * FROM " . BOOKING_USER_TABLE . " WHERE username = '" . $username . "'");
  if (!$result) {
		return false;  // not changed
  } else if (wrap_db_num_rows($result)==0) {
		return false; // email not in db
  } else {
		$fields = wrap_db_fetch_array($result);
  }
  return $fields;
}


function update_user_information($username, $firstname, $lastname, $email, $mail_opt_out='0')
// update user information
// return false, true or error message
{
  // check if username is unique
  $result = wrap_db_query("SELECT user_id FROM " . BOOKING_USER_TABLE . " WHERE username='$username'");
  if (!$result) {
		return false;  // no result
  } else if (wrap_db_num_rows($result)==1) {  // one result row
		$fields = wrap_db_fetch_array($result);
		$user_id = $fields['user_id'];
  } else {
		return false;
  }
  if (empty($user_id)) {
     return false;
  }
  // if ok, put in db and return result
  $result = wrap_db_query("UPDATE " . BOOKING_USER_TABLE . " SET
						firstname = '$firstname',
						lastname = '$lastname',
						email = '$email',
						mail_opt_out = '$mail_opt_out'
						WHERE username = '$username' AND user_id = '$user_id'");
  if (!$result)
    return false;
  else
    return true;
}


function admin_update_of_user_information($supplied_user_id, $username, $firstname, $lastname, $email, $login_enabled, $address_l1='', $address_l2='', $town='', $county='', $postcode='', $tel_home='', $tel_work='', $tel_mobile='', $dob='0000-00-00', $is_member='0', $gender='', $mail_opt_out='0', $credit_type_id='1' )
// update user information based on username. supplied_user_id can be supplied to allow changing of username
// return false, true or error message
{
  $new_username_is_unique = false ; //default to fail
  // check if new username is unique
  $result = wrap_db_query("SELECT user_id FROM " . BOOKING_USER_TABLE . " WHERE username='$username'");
  if (!$result) {
//echo "a" ;
        //db error, prevent changes
        $new_username_is_unique = false ;
  } else if (wrap_db_num_rows($result)>0) {  // one result row
//echo "b" ;
		//this username is taken, is it taken by the current user?
        $fields = wrap_db_fetch_array($result) ;
        if ($fields['user_id'] == $supplied_user_id ) {
    	    //the user is not changing their username so of course it already exists!
    	    //everything is fine here, carry on.
            $new_username_is_unique = true ;

//echo "(b1)" ;
        } else {
//echo "(b2)" ;
    		//no, it is taken by a different user and is therefore not available
    		$new_username_is_unique = false ;
    		return false ;
        }
  } else {
//echo "(b3)" ;
    //username is unique (not found in db) - user is changing their user_id
		$new_username_is_unique = true ;
  }
  if ($new_username_is_unique && ($supplied_user_id != '') ) {
//echo "c" ;
      // if ok, put in db and return result
      $result = wrap_db_query("UPDATE " . BOOKING_USER_TABLE . " SET
    						username = '$username',
    						credit_type_id = '$credit_type_id',
    						firstname = '$firstname',
    						lastname = '$lastname',
    						email = '$email',
    						login_enabled = '$login_enabled',
    						address_l1 = '$address_l1',
    						address_l2 = '$address_l2',
    						address_town = '$town',
    						address_county = '$county',
    						address_postcode = '$postcode',
    						phone_home = '$tel_home',
    						phone_work = '$tel_work',
    						phone_mobile = '$tel_mobile',
    						dob = '$dob',
    						gender = '$gender',
    						is_member = '$is_member',
    						mail_opt_out = '$mail_opt_out'
    						WHERE user_id = '$supplied_user_id' LIMIT 1");
    if (!$result) {
//echo "d" ;
        return false;
    } else {
//echo "e" ;
        return true;
    }

  } else {
    //there was a problem
//echo "f" ;
    return false ;
  }
//echo "g" ;
}



function is_admin($username)
// check if user with username is an admin user
// if yes, return true else return false
{
  // check if username is unique
  $result = wrap_db_query("SELECT is_admin FROM " . BOOKING_USER_TABLE . "
						WHERE username = '" . $username . "'");
  if (!$result) { return false; }
  $fields = wrap_db_fetch_array($result);

  # check to see if username was found
  # also to prevent username = "" sql default
  if ($fields[0] == "") { return false; }

  # check for admin login
  if ($fields[0] == '1') {
    return true ;
  }

  //if you get here then this user is not an administrator so return false
  return false;
}


function is_member($username)
// check if user with username is a member or not
// if yes, return true else return false
{
  // check if username is unique
  $result = wrap_db_query("SELECT is_member FROM " . BOOKING_USER_TABLE . "
						WHERE username = '" . $username . "'");
  if (!$result) { return false; }
  $fields = wrap_db_fetch_array($result);

  # check to see if username was found
  # also to prevent username = "" sql default
  if ($fields[0] == "") { return false; }

  # check for is member flag
  if ($fields[0] == '1') {
    return true ;
  }

  //if you get here then this user is not an administrator so return false
  return false;
}


function can_block_book($username)
// check if user with username can block book
// if yes, return true else return false
{
  // check if username is unique
  $result = wrap_db_query("SELECT block_book FROM " . BOOKING_USER_TABLE . "
						WHERE username = '" . $username . "'");
  if (!$result) { return false; }
  $fields = wrap_db_fetch_array($result);

  # check to see if username was found
  # also to prevent username = "" sql default
  if ($fields[0] == "") { return false; }

  # check for block_book status
  if ($fields[0] == '1') {
    return true ;
  }

  //if you get here then this user is not able to block book so return false
  return false;
}

function remaining_booking_credits($username)
// check the number of credits remaining (if used) for user with passed username
// return the number of credits remaining or the 'Not used' string.
{
  // check if username is unique
  $result = wrap_db_query("SELECT booking_credits FROM " . BOOKING_USER_TABLE . "
						WHERE username = '" . $username . "'");
  if (!$result) { return false; }
  $fields = wrap_db_fetch_array($result);

  # check to see if username was found
  # also to prevent username = "" sql default
  if ($fields[0] == "") { return false; }

  # return booking credits status
  return $fields[0] ;
}

function update_booking_credits( $username, $numCredits, $updateType='set' ) {
    //updateType can be 'set', 'inc' or 'dec'
    //
    // 'set' changes the users current number of credits to the $numCredits value
    //    (can be any int or 'Not_used' to remove the use of booking credits)
    // 'inc' increments the number of credits the user has by $numCredits
    // 'dec' decreases the number of credits the user has by $numCredits.

    //make sure we have a user id to prevent deleting other peoples events!
    if ( ( $username == '' ) || ( $username == '%' ) ) {
        return false ;
    }

    //get the current number of credits for that user
    $creditsRemaining = remaining_booking_credits( $username ) ;

    if ( $updateType == 'set' ) {
        $creditsRemaining = $numCredits ;
    } else if ( $updateType == 'inc' ) {
        //make sure the current value is not 'Not used'
        if ( $creditsRemaining == 'Not used' ) {
            //overwrite the not used value (treat it as a 0)
            $creditsRemaining = $numCredits ;
        } else {
            $creditsRemaining += $numCredits ;
        }
    } else if ( $updateType == 'dec' ) {
        //make sure the current value is not 'Not used'
        if ( $creditsRemaining == 'Not used' ) {
            //overwrite the not used value (treat it as a 0)
            $creditsRemaining = 0 ;
        } else {
            $creditsRemaining -= $numCredits ;
            //disallow negative credit
            if ( $creditsRemaining < 0 ) {
                $creditsRemaining = 0 ;
            }
        }
    }

    //are we updating our own or someone elses credits? If our own, update the session value
    if ( $username == $_SESSION['valid_user'] ) {
        //update the value stored in the session
        $_SESSION['booking_credits'] = "$creditsRemaining" ; //use of quotes forces zero's to be set correctly rather than being auto-cast to a boolean false. We can't use (int) to cast as this value may be a string 'Not used'.
    }

    //write the update back to the database
    $result = wrap_db_query("UPDATE " . BOOKING_USER_TABLE . " SET booking_credits = '$creditsRemaining' WHERE username = '$username' LIMIT 1");
    if (!$result) {
        //notify the sites administrator that this value could not be updated
        $emailMsg = "The bookwake system was unable to $updateType the current sumber of booking credits for user $username.\n\nThis user should now have $creditsRemaining credits remaining, please update this value manually using your bookwake control panel.\n" ;
        send_mail("", "", MAIL_MYNAME, MAIL_MYEMAIL, "Booking credit change failed for $username", $emailMsg) ;
        return false ;
    } else {
        return true ;
    }
}

function delete_user( $userID ) {
    //make sure we have a user id to prevent deleting other peoples events!
    if ( ( $userID == '' ) || ( $userID == '%' ) ) {
        return false ;
    }

    //convert the userid into a username
    $userDetails = get_user( $userID ) ;
    $username = $userDetails['username'] ;

    //remove a user and all related events
    $result = wrap_db_query("SELECT event_id FROM " . BOOKING_EVENT_TABLE . " WHERE user_id = '" . $userID . "'") ;
    if (!$result) { return false; }

    //delete all the events and associated links from the booking schedule
    while ( $fields = wrap_db_fetch_array($result) ) {
        delete_event($username, $fields['event_id'], false) ;
    }

    // delete any saved options set for this user
    $query = 'DELETE FROM ' . BOOKING_USER_OPTIONS_TABLE . ' WHERE user_id="' . $userID . '"' ;
    wrap_db_query( $query ) ; //attempt to delete any options found. No point failing if they can't be removed though.

	// delete any pending buddies for this user and any entrances where this user is the pending buddy
    $query = 'DELETE FROM ' . BOOKING_BUDDIES_PENDING . ' WHERE user_id="' . $userID . '" OR buddy_id="' . $userID . '"' ;
    wrap_db_query( $query ) ; //attempt to delete any pending buddies found. No point failing if they can't be removed though.

	// delete any buddies for this user and any entrances where this user is the buddy
    $query = 'DELETE FROM ' . BOOKING_BUDDIES . ' WHERE user_id="' . $userID . '" OR buddy_id="' . $userID . '"' ;
    wrap_db_query( $query ) ; //attempt to delete any buddies found. No point failing if they can't be removed though.

    //finally, delete the actual user
    $result = wrap_db_query("DELETE FROM " . BOOKING_USER_TABLE . " WHERE user_id = '" . $userID . "' LIMIT 1") ;
    if (!$result) { return false; }

    //if you get here then everything went smoothly
    return true ;
}


function pending_buddies($username)
{
  // get userid as we only have the username
   $result = wrap_db_query("SELECT user_id FROM " . BOOKING_USER_TABLE . "
						WHERE username = '" . $username . "' AND login_enabled = '1'");
  if (!$result) { return false; }
  	$fields = wrap_db_fetch_array($result);

  	if ($fields[0] == "") { return false; }
 		// get the number of pending buddies
 		 $result2 = wrap_db_query("SELECT count(*) FROM " . BOOKING_BUDDIES_PENDING . "
						WHERE user_id = '" . $fields[0] . "'");

 			 if (!$result2) { return false; }
  					$fields2 = wrap_db_fetch_array($result2);

			  if ($fields2[0] == "") { return false; }

			  return $fields2[0] ;
}


// get the users credit type value (advance booking days allowed)
// return the credit_type_booking_days value as an integer for the passed user, or false on error
function get_user_credit_type_days($user_id) {
    if (empty($user_id)) {
        return false;
    }
    $result = wrap_db_query("SELECT c.credit_type_booking_days FROM " . BOOKING_CREDIT_TYPES . " AS c, " . BOOKING_USER_TABLE . " AS u WHERE u.user_id='$user_id' AND u.credit_type_id=c.credit_type_id");
    if (!$result) {
  		  return false;  // no result
    } else if (wrap_db_num_rows($result)==1) {  // one result row
  		  $fields = wrap_db_fetch_array($result);
  		  return $fields['credit_type_booking_days'];
    } else {
  		  return false;
    }
}


// return an array of credit types or false on failure
function get_credit_types() {
    $result = wrap_db_query("SELECT * FROM " . BOOKING_CREDIT_TYPES);
    $returnArray = null ;
    if (!$result) {
		    return false;  // general connection or query error
    } else if (wrap_db_num_rows($result)==0) {
	  	  return false; // no results - odd!
    } else {
        while ( $fields = wrap_db_fetch_array($result) ) {
		        $returnArray[] = array( 'credit_type_id' => $fields['credit_type_id'],
		                                'credit_type_name' => $fields['credit_type_name'] ,
		                                'credit_type_booking_days' => $fields['credit_type_booking_days'] ) ;
		   }
    }
    return $returnArray;
}
?>