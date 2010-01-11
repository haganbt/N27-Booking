<?
// add_event_widget.php
// Display the Add Form for the Navigation Bar

//if (ALLOW_ADDITIONS FLAG) { //unused line of orig code. already commented when received by MJ.
//Modified by MJ on 10/03/05 - commented 'if (true) {' line below and replaced it with the following one.
//this checks if the user is an administrator and only displays the mmm/dd/yyyy boxes if they are.
//if (true) {
if ( wrap_session_is_registered("admin_user") ) {
?>

<!-- add_event_widget.php -->
<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
	<td nowrap="nowrap" align="center" valign="middle" class="BgcolorDull2">
	<img src="<?=DIR_WS_IMAGES?>/spacer.gif" width="15" height="15" />
	Add New Event: 
	<img src="<?=DIR_WS_IMAGES?>/spacer.gif" width="15" height="15" />
	</td>
  </tr>
</table>

<form action="<?=FILENAME_ADD_EVENT?>" method="post">
<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
	<td nowrap="nowrap" align="center" valign="middle" class="BgcolorNormal"><div class="FontSoftSmall">
	<select name="start_mon" class="FontSoftSmall">
<? for ($i=1; $i<=12; $i++) { // Defined 1-12 ?>
	<option value="<?=$i?>"<?=(SELECTED_DATE_MONTH+0 == $i) ? ' selected="selected"' : ''?>><?=month_short_name($i)?></option>
<? } ?>
	</select>
	<select name="start_day" class="FontSoftSmall">
<? for ($i=1; $i<=31; $i++) { ?>
	<option value="<?=$i?>"<?=(SELECTED_DATE_DAY+0 == $i) ? ' selected="selected"' : ''?>><?=$i?></option>
<? } ?>
	</select>,
	<select name="start_year" class="FontSoftSmall">
<? for ($i=SELECTED_DATE_YEAR-1; $i<=SELECTED_DATE_YEAR+11; $i++) { ?>
	<option value="<?=$i?>"<?=(SELECTED_DATE_YEAR+0 == $i) ? ' selected="selected"' : ""?>><?=$i?></option>
<? } ?>
	</select><?=make_hidden_fields(array('date', 'view', 'loc'))?>
	<input type="submit" name="display_add_form" value="Add" class="ButtonStyleSmall" />
	</div>
	</td>
  </tr>
</table>
</form>

<?php
} else {
?>
<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
	<td nowrap="nowrap" align="center" valign="middle" class="BgcolorDull2">
	<img src="<?=DIR_WS_IMAGES?>/spacer.gif" width="15" height="15" />
	Add New Event: 
	<img src="<?=DIR_WS_IMAGES?>/spacer.gif" width="15" height="15" />
	</td>
  </tr>
</table>

<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
	<td nowrap="nowrap" align="center" valign="middle" class="BgcolorNormal"><a href="<?= href_link(FILENAME_WEEK_VIEW,'view=week&'.make_hidden_fields_workstring(array('date', 'loc')), 'NONSSL')?>">Select date from calendar</a></td>
  </tr>
</table>
</form>
<?php
}
?>