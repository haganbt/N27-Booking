<? include_once("./includes/application_top.php"); ?>
<?
$page_title = 'Booking Calendar - Day View';
if (PAGE_REFRESH > 29) { $page_meta_refresh = true; }
include_once("header.php");
?>


<?php
if ( $_GET['print_view'] != '1' ) {
    include('day_widget.php') ;
} else {
    include('day_print_widget.php') ;
}
?>


<?

include_once("footer.php");

include_once("application_bottom.php");
?>