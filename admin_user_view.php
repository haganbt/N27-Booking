<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "View Users";
$page_title_bar = "View Users";
include_once("header.php");
?>
<p><a href="<?=FILENAME_ADMIN_UPDATE?>">Modify or remove an existing user account</a>&nbsp;|&nbsp;<a href="<?=FILENAME_ADMIN_BOOKING_CREDITS?>">Modify booking credits for an existing user</a>&nbsp;|&nbsp;<a href="<?=FILENAME_ADMIN_MODIFY_USER_GROUPS?>">Update Group Membership</a></p>
<table width="100%" border="0" cellpadding="4" cellspacing="0">
<tr>
  <td><b>Surname</b></td>
  <td><b>First Name</b></td>
  <td><b>User Name</b></td>
  <td><b>Email Address</b></td>
  <td><b>Credits</b></td>
  <td><b>Credit Type</b></td>
  <td><b>Advance Days</b></td>
  <? if ($_SESSION['PAYMENT_GATEWAY'] === true){ ?>
  	<td><b>Products</b></td>
  <? } ?>  
</tr>
<?
	// Define your colors for the alternating rows
	$color1 = "#FFFFFF";
    $color2 = "BgcolorNormal";
    $row_count = 0;

	//get a list of non-admin users
    $result = wrap_db_query("select u.user_id, u.username, u.firstname, u.lastname, u.email, u.booking_credits, c.credit_type_name, c.credit_type_booking_days 
FROM (" . BOOKING_USER_TABLE . " u 
LEFT JOIN " . BOOKING_CREDIT_TYPES . " c ON u.credit_type_id = c.credit_type_id) 
WHERE u.is_admin = '0' ORDER BY lastname, firstname, username");
		


	
	if ($result) {
    	while ( $fields = wrap_db_fetch_array($result) ) 
		{
			$row_color = ($row_count % 2) ? $color1 : $color2;		
			?>
            <tr>
              <td class="<?=$row_color?>" nowrap><?=$fields['lastname']?></td>
              <td class="<?=$row_color?>"><?=$fields['firstname']?></td>
              <td class="<?=$row_color?>"><?=$fields['username']?></td>
              <td class="<?=$row_color?>"><?=$fields['email']?></td>
              <td class="<?=$row_color?>"><?=$fields['booking_credits']?></td>
              <td class="<?=$row_color?>"><?=$fields['credit_type_name']?></td>
              <td class="<?=$row_color?>"><?
			  if ($fields['credit_type_booking_days'] == '0' )
			  {
				  echo "Use Site Default";
			  } else {
			   	echo $fields['credit_type_booking_days'];
			  }
			  
			  ?>
              
              </td>
              <? if ($_SESSION['PAYMENT_GATEWAY'] === true){ 
			  
			// For each user, load their products and groups
			$result2 = wrap_db_query("SELECT DISTINCT bpi.id, bpi.product_name, bpi.quantity, bpi.mc_gross, bpi.mc_currency 
							FROM (" . BOOKING_PRODUCT_ITEM . " bpi LEFT JOIN " . BOOKING_PRODUCT_GROUPS . " bpg ON bpg.product_id = bpi.id ) 
							WHERE group_id IN (SELECT DISTINCT group_id FROM " . BOOKING_USER_GROUPS_TABLE . " WHERE user_id = " . $fields['user_id'] .") ORDER BY bpi.quantity");
			  ?>
                  <td class="<?=$row_color?>">
                  <? while ( $products = wrap_db_fetch_array($result2) )	
                    {
                        echo $products['product_name']." (" . $products['mc_gross'] . " " .  $products['mc_currency'] . ", " . $products['quantity'] . " credits)<br />";	
                    } 
                    ?>
                  </td>
              <? } ?>
            </tr>
	<?
    $row_count++;

	}  ?>
</table>

<? }  // end if 
include_once("footer.php");
include_once("application_bottom.php"); ?>
