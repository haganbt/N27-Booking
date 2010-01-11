<? include_once("./includes/application_top.php"); ?>
<?
wrap_session_start();

  // see if somebody is logged in and notify them if not
  $display_login_form = true; // default
  if (isset($_REQUEST['username']) && isset($_REQUEST['login'])) {
	// they have just tried logging in

	if (login($_REQUEST['username'], $_REQUEST['passwd'])) {
		// if they are in the database register the user id
		//$valid_user = $_REQUEST['username'];
		$_SESSION['valid_user'] = $_REQUEST['username'];
		wrap_session_register("valid_user");
		$display_login_form = false;
		$page_info_message = "Login Successful!";
		// we know we have a valid user, now check if they are entitled to admin privileges
		if ( is_admin( $_REQUEST['username'] ) ) {
		    wrap_session_register("admin_user");
		} elseif ($_SESSION['BUDDY_LIST_EMAILS_SEND']) {
			// check if this user has any pending buddies - we only want to do this for non-admins and if buddy lists are switched on
			$_SESSION['number_pending_buddies'] =  pending_buddies( $_REQUEST['username'] ) ;
		}
		// set some session info about their privileges
        // can block book?
		if ( can_block_book( $_REQUEST['username'] ) ) {
		    wrap_session_register("block_book");
		}
		//booking credits remaining
		$_SESSION['booking_credits'] = remaining_booking_credits( $_REQUEST['username'] ) ;
		// Member check
		// check if the user is a member or not - but only if they are not an admin as this flag is not used for admins
		if ( !wrap_session_is_registered("admin_user") ) {
			$_SESSION['is_member'] = is_member( $_REQUEST['username'] ) ;
		}
		//can they view other users bookings?
		if ( is_admin( $_REQUEST['username'] ) ) {
		    //admins can always see everyone elses bookings
		    $_SESSION['SHOW_USER_DETAILS'] = true ;
		} else {
		    //how about regular users? This will depend on the site wide value set by an admin
            $result = wrap_db_query("SELECT function_value FROM " . SETTINGS_TABLE . " WHERE name = 'user_details_viewing' LIMIT 0,1 ;");
            if ($result) {
                if ($fields = wrap_db_fetch_array($result)) {
                    //change 1's and 0's to true and false
                    if ( $fields['function_value'] == "1" ) {
                        $_SESSION['SHOW_USER_DETAILS'] = true ;
                    } else {
                        $_SESSION['SHOW_USER_DETAILS'] = false ;
                    }
                }
            }
        }

	} else {
		// login failed, show error page
		$display_login_form = true;
		$page_error_message = "You could not be logged in. Please try again.";
	}
  } elseif (wrap_session_is_registered("valid_user")) {
	// logged in
	$display_login_form = false;
  } else {
	// they are not logged in, show login page output
	$display_login_form = true;
	if ($_REQUEST['orgin'] != FILENAME_LOGOUT && $_REQUEST['orgin'] != FILENAME_LOGIN && $_REQUEST['orgin'] != "") {
		$page_error_message = "You are not logged in. You must login to use this page.";
	}
  }



  // redirect back to "origin" page
  if (!$display_login_form && $_REQUEST['origin'] != '' && $_SESSION['valid_user'] != '') {
	if (@wrap_session_is_registered('valid_user')) {
		header('Location: ' . href_link($_REQUEST['origin'], make_hidden_fields_workstring(), 'NONSSL'));
		wrap_exit();
	}
  }
?>
<?
$page_title = "Booking Calendar - User Login";
$page_title_bar = "User Login:";
include_once("header.php");

if ($display_login_form) {
?>
<p align="center">
<form method="post" action="<?=FILENAME_LOGIN?>">
<table border="0" align="center" cellpadding="2" cellspacing="0">
<tr><td align="right">Username: </td><td><input type="text" name="username" value="<?=$_POST['username']?>" size="16" maxlength="16"></td></tr>
<tr><td align="right">Password: </td><td><input type="password" name="passwd" size="16" maxlength="16"></td></tr>
<tr><td colspan="2" align="center"><br />
<input type="hidden" name="origin" value="<? echo stripslashes($_REQUEST['origin']); ?>">
<? echo make_hidden_fields(); ?>
<input type="hidden" name="login" value="yes">
<input type="submit" name="login" value="Login" class="ButtonStyle">
<p>
<?php
if ($_SESSION['PUBLIC_REGISTER_FLAG']) {
    ?><a href="<?=FILENAME_REGISTER?>">Not a user? Register Today!</a><?php
}
?>
</p>
</td></tr>
</table>
</form>
</p>
<?

} else {
?>
<p align="center">
<table border="0" align="center" cellpadding="2" cellspacing="0">
<tr><td align="right">
You are curently logged in.
<a href="<? echo href_link(FILENAME_LOGOUT, '', 'NONSSL'); ?>">User Logout</a>
</td></tr>
</table>
</p>

<?
}

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>