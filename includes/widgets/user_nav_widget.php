<?
// user_nav_widget.php
// Display the User Navigation/Functions Bar

// If booking_credits session var is not present, refresh the users credit value
// Ths way we can force a refresh by unsetting this var e.g. after a paypal transaction

	$user_info = get_user( get_user_id($_SESSION['valid_user']) ) ;
			
	if ( !wrap_session_is_registered("admin_user")  && $user_info['booking_credits'] !== 'Not used'  &&  $_SESSION['PAYMENT_GATEWAY'] == '1'  && is_numeric($user_info['user_id']) )
	{
		$result = wrap_db_query("SELECT booking_credits FROM " . BOOKING_USER_TABLE . " where user_id = '" . $user_info['user_id'] . "'");
		if ($result) 
		{
            while ( $fields = wrap_db_fetch_array($result) ) 
			{       
			   $_SESSION['booking_credits'] = $fields['booking_credits'];
			}		
		}
	}

?>

<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
	<td nowrap="nowrap" align="center" valign="middle" class="BgcolorDull2">
	<img src="<?=DIR_WS_IMAGES?>/spacer.gif" width="15" height="15" />
	User Functions:
	<?  if ( isset( $_SESSION['valid_user'] ) && ( $_SESSION['valid_user'] != '' ) ) {
		    echo '<a href="' . FILENAME_MY_BOOKWAKE_VIEW . '"><b>' . $_SESSION['valid_user'] . '</b>' ;
			if ( $_SESSION['booking_credits'] != 'Not used' ) {
                echo ' (<b>' . $_SESSION['booking_credits'] . '</b> credit' ;
                if ( $_SESSION['booking_credits'] != 1 ) {
                    echo 's' ;
                }
                echo ')' ;
            }
            echo '</a>' ;
        } 
		?>
	<img src="<?=DIR_WS_IMAGES?>/spacer.gif" width="15" height="15" />
	</td>
  </tr>
</table>

<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
	<td nowrap="nowrap" align="center" valign="middle" class="BgcolorNormal"><span class="FontSoftSmall">

</span>
	  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
          <td><div align="center"><span class="FontSoftSmall">
            <?php
//see if new user registrations are permitted
if ($_SESSION['PUBLIC_REGISTER_FLAG'] && !isset( $_SESSION['valid_user'])) {
    ?>
            <a href="<?=href_link(FILENAME_REGISTER, '', 'NONSSL')?>">New User Register</a><br />
            <?php
}
?>
<a href="<?=href_link(FILENAME_LOGIN, '', 'NONSSL')?>">Login</a> / <a href="<?=href_link(FILENAME_LOGOUT, '', 'NONSSL')?>">Logout</a> <br />          <!--
<a href="<?=href_link(FILENAME_FORGOT_USERNAME, '', 'NONSSL')?>">Forgot Username?</a><br />
<a href="<?=href_link(FILENAME_FORGOT_PASSWD, '', 'NONSSL')?>">Forgot Password?</a><br />
<a href="<?=href_link(FILENAME_CHANGE_PASSWD, '', 'NONSSL')?>">Change Password</a><br />
<a href="<?=href_link(FILENAME_UPDATE, '', 'NONSSL')?>">Update User Info</a><br />
-->
            <a href="<?=href_link(FILENAME_HELP, '', 'NONSSL')?>">User Help</a> <br />
            <?php
        if ( wrap_session_is_registered("admin_user") ) {
            ?><a href="user_admin.php" class="FontSoftSmall">User&nbsp;Admin</a><br />
            <a href="site_admin.php" class="FontSoftSmall">Site&nbsp;Admin</a>&nbsp;<br />
            <?php
        }
        if ( isset( $_SESSION['valid_user'] ) && ( $_SESSION['valid_user'] != '' ) ) {
            echo '<a href="' . FILENAME_MY_BOOKWAKE_VIEW .'">My Bookings</a>' ;          

			if ( $_SESSION['BUDDY_LIST_EMAILS_SEND'] && !wrap_session_is_registered("admin_user") ) {	

				echo '<br><a href="' . FILENAME_BUDDY_LIST .'">My Buddylist </a>' ;          
      
					if ( isset( $_SESSION['number_pending_buddies'] ) &&  ($_SESSION['number_pending_buddies'] > 0 ) ) {  ; 
	 					 
						 echo "<img src=\"images/pending.gif\" width=\"13\" height=\"13\">"  ;
					}
			}	   
        
		
			// link to buy credits if the user has user credits enabled
		 				 if (( $_SESSION['booking_credits'] != 'Not used' ) && ( $_SESSION['PAYMENT_GATEWAY'] == '1' )){
            			echo '<br><a href="' . FILENAME_BUY_CREDITS .'">Buy Credits' ;          
       
        			}
		
		
		}
		

        ?>
            </span></div></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
