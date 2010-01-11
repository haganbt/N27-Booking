<?
  // set a variable that will be picked up by the user widget to say that it is the buy credits page, therefore
  // the number of credits bought by the user may have changed
  $buyCreditsPage=true;

include_once("./includes/application_top.php"); ?>
<?
if (REQUIRE_AUTH_FOR_ADDING_FLAG) {
    if (!@wrap_session_is_registered('valid_user')) {
    	header('Location: ' . href_link(FILENAME_LOGIN, 'origin=' . FILENAME_BUY_CREDITS . '&' . make_hidden_fields_workstring(), 'NONSSL'));
        wrap_exit();
    }
}
$page_title = 'Buy Credits';
$page_title_bar = "Buy Credits";
include_once("header.php");
	
	// Show the number of credits the user has
	echo "<p><strong>Booking credits remaining:</strong>&nbsp;" . $user_info['booking_credits'] . "&nbsp;credit";
	if ($user_info['booking_credits'] != 1)
	{
		 echo "s";
	} 
	echo "</p>";
?>
<table width="550" border="0" cellpadding="2" cellspacing="4">
    <tr>
      <td width="530">NOTE: Once payment has been made via Paypal, your account will be updated automatically with the additional credits once you have received a confirmation email. <font color="red">Please note this can take up to 20 minutes.</font></td>
    </tr>
  </table>
<?
	//Load the user info
	$user_info = get_user( get_user_id($_SESSION['valid_user']) ) ;


	// Check we have permissions to buy credits
	if (( wrap_session_is_registered("admin_user") )  || ( $user_info['booking_credits'] == 'Not used' ) || ( $_SESSION['PAYMENT_GATEWAY'] != '1' ) || !is_numeric($user_info['user_id']))
	{
		echo "<p>You do not have permission to purchase booking credits.  Please contact an Administrator.</p>";
		include_once("footer.php");
		include_once("application_bottom.php");
		die;

	}

	//  Load the products based on the users group membership
	$result = wrap_db_query("SELECT DISTINCT bpi.id, bpi.product_name, bpi.quantity, bpi.mc_gross, bpi.mc_currency 
							FROM (" . BOOKING_PRODUCT_ITEM . " bpi LEFT JOIN " . BOOKING_PRODUCT_GROUPS . " bpg ON bpg.product_id = bpi.id ) 
							WHERE group_id IN (SELECT DISTINCT group_id FROM " . BOOKING_USER_GROUPS_TABLE . " WHERE user_id = " . $user_info['user_id'] .") ORDER BY bpi.product_name, bpi.quantity");
	

	// If there are no products assigned, load the default
	 if (!(wrap_db_num_rows($result) >= 1) || !$result)
	{
		$result = wrap_db_query("SELECT DISTINCT id, product_name, quantity, mc_gross, mc_currency FROM " . BOOKING_PRODUCT_ITEM . " WHERE id = '1' LIMIT 1");
	}

	
	if ($result) 
	{	
		
		while ( $products = wrap_db_fetch_array($result) ) 
		{ 
		// LIVE   
		//  https://www.sandbox.paypal.com/cgi-bin/webscr

		?>
			<p>
		   <form action="https://www.paypal.com/cgi-bin/webscr" method="post"> 
            <input type="hidden" name="notify_url" value="<?=DOMAIN_NAME . substr(DIR_WS_SCRIPTS, 1) . "paypal_ipn_res.php"?>">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="<?=$_SESSION['PAYPAL_BUSINESS_EMAIL']?>">
            <input type="hidden" name="item_name" value="<?=$products['product_name']?>">
            <input type="hidden" name="no_shipping" value="1">	
            <input type="hidden" name="no_note" value="1">    
            <input type="hidden" name="lc" value="GB">    
            <input type="hidden" name="item_number" value="<?=$products['id']?>">
            <input type="hidden" name="amount" value="<?=$products['mc_gross']?>">
            <input type="hidden" name="currency_code" value="<?=$products['mc_currency']?>">
            <input type="hidden" name="bn" value="PP-BuyNowBF">
            <input type="hidden" name="custom" value="<?=$user_info['user_id']?>">
            <input type="hidden" name="return" value="<?=DOMAIN_NAME . substr(DIR_WS_SCRIPTS, 1) . "user_buy_credits.php"?>">
      
          <table width="550" border="0" cellpadding="2" cellspacing="4">
            <tr>
              <td width="70"><strong>Product:</strong></td>
              <td width="460"><?=$products['product_name']?></td>
            </tr>
            <tr>
              <td><strong>Price:</strong></td>
              <td><?php
                //currency code to html symbol conversion
                switch( $products['mc_currency'] ) {
                case 'USD':
                  echo '$' ; //no encoding needed for US dollars!
                  break ;
                case 'GBP':
                  echo '&pound;' ;
                  break ;
                case 'EUR':
                  echo '&euro;' ;
                  break ;
                case 'CAD':
                  echo 'CAD$' ; //doesn't really have it's own symbol but use code to differentiate from USD
                  break ;
                case 'JPY':
                  echo '&yen;' ;
                  break ;
                default:
                  echo $mc_currency_default ; //no matching lookup found, output what we were passed
                  break ;
                }
                echo $mc_gross . "&nbsp;" . $products['mc_gross'] ;
                ?></td>
            </tr>
            <tr>
              <td><strong>Quantity:</strong></td>
              <td><input name="quantity" type="text" id="quantity" value="1" size="4" maxlength="2" /> X <?=$products['quantity'] . "&nbsp;credit"?><? if ($products['quantity'] != 1){echo "s";} ?> </td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td><input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" /></td>
            </tr>
          </table>
                  </form>
    	</p>
<?		}	

	}
	else
	{
		echo "<p>Failed to load products.  Please contact your administrator.</p>";	
	}
					
?>
  


<?
// The user may have bought credits, so unsetting this forces the value to be re-loaded
// within user_nav_widget.php
unset($_SESSION['booking_credits']);

include_once("footer.php");

include_once("application_bottom.php");
?>
