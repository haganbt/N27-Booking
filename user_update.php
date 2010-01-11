<? include_once("./includes/application_top.php"); ?>
<?
  if (!@wrap_session_is_registered('valid_user')) {
    header('Location: ' . href_link(FILENAME_LOGIN, 'origin=' . FILENAME_UPDATE, 'NONSSL'));
    wrap_exit();
  }
?>
<?
$page_title = "Update User Information";

$page_error_message = '';
$update_result = false; // default

if ($_POST['update'] == "") {

  $user_info_fields = get_user_information($_SESSION['valid_user']);
  if (!(count($user_info_fields)>0)) {
		$page_error_message = "Your user information could not be found! Please try again. ".$_SESSION['valid_user']."";
		$update_result = false;
  } else {
	  // Set the $_POST variable for the Form fields below.
	  foreach (array_keys($user_info_fields) as $key) {
			$_POST[$key] = addslashes($user_info_fields[$key]);
	  }
  }

} else if ($_POST['update'] != "") { // Update Form Submit

  if ($_POST['username'] == "" || $_POST['email'] == "") {  // check forms filled in - required fields
	$page_title = "Problem Updating User Information!";
	$page_error_message = "You have not filled the form out correctly. " . 
		"Please make sure to fill out all required fields.";
  }
  elseif (!validate_email($_POST['email'])) {  // email address not valid
	$page_title = "Problem Updating User Information!";
    $page_error_message = "Your email address is not valid. Please try again.";
  }

  if ($page_error_message == '') {  // attempt to update user info if no error message
	$update_result = update_user_information($_POST['username'], $_POST['firstname'], 
				$_POST['lastname'], $_POST['email']);
	if ($update_result) {
		$page_title = "Update Successful!";
	} else {
		// update problem: database error, etc
		$page_title = "Problem Updating User Information!3";
		$page_error_message = $update_result;
	}
  }

} // end of if ($_POST['update']
?>
<?
$page_title = "Booking Calendar - User Information Update";
$page_title_bar = "User Information Update:";
include_once("header.php");

if ($update_result) {
	
	// Update Successful!
	echo '<p align="center">Your update was successful!</p>';
	
}
	
    // Update Form or Update Problem.
?>
<form method="post" action="<?=FILENAME_UPDATE?>">
<input type="hidden" name="username" value="<? echo stripslashes($_POST['username']); ?>">
<table cellpadding="2" cellspacing="0" border="0" width="100%">
<tr><td align="right">Your Username:</td><td><? echo stripslashes($_POST['username']); ?></td></tr>
<tr><td align="center" colspan="2"><table cellpadding="0" cellspacing="0" border="0" width="50%"><tr><td class="BgColorHighlight"><img 
src="/spacer.gif" width="300" height="1" alt="" /></td></tr></table></td></tr>
<tr><td align="center" colspan="2"><span class="FontBlackSmall">
Note: Blank fields are updated!</span></td></tr>
<tr><td align="right">First Name: </td>
<td><INPUT TYPE="text" name="firstname" value="<? echo stripslashes($_POST['firstname']) ?>" size="25" maxlength="90"></td></tr>
<tr><td align="right">Last Name: </td>
<td><INPUT TYPE="text" name="lastname" value="<? echo stripslashes($_POST['lastname']) ?>" size="25" maxlength="90"></td></tr>
<tr><td align="right">E-mail Address: <br /><span class="FontBlackSmall"><em>(required)</em></span> </td>
<td><INPUT TYPE="text" name="email" value="<? echo stripslashes($_POST['email']); ?>" size="30" maxlength="90"></td></tr>
<tr><td align="center" colspan="2"><br />
<input type="hidden" name="groups" value="">
<input type="hidden" name="update" value="yes">
<input type="submit" name="update" value="Submit New User Information" class="ButtonStyle">
</td></tr></table>
</form>
<?

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>