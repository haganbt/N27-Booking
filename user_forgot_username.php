<? include_once("./includes/application_top.php"); ?>
<?

$page_title = "Booking Calendar - Forgot Username";
$page_title_bar = "Forgot Username?";

$page_error_message = '';
$valid_username = false;

if ($_POST['forgot_username'] != "") { // Forgot Username Form Submit

  if ($_POST['email'] == "") {
	$page_title = "Forgot Username Problem";
	$page_error_message = "You have not filled out the form completely. Please try again.";
  }
  elseif (!validate_email($_POST['email'])) {  // email address not valid
	$page_title = "Forgot Username Problem";
    $page_error_message = "Your email address is not valid. Please try again.";
  }

  if ($page_error_message == '') {
	$username = get_username($_POST['email']);
	if ($username != '') { 
		$valid_username = true;
		$msg =  "Your username is: $username \n" . 
				"Thank You. \n";
		if (send_mail("", "", $_POST['email'], $_POST['email'], 
					"Booking Calendar: User Login Information", $msg)) {
			$page_title = "Username Found!";
			$page_info_message = "Your username has been sent to your email address.";
		} else {
			$page_title = "Username E-mail Problem";
			$page_error_message = "Your username could not be e-mailed to you. Try pressing your refresh button.";
		}
	} else {
		$page_title = "Forgot Username Problem";
		$page_error_message = "Your username could not be determined. Make sure you typed in the correct email address and try again.";
	}
  }

} // end of if ($_POST['forgot_password'] != "")
?>
<?
include_once("header.php");

if ($valid_username) {
  // Display Change Success
} else {
  // display HTML form to reset and email password
?>

<p align="center">
<form action="user_forgot_username.php" method="post">
<table cellpadding="2" cellspacing="0" border="0">
<tr><td align="right">E-mail Address: <br /><span class="FontBlackSmall"><em>(required)</em></span> </td>
<td><INPUT TYPE="text" name="email" value="<? echo stripslashes($_POST['email']); ?>" size="30" maxlength="60"></td></tr>
<tr><td colspan="2" align="center"><br />
<input type="hidden" name="forgot_username" value="yes">
<input type="submit" name="submit" value="Submit Information" class="ButtonStyle">
</td></tr></table>
</p>
<?
}

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>