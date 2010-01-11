<? include_once("./includes/application_top.php"); ?>
<?
$page_title = 'Mailshot Opt-out';
$hideNavBarWidget = true ;
include_once("header.php");
?>

<!-- content here -->
<h3>Mailshot Opt-out</h3>

<?php
$emailRemovedOK = false ;
if ( isset( $_GET['emailAddress'] ) && ( trim( $_GET['emailAddress'] ) != '' ) ) {
    //remove e-mail from db
    if ( removeMailshotSubscriber( $_GET['emailAddress'] ) ) {
        $emailRemovedOK = true ;
        ?>
        <br><b>Preferences updated</b><br><br>
		Your preferences have been updated and you should no longer receive any further mailshots from <?= SITE_NAME ; ?><br>
		<br>
        <?php
    } else {
        //address could not be removed, probably does not exists in the subscribers list
        ?>
        <br><b>E-mail address not found</b><br><br>
        I'm sorry, but the e-mail address you entered (<?= $_GET['emailAddress'] ; ?>) was not found in our user records.<br>
        Please check that the address has been entered correctly and try again.<br>
        If you have already unsubscribed then you cannot unsubcribe again.<br>
        <br>
        <?php
    }
}

function removeMailshotSubscriber( $emailAddress ) {
    //check if e-mail address is actually in the db

  	//add this id into the opt-in table
  	$query = "UPDATE " . BOOKING_USER_TABLE . " SET mail_opt_out='1' WHERE email='" . mysql_real_escape_string( trim( $_GET['emailAddress'] ) ) . "' LIMIT 1" ;
  	$result = mysql_query( $query ) ;
  	if ( $result && ( mysql_affected_rows() > 0 ) ) {
  	    return true ;
  	} else {
  	    //address doesn't exist or user has already opted-out
	      return false ;
	  }
}

if ( !$emailRemovedOK ) {
?>

To stop receiving our special offer e-mails simply enter your e-mail address in the box below and press the "Update Preferences" button.<br>
<br>

<form name="form1" method="GET" action="remove.php">

<table border="0" cellspacing="0" cellpadding="4">
    <tr>
        <td>E-mail Address:&nbsp;</td>
        <td><input name="emailAddress" type="text" size="30" value="<?= ( ($_GET['emailAddress']!='') && !$emailRemovedOK ) ? $_GET['emailAddress'] : '' ; ?>">&nbsp;</td>
        <td><input type="submit" name="Submit" value="Update Preferences" class="ButtonStyle"></td>
    </tr>
</table>

</form>
<?php
}
?>

<br>
<br>
<br>

<!-- end content -->
<?php
include_once("footer.php");
include_once("application_bottom.php");
?>