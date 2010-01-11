<?
// day_nav_widget.php
// Displays the Day Navigation

  // Additional Style Features
  $highlight_style = ' style="color : #000000; font-size : 11px; font-weight : bold; text-decoration : none;"';
?>

<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
      <td nowrap="nowrap" align="center" valign="middle" class="BgcolorDull2">
	  <img src="<?=DIR_WS_IMAGES?>/spacer.gif" width="15" height="15" />
      Select View Mode: &nbsp;&nbsp;
	  <img src="<?=DIR_WS_IMAGES?>/spacer.gif" width="15" height="15" />
      </td>
  </tr>
</table>

<table cellspacing="1" cellpadding="1" width="100%" border="0">
  <tr>
	<td nowrap="nowrap" align="center" valign="middle" class="BgcolorNormal"><span class="FontSoftSmall">
		<a href="<?=href_link(FILENAME_DAY_VIEW,'view=day&'.make_hidden_fields_workstring(array('date', 'loc')), 'NONSSL')?>" 
		<?= (NAV_SCRIPT_NAME == FILENAME_DAY_VIEW) ? $highlight_style : ''?>><b>Day</b></a>
&nbsp;
		<a href="<?=href_link(FILENAME_WEEK_VIEW,'view=week&'.make_hidden_fields_workstring(array('date', 'loc')), 'NONSSL')?>" 
		<?= (NAV_SCRIPT_NAME == FILENAME_WEEK_VIEW) ? $highlight_style : ''?>><b>Week</b></a>
&nbsp;
		<a href="<?=href_link(FILENAME_MONTH_VIEW,'view=month&'.make_hidden_fields_workstring(array('date', 'loc')), 'NONSSL')?>" 
		<?= (NAV_SCRIPT_NAME == FILENAME_MONTH_VIEW) ? $highlight_style : ''?>><b>Month</b></a>
	</span></td>
  </tr>
</table>

