<? include_once("./includes/application_top.php");

// check if the user is logged in
if (REQUIRE_AUTH_FOR_ADDING_FLAG) {
    if (!@wrap_session_is_registered('valid_user')) {
    	header('Location: ' . href_link(FILENAME_LOGIN, 'origin=' . FILENAME_BUDDY_LIST . '&' . make_hidden_fields_workstring(), 'NONSSL'));
        wrap_exit();
    }
}

$page_title = "Buddy List Administration";
$page_title_bar = "Buddy List Administration";


$page_error_message = '' ;

	// get the current user id of the logged in user
	$user_info = get_user( get_user_id($_SESSION['valid_user']) ) ;	
	
// if the admin has switched on buddy list notifications
if ( $_SESSION['BUDDY_LIST_EMAILS_SEND'] ) {		
	// check admins cannot access this page
	if ( wrap_session_is_registered("admin_user") ) {

		echo "<br>Admin users cannot have buddy lists.<br><br>";

		}else{  // we continue



		//  if someone has submitted to allow a pending user
		if ($_POST['allow_selected'] != '') {
			
			//  insert the pending user into the buddy list
			$allow_pending = "INSERT INTO " . BOOKING_BUDDIES . " ( `user_id` , `buddy_id` ) VALUES ('" . $user_info['user_id'] . "', '" . $allow_selected . "'), ('" . $allow_selected . "', '" . $user_info['user_id'] . "')";
	 			wrap_db_query($allow_pending) or die(mysql_error());
		
			// delete the user from the pending table as they are now a buddy
			$delete_old_pending = "DELETE FROM  " . BOOKING_BUDDIES_PENDING . " WHERE  user_id = '" . $user_info['user_id'] . "' AND buddy_id = '" . $allow_selected . "'";
	 		wrap_db_query($delete_old_pending) or die(mysql_error());
			
			$allow_selected ='' ; 
		
		} 

		// if someone selected a user to deny
		if ($_POST['deny_selected'] != '') {
			// delete the user from the pending table 
			$reject_pending = "DELETE FROM  " . BOOKING_BUDDIES_PENDING . " WHERE  user_id = '" . $user_info['user_id'] . "' AND buddy_id = '" . $deny_selected . "'";
	 		wrap_db_query($reject_pending) or die(mysql_error());			
			$deny_selected ='' ; 
		} 



//check for form submission
if ($_POST['Submit'] != '') {

    //check if we are adding a user to our list
    if ($_POST['Submit'] == "->") {
        //check if a user was selected
        if ($_POST['user_select'] != '') {
            // show the user who has been selected

            $new_user_id_value = $_POST['user_select'] ;
			//echo "<br><br>This would add user " . $new_user_id_value . " to your buddies. <br><br>"  ;
			
			// insert the selected user into the pending table where is our pending buddy and insert us ans the pending buddys - pending buddy
			$insert_pending = "INSERT INTO " . BOOKING_BUDDIES_PENDING . " ( `user_id` , `buddy_id` ) VALUES ('" . $new_user_id_value . "', '" . $user_info['user_id'] . "')";
	 			wrap_db_query($insert_pending) or die(mysql_error());
				
				$new_user_id_value = ''  ;
				
        $page_info_message = "User successfully added to pending list.  Once confirmed, the buddy will receive notifications of your bookings."  ;
		
		} else {
            $page_error_message = "Please select a user that you wish to add to your buddy list before submitting the form." ;
        }
    } 

// user has selected a buddy to remove

	if ($_POST['Submit'] == "<-") {
        //check if a user was selected
        if ($_POST['buddy_select'] != '') {
            // show the user who has been selected

            $new_not_buddy_value = $_POST['buddy_select'] ;
			//echo "<br><br>This would remove buddy " . $new_not_buddy_value . " from your buddy list. <br><br>"  ;
			
			$delete_pending = "DELETE FROM  " . BOOKING_BUDDIES . " WHERE buddy_id = '" . $new_not_buddy_value . "' OR user_id = '" . $new_not_buddy_value . "'";
	 		wrap_db_query($delete_pending) or die(mysql_error());
			
			// also delete from pending table as the user may have selected a pending user i.e. changed their minds about adding this user
			$delete_pending = "DELETE FROM  " . BOOKING_BUDDIES_PENDING . " WHERE  user_id = '" . $new_not_buddy_value . "' AND buddy_id = '" . $user_info['user_id'] . "'";
	 		wrap_db_query($delete_pending) or die(mysql_error());			
			
			$new_not_buddy_value = ''  ;
			
			$page_info_message = "User successfully removed from your buddy list."  ;	
			
        } else {
            $page_error_message = "Please select a buddy that you wish to remove from your buddy list before submitting this form." ;
        }
    } 

}  


include_once("header.php");


// get our current buddies
	$Buddies = wrap_db_query( "SELECT buddy_id FROM " . BOOKING_BUDDIES . " where user_id = '" . $user_info['user_id'] . "'" ) ;
    	while ( $myBuddies = wrap_db_fetch_array( $Buddies ) ) {
      	$myBuddyBuddyIDs[] = $myBuddies['buddy_id'] ;
    	}
	
	// get pending buddies for our user
	$pendingBuddies = wrap_db_query( "SELECT user_id, buddy_id FROM " . BOOKING_BUDDIES_PENDING . " where buddy_id = '" . $user_info['user_id'] . "' OR user_id='" . $user_info['user_id'] . "'" ) ;
    	while ( $myPendingBuddies = wrap_db_fetch_array( $pendingBuddies ) ) {
      	$myPendingUserBuddyIDs[] = $myPendingBuddies['user_id'] ;
		$myPendingBuddyBuddyIDs[] = $myPendingBuddies['buddy_id'] ;
    	
		}
			// if the user does not have any pending buddies, set the pending session variable to false
			// so that the indicator flag in the control panel does not show
			if ( !is_array( $myPendingBuddyBuddyIDs ) ) {    
				$_SESSION['number_pending_buddies'] = false  ;
			}	
		

	// get our current buddies
	$allUsers = wrap_db_query( "SELECT user_id, username, firstname, lastname, email FROM " . BOOKING_USER_TABLE . " where user_id <> '" . $user_info['user_id'] . "' AND is_admin = '0' ORDER BY lastname, firstname, username" ) ;
		while ( $myUsers = wrap_db_fetch_array( $allUsers ) ) {

				foreach ($myUsers as $item) {
              		$my_users[$myUsers['user_id']]['user_id'] = $myUsers['user_id'];
					$my_users[$myUsers['user_id']]['username'] = $myUsers['username'];
					$my_users[$myUsers['user_id']]['firstname'] = $myUsers['firstname'];
					$my_users[$myUsers['user_id']]['lastname'] = $myUsers['lastname'];
					$my_users[$myUsers['user_id']]['email'] = $myUsers['email'];
				}  
		}

// java script for allow or deny links
?>
<script language="JavaScript" type="text/javascript">
<!--
function allow ( selectedtype )
{
  document.submit_pending.allow_selected.value = selectedtype ;
  document.submit_pending.submit() ;
}
function deny ( selectedtype )
{
  document.submit_pending.deny_selected.value = selectedtype ;
  document.submit_pending.submit() ;
}
-->

</script>
<?
		// Here we will display any pending buddy's so that the logged in user can allow or deny
 		if ( is_array( $myPendingUserBuddyIDs ) ) {    
			echo "<form name=\"submit_pending\" method=\"post\" action=\"" . FILENAME_BUDDY_LIST . "\"><br>"  ; 
				foreach($my_users as $b => $s){	
						if ( in_array( $s['user_id'], $myPendingBuddyBuddyIDs ) ) {
        			
							echo "User <b>" . $s['firstname'] . " " . $s['lastname'] . "</b> ( " .  $s['email'] . " ) is pending approval : <a href=\"javascript:allow('". $s['user_id'] ."')\">Allow</a>  |  <a href=\"javascript:deny('". $s['user_id'] ."')\">Deny</a><br>" ; 
						}
				}
 		
    		echo "<input type=\"hidden\" name=\"allow_selected\" /><input type=\"hidden\" name=\"deny_selected\" /></form>"  ;	
		}	

?>
<p>All buddies within your buddy list will be notified by email each time you make a booking.</p>
<p>Use the controls below to add or remove users to and from your buddy list:<br>
</p>
	<form name="form1" method="post" action="<?=FILENAME_BUDDY_LIST?>">
	<table border="0" cellspacing="10" cellpadding="0">
    	<tr>
        	<td><b>All Users</b></td>
        	<td>&nbsp;</td>
        	<td><b>My Buddy List</b></td>
   		</tr>
    	<tr>
        	<td><select name="user_select" size="15">
<?php       
			
			 // build array of all buddy id's and pending ids to produce a list of remaining users which can be added
			$allRemainingUser = @array_merge($myPendingUserBuddyIDs, $myPendingBuddyBuddyIDs, $myBuddyBuddyIDs);
			
					foreach($my_users as $d => $z){	
						if ( !in_array( $z['user_id'], $allRemainingUser) ) {

					
						echo '<option value="' . $z['user_id'] . '" title="' . $z['email'] . '">' . $z['lastname'] . ', ' . $z['firstname'] . ' (' . $z['username'] . ')</option>' . "\n\t\t" ;	
					}
		 		}
			 
?>
            </select>
        </td>
		<td><input type="submit" name="Submit" value="-&gt;" class="ButtonStyle"><br><br><input type="submit" name="Submit" value="&lt;-" class="ButtonStyle" onclick="return confirm('Are you sure you wish to delete this user from your buddy list?');"></td>
        <td><select name="buddy_select" size="15" id="buddy_select">
<?php
			 // List our buddies data
 			if ( is_array( $myBuddyBuddyIDs ) ) {    
					foreach($my_users as $d => $v){	
						if ( in_array( $v['user_id'], $myBuddyBuddyIDs ) ) {
        				echo '<option value="' . $v['user_id'] . '" title="' . $v['email'] . '">' . $v['lastname'] . ', ' . $v['firstname'] . ' (' . $v['username'] . ')</option>' . "\n\t\t" ;
						} 
					}
 			}
			
			// List our pending users also so people can see they are waiting for approval
		if ( is_array( $myPendingUserBuddyIDs ) ) {    
				foreach($my_users as $b => $s){	
						if ( in_array( $s['user_id'], $myPendingUserBuddyIDs ) ) {
        			
							echo '<option class="BgcolorNormal" value="' . $s['user_id'] . '" title="' . $s['email'] . '">' . $s['lastname'] . ', ' . $s['firstname'] . ' (Pending)</option>' . "\n\t\t" ;
						}
				}
 		
    		echo "<br><br><input type=\"hidden\" name=\"allow_selected\" /><input type=\"hidden\" name=\"deny_selected\" /></form>"  ;	
		}	
?>
          	  </select></td>
	  </tr>
	</table>
	</form>

<p><font color="gray" style="padding-left: 20px;">NOTE: Users in your buddy list marked as &quot;Pending&quot; need to be approved by your buddy before becoming active. </font></p>
<?

		}  // end if user is admin loop  - if ( wrap_session_is_registered("admin_user") ) {
	
	} else {
	// end if admin has switched on buudy list notification
	echo "<br>Buddy List Notification is not enabled."  ;
	} 


include_once("footer.php");
include_once("application_bottom.php"); 

?>
