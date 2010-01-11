<? include_once("./includes/application_top.php"); ?>
<?
$old_user = $_SESSION['valid_user'];  // store to test if they *were* logged in
$result_unreg = wrap_session_unregister("valid_user");
$result_dest = wrap_session_destroy();

if (!empty($old_user)) {
  if ($result_unreg && $result_dest) {  // if they were logged in and are now logged out
    $page_info_message = "You are now logged out.";
	$logged_out = true;
  } else {  // they were logged in and could not be logged out
    $page_info_message = "Error: Sorry, we could not log you out! Please try again.";
	$logged_out = false;
  }
} else {  // if they weren't logged in but came to this page somehow
  $page_info_message = "You were not logged in, so you have not been logged out.";
  $logged_out = true;
}
?>
<?
$page_title = "Booking Calendar - User Logout";
$page_title_bar = "User Logout:";
include_once("header.php");

echo '<p align="center">';
if ($logged_out) {
	echo '<a href="' . href_link(FILENAME_LOGIN, '', 'NONSSL') . '">User Login</a>';
} else {
	echo '<a href="' . href_link(FILENAME_LOGOUT, '', 'NONSSL') . '">User Logout</a>';
}
echo '</p>';

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>