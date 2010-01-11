<? include_once("./includes/application_top.php"); ?>
<?
  if (!@wrap_session_is_registered('valid_user')) {
    header('Location: ' . href_link(FILENAME_LOGIN, 'origin=' . FILENAME_CHANGE_PASSWD, 'NONSSL'));
    wrap_exit();
  }
?>
<?
$page_title = "Booking Calendar - Change Password";
$page_title_bar = "Change Password?";

$page_error_message = '';
$password_changed = false;

if ($_POST['change_password'] != "") { // Change Passwd Form Submit

  if ($_POST['old_passwd'] == "" || $_POST['new_passwd'] == "" || $_POST['new_passwd2'] == "" || $_POST['email'] == "") {
	$page_title = "Password Change Problem";
	$page_error_message = "You have not filled out the form completely. Please try again.";
  }
  elseif (!validate_email($_POST['email'])) {  // email address not valid
	$page_title = "Password Change Problem";
    $page_error_message = "Your email address is not valid. Please try again.";
  }
  elseif ($_POST['new_passwd'] != $_POST['new_passwd2']) {  // passwords not the same
	$page_title = "Password Change Problem";
	$page_error_message = "The passwords you entered do not match. Please try again.";
	$_POST['passwd2'] = '';
  }
  elseif (strlen($_POST['new_passwd']) < 6 || strlen($_POST['new_passwd']) > 16) {   // check new password length
	$page_title = "Password Change Problem";
	$page_error_message = "New password must be between 6 and 16 characters. Please try again.";
  }

  if ($page_error_message == '') {  // attempt update if no error message
		if (change_password($valid_user, $_POST['old_passwd'], $_POST['new_passwd'], $_POST['email'])) {
			$page_info_message = "Your password has been changed successfully!";
			$password_changed = true;
		} else {
			$page_error_message = "Your password could not be changed. Make sure you typed your old password correctly. Please try again.";
		}
  }

} // end of if ($_POST['change_password'] != "")
?>
<?
include_once("header.php");

if ($password_changed) {
  // Display Change Success
} else {
  // display html change password form
?>
<p align="center">
<form action="<?=FILENAME_CHANGE_PASSWD?>" method="post">
<table cellpadding="2" cellspacing="0" border="0">
<tr><td align="right">Old password: </td>
<td><input type="password" name="old_passwd" size="16" maxlength="16"></td></tr>
<tr><td align="right">New password: </td>
<td><input type="password" name="new_passwd" size="16" maxlength="16"></td></tr>
<tr><td nowrap="nowrap" align="right">Repeat New Password: </td>
<td><input type="password" name="new_passwd2" size="16" maxlength="16"></td></tr>
<tr><td align="right">E-mail Address: <br /><span class="FontBlackSmall"><em>(required)</em></span> </td>
<td><INPUT TYPE="text" name="email" value="<? echo stripslashes($_POST['email']); ?>" size="30" maxlength="60"></td></tr>
<tr><td colspan="2" align="center"><br />
<input type="hidden" name="change_password" value="yes">
<input type="submit" name="submit" value="Change Password" class="ButtonStyle">
</td></tr></table>
</p>
<?
} // end of if $password_changed

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>