<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Set Admin Privileges";
$page_title_bar = "Set Admin Privileges";

//echo '<pre>' ;
//print_r( $_POST ) ;
//echo '</pre>' ;

$errorMsg = false ; //holder for potential error messages
$new_is_admin_value = '' ;
$new_user_id_value = '' ;
//check for form submission
if ($_POST['Submit'] != '') {
    //check if we are creating an admin
    if ($_POST['Submit'] == "->") {
        //check if a user was selected
        if ($_POST['user_select'] != '') {
            $new_is_admin_value = '1' ;
            $new_user_id_value = $_POST['user_select'] ;
        } else {
            $errorMsg = 'Please select a user that you wish to grant administrator privileges to before submitting this form.' ;
        }
    } else if ($_POST['Submit'] == "<-") {
        //check if an admin was selected
        if ($_POST['admin_select'] != '') {
            //check if the user is attempting to move the main admin account and block if this is the case
            if ($_POST['admin_select'] == $_POST['default_admin_acc_id']) {
                //the user is trying to move the default admin account. Prevent this!
                $errorMsg = 'You cannot revoke admin privileges from the default admin account!' ;
            } else {
                $new_is_admin_value = '0' ;
                $new_user_id_value = $_POST['admin_select'] ;
            }
        } else {
            $errorMsg = 'Please select an administrator that you wish to revoke administrator privileges from before submitting this form.' ;
        }
    }

    //check if it is okay to make changes, if not echo the error
    if ($errorMsg === false) {
        $query = "UPDATE " . BOOKING_USER_TABLE . " SET is_admin='" . $new_is_admin_value . "'" ;
        if ( $new_is_admin_value == "1" ) {
            //admins don't use credits. remove them from this user as we make them an admin
            $query .= ", booking_credits='Not used'" ;
        }
        $query .= " WHERE user_id='" . $new_user_id_value . "' LIMIT 1 ;" ;
        $result = wrap_db_query($query);
    } else {
        //looks like there is a problem, report it to the user
        $page_error_message = $errorMsg ;
    }
        
}

include_once("header.php");

$admin_account_id = '' ;
?>
<br>
Use the controls below to provide users with admin privileges<br>(or to remove admin privileges from existing administrators):<br>
<br>

<form name="form1" method="post" action="<?=FILENAME_ADMIN_PRIVILEGES?>">

<table border="0" cellspacing="10" cellpadding="0">
    <tr>
        <td><b>Users</b></td>
        <td>&nbsp;</td>
        <td><b>Administrators</b></td>
    </tr>
    <tr>
        <td><select name="user_select" size="15">
            <?php
                //get a list of non-admin users
                $result = wrap_db_query("SELECT user_id, username, firstname, lastname, email FROM " . BOOKING_USER_TABLE . " WHERE is_admin = '0' ORDER BY lastname, firstname, username");
                if ($result) {
                    while ( $fields = wrap_db_fetch_array($result) ) {
                        echo '<option value="' . $fields['user_id'] . '" title="' . $fields['email'] . '">' . $fields['lastname'] . ', ' . $fields['firstname'] . ' (' . $fields['username'] . ')</option>' . "\n\t\t" ;
                    }
                }
            ?>
            </select>
        </td>
        <td><input type="submit" name="Submit" value="-&gt;" class="ButtonStyle"><br><br><input type="submit" name="Submit" value="&lt;-" class="ButtonStyle"></td>
        <td><select name="admin_select" size="15">
            <?php
                //get a list of non-admin users
                $result = wrap_db_query("SELECT user_id, username, firstname, lastname, email FROM " . BOOKING_USER_TABLE . " WHERE is_admin = '1' ORDER BY lastname, firstname, username");
                if ($result) {
                    while ( $fields = wrap_db_fetch_array($result) ) {
                        echo '<option value="' . $fields['user_id'] . '" title="' . $fields['email'] . '">' . $fields['lastname'] . ', ' . $fields['firstname'] . ' (' . $fields['username'] . ')</option>' . "\n\t\t" ;
                        //check if this is the main admin account
                        if ($fields['username'] == 'admin') {
                            $admin_account_id = $fields['user_id'] ;
                        }
                    }
                }
            ?>
            </select></td>
    </tr>
</table>
<?php
//output a hidden field containing the id of the admin account
//so that we can block the transfer of this account
?>
<input type="hidden" name="default_admin_acc_id" value="<?= $admin_account_id ; ?>">

</form>

<?php
include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>