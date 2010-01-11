<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Site Administration";
$page_title_bar = "Site Administration";

// Site Settings Form Submit
if ($_POST['save_changes'] == 'yes') {
    //check we have a valid value for allow_new_reg
    if ( ($_POST['allow_new_reg'] == '0') || ($_POST['allow_new_reg'] == '1') ) {

        //update the value in the db
        $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . $_POST['allow_new_reg'] . "' WHERE name = 'public_register' LIMIT 1 ;" ;
        $result = wrap_db_query($query);

        //update the value in the session
        $new_sess_val = false ;
        if ($_POST['allow_new_reg'] == '1') {
            $new_sess_val = true ;
        }
        $_SESSION['PUBLIC_REGISTER_FLAG'] = $new_sess_val ;
    }

    //check we have a valid value for hideDetailsFromPublic
    if ( ($_POST['hideDetailsFromPublic'] == '0') || ($_POST['hideDetailsFromPublic'] == '1') ) {

        //update the value in the db
        $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . $_POST['hideDetailsFromPublic'] . "' WHERE name = 'public_details_viewing' LIMIT 1 ;" ;
        $result = wrap_db_query($query);

        //nothing to update in the current users session as admins can always see this info
    }

    //check we have a valid value for hideDetailsFromNonAdmins
    if ( ($_POST['hideDetailsFromNonAdmins'] == '0') || ($_POST['hideDetailsFromNonAdmins'] == '1') ) {

        //update the value in the db
        $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . $_POST['hideDetailsFromNonAdmins'] . "' WHERE name = 'user_details_viewing' LIMIT 1 ;" ;
        $result = wrap_db_query($query);

        //nothing to update in the current users session as admins can always see this info
    }

    //check we have a valid value for advance_minimum_booking_hours
    if ( ($_POST['advance_minimum_booking_hours'] >= 0) && ($_POST['advance_minimum_booking_hours'] < 73) ) {

        //update the value in the db
        $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . $_POST['advance_minimum_booking_hours'] . "' WHERE name = 'minimum_booking_hours_limit' LIMIT 1 ;" ;
        $result = wrap_db_query($query);

        //update the value in the session
        $_SESSION['MINIMUM_ADVANCE_BOOKING_LIMIT'] = $_POST['advance_minimum_booking_hours'] ;
    }

    //check we have a valid value for advance_booking_days
    if ( ($_POST['advance_booking_days'] >= 0) && ($_POST['advance_booking_days'] < 121) ) {

        //update the value in the db
        $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . ( $_POST['advance_booking_days'] * 24 ) . "' WHERE name = 'booking_hours_limit' LIMIT 1 ;" ;
        $result = wrap_db_query($query);

        //update the value in the session
        $_SESSION['ADVANCE_BOOKING_LIMIT'] = ( $_POST['advance_booking_days'] * 24 ) ;
    }

    //check we have a valid value for cancel_booking_hours
    $sizeOfMinCancelHourOpt = count($_SESSION['MINIMUM_CANCELLATION_HOUR_OPTIONS']) - 1 ;
    $maxCancelValue = $_SESSION['MINIMUM_CANCELLATION_HOUR_OPTIONS'][$sizeOfMinCancelHourOpt] ;
    if ( ($_POST['cancel_booking_hours'] > 0) && ($_POST['cancel_booking_hours'] <= $maxCancelValue) ) {

        //update the value in the db
        $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . $_POST['cancel_booking_hours'] . "' WHERE name = 'cancellation_hours_limit' LIMIT 1 ;" ;
        $result = wrap_db_query($query);

        //update the value in the session
        $_SESSION['ADVANCE_CANCEL_LIMIT'] = $_POST['cancel_booking_hours'] ;
    }

}

$show_admin_site_admin_menu = true ;
include_once("header.php");
?>
<br>
<!-- <b>Please select a task from the following options:</b><br> -->
<b>Site Settings:</b><br>
<br>
<!--
<ul>
    <li><a href="admin_user_register.php">Create a new user account</a></li>
</ul>
-->
<form method="post" action="<?=FILENAME_SITE_ADMIN?>">
<table cellpadding="2" cellspacing="0" border="0">
<tr>
    <td>Allow new user registrations:</td>
    <td width="20">&nbsp;</td>
    <td><INPUT TYPE="radio" name="allow_new_reg" value="0"<?= ( $_SESSION['PUBLIC_REGISTER_FLAG'] !== true ) ? ' checked="true"' : '' ; ?>> No &nbsp;&nbsp;&nbsp;&nbsp; <INPUT TYPE="radio" name="allow_new_reg" value="1"<?= ( $_SESSION['PUBLIC_REGISTER_FLAG'] === true ) ? ' checked="true"' : '' ; ?>> Yes</td>
</tr>
<?php
$result = wrap_db_query("SELECT function_value FROM " . SETTINGS_TABLE . " WHERE name = 'public_details_viewing' LIMIT 0,1 ;");
if ($result) {
    if ($fields = wrap_db_fetch_array($result)) {
        //change 1's and 0's to true and false
        if ( $fields['function_value'] == "1" ) {
            $showPublicDetailsFlag = true ;
        } else {
            $showPublicDetailsFlag = false ;
        }
    }
}
?>
<tr>
    <td>Show user details to public viewers:</td>
    <td width="20">&nbsp;</td>
    <td><INPUT TYPE="radio" name="hideDetailsFromPublic" value="0"<?= ( $showPublicDetailsFlag != true ) ? ' checked="true"' : '' ; ?>> No &nbsp;&nbsp;&nbsp;&nbsp; <INPUT TYPE="radio" name="hideDetailsFromPublic" value="1"<?= ( $showPublicDetailsFlag == true ) ? ' checked="true"' : '' ; ?>> Yes</td>
</tr>
<?php
$result = wrap_db_query("SELECT function_value FROM " . SETTINGS_TABLE . " WHERE name = 'user_details_viewing' LIMIT 0,1 ;");
if ($result) {
    if ($fields = wrap_db_fetch_array($result)) {
        //change 1's and 0's to true and false
        if ( $fields['function_value'] == "1" ) {
            $showUserDetailsFlag = true ;
        } else {
            $showUserDetailsFlag = false ;
        }
    }
}
?>
<tr>
    <td>Show user details to logged in users:</td>
    <td width="20">&nbsp;</td>
    <td><INPUT TYPE="radio" name="hideDetailsFromNonAdmins" value="0"<?= ( $showUserDetailsFlag != true ) ? ' checked="true"' : '' ; ?>> No &nbsp;&nbsp;&nbsp;&nbsp; <INPUT TYPE="radio" name="hideDetailsFromNonAdmins" value="1"<?= ( $showUserDetailsFlag == true ) ? ' checked="true"' : '' ; ?>> Yes <font color="gray"><i>(does not apply to admins)</i></font></td>
</tr>
<tr>
    <td>Advance booking minimum limit:</td>
    <td width="20">&nbsp;</td>
    <td><select name="advance_minimum_booking_hours" size="1" style="text-align: right;">
            <?php
            $min_booking_limit_val_days = $_SESSION['MINIMUM_ADVANCE_BOOKING_LIMIT'] ;
            for ( $i = 0 ; $i < 73 ; $i++ ) {
                echo '<option value="' . $i . '"' ;
                if ($i == $min_booking_limit_val_days) {
                    echo ' selected="true"' ;
                }
                echo '>' ;
                if ($i != 0) {
                    echo $i . ' hour';
                    if ($i > 1) {
                        echo 's' ;
                    }
                } else {
                    echo 'No limit' ;
                }
                echo "</option>\n" ;
            }
            ?>
        </select> <font color="gray"><i>(hours from now - does not apply to admins)</i></font>
    </td>
</tr>
<tr>
    <td>Advance booking maximum limit:</td>
    <td width="20">&nbsp;</td>
    <td><select name="advance_booking_days" size="1" style="text-align: right;">
            <?php
            $booking_limit_val_days = ($_SESSION['ADVANCE_BOOKING_LIMIT'] / 24) ;
            for ( $i = 0 ; $i < 121 ; $i++ ) {
                echo '<option value="' . $i . '"' ;
                if ($i == $booking_limit_val_days) {
                    echo ' selected="true"' ;
                }
                echo '>' ;
                if ($i != 0) {
                    echo $i . ' day';
                    if ($i > 1) {
                        echo 's' ;
                    }
                } else {
                    echo 'No limit' ;
                }
                echo "</option>\n" ;
            }
            ?>
        </select> <font color="gray"><i>(days from now - does not apply to admins)</i></font>
    </td>
</tr>
<tr>
    <td>Minimum cancellation period:</td>
    <td width="20">&nbsp;</td>
    <td><select name="cancel_booking_hours" size="1" style="text-align: right;">
            <?php
            $numCancelHourOpts = count($_SESSION['MINIMUM_CANCELLATION_HOUR_OPTIONS']) ;
            for ( $i = 0 ; $i < $numCancelHourOpts ; $i++ ) {
                echo '<option value="' . $_SESSION['MINIMUM_CANCELLATION_HOUR_OPTIONS'][$i] . '"' ;
                if ($_SESSION['MINIMUM_CANCELLATION_HOUR_OPTIONS'][$i] == $_SESSION['ADVANCE_CANCEL_LIMIT']) {
                    echo ' selected="true"' ;
                }
                echo '>' . $_SESSION['MINIMUM_CANCELLATION_HOUR_OPTIONS'][$i] . ' hour' ;
                if ($_SESSION['MINIMUM_CANCELLATION_HOUR_OPTIONS'][$i] > 1) {
                    echo 's' ;
                }
                echo "</option>\n" ;
            }
            ?>
        </select> <font color="gray"><i>(hours before slot starts - does not apply to admins)</i></font>
    </td>
</tr>
<tr>
    <td align="center" colspan="3"><br>
        <input type="hidden" name="groups" value="">
        <input type="hidden" name="save_changes" value="yes">
        <input type="submit" name="register" value="Save Settings" class="ButtonStyle">
    </td>
</tr>
</table>
</form>

<?php
include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>