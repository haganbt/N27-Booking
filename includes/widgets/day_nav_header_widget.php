<?
// day_nav_header_widget.php
// Displays the Day Navigation Header
?>


<!-- day_nav_header_widget.php -->
<table cellspacing="1" cellpadding="1" width="100%" border="0">
	<tr>
		<td class="BgcolorDull2" align="center" valign="middle"><a 
		href="<?=href_link(NAV_SCRIPT_NAME, 'date='.PREVIOUS_DAY_DATE.'&'.make_hidden_fields_workstring(array('view', 'loc')), 'NONSSL')?>"><img 
		src="<?=DIR_WS_IMAGES?>/prev.gif" 
		alt="Previous Day" /></a><?=SELECTED_DATE_SHORTSTR?><a 
		href="<?=href_link(NAV_SCRIPT_NAME, 'date='.NEXT_DAY_DATE.'&'.make_hidden_fields_workstring(array('view', 'loc')), 'NONSSL')?>"><img 
		src="<?=DIR_WS_IMAGES?>/next.gif" alt="Next Day" /></a></td>
	</tr>
</table>

