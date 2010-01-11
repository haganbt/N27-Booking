<?
// Member Header Include Variables

// MAIN INPUT VARIABLES
// $page_title
// $page_meta_description
// $page_meta_keywords

// $page_error_message
// $page_info_message

// Set Defaults
if (@$page_meta_description == "") { $page_meta_description = $page_title; }
if (@$page_meta_keywords == "") { $page_meta_keywords = $page_title; }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?=$page_title?></title>
<meta name="Description" content="Online Booking Website" />
<? if (@$page_meta_refresh == true) {?>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
<meta http-equiv="refresh" content="<?=PAGE_REFRESH?>" />
<?}?>
<meta name="robots" content="index,nofollow" />
<link rel="stylesheet" type="text/css" href="./main.css" />
<?php
if( $_GET['print_view'] == '1' ) {
    ?>
<link rel="stylesheet" type="text/css" href="./print.css" />
<script language="JavaScript">
<!--
function printPage() {
	if (window.print()) {
		//window.close() ;
	}
}
//-->
</script>
    <?php
}
?>
<script type="text/javascript" src="<?=DIR_WS_SCRIPTS?>/overlib.js"></script>
<script type="text/javascript">
<!--
function outsideBookingLimit() {
    alert('You may not make a booking more than <?= ( $_SESSION['ADVANCE_BOOKING_LIMIT'] / 24 ) ; ?> days in advance.\n\nPlease select an earlier date or try booking this slot again closer to the time.') ;
    return false ;
}
//-->
</script>
</head>

<body<?= ( $_GET['print_view'] ) ? ' onLoad="printPage();"' : '' ; ?>>

<div id="overDiv" style="position:absolute; visibility:hide;"></div>

<div id="HeaderNavAndBannerContainer" width="100%">

<?php
if ( $hide_navigation !== true ) {
?>
<table width="100%" height="111" border="0" cellpadding="0" cellspacing="0" class="DoNotPrint" id="HeaderBannerContainer">
  <tr>
    <!-- <td width="4" height="86">&nbsp;</td> -->
    <td width="8" height="86">&nbsp;</td>
    <td><a href="day_view.php"><img src="images/network27_logo_blue.png" /></a></td>
    </tr>
</table>
<table cellspacing="0" cellpadding="0" width="100%" border="0" id="HeaderNavContainer">
<tr><td valign="top">
<?php
}

if ( $hide_navigation !== true ) {

    if( $_GET['print_view'] != '1' ) {
        include('nav_bar_widget.php') ;
    }
?></td></tr>
</table>
</div>

<div id="MainContentContainer" width="100%">
<table cellspacing="0" cellpadding="0" width="100%" border="0" style="padding: 0px 10px 10px 10px;">
<tr><td>

<?
}
  if (!empty($page_title_bar)) {
?>
<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
    <td align="left" class="SectionHeaderStyle">
	<?=$page_title_bar?>
    </td>
  </tr>
</table>
<?
}
?>

<?
  if (!empty($page_error_message)) {
?>

<p align="center" class="Warning"><? echo $page_error_message; ?></p>

<?
  }
?>
<?
  if (!empty($page_info_message)) {
?>
<p align="center" class="FontBlack"><? echo $page_info_message; ?></p>
<?
  }

  if ( $show_admin_site_admin_menu === true ) {
      include_once("site_admin_links.php");
  }
?>
