<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Administrator - User Paypal Transactions";

$page_title_bar = "View User Paypal Transactions:";
$show_admin_site_admin_menu = true ;
include_once("header.php");
// Only allow access to this page if the Payment gateway is switched on
if ($_SESSION['PAYMENT_GATEWAY'] == '1' ) {

?>

<table width="100%" border="0" cellpadding="0" cellspacing="5">
  <tr>
    <td colspan="3"><form name="allTransactions" method="post" action="<? echo FILENAME_ADMIN_PAYPAL_TRANSACTIONS .'?all=true' ;?>">
        <br />
        <a href="#" onClick="document.allTransactions.submit()"><strong>View All Transactions</strong></a>
      </form></td>
  </tr>
  <tr>
    <td width="45">&nbsp;</td>
    <td width="885">&nbsp;</td>
  </tr>
  <tr>
    <td><strong>Select User:</strong></td>
  </tr>
  <tr>
    <form name="form1" method="post" action="<?=FILENAME_ADMIN_PAYPAL_TRANSACTIONS?>">
      <td valign="top"><select name="user_select" size="15" onchange="document.form1.submit()">
          <?php
                //get a list of users
                $result = wrap_db_query("SELECT user_id, username, firstname, lastname, email FROM " . BOOKING_USER_TABLE . " WHERE is_admin ='0' ORDER BY lastname, firstname, username");
                if ($result) {
                    while ( $fields = wrap_db_fetch_array($result) ) {
                        //check if this is the main admin account
                        if ($fields['username'] == 'admin') {
                            //it is so skip it and move on to the next one, ie don't display the admin account
                            continue ;
                        }
                        echo '<option value="' . $fields['user_id'] . '" title="' . $fields['email'] . '"' ;
                        if ( $_POST['user_select'] == $fields['user_id'] ) {
                            echo ' selected="true"' ;
                            //store the users name and current limit for use in a later part of the form
                            $users_full_name = $fields['firstname'] . ' ' . $fields['lastname'] ;
                        }
                        echo '>' . $fields['lastname'] . ', ' . $fields['firstname'] . ' (' . $fields['username'] . ')</option>' . "\n\t\t" ;
                    }
                }
            ?>
        </select>
      </td>
    </form>
    <td width="99%" valign="top"><?    if (($_POST['user_select'] != '')  || ( $all == true ) ){ 
                //check that we have not just made a successful update

				if ( $all == true ) { 
					$query= "SELECT * FROM " . PAYPAL_TRANSACTIONS . " order by payment_date LIMIT 100";
				} else {
					$query = "SELECT * FROM " . PAYPAL_TRANSACTIONS . " WHERE n27_user_id = '" . $_POST['user_select'] . "' order by payment_date LIMIT 30";
				}
					
			$result = wrap_db_query($query);
		   if ( $result && ( wrap_db_num_rows( $result) > 0 ) ) {
		  
		  
		  		if ( $all == true ) { 
					echo "Last 100 transactions for all users:<br /><br />";
				} else {
					echo "Last 30 transactions:<br /><br />";
				}
		  
		  
		  ?>
      <table width="98%" border="0" cellpadding="2" cellspacing="0">
        <tr>
          <td width="22%" class="BgcolorDull2">Date</td>
          <td width="19%" class="BgcolorDull2">Payer Name</td>
          <td width="27%" class="BgcolorDull2">Payer Email</td>
          <td width="8%" class="BgcolorDull2" align="center">Quantity</td>
          <td width="8%" class="BgcolorDull2">Value</td>
          <td width="8%" class="BgcolorDull2" align="center">Currency</td>
          <td width="8%" class="BgcolorDull2">Status</td>
        </tr>
      </table>
      <?
				while ($fields = wrap_db_fetch_array($result)) {             ?>
      <table width="98%" border="0" cellpadding="2" cellspacing="0">
        <tr>
          <td width="22%"><? echo $fields['payment_date'] ; ?></td>
          <td width="19%"><? echo $fields['first_name'] ." " . $fields['last_name'] ; ?></td>
          <td width="27%"><? echo $fields['payer_email'] ; ?></td>
          <td width="8%"  align="center"><? echo $fields['quantity'] ; ?></td>
          <td width="8%"><? echo $fields['mc_gross'] ; ?></td>
          <td width="8%" align="center"><? echo $fields['mc_currency'] ; ?></td>
          <td width="8%"><? echo $fields['payment_status'] ; ?></td>
        </tr>
      </table>
      <?php
	  
                        }
               } else {
			   echo "No transactions made.";
			   }
			 }

            ?>
    </td>
  </tr>
</table>
<?
  } else { // Only allow access to this page if the Payment gateway is switched on ?>
<br />
Please enable the <a href="<?=FILENAME_ADMIN_PAYMENT_GATEWAY?>">Payment Gateway</a> to view user transactions.<br />
<? } 

include_once("footer.php");
include_once("application_bottom.php"); ?>
