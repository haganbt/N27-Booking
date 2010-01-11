<?
// month_nav_widget.php
// Displays the Month Navigation

  // Setup the weekday index array values
  $wdays_ind = array ();
  $wdays_ind = weekday_index_array(WEEK_START);

  $days_in_the_month = number_of_days_in_month(SELECTED_DATE_YEAR, SELECTED_DATE_MONTH);
  $month_begin_wday = weekday_short_name(beginning_weekday_of_the_month(SELECTED_DATE_YEAR, SELECTED_DATE_MONTH));
?>


<!-- month_nav_widget.php -->
<table cellspacing="2" cellpadding="1" width="100%" border="0">
<!-- header -->
<tr>
<?
  reset ($wdays_ind);
  foreach ($wdays_ind as $index) {
?>
  <td align="center" valign="middle" class="BgcolorBright"><b class="FontSoftSmall"><?=weekday_short_name($index);?></b></td>
<?
  }
?>
</tr>
  <!-- rows -->
<?
  $count = 0;
  for ($row = 1; $row < 7; $row++) {
?>
  <tr>
<?
	reset($wdays_ind);
	foreach ($wdays_ind as $wday_ind) {
	
		$selected = (SELECTED_DATE_DAY == $count + 1) ? 1 : 0;
		$count = ($count > 0 || (weekday_short_name($wday_ind) == $month_begin_wday)) ? $count + 1 : 0;
		
		if ($count && $count <= $days_in_the_month) {
			if ($selected) {
?>
				<td align="center" valign="middle" class="BgcolorDull"><span class="FontSoftSmall"><b><?=$count?></b></span></td>
<?
			} else {
				$date = sprintf("%04d-%02d-%02d",SELECTED_DATE_YEAR ,SELECTED_DATE_MONTH , $count);
?>
			<td align="center" valign="middle" class="BgcolorNormal"><span class="FontSoftSmall" 
			><a href="<?=href_link(FILENAME_DAY_VIEW, 'date='.$date.'&'.make_hidden_fields_workstring(array('view', 'loc')), 'NONSSL')?>"><?=$count?></a></span></td>
<?
			}
		} else {
?>
			<td align="center" valign="middle"><span class="FontSoftSmall">&nbsp;</span></td>
<?
		}
    } // end of foreach
?>
  </tr>
<?
	if ($count >= $days_in_the_month) { break; }
  } // end for loop
?>
</table>

