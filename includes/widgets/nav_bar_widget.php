<?
// month_nav_widget.php
// Display arrangement for the navigation bar.
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="NavBarContainer">
  <tr>
	<td align="center" valign="top" colspan="4">
		<table cellspacing="1" cellpadding="1" width="100%" border="0">
		  <tr><td align="left" class="SectionHeaderStyle">Booking Calendar Navigation<!--<div style="float: right;"><a href="#" onclick="document.getElementById('NavWidgetRow').style.display='block'; return false;">+</a>/<a href="#" onclick="document.getElementById('NavWidgetRow').style.display='none'; return false;">-</a></div>--></td></tr>
		</table>
	</td>
  </tr>
  <tr id="NavWidgetRow">
  	<td align="center" valign="top">
  		<?include('user_nav_widget.php')?>

  	</td>
  	<td align="center" valign="top">
  		<?include('day_nav_header_widget.php')?>
  		<?include('day_nav_widget.php')?>
  		<?include('view_nav_widget.php')?>
  		<?include('loc_nav_widget.php')?>
  	</td>
  	<td align="center" valign="top">
  		<?include('month_nav_header_widget.php')?>
  		<?include('month_nav_widget.php')?>
  	</td>
  	<td align="center" valign="top">
  		<?include('year_nav_header_widget.php')?>
  		<?include('year_nav_widget.php')?>
  	</td>
  </tr>
</table>
