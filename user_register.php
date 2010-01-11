<? include_once("./includes/application_top.php"); ?>
<? session_start(); ?>
<?
$page_title = "User Registration";

$page_error_message = '';
$reg_result = false; // default


if ($_POST['register'] != "") { // Register Form Submit

  if ($_POST['username'] == "" || $_POST['passwd'] == "" || $_POST['email'] == "") {  // check forms filled in - required fields
	$page_title = "User Registration Problem";
	$page_error_message = "You have not filled the form out correctly. " . 
		"Please make sure to fill out all required fields (username, password and e-mail).";
  }
  elseif (!validate_email($_POST['email'])) {  // email address not valid
	$page_title = "User Registration Problem";
    $page_error_message = "Your email address is not valid. Please try again.";
  }
  elseif ($_POST['passwd'] != $_POST['passwd2']) {  // passwords not the same
	$page_title = "User Registration Problem";
	$page_error_message = "The passwords you entered do not match. Please try again.";
	$_POST['passwd2'] = '';
  }
  elseif (strlen($_POST['passwd']) < 6 || strlen($_POST['passwd']) > 16) {   // check password length
	$page_title = "User Registration Problem";
	$page_error_message = "Your password must be between 6 and 16 characters. Please try again.";
  }
  elseif (($_SESSION['security_code'] != $_POST['security_code']) || (empty($_SESSION['security_code'])) ) {
	$page_title = "User Registration Problem";
	$page_error_message = "Invalid security code.  Please enter the letters shown within the image.";
  }

	// Check if the username is already in use
    $result = wrap_db_query("SELECT username FROM " . BOOKING_USER_TABLE . " WHERE username ='". strtolower(trim($_POST['username'])) ."' LIMIT 1");
	if ( $result && ( wrap_db_num_rows( $result ) > 0 ) ) 
	{
		$page_title = "User Registration Problem";
		$page_error_message = "Username already taken.  Please choose another.";		
	}

	// Check if the email is already in use
    $result = wrap_db_query("SELECT email FROM " . BOOKING_USER_TABLE . " WHERE email ='". strtolower(trim($_POST['email'])) ."' LIMIT 1");
	if ( $result && ( wrap_db_num_rows( $result ) > 0 ) ) 
	{
		$page_title = "User Registration Problem";
		$page_error_message = "Email address already in use.  Please choose another.";		
	}


  if ($page_error_message == '') {  // attempt to register if no error message
	$reg_result = register($_POST['username'], $_POST['passwd'], $_POST['firstname'], $_POST['lastname'], $_POST['groups'], $_POST['email']);
	if ($reg_result) {
		// register session variable 
		unset($_SESSION['security_code']);
		$_SESSION['valid_user'] = $_POST['username'];
		wrap_session_register("valid_user");
		$page_title = "Registration Successful!";
	} else {
		// register problem: username taken, database error
		$page_title = "User Registration Problem";
		$page_error_message = $reg_result;
	}
  }

} // end of $_POST['register'] != ""
?>
<?
$page_title = "Booking Calendar - User Registration";
$page_title_bar = "User Registration:";
include_once("header.php");

	// Check we are logged in
	if (( wrap_session_is_registered("admin_user") )  || ( wrap_session_is_registered("valid_user") ) && (!$reg_result))
	{
		echo "<p>Logged in users cannot register.</p>";
		include_once("footer.php");
		include_once("application_bottom.php");
		die;

	}



if ($reg_result) {
	// Registration Successful! Provide link to display wants page.
	echo "Your registration was successful!  You are now logged in.<br /><br />";
	if ( $_SESSION['PAYMENT_GATEWAY'] == '1') {
		echo "You can <a href=\"". FILENAME_BUY_CREDITS . "\">buy credits</a> and then make a booking.<br /><br />";
	}
	
	
} else {
    //make sure new user registrations are allowed
    if ($_SESSION['PUBLIC_REGISTER_FLAG']) {
        // New Registration or Problem.
        ?>

<form method="post" action="<?=FILENAME_REGISTER?>">
  <table cellpadding="2" cellspacing="0" border="0" width="100%">
    <tr>
      <td align="right">Preferred Username:<br />
        <span class="FontBlackSmall"><em>(max 16 chars)</em></span></td>
      <td><INPUT TYPE="text" name="username" value="<? echo stripslashes($_POST['username']); ?>" size="16" maxlength="16"></td>
    </tr>
    <tr>
      <td align="right">Password:<br />
        <span class="FontBlackSmall"><em>(between 6 and 16 chars)</em></span></td>
      <td><INPUT TYPE="password" name="passwd" value="<? echo stripslashes($_POST['passwd']); ?>" size="16" maxlength="16"></td>
    </tr>
    <tr>
      <td align="right">Confirm Password:</td>
      <td><INPUT TYPE="password" name="passwd2" value="<? echo stripslashes($_POST['passwd2']); ?>" size="16" maxlength="16"></td>
    </tr>
    <tr>
      <td align="right">First Name: </td>
      <td><INPUT TYPE="text" name="firstname" value="<? echo stripslashes($_POST['firstname']) ?>" size="25" maxlength="90"></td>
    </tr>
    <tr>
      <td align="right">Last Name: </td>
      <td><INPUT TYPE="text" name="lastname" value="<? echo stripslashes($_POST['lastname']) ?>" size="25" maxlength="90"></td>
    </tr>
    <tr>
      <td align="right">E-mail Address: <br />
        <span class="FontBlackSmall"><em>(required)</em></span></td>
      <td><INPUT TYPE="text" name="email" value="<? echo stripslashes($_POST['email']); ?>" size="30" maxlength="90"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><img src="captach.php" id="<?=time()?>"/></td>
    </tr>
    <tr>
      <td align="right">Security Code:</td>
      <td><input name="security_code" type="text" id="security_code" size="6" maxlength="4" />
        <span class="FontBlackSmall"><em>&nbsp;(Enter the letters shown above)</em></span></td>
    </tr>
    <tr>
      <td></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td></td>
      <td><input type="hidden" name="groups" value="">
        <input type="hidden" name="register" value="yes">
        <input type="submit" name="register" value="Submit User Information" class="ButtonStyle"></td>
    </tr>
  </table>
  <br />
</form>
<?php
    } else {
        //new user registration is not allowed
        ?>
<br>
<b>Error</b><br>
<br>
Sorry, public users cannot register.<br>
<?php
    }
} // end of if $reg_result

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>
