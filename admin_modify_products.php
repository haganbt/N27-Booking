<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Modify Products";
$page_title_bar = "Modify Products";

//value of action
$actionValue = 'add' ;

//check for form submission
if ( $_POST['submitted'] == 'submitted' ) {

  //make sure the new name is not blank and the price is numeric
  if ( trim( $_POST['product_name'] ) != '' && trim( $_POST['product_price']) != '' && is_numeric( $_POST['product_quantity']) && is_numeric( $_POST['product_price'])) {

    //check our action
    switch( $_POST['action'] ) {

      case 'add' :
			$sql="INSERT INTO " . BOOKING_PRODUCT_ITEM . " VALUES (NULL , '" . mysql_real_escape_string( $_POST['product_name'] ) . "', '" . trim($_POST['product_quantity']) . "' , '" . mysql_real_escape_string( $_POST['product_price'] ) . "' , '" . mysql_real_escape_string( $_POST['product_currency'] ) . "')";

		$theMsg = 'New product added successfully' ;
        break;

      case 'edit' :
        $sql = 'UPDATE ' . BOOKING_PRODUCT_ITEM . ' SET product_name="' . mysql_real_escape_string( trim($_POST['product_name'] ) ) . '", quantity="' . trim($_POST['product_quantity']) . '" , mc_gross="' . trim( $_POST['product_price'] ) . '", mc_currency="' . trim( $_POST['product_currency'] ) . '" WHERE id=' . $_POST['products_id'] . ' LIMIT 1' ;
        $theMsg = 'Changes to product saved successfully' ;
        break;

      default:
        break;
    }
    $res = wrap_db_query( $sql ) ;

    if ( ( $res != false ) && ( $theMsg != '' ) ) {
      //note changes were made okay
      $page_info_message = $theMsg ;
    }

  } else {
    //the user tried to add a product with no name or to change the name to a blank string
    $page_error_message = 'The product name and price cannot be blank.' ;
  }
}

//check for link click - product id 1 is default product
if ( ( isset( $_GET['action'] ) && ( $_GET['action'] != '' ) ) && ( isset( $_GET['product_id'] ) && ( trim( $_GET['product_id'] ) != '' &&  trim( $_GET['product_id'] ) != '1' ) ) ) {

  //check our action
  switch( $_GET['action'] ) {

    case 'edit' :
      $actionValue = 'edit' ;
      $sql = 'SELECT * FROM ' . BOOKING_PRODUCT_ITEM . ' WHERE id=' . trim($_GET['product_id']) . ' LIMIT 1' ;
      $res = wrap_db_query( $sql ) ;
      if ( $res ) {

        while ( $row = wrap_db_fetch_array( $res ) ) {

          $chosenProduct = $row ;
        }
      }
      $page_info_message = 'Product saved successfully.' ;
      break;

    case 'delete' :
      //delete the product
      $sql = 'DELETE FROM ' . BOOKING_PRODUCT_ITEM . ' WHERE id=' . mysql_real_escape_string ($_GET['product_id']) . ' LIMIT 1' ;
      if ( $res = wrap_db_query( $sql ) ) {
        //clearup details of the product from the booking_product_groups table
		$sql = 'DELETE FROM ' . BOOKING_PRODUCT_GROUPS . ' WHERE product_id="' . mysql_real_escape_string ( $_GET['product_id'] ) . '"' ;
		$res = wrap_db_query( $sql ) ;
      }

      $page_info_message = 'Product deleted successfully.' ;
      break;

    default:
      break;
  }
}

$show_admin_site_admin_menu = true ;
include_once("header.php");
// Only allow access to this page if the Payment gateway is switched on
if ($_SESSION['PAYMENT_GATEWAY'] == '0' ) {

	echo "<p>Payment gateway must be enabled to modify products.&nbsp;<a href=" . FILENAME_ADMIN_PAYMENT_GATEWAY. ">Payment Gateway</a></p>";
	exit;
}
?>

<p>Use the controls below to add/edit products and prices.</p>
<p><a href="<?=FILENAME_ADMIN_MODIFY_GROUP_PRODUCTS?>">Assign Products To Groups</a>&nbsp;|&nbsp;<a href="<?=FILENAME_ADMIN_MODIFY_USER_GROUPS?>">Update Group Membership</a></p>

<form name="form1" method="post" action="<?= FILENAME_ADMIN_MODIFY_PRODUCTS ;?>">
  <p>
    <input type="hidden" name="action" value="<?= $actionValue ;?>" />
    <input type="hidden" name="products_id" value="<?= $chosenProduct['id'] ;?>" />
    <b>
    <?= ucwords( $actionValue ) ;?>
    Product</b></p>
  <table border="0" cellpadding="4" cellspacing="2">
    <tr>
      <th width="150" class="BgcolorDull2">Name</th>
      <th width="75" class="BgcolorDull2">Price</th>
      <th width="75" class="BgcolorDull2">Quantity</th>
      <th width="75" class="BgcolorDull2">Currency</th>
    </tr>
    <tr>
      <th width="150" align="left"><input type="text" name="product_name" value="<?= stripslashes( $chosenProduct['product_name'] ) ;?>" /></th>
      <th width="75" align="left"><input name="product_price" type="text" value="<?= stripslashes( $chosenProduct['mc_gross'] ) ;?>" size="6" maxlength="6" onchange="this.value = checkCreditPrice( this.value );" /></th>
      <th width="75" align="left"><select name="product_quantity" id="product_quantity">
                        <?php
                        for ( $i = 1 ; $i < 151 ; $i++ ) {
                            echo '<option value="' . $i . '"' ;
                            if ("$i" == stripslashes( $chosenProduct['quantity'] )  ) {
                                echo ' selected="true"' ;
                            }
                            echo '>' . $i . "</option>\n" ;
                        }
                        ?>
	  </select></th>
      <th width="75" align="left"><select name="product_currency" id="product_currency">
          <?php

          //if no default exists, HTML will use the 1st one in the list as the initally selected default
          $currOpts = array( 'GBP', 'USD', 'EUR', 'CAD', 'JPY' ) ;
          $numCurrOpts = count( $currOpts ) ;
          for ( $c = 0 ; $c < $numCurrOpts ; $c++ ) {
            echo '<option value="' . $currOpts[$c] . '"' ;
            if( $currOpts[$c] == stripslashes( $chosenProduct['mc_currency'] ) ) {
              echo ' selected="true"' ;
            }
            echo '>' . $currOpts[$c] . '</option>' . "\n" ;
  		    }
        ?>
        </select></th>
    </tr>
  </table>
  <p>
    <input type="submit" name="submit" value="Save" class="ButtonStyle" />
  </p>
  <p><br />
    <?php
//output a hidden field containing a tracker to let us know when the form has been submitted
?>
    <input type="hidden" name="submitted" value="submitted">
  </p>
</form>
<b>Current Products</b>
<table width="752" border="0" cellpadding="4" cellspacing="2">
  <tr>
    <th class="BgcolorDull2" width="258">Name</th>
    <th width="100" class="BgcolorDull2">Price</th>
    <th width="100" class="BgcolorDull2">Quantity</th>
    <th width="100" class="BgcolorDull2">Currency</th>
    <th width="142" class="BgcolorDull2">Control</th>
  </tr>
  <?php
	//get all our current products except for the default product
	$result = wrap_db_query("SELECT * FROM " . BOOKING_PRODUCT_ITEM . " where id not in('1') ORDER BY product_name ASC");
	if ($result) {
		$i = 0 ;
		while ( $fields = wrap_db_fetch_array($result) ) {

			$i++;
            $class = 'BgcolorNormal' ;
            if ( $i % 2 == 1 ) 
			{
              $class = 'BgcolorBody' ;
			}?>
  <tr>
    <td width="258" align="left" class="<?= $class ;?>"><?= stripslashes( $fields['product_name'] ) ; ?></td>
    <td width="100" align="center" class="<?= $class ;?>"><?= $fields['mc_gross'] ; ?></td>
    <td width="100" align="center" class="<?= $class ;?>"><?= $fields['quantity'] ; ?></td>
    <td width="100" align="center" class="<?= $class ;?>"><?= $fields['mc_currency'] ; ?></td>
    <td width="142" align="center" class="<?= $class ;?>"><a href="<?= FILENAME_ADMIN_MODIFY_PRODUCTS ;?>?action=edit&product_id=<?= $fields['id'] ;?>">edit</a> | <a href="<?= FILENAME_ADMIN_MODIFY_PRODUCTS ;?>?action=delete&product_id=<?= $fields['id'] ;?>" 
                onclick="return confirm('Are you sure you wish to delete this product?\n\nThis action is permanent and cannot be undone.\n');">delete</a></td>
  </tr>
  <? }   ?>
</table>
<?php
} else {
  ?>
<b>No products currently exist.</b>
<?php
}
?>
<script language="javascript">
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
</script>
<?
include_once("footer.php");
include_once("application_bottom.php"); ?>
