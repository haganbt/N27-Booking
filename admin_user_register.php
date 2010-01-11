<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Administrator - User Registration";

$page_error_message = '';
$reg_result = false; // default

if ($_POST['register'] != "") { // Register Form Submit

  if ($_POST['username'] == "" || $_POST['passwd'] == "" || $_POST['email'] == "") {  // check forms filled in - required fields
	$page_title = "User Registration Problem";
	$page_error_message = "You have not filled the form out correctly. " .
		"Please make sure to fill out all required fields (username, password and e-mail).";
  }
  elseif (!validate_email($_POST['email'])) {  // email address not valid
	$page_title = "User Registration Problem";
    $page_error_message = "Your email address is not valid. Please try again.";
  }
  elseif (strlen($_POST['passwd']) < 6 || strlen($_POST['passwd']) > 16) {   // check password length
	$page_title = "User Registration Problem";
	$page_error_message = "Your password must be between 6 and 16 characters. Please try again.";
  }
   /*
   elseif (strlen($_POST['address_l1']) < 1 || strlen($_POST['address_l2']) < 1) {   // check address line 1 length
	$page_title = "User Registration Problem";
	$page_error_message = "Please enter an Address line 1 and 2.";
  }
     elseif (strlen($_POST['address_town']) < 1) {   // check Town/City line length
	$page_title = "User Registration Problem";
	$page_error_message = "Please enter a Town/City.";
  }
     elseif (strlen($_POST['address_county']) < 1) {   // check county
	$page_title = "User Registration Problem";
	$page_error_message = "Please enter a County.";
  }
     elseif (strlen($_POST['address_postcode']) < 1) {   // check postcode
	$page_title = "User Registration Problem";
	$page_error_message = "Please enter a Post Code.";
  }
     elseif (strlen($_POST['tel_home']) < 1) {   // check home telephone
	$page_title = "User Registration Problem";
	$page_error_message = "Please enter a home telephone number.";
  }
     elseif (strlen($_POST['tel_mobile']) < 1) {   // check mobile telephone
	$page_title = "User Registration Problem";
	$page_error_message = "Please enter a mobile telephone number.";
  }
	 elseif (strlen($_POST['gender']) < 4) {  // Gender
	$page_title = "User Registration Problem";
	$page_error_message = "Please select a gender.";
  }
*/
  //attempt to register if no error message
  if ($page_error_message == '') {
    //see if we were supplied a dob
    $dob = '0000-00-00' ;
    if ( $_POST['dob_dd'] != '' ) {
        $dob = $_POST['dob_yyyy'] . '-' . $_POST['dob_mm'] . '-' . $_POST['dob_dd'] ;
    }
    //clean up the ismember value so that a non-sensical mem type and credit type are not passed for non-members
    if( $_POST['ismember'] == '0' ) {
      $_POST['member_type'] = "" ;   //the default for no member type selection
      $_POST['credit_type'] = "1" ;  //the default for the 'None (0 days)' credit type id in the db
    }

	$reg_result = register( $_POST['username'], $_POST['passwd'], $_POST['firstname'], $_POST['lastname'], $_POST['groups'], $_POST['email'], $_POST['isadmin'], $_POST['canlogin'], $_POST['address_l1'], $_POST['address_l2'], $_POST['address_town'], $_POST['address_county'], $_POST['address_postcode'], $_POST['tel_home'], $_POST['tel_work'], $_POST['tel_mobile'], $dob, $_POST['ismember'], $_POST['gender'], '0', $_POST['credit_type'] ) ;
	if ($reg_result) {
		// register session variable
		//$valid_user = $_POST['username'];
		//$_SESSION['valid_user'] = $_POST['username'];
		//wrap_session_register("valid_user");
		$page_title = "Registration Successful!";
	} else {
		// register problem: username taken, database error
		$page_title = "User Registration Problem";
		$page_error_message = $reg_result;
	}
  }

} // end of $_POST['register'] != ""
?>
<?
$page_title = "Administrator - User Registration";
$page_title_bar = "Adminstrator - User Registration:";
include_once("header.php");

if ($reg_result) {
	// Registration Successful! Provide link to display wants page.
	echo "Your registration was successful!.<br /><br />";
} else {
    // New Registration or Problem.
?>
<br>
<form method="post" name="new_member_form" action="<?=FILENAME_ADMIN_REGISTER?>">
<table cellpadding="2" cellspacing="0" border="0" align="center">

<tr><td colspan="2" align="center" class="BgcolorDull2"><b>Required Details</b></td></tr>
<tr><td align="right" class="BgcolorDull2">First Name: </td>
<td class="BgcolorDull2"><INPUT TYPE="text" name="firstname" value="<? echo stripslashes($_POST['firstname']) ?>" size="25" maxlength="90"></td></tr>
<tr><td align="right" class="BgcolorDull2">Last Name: </td>
<td class="BgcolorDull2"><INPUT TYPE="text" name="lastname" value="<? echo stripslashes($_POST['lastname']) ?>" size="25" maxlength="90"></td></tr>
<tr>
  <td align="right" class="BgcolorDull2">E-mail Address: </td>
  <td class="BgcolorDull2"><INPUT TYPE="text" name="email" value="<? echo stripslashes($_POST['email']); ?>" size="30" maxlength="90"></td>
</tr>
<tr>
  <td align="right" class="BgcolorDull2">&nbsp;</td>
  <td class="BgcolorDull2">&nbsp;</td>
</tr>
<tr>
  <td align="right" class="BgcolorDull2">Is Administrator: </td>
  <td class="BgcolorDull2"><INPUT TYPE="radio" name="isadmin" value="0"<?= ( $_POST['isadmin'] != '1' ) ? ' checked="true"' : '' ; ?>>
    No &nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="radio" name="isadmin" value="1"<?= ( $_POST['isadmin'] == '1' ) ? ' checked="true"' : '' ; ?>>
    Yes</td>
</tr>
<tr>
  <td align="right" class="BgcolorDull2">Login Enabled: </td>
  <td class="BgcolorDull2"><INPUT TYPE="radio" name="canlogin" value="0"<?= ( $_POST['canlogin'] == '0' ) ? ' checked="true"' : '' ; ?>>
    No &nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="radio" name="canlogin" value="1"<?= ( $_POST['canlogin'] != '0' ) ? ' checked="true"' : '' ; ?>>
    Yes</td>
</tr>

<tr>
  <td align="right" class="BgcolorDull2">Preferred Username:<br />
      <span class="FontBlackSmall"><em>(max 16 chars)</em></span></td>
  <td class="BgcolorDull2"><INPUT TYPE="text" name="username" value="<? echo stripslashes($_POST['username']); ?>" size="16" maxlength="16"></td>
</tr>
<tr>
  <td align="right" class="BgcolorDull2">Password:<br />
      <span class="FontBlackSmall"><em>(between 6 and 16 chars)</em></span></td>
  <td class="BgcolorDull2"><INPUT TYPE="text" name="passwd" value="<? echo stripslashes($_POST['passwd']); ?>" size="16" maxlength="16"></td>
</tr>
<tr>
  <td align="right" class="BgcolorDull2">Is Member: </td>
  <td class="BgcolorDull2">
    <INPUT TYPE="radio" name="ismember" value="0" onclick="toggleMemberEditingOptions('disable');"<?= ( $_POST['ismember'] != '1' ) ? ' checked="true"' : '' ; ?>>
      No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="radio" name="ismember" value="1" onclick="toggleMemberEditingOptions('enable');"<?= ( $_POST['ismember'] == '1' ) ? ' checked="true"' : '' ; ?>>
      Yes</td>
</tr>
<tr id="credit_type_tr" style="display:none;">
  <td align="right" class="BgcolorDull2">Credit Type: </td>
  <td class="BgcolorDull2"><select name="credit_type">
      <?php
      $numCreditTypes = count( $_SESSION['CREDIT_TYPES'] ) ;
      for ( $i = 0 ; $i < $numCreditTypes; $i++ ) {
          echo "\n\t" . '<option value="' . $_SESSION['CREDIT_TYPES'][$i]['credit_type_id'] . '"' ;
          if ( stripslashes( $_POST['credit_type'] ) == $_SESSION['CREDIT_TYPES'][$i]['credit_type_id'] ) {
              echo ' selected="selected"' ;
          }
          echo '>' . $_SESSION['CREDIT_TYPES'][$i]['credit_type_name'] . ' - (' . $_SESSION['CREDIT_TYPES'][$i]['credit_type_booking_days'] . '  days)</option>' ;
      }
      ?>
      </select>
    </td>
</tr>


<tr>
  <td align="right" class="BgcolorDull2">Receive Mailshots: </td>
  <td class="BgcolorDull2"><INPUT TYPE="radio" name="optout" value="1"<?= ( $_POST['optout'] == '1' ) ? ' checked="true"' : '' ; ?>>
    No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <INPUT TYPE="radio" name="optout" value="0"<?= ( $_POST['optout'] != '1' ) ? ' checked="true"' : '' ; ?>>
    Yes</td>
</tr>
<tr>
  <td align="right">&nbsp;</td>
  <td align="right">&nbsp;</td>
</tr>

<tr><td colspan="2" align="center" class="BgcolorNormal"><b>Optional Details</b></td></tr>
<tr>
  <td align="right" class="BgcolorNormal">Address: </td>
  <td class="BgcolorNormal"><INPUT TYPE="text" name="address_l1" value="<? echo stripslashes($_POST['address_l1']) ?>" size="25" maxlength="90"></td>
</tr>
<tr>
  <td align="right" class="BgcolorNormal">&nbsp;</td>
  <td class="BgcolorNormal"><INPUT TYPE="text" name="address_l2" value="<? echo stripslashes($_POST['address_l2']) ?>" size="25" maxlength="90"></td>
</tr>
<tr>
  <td align="right" class="BgcolorNormal">Town/city: </td>
  <td class="BgcolorNormal"><INPUT TYPE="text" name="address_town" value="<? echo stripslashes($_POST['address_town']) ?>" size="25" maxlength="90"></td>
</tr>
<tr>
  <td align="right" class="BgcolorNormal">County: </td>
  <td class="BgcolorNormal"><INPUT TYPE="text" name="address_county" value="<? echo stripslashes($_POST['address_county']) ?>" size="25" maxlength="90"></td>
</tr>
<tr>
  <td align="right" class="BgcolorNormal">Postcode: </td>
  <td class="BgcolorNormal"><INPUT TYPE="text" name="address_postcode" value="<? echo stripslashes($_POST['address_postcode']) ?>" size="10" maxlength="90"></td>
</tr>
<tr>
  <td align="right" class="BgcolorNormal">Tel (home): </td>
  <td class="BgcolorNormal"><INPUT TYPE="text" name="tel_home" value="<? echo stripslashes($_POST['tel_home']) ?>" size="16" maxlength="90"></td>
</tr>
<tr>
  <td align="right" class="BgcolorNormal">Tel (mobile): </td>
  <td class="BgcolorNormal"><INPUT TYPE="text" name="tel_mobile" value="<? echo stripslashes($_POST['tel_mobile']) ?>" size="16" maxlength="90"></td>
</tr>
<tr><td align="right" class="BgcolorNormal">Tel (work): </td>
<td class="BgcolorNormal"><INPUT TYPE="text" name="tel_work" value="<? echo stripslashes($_POST['tel_work']) ?>" size="16" maxlength="90"></td></tr>
<tr><td align="right" class="BgcolorNormal">Date of Birth: </td>
<td class="BgcolorNormal"><select name="dob_dd">
    <option value=""> </option>
    <?php
    for ( $i = 1 ; $i < 32 ; $i++ ) {
        $thisVal = str_pad( $i, 2, '0', STR_PAD_LEFT ) ;
        echo "\n\t" . '<option value="' . $thisVal . '"' ;
        if ($_POST['dob_dd'] == $thisVal) {
            echo ' selected="selected"' ;
        }
        echo '>' . $thisVal . '</option>' ;
    }
    ?>
    </select>
    <select name="dob_mm"><?php
    for ( $i = 1 ; $i < 13 ; $i++ ) {
        $thisVal = str_pad( $i, 2, '0', STR_PAD_LEFT ) ;
        echo "\n\t" . '<option value="' . $thisVal . '"' ;
        if ($_POST['dob_mm'] == $thisVal) {
            echo ' selected="selected"' ;
        }
        echo '>' . month_short_name( $i ) . '</option>' ;
    }
    ?>
    </select>
    <select name="dob_yyyy"><?php
    $thisYear = date( 'Y' ) ;
    $defaultVal = 1975 ;
    if ( isset( $_POST['dob_yyyy'] ) && ( $_POST['dob_yyyy'] != '' ) ) {
        $defaultVal = $_POST['dob_yyyy'] ;
    }
    for ( $i = 1920 ; $i <= $thisYear ; $i++ ) {
        echo "\n\t" . '<option value="' . $i . '"' ;
        if ( $i == $defaultVal ) {
            echo ' selected="selected"' ;
        }
        echo '>' . $i . '</option>' ;
    }
    ?>
    </select>
</td></tr>
<tr>
  <td align="right" class="BgcolorNormal">Gender: </td>
  <td class="BgcolorNormal"><INPUT TYPE="radio" name="gender" value="male"<?= ( $_POST['gender'] == 'male' ) ? ' checked="checked"' : '' ; ?>>
    Male &nbsp;&nbsp;
    <INPUT TYPE="radio" name="gender" value="female"<?= ( $_POST['gender'] == 'female' ) ? ' checked="checked"' : '' ; ?>>
    Female</td>
</tr>
<tr><td align="center" colspan="2"><br />
<input type="hidden" name="groups" value="">
<input type="hidden" name="register" value="yes">
<input type="submit" name="register" value="Submit User Information" class="ButtonStyle">
</td></tr>
</table>
</form>

<script language="javascript">
<!--
function toggleMemberEditingOptions( toggleTo ) {
    var tr2 = document.getElementById( 'credit_type_tr' ) ;

    if( toggleTo == 'disable' ) {
        //hide
        tr2.style.display = 'none' ;
    } else {
        //show
        tr2.style.display = '' ;
    }
}
//-->
</script>
<?
} // end of if $reg_result

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>