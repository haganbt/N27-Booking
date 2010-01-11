<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "User Administration";
$page_title_bar = "User Administration";

include_once("header.php");
?>
<br>
<b>Please select a task from the following options:</b><br>
<br>
<table border="0" cellpadding="0" cellspacing="2">
  <tr>
    <td valign="top">
      <ul>
      	<li><a href="admin_user_view.php">View Users</a></li>
          <li><a href="admin_user_register.php">Create a new user or admin account</a></li>
          <li><a href="admin_user_update.php">Modify or remove an existing user account</a></li>
          <li><a href="admin_user_privileges.php">Modify admin privileges for an existing user</a></li>
          <li><a href="admin_limit_user_bookings.php">Modify max number of bookings for a user</a></li>
          <li><a href="admin_set_booking_credits.php">Modify booking credits for an existing user</a></li>
          <li><a href="admin_block_booking.php">Modify block booking privileges for non-administrators</a></li>
      </ul>
    </td>
    <td width="20">&nbsp;</td>
    <td valign="top">
      <ul>
      	<li><a href="admin_modify_groups.php">Manage Groups</a></li>
        <li><a href="admin_modify_user_groups.php">Update Group Membership</a></li>
        <? // Only allow access to this page if the Payment gateway is switched on
		if ($_SESSION['PAYMENT_GATEWAY'] == '1' ) { ?>
        <li><a href="admin_modify_group_products.php">Assign Products To Groups</a></li>
		<? } ?>
      </ul>
    </td>
  </tr>
</table>

<?php
include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>