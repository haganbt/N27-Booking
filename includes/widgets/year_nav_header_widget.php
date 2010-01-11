<?
// year_nav_header_widget.php
// Displays the Year Navigation Header
?>


<!-- year_nav_header_widget.php -->
<table cellspacing="1" cellpadding="1" width="100%" border="0">
	<tr>
		<td class="BgcolorDull2" align="center" valign="middle"><a 
		href="<?=href_link(NAV_SCRIPT_NAME, 'date='.PREVIOUS_YEAR_DATE.'&'.make_hidden_fields_workstring(array('view', 'loc')), 'NONSSL')?>"><img 
		src="<?=DIR_WS_IMAGES?>/prev.gif" 
		alt="Previous Year" /></a><?=SELECTED_DATE_YEAR?><a 
		href="<?=href_link(NAV_SCRIPT_NAME, 'date='.NEXT_YEAR_DATE.'&'.make_hidden_fields_workstring(array('view', 'loc')), 'NONSSL')?>"><img 
		src="<?=DIR_WS_IMAGES?>/next.gif" alt="Next Year" /></a></td>
	</tr>
</table>

