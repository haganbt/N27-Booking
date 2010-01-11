<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Set Block Booking Privileges";
$page_title_bar = "Set Block Booking Privileges";

//echo '<pre>' ;
//print_r( $_POST ) ;
//echo '</pre>' ;

$errorMsg = false ; //holder for potential error messages
$new_block_book_value = '' ;
$new_user_id_value = '' ;
//check for form submission
if ($_POST['Submit'] != '') {
    //check if we are creating a block booker
    if ($_POST['Submit'] == "->") {
        //check if a user was selected
        if ($_POST['single_select'] != '') {
            $new_block_book_value = '1' ;
            $new_user_id_value = $_POST['single_select'] ;
        } else {
            $errorMsg = 'Please select a user that you wish to grant block booking privileges to before submitting this form.' ;
        }
    } else if ($_POST['Submit'] == "<-") {
        //check if an user was selected
        if ($_POST['block_select'] != '') {
            $new_block_book_value = '0' ;
            $new_user_id_value = $_POST['block_select'] ;
        } else {
            $errorMsg = 'Please select a user that you wish to revoke block booking privileges from before submitting this form.' ;
        }
    }

    //check if it is okay to make changes, if not echo the error
    if ($errorMsg === false) {
        $query = "UPDATE " . BOOKING_USER_TABLE . " SET block_book='" . $new_block_book_value . "' WHERE user_id='" . $new_user_id_value . "' LIMIT 1 ;" ;
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
Use the controls below to provide regular users with block booking privileges<br>(or to remove them from those already with block booking privileges):<br>
<br>
Note: This page shows regular users only as all admins always have blook booking privileges.<br>
<br>

<form name="form1" method="post" action="<?=FILENAME_ADMIN_BLOCK_BOOKING?>">

<table border="0" cellspacing="10" cellpadding="0">
    <tr>
        <td><b>Single Slot Only</b></td>
        <td>&nbsp;</td>
        <td><b>Multi Slot Capable</b></td>
    </tr>
    <tr>
        <td><select name="single_select" size="15">
            <?php
                //get a list of non block bookers
                $result = wrap_db_query("SELECT user_id, username, firstname, lastname, email FROM " . BOOKING_USER_TABLE . " WHERE is_admin = '0' AND block_book = '0' ORDER BY lastname, firstname, username");
                if ($result) {
                    while ( $fields = wrap_db_fetch_array($result) ) {
                        echo '<option value="' . $fields['user_id'] . '" title="' . $fields['email'] . '">' . $fields['lastname'] . ', ' . $fields['firstname'] . ' (' . $fields['username'] . ')</option>' . "\n\t\t" ;
                    }
                }
            ?>
            </select>
        </td>
        <td><input type="submit" name="Submit" value="-&gt;" class="ButtonStyle"><br><br><input type="submit" name="Submit" value="&lt;-" class="ButtonStyle"></td>
        <td><select name="block_select" size="15">
            <?php
                //get a list of non-admin users
                $result = wrap_db_query("SELECT user_id, username, firstname, lastname, email FROM " . BOOKING_USER_TABLE . " WHERE is_admin = '0' AND block_book = '1' ORDER BY lastname, firstname, username");
                if ($result) {
                    while ( $fields = wrap_db_fetch_array($result) ) {
                        echo '<option value="' . $fields['user_id'] . '" title="' . $fields['email'] . '">' . $fields['lastname'] . ', ' . $fields['firstname'] . ' (' . $fields['username'] . ')</option>' . "\n\t\t" ;
                    }
                }
            ?>
            </select></td>
    </tr>
</table>

</form>

<?php
include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>