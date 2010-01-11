<? include_once("./includes/application_top.php"); ?>
<?

$page_title = "Booking Calendar - Forgot Password";
$page_title_bar = "Forgot Password?";

$page_error_message = '';
$password_changed = false;

if ($_POST['forgot_password'] != "") { // Forgot Passwd Form Submit

  if ($_POST['username'] == "" || $_POST['email'] == "") {
	$page_title = "Reset Password Problem";
	$page_error_message = "You have not filled out the form completely. Please try again.";
  }
  elseif (!validate_email($_POST['email'])) {  // email address not valid
	$page_title = "Reset Password Problem";
    $page_error_message = "Your email address is not valid. Please try again.";
  }

  if ($page_error_message == '') {
	$reset_passwd = reset_password($_POST['username'], $_POST['email']);
	if ($reset_passwd != false && $reset_passwd != '') { 
		$password_changed = true;
		$msg =  "Your user password has been changed to $reset_passwd \n" . 
				"Please change it next time you log in. Thank You. \n";
		if (send_mail("", "", $_POST['email'], $_POST['email'], 
					"Booking Calendar: User Login Information", $msg)) {
			$page_title = "Resetting Password Successful!";
			$page_info_message = "Your new password has been sent to your email address.";
		} else {
			$page_title = "Resetting Password E-mail Problem";
			$page_error_message = "Your password could not be e-mailed to you. Try pressing your refresh button.";
		}
	} else {
		$page_title = "Resetting Password Problem";
		$page_error_message = "Your password could not be reset. Make sure you typed in the correct username and email address and try again.";
	}
  }

} // end of if ($_POST['forgot_password'] != "")
?>
<?
include_once("header.php");

if ($password_changed) {
  // Display Change Success
} else {
  // display HTML form to reset and email password
?>

<p align="center">
<form action="user_forgot_passwd.php" method="post">
<table cellpadding="2" cellspacing="0" border="0">
<tr><td nowrap="nowrap" align="right">Username: </td>
<td><input type="text" name="username" size="16" maxlength="16"></td></tr>
<tr><td align="right">E-mail Address: <br /><span class="FontBlackSmall"><em>(required)</em></span> </td>
<td><INPUT TYPE="text" name="email" value="<? echo stripslashes($_POST['email']); ?>" size="30" maxlength="60"></td></tr>
<tr><td colspan="2" align="center"><br />
<input type="hidden" name="forgot_password" value="yes">
<input type="submit" name="submit" value="Submit Information" class="ButtonStyle">
</td></tr></table>
</p>
<?
}

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>