<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Payment Gateway";
$page_title_bar = "Payment Gateway";
// Payment Gateway Settings Form Submit
if ($_POST['save_changes'] == 'yes') {

	// Enable or disable the gateway if the session variable is different from the posted value
    if ( ($_POST['gateway_enable'] != $_SESSION['PAYMENT_GATEWAY'] ) ) {

        //update the value in the db
        $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . $_POST['gateway_enable'] . "' WHERE name = 'payment_gateway' LIMIT 1" ;
        $result = wrap_db_query($query);
	
		//update the value in the session
        if ($_POST['gateway_enable'] == '1') {

		    $_SESSION['PAYMENT_GATEWAY'] = true ;

			// Update all user accounts that are not admins and do not currently use BC to use booking credits
			$query2 = "UPDATE " . BOOKING_USER_TABLE . " SET booking_credits ='0' WHERE booking_credits ='Not used' AND is_admin = '0'" ;
	        $result2 = wrap_db_query($query2);

			// Set the default value of booking credits to be 0 so that new registrations (new row inserts) cannot make
			// bookings by default.
			$query3 = "ALTER TABLE " . BOOKING_USER_TABLE . " CHANGE `booking_credits` `booking_credits` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0'" ;
	        $result3 = wrap_db_query($query3);
			$page_info_message = "Payment Gateway enabled.  All user accounts now use Booking Credits as default. This includes new user registrations. <br />The \"Buy Credits\" option will be enabled for all non-administrator users.";

        } else {

			$_SESSION['PAYMENT_GATEWAY'] = false ;

			// Set the default value of booking credits to be "Not used" so that new registrations (new row inserts) will
			// be able to make bookings by default
			$query4 = "ALTER TABLE " . BOOKING_USER_TABLE . " CHANGE `booking_credits` `booking_credits` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Not used'" ;
	        $result4 = wrap_db_query($query4);
			$page_info_message = "Payment Gateway disabled.  All new users will be set NOT to use Booking Credits as default. <br />  This includes public registrations (if enabled).";

		}

	}


	// Save the defaults
	 if ($_POST['gateway_enable'] != '0' && $_SESSION['PAYMENT_GATEWAY'] === true) {
	
		// Update the default product item - id = 1
		if ( isset( $_POST['default_product_name'] ) && ( $_POST['default_product_name'] != '' ) && isset( $_POST['default_product_price'] ) && ( $_POST['default_product_price'] != '' ) && isset( $_POST['currency'] ) && ( $_POST['currency'] != '' ) && isset( $_POST['default_product_quantity'] ) && ( $_POST['default_product_quantity'] != '' ) ) 
		{
			//quantity should always be an int
			$default_product_quantity = (int)trim($_POST['default_product_quantity']);
			
			
			$query = "UPDATE " . BOOKING_PRODUCT_ITEM . " SET product_name = '" . mysql_real_escape_string(trim($_POST['default_product_name'])) . "', quantity = '" . $default_product_quantity . "', mc_currency = '" . mysql_real_escape_string(trim($_POST['currency'])) . "', mc_gross ='" . mysql_real_escape_string(trim($_POST['default_product_price'])) 
			. "' WHERE id ='1' LIMIT 1" ;
			
			$result = wrap_db_query($query);
			
			//update the page vars
			$mc_gross_default = $_POST['default_product_price'] ;
			$product_name_default = trim($_POST['default_product_name']) ;
			$mc_currency_default = trim($_POST['currency']) ;
			$default_product_quantity = $quantity;
		}
	
	
		// Update the paypal business emailif the posted value is different from the session value
		if ( ($_POST['paypal_address'] !=='') && ($_POST['paypal_address'] !== $_SESSION['PAYPAL_BUSINESS_EMAIL']) ) {
			//update the value in the db
			$query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" .
			mysql_real_escape_string(trim($_POST['paypal_address'])) . "' WHERE name = 'paypal_business_email' LIMIT 1 ;" ;
			$result = wrap_db_query($query);
			//update the value in the session
			$_SESSION['PAYPAL_BUSINESS_EMAIL'] = trim($_POST['paypal_address']) ;
		}
	
	
		// Update the notification email address if the posted value is different from the session value
		if ( ($_POST['notification_email'] !=='') && ($_POST['notification_email'] !== $_SESSION['PAYPAL_NOTIFICATION_EMAIL']) ) {
			//update the value in the db
			$query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" .
			trim($_POST['notification_email']) . "' WHERE name = 'paypal_notification_email' LIMIT 1" ;
			$result = wrap_db_query($query);
			//update the value in the session
			$_SESSION['PAYPAL_NOTIFICATION_EMAIL'] = trim($_POST['notification_email']) ;
		}
	
	} // end  	 if ($_POST['gateway_enable'] != '0' && $_SESSION['PAYMENT_GATEWAY'] === true) {



} // end if ($_POST['save_changes'] == 'yes') {


	//  Load the default product - product with id 1 is default product
	if (( $product_name_default == '') || ( $default_product_quantity =='') || ( $mc_gross_default == '') || ( $mc_currency_default == '') &&  ( $_SESSION['PAYMENT_GATEWAY'] === true) ) {
		$result = wrap_db_query( "SELECT product_name, quantity, mc_gross, mc_currency FROM " . BOOKING_PRODUCT_ITEM . " WHERE id = '1' LIMIT 1" ) ;
			if ( $result ) {
				while( $fields = wrap_db_fetch_array( $result ) ) {

					$product_name_default = $fields['product_name']  ;
					$default_product_quantity = $fields['quantity']  ;
					$mc_gross_default = $fields['mc_gross']  ;
					$mc_currency_default = $fields['mc_currency']  ;
					$default_product_quantity = $fields['quantity']  ;
				}
			}	
	}





$show_admin_site_admin_menu = true ;
include_once("header.php");
?>

<script language="javascript">
<!--
function checkCreditPrice( val ) { // force to valid dollar amount
  var str, pos, rnd = 0 ;
  if ( val < .995 ) {
    rnd = 1; //naff fix for old Netscape browsers
  }
  str = escape( ( val * 1.0 ) + 0.005001 + rnd ) ; //float, round, escape
  pos = str.indexOf(".") ;
  if ( pos > 0 ) {
    str = str.substring( rnd, ( pos + 3 ) ) ;
  }

  //check string is a number (ie non 'NaN')
  if( str == 'NaN' ) {
    str = '0.00' ;
  }
  return str ;
}
//-->
</script>
<br />
<form method="post" name="form_payment_gateway" action="<?=FILENAME_ADMIN_PAYMENT_GATEWAY?>" onSubmit="return checkRequiredFields(this);">
  <table width="650" border="0" cellpadding="2" cellspacing="4">
    <tr>
      <td width="191"><strong>Enable Payment Gateway:</strong></td>
      <td width="439"><input type="radio" name="gateway_enable" onclick="togglePaymentOptions('disable');" value="0"<?= ( $_SESSION['PAYMENT_GATEWAY'] === false ) ? ' checked="true"' : '' ; ?> />
        No &nbsp;&nbsp;&nbsp;&nbsp;
        <input type="radio" name="gateway_enable" onclick="togglePaymentOptions('enable');" value="1"<?= ( $_SESSION['PAYMENT_GATEWAY'] !== false ) ? ' checked="true"' : '' ; ?> />
        Yes</td>
    </tr>
    <tr>
      <td colspan="2"><font color="gray"><strong>Note: </strong>By enabling the payment gateway, ALL user accounts will be set to use Booking Credits as default. This includes new user registrations. The &quot;Buy Credits&quot; option will be enabled for all users (not applicable to Admins).</font></td>
    </tr>
  </table>
  <br />
  <table width="648" border="0" cellpadding="2" cellspacing="4" id="booking_payment_table" style="filter: progid:DXImageTransform.Microsoft.Alpha(opacity=<?= ($_SESSION['PAYMENT_GATEWAY']) ? '100' : '50' ; ?>);">
    <tr>
      <td colspan="2"><strong>Paypal Settings:</strong></td>
    </tr>
    <tr>
      <td colspan="2"><a href="<?=FILENAME_ADMIN_PAYPAL_TRANSACTIONS?>">View Paypal Transactions</a>&nbsp;|&nbsp;<a href="<?=FILENAME_ADMIN_MODIFY_PRODUCTS?>">Manage Products and Prices</a>&nbsp;|&nbsp;<a href="<?=FILENAME_ADMIN_MODIFY_GROUP_PRODUCTS?>">Assign Products To Groups</a></td>
    </tr>
    <tr>
      <td colspan="2"></td>
    </tr>
    <tr>
      <td width="172">Default Product Price:</td>
      <td width="456"><input name="default_product_price" type="text" id="default_product_price" size="6" maxlength="6" value="<?= ( isset( $_POST['default_product_price'] ) && ( $_POST['gateway_enable'] == '1' ) ) ? str_replace( '"', "''", stripslashes( $_POST['default_product_price'] ) ) : str_replace( '"', "''", $mc_gross_default ) ; ?>"<?= ( $_SESSION['PAYMENT_GATEWAY'] === false ) ? ' readonly' : '' ; ?> onchange="this.value = checkCreditPrice( this.value );">
        <br /><font color="gray">Include 2 decimal places e.g. 10.00</font></td>
    </tr>
    <tr>
      <td>Default Product Quantity:</td>
      <td>
      	<select name="default_product_quantity" id="default_product_quantity">
                        <?php
                        for ( $i = 1 ; $i < 151 ; $i++ ) {
                            echo '<option value="' . $i . '"' ;
                            if ("$i" == $default_product_quantity  ) {
                                echo ' selected="true"' ;
                            }
                            echo '>' . $i . "</option>\n" ;
                        }
                        ?>
  		</select><br /><font color="gray">Number of credits purchased with each sale of the default product.</font>
      </td>
    </tr>
    <tr>
      <td>Default Product Name:</td>
      <td><input name="default_product_name" type="text" id="default_product_name" size="40" maxlength="128" value="<?= ( isset( $_POST['default_product_name'] ) && ( $_POST['gateway_enable'] == '1' ) ) ? str_replace( '"', "''", stripslashes( $_POST['default_product_name'] ) ) : str_replace( '"', "''", $product_name_default ) ; ?>"<?= ( $_SESSION['PAYMENT_GATEWAY'] === false ) ? ' readonly' : '' ; ?>>
        <br />
        <font color="gray">The name of the default product e.g. &quot;Tennis Booking Credit&quot;.</font></td>
    </tr>
    <tr>
      <td>Currency:</td>
      <td><select name="currency" id="currency">
        <?php

          //if no default exists, HTML will use the 1st one in the list as the initally selected default
          $currOpts = array( 'USD', 'GBP', 'EUR', 'CAD', 'JPY' ) ;
          $numCurrOpts = count( $currOpts ) ;
          for ( $c = 0 ; $c < $numCurrOpts ; $c++ ) {
            echo '<option value="' . $currOpts[$c] . '"' ;
            if( $currOpts[$c] == $mc_currency_default ) {
              echo ' selected="true"' ;
            }
            echo '>' . $currOpts[$c] . '</option>' . "\n" ;
  		    }
        ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Paypal Business Email:</td>
      <td><input name="paypal_address" type="text" id="paypal_address" size="40" maxlength="50" value="<?= ( isset( $_POST['paypal_address'] ) && ( $_POST['gateway_enable'] == '1' ) ) ? str_replace( '"', "''", stripslashes( $_POST['paypal_address'] ) ) : str_replace( '"', "''", $_SESSION['PAYPAL_BUSINESS_EMAIL'] ) ; ?>"<?= ( $_SESSION['PAYMENT_GATEWAY'] === false ) ? ' readonly' : '' ; ?>>
        <font color="gray"><br />
        The Paypal account email address.</font></td>
    </tr>
    <tr>
      <td> Notification Email:</td>
      <td><input name="notification_email" type="text" id="notification_email" size="40" maxlength="50" value="<?= ( isset( $_POST['notification_email'] ) && ( $_POST['gateway_enable'] == '1' ) ) ? str_replace( '"', "''", stripslashes( $_POST['notification_email'] ) ) : str_replace( '"', "''", $_SESSION['PAYPAL_NOTIFICATION_EMAIL'] ) ; ?>"<?= ( $_SESSION['PAYMENT_GATEWAY'] === false ) ? ' readonly' : '' ; ?>>
        <br />
        <font color="gray">The email address where payment notifications will be sent.</font></td>
    </tr>
    <tr>
      <td colspan="2"></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="hidden" name="save_changes" value="yes"></td>
    </tr>
  </table>
    <table width="650" border="0" cellpadding="2" cellspacing="4">
        <tr>
          <td width="630" align="center"><input type="submit" name="Save" value="Save Settings" class="ButtonStyle" /></td>
        </tr>
      </table>
</form>
<script language="javascript">
<!--
function togglePaymentOptions( toggleTo ) {
    var paymentTable = document.getElementById( 'booking_payment_table' ) ;

    if( toggleTo == 'disable' ) {
        if (document.all) {
            paymentTable.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 50 ;
        } else {
            paymentTable.style.MozOpacity=0.5;
        }
        document.forms.form_payment_gateway.default_product_price.readOnly=true;
		document.forms.form_payment_gateway.default_product_name.readOnly=true;		
		document.forms.form_payment_gateway.currency.readOnly=true;
		document.forms.form_payment_gateway.paypal_address.readOnly=true;
		document.forms.form_payment_gateway.notification_email.readOnly=true;
    } else {
        if (document.all) {
            paymentTable.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100 ;
        } else {
            paymentTable.style.MozOpacity=1.0;
        }
        document.forms.form_payment_gateway.default_product_price.readOnly=false;
		document.forms.form_payment_gateway.default_product_name.readOnly=false;		
		document.forms.form_payment_gateway.currency.readOnly=false;
		document.forms.form_payment_gateway.paypal_address.readOnly=false;
		document.forms.form_payment_gateway.notification_email.readOnly=false;

    }
}

function checkRequiredFields(input) {
    //see if the user even wants to send confirmation e-mails
    var checkOtherFields = '0' ;
    for( var j = 0 ; j < input.gateway_enable.length ; j++ ) {
        if( input.gateway_enable[j].checked ) {
            checkOtherFields = input.gateway_enable[j].value ;
            break ; //only one radio option can be selected at once there is so no point looking at any remaining options
        }
    }

    if ( checkOtherFields == '0' ) {
        return confirm( 'Any ammendments made to the Paypal settings below will not be checked\nor saved since you have selected not to enable the Payment gateway.\n\nAre you sure you want to disable the Payment gateway?' ) ;
    } else {
        var requiredFields = new Array("default_product_price",
									   "default_product_name",
                                       "paypal_address",
                                       "notification_email");

        var fieldNames = new Array("Default Product Price",
								   "Default Product name",
                                   "Paypal Business Email Address",
                                   "Notification Email Address");

        var fieldCheck = true;
        var fieldsNeeded = "\nA value must be entered in the following field(s):\n\n\t";

        for(var fieldNum=0; fieldNum < requiredFields.length; fieldNum++) {
            if ((input.elements[requiredFields[fieldNum]].value == "") || (input.elements[requiredFields[fieldNum]].value == " ")) {

                fieldsNeeded += fieldNames[fieldNum] + "\n\t";
                fieldCheck = false;
            }
        }
        if (fieldCheck == true) {
            //check the e-mail address
            emlCheckExpr = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/ ;
            if ( !(emlCheckExpr.test(input.paypal_address.value) ) ) {
                alert ( "The Paypal Business email address supplied is invalid.\nPlease check the e-mail address entered." ) ;
                input.paypal_address.focus();
                return false ;
            }
			if ( !(emlCheckExpr.test(input.notification_email.value) ) ) {
                alert ( "The Notification email address supplied is invalid.\nPlease check the e-mail address entered." ) ;
                input.notification_email.focus();
                return false ;
            }

        } else {
            alert(fieldsNeeded);
            return false;
        }
    }
}
</script>
