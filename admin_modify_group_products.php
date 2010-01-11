<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Assign Products To User Groups";
$page_title_bar = "Assign Products To User Groups";

//check for form submission
if ( $_POST['submitted'] == 'submitted' ) {

  //make sure a valid group_id value was supplied
  if ( trim( $_POST['group_id'] ) != '' ) {

    //delete all existing $_POST['group_id'] entries in db
    $sql = 'DELETE FROM ' . BOOKING_PRODUCT_GROUPS . ' WHERE group_id="' . mysql_real_escape_string ( $_POST['group_id'] ) . '"' ;
    $res = wrap_db_query( $sql ) ;

    //now insert all our new entries... foreach on $_POST['product_ids']
    if ( ( is_array( $_POST['product_ids'] ) ) && ( sizeof( $_POST['product_ids'] ) > 0 ) ) {
      foreach( $_POST['product_ids'] as $product_id ) {
        $sql = 'INSERT INTO ' . BOOKING_PRODUCT_GROUPS . ' ( product_id , group_id , created ) VALUES ( "' . mysql_real_escape_string( $product_id ) . '" , "' . mysql_real_escape_string ( $_POST['group_id'] ) . '" , NOW() ) ' ;
        $res = wrap_db_query( $sql ) ;
      }
    }

    //note changes were made okay
    $page_info_message = 'Changes to product/group assignment saved successfully' ;
  }
}

//get users in any specified group id
if ( isset( $_REQUEST['group_id'] ) && ( trim( $_REQUEST['group_id'] ) > 0 ) ) {
  $sql = 'SELECT * FROM ' . BOOKING_PRODUCT_GROUPS . ' WHERE group_id=' . $_REQUEST['group_id'] ;
  $res = wrap_db_query( $sql ) ;
  if ( $res && ( wrap_db_num_rows( $res ) > 0 ) ) {
    while ( $row = wrap_db_fetch_array( $res ) ) {
      $thisGroupsProducts[] = $row['product_id'] ;
    }
  }
}

//get all our current groups
$sql = 'SELECT group_id, group_name FROM ' . BOOKING_GROUPS_TABLE . ' ORDER BY group_name ASC' ;
$res = wrap_db_query( $sql ) ;
if ( $res ) {
  while ( $row = wrap_db_fetch_array( $res ) ) {
    $groups[] = $row ;
  }
}

//get all available products
$sql = 'SELECT id , product_name, mc_currency, mc_gross FROM ' . BOOKING_PRODUCT_ITEM . ' WHERE id > 1 ORDER BY product_name ASC';
$res = wrap_db_query( $sql ) ;
if ( $res ) {
  while ( $row = wrap_db_fetch_array( $res ) ) {
    $products[] = $row ;
  }
}

$show_admin_site_admin_menu = true ;
include_once("header.php");

// Only allow access to this page if the Payment gateway is switched on
if ($_SESSION['PAYMENT_GATEWAY'] == '0' ) {

	echo "<p>Payment gateway must be enabled to assign products to user groups.&nbsp;<a href=" . FILENAME_ADMIN_PAYMENT_GATEWAY. ">Payment Gateway</a></p>";
	exit;
}

?>
<br />
Select a user group from the list below, then place a tick next to the product to assign that product to the group.
<p>All members of that group will then have the option to buy that product.  If a group does not have a product assigned,<br />
or a user is not a member of any group, the <a href="<?=FILENAME_ADMIN_PAYMENT_GATEWAY?>">default product</a> will be used.</p>


<form name="form1" method="post" action="<?= FILENAME_ADMIN_MODIFY_GROUP_PRODUCTS ;?>">
<b>Assign Products to a User Group</b>
<br>
<?php

//output all our existing groups with links to edit / delete
$numGroups = sizeof( $groups ) ;
$numProducts = sizeof( $products ) ;
if ( ( is_array( $groups ) ) && ( $numGroups > 0 ) ) {

  ?><br />
  <br />
  <table border="0" cellpadding="0" cellspacing="2">
    <tr>
      <th width="100">Group :</th>
      <td>
        <select name="group_id" onchange="if( this.value != '' ) { document.location.href=('<?= FILENAME_ADMIN_MODIFY_GROUP_PRODUCTS ;?>?group_id=' + this.value); } else { document.location.href=('<?= FILENAME_ADMIN_MODIFY_GROUP_PRODUCTS ;?>'); }">
          <option value="">--- Select Group ---</option>
      <?php

      for ( $i = 0 ; $i < $numGroups ; $i++ ) {

        $class = 'BgcolorNormal' ;
        if ( $i % 2 == 1 ) {

          $class = 'BgcolorBody' ;
        }
        ?><option value="<?= stripslashes( $groups[$i]['group_id'] ) ;?>" class="<?= $class ; ?>"<?php
        if ( $_REQUEST['group_id'] == $groups[$i]['group_id'] ) {
          echo ' selected="selected"' ;
        }
        ?>><?= stripslashes( $groups[$i]['group_name'] ) ;?></option><?php
      }
      ?></select></td>
    </tr>
    <?php
    if ( isset( $_REQUEST['group_id'] ) && ( trim( $_REQUEST['group_id'] ) != '' ) ) {
      ?>
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <th valign="top">Products :</th>
      <td><div class="scrollingDivBox"><?php

      for ( $i = 0 ; $i < $numProducts ; $i++ ) {

        ?><input type="checkbox" name="product_ids[]" value="<?= $products[$i]['id'] ;?>"<?php
        if ( is_array( $thisGroupsProducts ) ) {
          if ( in_array( $products[$i]['id'] , $thisGroupsProducts ) ) {
            echo ' checked="checked"' ;
          }
        }
        ?> /><?= stripslashes( $products[$i]['product_name'] ) . " (" . stripslashes( $products[$i]['mc_gross'] ). " " . stripslashes( $products[$i]['mc_currency'] ) . ")" ?><br /><?php
      }
      ?></div></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><a href="<?=FILENAME_ADMIN_MODIFY_PRODUCTS?>">Manage Products and Prices</a></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><br><input type="submit" name="submit" value="Save Changes" class="ButtonStyle" /></td>
    </tr>
    <?php
    }
    ?>
  </table><?php

} else {

  ?>There are currently no groups set...<br />
  Please create a user group <a href="<?= FILENAME_ADMIN_MODIFY_GROUPS ;?>">here</a> first.<br /><?php
}

//output a hidden field containing a tracker to let us know when the form has been submitted
?>
<input type="hidden" name="previousProductsThisGroup" value="<?= $numProducts ; ?>">
<input type="hidden" name="submitted" value="submitted">
</form>
<br>
<br>
<?php
include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>