<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Set Booking Credits";
$page_title_bar = "Set Booking Credits";

//echo '<pre>' ;
//print_r( $_POST ) ;
//echo '</pre>' ;

$saved_changes = false ; //holder for potential save state
//check for form submission
if ($_POST['send_form'] != '') {
    //ensure that both a user and a value were submitted
    if ( ($_POST['user_select'] != '') && ($_POST['booking_credits'] != '') ) {
        $userDetails = get_user( $_POST['user_select'] ) ;
        $username = $userDetails['username'] ;        
        update_booking_credits( $username, $_POST['booking_credits'], 'set' ) ;
    } else {
        $page_error_message = 'Unable to save changes. Please make your selection again before resubmitting this form.' ;
    }
}

include_once("header.php");

$admin_account_id = '' ;
$users_full_name = '' ;
$users_current_booking_limit = '' ;
?>
<br>
Select the user account you wish to modify, then select a new value and press the 'Save Changes' button.
<br>

<form name="form1" method="post" action="<?=FILENAME_ADMIN_BOOKING_CREDITS?>">

<table border="0" cellspacing="10" cellpadding="0">
    <tr>
        <td><b>Users</b></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td valign="top"><select name="user_select" size="15" onchange="document.form1.submit()">
            <?php
                //get a list of users
                $result = wrap_db_query("SELECT user_id, username, firstname, lastname, email, max_bookings, booking_credits FROM " . BOOKING_USER_TABLE . " WHERE is_admin='0' ORDER BY lastname, firstname, username");
                if ($result) {
                    while ( $fields = wrap_db_fetch_array($result) ) {
                        $user_booking_credits = $fields['booking_credits'] ;
                        if ( $fields['booking_credits'] != 'Not used' ) {
                            $user_booking_credits .= ' credit' ;
                            if ($fields['booking_credits'] != 1) {
                                $user_booking_credits .= 's' ;
                            }                            
                        }
                        echo '<option value="' . $fields['user_id'] . '" title="' . $fields['email'] . '"' ;
                        if ( $_POST['user_select'] == $fields['user_id'] ) {
                            echo ' selected="true"' ;
                            //store the users name and current limit for use in a later part of the form
                            $users_full_name = $fields['firstname'] . ' ' . $fields['lastname'] ;
                            $users_current_booking_limit = $fields['max_bookings'] ;
                            $users_current_booking_credits = $fields['booking_credits'] ;
                        }
                        //check if this is the main admin account
                        if ($fields['username'] == 'admin') {
                            $admin_account_id = $fields['user_id'] ;
                        }
                        echo '>' . $fields['lastname'] . ', ' . $fields['firstname'] . ' (' . $fields['username'] . ') - ' . $user_booking_credits . '</option>' . "\n\t\t" ;
                    }
                }
            ?>
            </select>
        </td>
        <td width="10">&nbsp;</td>
        <td valign="top"><?php
            if ( $_POST['user_select'] != '') {
                //see if we just saved some changes
                if ($saved_changes == true) {
                    echo '<b>Changes saved</b><br><br>' ;
                    if ( $users_current_booking_credits == 'Not used' ) {
                        echo 'Booking credits are no longer used for ' . $users_full_name ;
                    } else {
                        echo $users_full_name . ' now has ' ;
                        echo $users_current_booking_credits . ' booking credit' ;
                        if ($users_current_booking_credits != 1) {
                            echo 's' ;
                        }
                    }
                    echo '.' ;
                } else {
                    //prevent changes to the default admin account
                    if ($_POST['user_select'] != $admin_account_id) {
                        ?>
                        Total booking credits for <?= $users_full_name ; ?>: 
                        <select name="booking_credits" size="1">
                            <option value="Not used"<?= ($users_current_booking_credits == 'Not used') ? ' selected="true"' : '' ; ?>>Not used</option>
                        <?php
                        for ( $i = 0 ; $i < 151 ; $i++ ) {
                            echo '<option value="' . $i . '"' ;
                            if ("$i" == $users_current_booking_credits) {
                                echo ' selected="true"' ;
                            }
                            echo '>' . $i . "</option>\n" ;
                        }
                        ?>
                        </select><br>
                        <br>
                        <br>
                        <center><input type="submit" name="send_form" value="Save Changes" class="ButtonStyle"></center>
                        <br>
                        <br>
                        <br>
                        <?php
                        echo 'NOTE: User ' . $users_full_name . ' can currently make ' ;
                        if ($users_current_booking_limit != 0) {
                            echo 'up to ' .$users_current_booking_limit . ' advance booking' ;
                            if ($users_current_booking_limit > 1) {
                                echo 's' ;
                            }
                        } else {
                            echo 'an unlimited number of advance bookings' ;
                        }
                        echo '.' ;
                        ?><br>
                        <a href="<?= FILENAME_ADMIN_MAX_BOOKINGS ; ?>">Modify advance booking limit.</a><br>
                        <br>
                        <?php
                    } else {
                        echo '<font color="#ff0000"><b>You cannot modify this value for the default admin account!</b></font><br>' ;
                        echo '<br>Please select a different user from the list on the left.' ;
                    }
                }
            }
            ?>
        </td>
    </tr>
</table>

</form>

<?php
include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>