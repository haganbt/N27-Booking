<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Set Admin Privileges";
$page_title_bar = "Set Admin Privileges";

//echo '<pre>' ;
//print_r( $_POST ) ;
//echo '</pre>' ;

$saved_changes = false ; //holder for potential save state
//check for form submission
if ($_POST['send_form'] != '') {
    //ensure that both a user and a value were submitted
    if ( ($_POST['user_select'] != '') && ($_POST['max_bookings'] != '') ) {
        $query = "UPDATE " . BOOKING_USER_TABLE . " SET max_bookings='" . $_POST['max_bookings'] . "' WHERE user_id='" . $_POST['user_select'] . "' LIMIT 1 ;" ;
        $result = wrap_db_query($query);
        $saved_changes = true ;
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

<form name="form1" method="post" action="<?=FILENAME_ADMIN_MAX_BOOKINGS?>">

<table border="0" cellspacing="10" cellpadding="0">
    <tr>
        <td><b>Users</b></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td><select name="user_select" size="15" onchange="document.form1.submit()">
            <?php
                //get a list of users
                $result = wrap_db_query("SELECT user_id, username, firstname, lastname, email, max_bookings FROM " . BOOKING_USER_TABLE . " ORDER BY lastname, firstname, username");
                if ($result) {
                    while ( $fields = wrap_db_fetch_array($result) ) {
                        $max_bookings = $fields['max_bookings'] . ' booking' ;
                        if ($fields['max_bookings'] > 1) {
                            $max_bookings .= 's' ;
                        }                            
                        if ( $fields['max_bookings'] == 0 ) {
                            $max_bookings = 'Unlimited bookings' ;
                        }
                        echo '<option value="' . $fields['user_id'] . '" title="' . $fields['email'] . '"' ;
                        if ( $_POST['user_select'] == $fields['user_id'] ) {
                            echo ' selected="true"' ;
                            //store the users name and current limit for use in a later part of the form
                            $users_full_name = $fields['firstname'] . ' ' . $fields['lastname'] ;
                            $users_current_booking_limit = $fields['max_bookings'] ;
                        }
                        //check if this is the main admin account
                        if ($fields['username'] == 'admin') {
                            $admin_account_id = $fields['user_id'] ;
                        }
                        echo '>' . $fields['lastname'] . ', ' . $fields['firstname'] . ' (' . $fields['username'] . ') - ' . $max_bookings . '</option>' . "\n\t\t" ;
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
                    echo $users_full_name . ' may now make ' ;
                    if ($users_current_booking_limit != 0) {
                        echo 'up to ' .$users_current_booking_limit . ' advance booking' ;
                        if ($users_current_booking_limit > 1) {
                            echo 's' ;
                        }
                    } else {
                        echo 'an unlimited number of advance bookings' ;
                    }
                    echo '.' ;
                } else {
                    //prevent changes to the default admin account
                    if ($_POST['user_select'] != $admin_account_id) {
                        ?>
                        Maximum advance bookings for <?= $users_full_name ; ?>: 
                        <select name="max_bookings" size="1">
                        <?php
                        for ( $i = 0 ; $i < 51 ; $i++ ) {
                            echo '<option value="' . $i . '"' ;
                            if ($i == $users_current_booking_limit) {
                                echo ' selected="true"' ;
                            }
                            echo '>' ;
                            if ($i != 0) {
                                echo $i ;
                            } else {
                                echo 'Unlimited' ;
                            }
                            echo "</option>\n" ;
                        }
                        ?>
                        </select><br>
                        <br>
                        <br>
                        <center><input type="submit" name="send_form" value="Save Changes" class="ButtonStyle"></center>
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