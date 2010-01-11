<? include_once("./includes/application_top.php"); ?>
<?
$page_title = 'My Bookings';
$page_title_bar = "My Bookings";
include_once("header.php");

$user_info = get_user( get_user_id($_SESSION['valid_user']) ) ;
//print_r( $user_info ) ;
?>

<br>

<?php
if ( $user_info['booking_credits'] != 'Not used' ) {
    ?><b>Booking credits remaining:</b> <?= $user_info['booking_credits'] ; ?> credit<?= ( $user_info['booking_credits'] != 1 ) ? 's' : '' ; ?>
    <br>
    <br>
    <br>
    <?php
}
?>

<b>Current Bookings:</b><br>
<br>
<?php
// Check how many upcoming bookings the user already has reserved in the system
$showedABooking = false ;
$user_events_result = get_user_events($user_info['username'], true, 50) ; //get a max of 50 results
$num_events_results = wrap_db_num_rows( $user_events_result ) ;
if ( $num_events_results >= 50 ) {
    echo "NOTE: You currently have more than 50 advance bookings. Only the next 50 are shown below.<br><br>" ;
}
?>
<table cellpadding="2" cellspacing="0" border="0" style="margin-left: 20px;">
<?php
while ($user_events_row = wrap_db_fetch_array( $user_events_result) ) {
//    echo '<pre>' ;
//    print_r( $user_events_row ) ;
//    echo '</pre>' ;
	$display_dates_and_time_ranges = get_event_dates_and_time_ranges($user_events_row['event_id'], $user_events_row['location']);


	if (count($display_dates_and_time_ranges) > 0) {
		reset ($display_dates_and_time_ranges);
		foreach ($display_dates_and_time_ranges as $display_date_and_time) {
			list ($date, $time_range) = explode(" ", $display_date_and_time);
			list ($from_time, $to_time) = explode("-", $time_range);
?>
    <tr>
        <td align="left" valign="top" nowrap="nowrap"><?=short_date_format_with_day_of_week($date);?> &nbsp; </td>
        <td align="right" valign="top" nowrap="nowrap"><?=format_time_to_ampm($from_time)?></td>
        <td align="left" valign="top" nowrap="nowrap">-</td>
        <td align="left" valign="top" nowrap="nowrap"><?=format_time_to_ampm($to_time)?></td>
        <td align="left" valign="top" nowrap="nowrap"> &nbsp;&nbsp;&nbsp; View: <a href="<?= href_link(FILENAME_DETAILS_VIEW, 'event_id='.$user_events_row['event_id'].'&'.make_hidden_fields_workstring(array('date', 'view', 'loc')), 'NONSSL') ; ?>"><strong>Details</strong></a>, </td>
        <td align="left" valign="top" nowrap="nowrap"> <a href="<?= href_link(FILENAME_DAY_VIEW, 'date='.$date.'&'.make_hidden_fields_workstring(array('view', 'loc')), 'NONSSL') ; ?>"><strong>Calendar</strong></a></td>

    </tr>
<?
            $showedABooking = true ;
	    }

    }
}

if ( !$showedABooking ) {
    //user has no forthcoming bookings
    ?>
    <tr>
        <td>No current bookings made.</td>
    </tr>
    <?php
}
?>
</table>

<br>
<br>

<b>Booking Rules for user <?= $user_info['firstname'] ; ?> <?= $user_info['lastname'] ; ?> (<?= $user_info['username'] ; ?>):</b><br>
<br>
<ul>
    <li>You can have <?= ($user_info['max_bookings'] > 0) ? $user_info['max_bookings'] : 'an unlimited number of' ; ?> concurrent bookings at any one time.</li>
    <li>You can make bookings <?= ( wrap_session_is_registered("admin_user") ) ? 'any number of' : ( $_SESSION['ADVANCE_BOOKING_LIMIT'] / 24 ) ; ?> days in advance.</li>
    <li>You can modify current bookings <?= ( wrap_session_is_registered("admin_user") ) ? 'at any time' : ( $_SESSION['ADVANCE_CANCEL_LIMIT'] . ' hours' ) ; ?> prior to the booking date/time.</li>
</ul>

<?

include_once("footer.php");

include_once("application_bottom.php");
?>