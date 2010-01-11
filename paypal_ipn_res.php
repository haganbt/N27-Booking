<?
// Process the returned IPN from Paypal, and credit the users account accordingly

//include the standard application_top
include_once("./includes/application_top.php");

//define paypal classes
class paypal_ipn {
	var $paypal_post_vars;
	var $paypal_response;
	var $timeout;

	var $error_email;

	function paypal_ipn($paypal_post_vars) {
		$this->paypal_post_vars = $paypal_post_vars;
		$this->timeout = 120;
	}

	function send_response() {
	  	//TESTING USE
		//$fp = @fsockopen( "www.sandbox.paypal.com", 80, &$errno, &$errstr, 120 );
		//LIVE USE
		$fp = @fsockopen( "www.paypal.com", 80, &$errno, &$errstr, 120 );

		if (!$fp) {
			$this->error_out("PHP fsockopen() error: " . $errstr , "");
		} else {
			foreach($this->paypal_post_vars AS $key => $value) {
				if (@get_magic_quotes_gpc()) {
					$value = stripslashes($value);
				}
				$values[] = "$key" . "=" . urlencode($value);
			}

			$response = @implode("&", $values);
			$response .= "&cmd=_notify-validate";

			fputs( $fp, "POST /cgi-bin/webscr HTTP/1.0\r\n" );
			fputs( $fp, "Content-type: application/x-www-form-urlencoded\r\n" );
			fputs( $fp, "Content-length: " . strlen($response) . "\r\n\n" );
			fputs( $fp, "$response\n\r" );
			fputs( $fp, "\r\n" );

			$this->send_time = time();
			$this->paypal_response = "";

			// get response from paypal
			while (!feof($fp)) {
				$this->paypal_response .= fgets( $fp, 1024 );

				if ($this->send_time < time() - $this->timeout) {
					$this->error_out("Timed out waiting for a response from PayPal. ($this->timeout seconds)" , "");
				}
			}

			fclose( $fp );

		}
	}

	function is_verified() {
		if( ereg("VERIFIED", $this->paypal_response) )
			return true;
		else
			return false;
	}

	function get_payment_status() {
		return $this->paypal_post_vars['payment_status'];
	}

	function error_out($message, $em_headers)	{

		$date = date("D M j G:i:s T Y", time());
		$message .= "\n\nThe following data was received from PayPal:\n\n";

		@reset($this->paypal_post_vars);
		while( @list($key,$value) = @each($this->paypal_post_vars)) {
			$message .= $key . ':' . " \t$value\n";
		}
		mail($this->error_email, "[$date] paypay_ipn notification", $message, $em_headers);
	}
}


// get the userid out of the first part of the POSTed $custom value from paypal
$n27_userid = trim( $_POST['custom'] ) ;

// email header used for the payment notifications
$em_headers  = "From: noreply@mydomain.com <from_email>\n";
$em_headers .= "Reply-To: from_email\n";
$em_headers .= "Return-Path: from_email\n";
$em_headers .= "Organization: Network27\n";
$em_headers .= "X-Priority: 3\n";


$paypal_ipn = new paypal_ipn( $_POST );


foreach ($paypal_ipn->paypal_post_vars as $key=>$value) {
	if (getType($key)=="string") {
		eval("\$$key=\$value;");
	}
}

$paypal_ipn->send_response();
$paypal_ipn->error_email = $_SESSION['PAYPAL_NOTIFICATION_EMAIL'];


if (!$paypal_ipn->is_verified()) {
	$paypal_ipn->error_out("Bad order (PayPal says it's invalid)" . $paypal_ipn->paypal_response , $em_headers);
	die();
}


switch( $paypal_ipn->get_payment_status() ){
	case 'Pending':

		$pending_reason=$paypal_ipn->paypal_post_vars['pending_reason'];

		if ($pending_reason!="intl") {
			$paypal_ipn->error_out("Pending Payment - $pending_reason", $em_headers);
			break;
		}


	case 'Completed':


		if ($paypal_ipn->paypal_post_vars['txn_type']=="reversal") 
		{
			$reason_code=$paypal_ipn->paypal_post_vars['reason_code'];
			$paypal_ipn->error_out("PayPal reversed an earlier transaction.", $em_headers);
		}
		else
		{

			// Load the product from the DB
			
			// Get the product id returned from paypal
			$productId = strtolower(trim($_POST['item_number']));
			
			if (is_numeric($productId))
			{
				// Load the approprate product
				$qry= "SELECT id, product_name, quantity, mc_gross, mc_currency FROM " . BOOKING_PRODUCT_ITEM . " WHERE id='$productId' LIMIT 1";
				$res=mysql_query ($qry);
				$config=mysql_fetch_array($res);
			} 
			else 
			{
				$paypal_ipn->error_out("Failed to load the product based on the productId returned from Paypal.", $em_headers);	
			}
			
			
			// Verify all details are good
			if ( (strtolower(trim( $_POST['business'] )) == $_SESSION['PAYPAL_BUSINESS_EMAIL']) && (trim($mc_currency)==$config['mc_currency'])  && (trim($mc_gross)-$tax == $quantity*$config['mc_gross']) ) 
			{

				// all the variables are good so record the transaction in the database
				$qry="INSERT INTO booking_paypal_transactions VALUES (0 , '$payer_id', '$n27_userid ', '$payment_date', '$txn_id', '$first_name', '$last_name', '$payer_email', 
				'$payer_status', '$payment_type', '$memo', '$item_name', '$item_number', $quantity, $mc_gross, '$mc_currency', '$address_name', '".nl2br($address_street)."', 
				'$address_city', '$address_state', '$address_zip', '$address_country', '$address_status', '$payer_business_name', '$payment_status', '$pending_reason', '$reason_code', '$txn_type')";


				if (mysql_query($qry)) 
				{

					//get current number of credits

					$qry_current_credits= "SELECT firstname, lastname, booking_credits, email FROM booking_user WHERE user_id='$n27_userid' LIMIT 1";
					$res_current_credits=mysql_query ($qry_current_credits);
					$current_credits=mysql_fetch_array($res_current_credits);
					$credit_total=$current_credits['booking_credits']+(trim($quantity) * trim($config['quantity']));
					$firstName = $current_credits['firstname'] ;
					$lastName = $current_credits['lastname'] ;
					$booking_user_email = $current_credits['email'] ;

					// update user table with credits they have purchased
					$update_credits= "UPDATE booking_user SET booking_credits ='$credit_total' WHERE user_id='$n27_userid' LIMIT 1";
					mysql_query($update_credits);

					// Email the user who has made the booking
$mailMsg = 'Hello ' . $firstName . ',

Your Paypal transaction was successful and has been processed by the booking system.

' . trim($quantity) * trim($config['quantity']) . ' credit(s) have been added to your account.  You now have ' . $credit_total . ' credit(s) in total.


Thank you.



*** This is an automated email, please do not reply.  For any Payapl queries, please contact your booking site administrator. ***' ;

    $mailSubject = $firstName . ' - Booking Credits Purchase Notification' ;
    mail( $booking_user_email, $mailSubject, $mailMsg, null, '-fnoreply@n27.co.uk' ) ;



					// Email the paypal administrator
					$paypal_ipn->error_out("This was a successful transaction. " . trim($quantity) * trim($config['quantity']) . " credit(s) have been added to $firstName $lastName's account.  They now have $credit_total credit(s) in total.", $em_headers);


				// Finally unset the session var -  this forces the value to be re-loaded
				// within user_nav_widget.php
				unset($_SESSION['booking_credits']);


				} else {
					$paypal_ipn->error_out("This was a duplicate transaction", $em_headers);
				}
			} else {
				$paypal_ipn->error_out("Someone attempted a sale using a manipulated URL", $em_headers);
			} // end if ( (strtolower(trim($paypal_ipn->paypal_post_vars['business']))...
		} // end else - $paypal_ipn->paypal_post_vars['txn_type']=="reversal") 

	
	
	
	
	
	break;

	case 'Failed':
		// this will only happen in case of echeck.
		$paypal_ipn->error_out("Failed Payment", $em_headers);
	break;

	case 'Denied':
		// denied payment by us
		$paypal_ipn->error_out("Denied Payment", $em_headers);
	break;

	case 'Refunded':
		// payment refunded by us
		$paypal_ipn->error_out("Refunded Payment", $em_headers);
	break;

	case 'Canceled':
		// reversal cancelled
		// mark the payment as dispute cancelled
		$paypal_ipn->error_out("Cancelled reversal", $em_headers);
	break;

	default:
		// order is not good
		$paypal_ipn->error_out("Unknown Payment Status - " . $paypal_ipn->get_payment_status(), $em_headers);
	break;

}

?>