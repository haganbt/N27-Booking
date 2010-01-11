<?
// day_nav_widget.php
// Displays the Day Navigation
?>


<table cellspacing="2" cellpadding="1" width="100%" border="0">
  <tr>
	<td class="BgcolorNormal" align="center" valign="middle" nowrap="nowrap">
		<div class="FontSoftSmall">Today:
		<b><a href="<?=href_link(NAV_SCRIPT_NAME, 'date='.TODAYS_DATE.'&'.make_hidden_fields_workstring(array('view', 'loc')), 'NONSSL')?>"><?=TODAYS_DATE_SHORTSTR?></a></b></div>
	</td>
  </tr>
</table>

