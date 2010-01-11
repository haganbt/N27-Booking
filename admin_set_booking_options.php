<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Site Administration";
$page_title_bar = "Configure Booking Options";

// Site Settings Form Submit
if ($_POST['save_changes'] == 'yes') {

    //check we have a valid value for hideDetailsFromNonAdmins
    if ( ($_POST['minUserBookingOptions'] == '0') || ($_POST['minUserBookingOptions'] == '1') ) {

        //update the value in the db
        $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . $_POST['minUserBookingOptions'] . "' WHERE name = 'user_minimum_booking_options' LIMIT 1 ;" ;
        $result = wrap_db_query($query);

        //nothing to update in the current users session as admins can always see this info
    }

    //check we have a valid value for hideDetailsFromNonAdmins
    if ( ($_POST['minAdminBookingOptions'] == '0') || ($_POST['minAdminBookingOptions'] == '1') ) {

        //update the value in the db
        $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . $_POST['minAdminBookingOptions'] . "' WHERE name = 'admin_minimum_booking_options' LIMIT 1 ;" ;
        $result = wrap_db_query($query);

        //nothing to update in the current users session as admins can always see this info
    }

}

//new option form submit
if ( isset( $_POST['newBookingOption'] ) && ( trim( $_POST['newBookingOption'] ) != '' ) ) {
    $query = "INSERT INTO " . BOOKING_OPTIONS_TABLE . " ( description ) VALUES ( '" . mysql_escape_string( trim( $_POST['newBookingOption'] ) ) . "' )" ;
    wrap_db_query($query);
    //no need to check if it got added, the user will see this for themselves soon enough :)
}

//delete option submitted on GET string
if ( isset( $_GET['delOpt'] ) && ( $_GET['delOpt'] == '1' ) && ( $_GET['optID'] > 0 ) ) {
    $query = "DELETE FROM " . BOOKING_OPTIONS_TABLE . " WHERE option_id='" . $_GET['optID'] . "' LIMIT 1" ;
    wrap_db_query($query);
    //no need to check if it got deleted, the user will see this for themselves soon enough
}

$show_admin_site_admin_menu = true ;
include_once("header.php");
?>
<br>
<b>Current options:</b><br>
<br>
<form method="post" action="<?=FILENAME_ADMIN_BOOKING_OPTIONS?>">

<?php
$result = wrap_db_query("SELECT option_id, description FROM " . BOOKING_OPTIONS_TABLE . " ORDER BY description ASC");
if ( $result && ( wrap_db_num_rows( $result ) > 0 ) ) {
?>

    <table border="0" cellpadding="0" cellspacing="2">
    <?php
    //load any saved booking option preferences this user may have
    $savedUserPrefOptions = null ;
    $userPrefResult = wrap_db_query("SELECT option_id FROM " . BOOKING_USER_OPTIONS_TABLE . " WHERE user_id='" . $bookingByUserID . "'");
    if ( $userPrefResult && ( wrap_db_num_rows( $userPrefResult ) > 0 ) ) {
        while ( $userPrefFields = wrap_db_fetch_array($userPrefResult) ) {
            $savedUserPrefOptions[] = $userPrefFields['option_id'] ;
        }
    }

    $rightCol = false ;
    for ( $r = 0 ; $fields = wrap_db_fetch_array($result) ; $r++ ) {
        //is this a left or right column?
        if ( ( $r % 2 ) == 0 ) {
            //left column
            echo '<tr align="left"><td>' ;
            $rightCol = false ;
        } else {
            //right column
            echo '<td width="20">&nbsp;</td><td>' ;
            $rightCol = true ;
        }
        echo '- ' . htmlspecialchars( $fields['description'] ) . ' </td>';
        echo '<td> [<a href="' . FILENAME_ADMIN_BOOKING_OPTIONS . '?delOpt=1&optID=' . $fields['option_id'] . '" onclick="return confirm( \'Are you sure you want to delete this option?\' );">del</a>]' ;
        echo '</td>' ;
        if ( $rightCol ) {
            echo '</tr>' ;
        }
    }
    //ensure the right column is properly closed if we have an odd number of options
    if ( !$rightCol ) {
        echo '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>' ;
    }
    ?>
    </table>

    <?php
} else {
    echo "There are currently no booking options configured." ;
}
?>

<br>
<br>

<table border="0" cellpadding="0" cellspacing="2">
    <tr>
        <td><b>Add Booking Option:</b></td>
        <td width="20">&nbsp;</td>
        <td><input type="text" name="newBookingOption" value=""></td>
        <td width="20">&nbsp;</td>
        <td><input type="submit" name="submit" value="Add Option" class="ButtonStyle"></td>
    </tr>
</table>

</form>

<form method="post" action="<?=FILENAME_ADMIN_BOOKING_OPTIONS?>">

<br>
<br>
<b>Booking Option Settings:</b><br>
<br>

<table border="0" cellpadding="0" cellspacing="2">
<?php
$result = wrap_db_query("SELECT function_value FROM " . SETTINGS_TABLE . " WHERE name = 'user_minimum_booking_options' LIMIT 0,1 ;");
if ($result) {
    if ($fields = wrap_db_fetch_array($result)) {
        //change 1's and 0's to true and false
        if ( $fields['function_value'] > 0 ) {
            $minUserBookingOptionsFlag = true ;
        } else {
            $minUserBookingOptionsFlag = false ;
        }
    }
}
?>
<tr>
    <td>Force users to select booking options:</td>
    <td width="20">&nbsp;</td>
    <td><INPUT TYPE="radio" name="minUserBookingOptions" value="0"<?= ( $minUserBookingOptionsFlag != true ) ? ' checked="true"' : '' ; ?>> No &nbsp;&nbsp;&nbsp;&nbsp; <INPUT TYPE="radio" name="minUserBookingOptions" value="1"<?= ( $minUserBookingOptionsFlag == true ) ? ' checked="true"' : '' ; ?>> Yes</td>
</tr>

<?php
$result = wrap_db_query("SELECT function_value FROM " . SETTINGS_TABLE . " WHERE name = 'admin_minimum_booking_options' LIMIT 0,1 ;");
if ($result) {
    if ($fields = wrap_db_fetch_array($result)) {
        //change 1's and 0's to true and false
        if ( $fields['function_value'] > 0 ) {
            $minAdminBookingOptionsFlag = true ;
        } else {
            $minAdminBookingOptionsFlag = false ;
        }
    }
}
?>
<tr>
    <td>Force admins to select booking options:</td>
    <td width="20">&nbsp;</td>
    <td><INPUT TYPE="radio" name="minAdminBookingOptions" value="0"<?= ( $minAdminBookingOptionsFlag != true ) ? ' checked="true"' : '' ; ?>> No &nbsp;&nbsp;&nbsp;&nbsp; <INPUT TYPE="radio" name="minAdminBookingOptions" value="1"<?= ( $minAdminBookingOptionsFlag == true ) ? ' checked="true"' : '' ; ?>> Yes</td>
</tr>

</table>

<br>

<input type="hidden" name="save_changes" value="yes">
<input type="submit" name="register" value="Save Settings" class="ButtonStyle">
</form>

<?php
include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>