<?
// year_nav_widget.php
// Displays the Year Navigation
  
  $cols = 3; // Number of Columns
?>


<table cellspacing="2" cellpadding="1" width="100%" border="0">
<?
  for ($i = 1; $i <= 12; $i++) {
	$mon_str = month_short_name($i);
	if ($i-1 == 0 || ($i-1)%$cols == 0) {
?>
		<tr>
<?
	}
	if (SELECTED_DATE_MONTH == $i) {
?>
		<td align="center" valign="middle" class="BgcolorDull"><span class="FontSoftSmall"><b><?=$mon_str?></b></span></td>
<?
	} else {
		$date = SELECTED_DATE_YEAR.'-'.sprintf("%02d", $i).'-'.SELECTED_DATE_DAY;
?>
		<td align="center" valign="middle" class="BgcolorNormal"><span class="FontSoftSmall"><a 
			href="<?=href_link(FILENAME_MONTH_VIEW,'date='.$date.'&'.make_hidden_fields_workstring(array('view', 'loc')), 'NONSSL')?>"><?=$mon_str?></a></span></td>
<?
	}
	if ($i%$cols == 0) {
?>
		</tr>
<?
	}
  } // end of for loop
  
  if (12%$cols != 0) {
    while ($x < $cols - (sizeof($months_str) % $cols)) {
?>
		<td></td>
<?
		$x++;
	}
?>
	</tr>
<?
  }
?>
</table>

