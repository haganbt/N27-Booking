<? include_once("./includes/application_top.php"); ?>
<?
$page_title = "Booking Calendar - User Help";
$page_title_bar = "User Help:";
include_once("header.php");

?>

<p align="center">

<a href="<?=href_link(FILENAME_LOGIN, '', 'NONSSL')?>">User Login</a><br />
<a href="<?=href_link(FILENAME_LOGOUT, '', 'NONSSL')?>">User Logout</a><br />
<br />
<a href="<?=href_link(FILENAME_FORGOT_USERNAME, '', 'NONSSL')?>">Forgot Username?</a><br />
<a href="<?=href_link(FILENAME_FORGOT_PASSWD, '', 'NONSSL')?>">Forgot Password?</a><br />
<a href="<?=href_link(FILENAME_CHANGE_PASSWD, '', 'NONSSL')?>">Change Password</a><br />
<a href="<?=href_link(FILENAME_UPDATE, '', 'NONSSL')?>">Update User Info</a><br />

</p>

<?

include_once("footer.php");

include_once("application_bottom.php");
?>
