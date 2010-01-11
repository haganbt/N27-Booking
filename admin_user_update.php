<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Administrator - User Registration";

$page_error_message = '';
$page_success_message = '';
$reg_result = false; // default
$change_password = false ; // default

if ($_POST['register'] != "") { // Register Form Submit

  if ($_POST['register'] == 'Remove User') {

    //delete user and remove all associated bookings
    if ( delete_user( $_POST['user_select'] ) ) {
        $page_success_message = 'User removed.' ;
    } else {
        $page_title = "User Removal Problem";
        $page_error_message = 'The user could not be removed at this time. Please try again later.' ;
    }

  } else {

      //update the details for a user

      if ($_POST['username'] == "" || $_POST['email'] == "") {  // check forms filled in - required fields
    	$page_title = "User Registration Problem";
    	$page_error_message = "You have not filled the form out correctly. " .
    		"Please make sure to fill out all required fields (username and e-mail).";
      }
      elseif (!validate_email($_POST['email'])) {  // email address not valid
    	$page_title = "User Registration Problem";
        $page_error_message = "The email address is not valid. Please try again.";
      }

      if ( $_POST['passwd'] != '' ) {
          if (strlen($_POST['passwd']) < 6 || strlen($_POST['passwd']) > 16) {   // check password length
        	$page_title = "User Registration Problem";
        	$page_error_message = "The password must be between 6 and 16 characters. Please try again.";
          } else {
            //make a note to update the password
            $change_password = true ;
          }
      }

      if ($page_error_message == '') {  // attempt to register if no error message
        //see if we were supplied a dob
        $dob = '0000-00-00' ;
        if ( $_POST['dob_dd'] != '' ) {
            $dob = $_POST['dob_yyyy'] . '-' . $_POST['dob_mm'] . '-' . $_POST['dob_dd'] ;
        }
        //clean up the ismember value so that a non-sensical mem type and credit type are not passed for non-members
        if( $_POST['ismember'] == '0' ) {
          $_POST['credit_type'] = "1" ;  //the default for the 'None (0 days)' credit type id in the db
        }

    	$reg_result = admin_update_of_user_information($_POST['user_select'], $_POST['username'], $_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['can_login'], $_POST['address_l1'], $_POST['address_l2'], $_POST['address_town'], $_POST['address_county'], $_POST['address_postcode'], $_POST['tel_home'], $_POST['tel_work'], $_POST['tel_mobile'], $dob, $_POST['ismember'], $_POST['gender'], $_POST['optout'], $_POST['credit_type']) ;
    //echo "2" ;
    	if ($reg_result) {
    //echo "3" ;
    		$page_success_message = "Update successful!";
    	} else {
    //echo "4" ;
    		// register problem: username taken, database error
    		$page_title = "User update problem";
    		$page_error_message = 'Unable to update user information. Please ensure that the username is unique and then try again.' ;
    	}

    	if ( $change_password == true ) {
    	    if ( change_password($_POST['username'], '', $_POST['passwd'], $_POST['email'], true) ) {
    	        $page_success_message .= '<br><br>Password updated sucessfully.' ;
    	    } else {
    	        $page_success_message .= '<br><br>Password update failed, please try again.' ;
    	    }
    	}
      }
  }
} // end of $_POST['register'] != ""
?>
<?
$page_title_bar = "User Info Update:";
include_once("header.php");

//if ($reg_result) {
//	// Registration Successful! Provide link to display wants page.
//	echo "Your registration was successful!.<br /><br />";
//} else {
//    // New Registration or Problem.
?>
<table border="0" cellspacing="10" cellpadding="0">
    <tr>
        <td><b>Users</b></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <form name="form1" method="post" action="<?=FILENAME_ADMIN_UPDATE?>">
        <td valign="top"><select name="user_select" size="15" onchange="document.form1.submit()">
            <?php
                //get a list of users
                $result = wrap_db_query("SELECT user_id, username, firstname, lastname, email FROM " . BOOKING_USER_TABLE . " ORDER BY lastname, firstname, username");
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
        <td width="10">&nbsp;</td>
        <td valign="top">
            <?php
            if ($_POST['user_select'] != '') {
                //check that we have not just made a successful update
                if ($page_success_message == '') {
                    $result = wrap_db_query("SELECT * FROM " . BOOKING_USER_TABLE . " WHERE user_id = '" . $_POST['user_select'] . "' LIMIT 0,1");
                    if ($result) {
                        if ($fields = wrap_db_fetch_array($result)) {
                            ?>
                            <form method="post" action="<?=FILENAME_ADMIN_UPDATE?>">

                            <table cellpadding="2" cellspacing="0" border="0">
                            <tr><td colspan="2" align="center" class="BgcolorDull2"><b>Required Details</b></td></tr>
                            <tr><td align="right" class="BgcolorDull2" width="32%">Username:<br /><span class="FontBlackSmall"><em>(max 16 chars)</em></span></td>
                            <td class="BgcolorDull2"><INPUT TYPE="text" name="username" value="<?= (($_POST['username'])) ? stripslashes($_POST['username']) : $fields['username'] ; ?>" size="16" maxlength="16"></td></tr>
                            <tr><td align="right" class="BgcolorDull2">First Name: </td>
                            <td class="BgcolorDull2"><INPUT TYPE="text" name="firstname" value="<?= (($_POST['firstname'])) ? stripslashes($_POST['firstname']) : $fields['firstname'] ; ?>" size="25" maxlength="90"></td></tr>
                            <tr><td align="right" class="BgcolorDull2">Last Name: </td>
                            <td class="BgcolorDull2"><INPUT TYPE="text" name="lastname" value="<?= (($_POST['lastname'])) ? stripslashes($_POST['lastname']) : $fields['lastname'] ; ?>" size="25" maxlength="90"></td></tr>
                            <tr><td align="right" class="BgcolorDull2">E-mail Address: <br /><span class="FontBlackSmall"><em>(required)</em></span> </td>
                            <td class="BgcolorDull2"><INPUT TYPE="text" name="email" value="<?= (($_POST['email'])) ? stripslashes($_POST['email']) : $fields['email'] ; ?>" size="30" maxlength="90"></td></tr>
                            <tr><td align="right" class="BgcolorDull2">Login Enabled: </td>
                            <td class="BgcolorDull2"><INPUT TYPE="radio" name="can_login" value="0"<?= ( (isset($_POST['can_login']) && ($_POST['can_login'] == "0")) || (!isset($_POST['can_login']) && ($fields['login_enabled'] == "0")) ) ? ' checked="checked"' : '' ; ?>> No
                                &nbsp;&nbsp;&nbsp;
                                <INPUT TYPE="radio" name="can_login" value="1"<?= ( (isset($_POST['can_login']) && ($_POST['can_login'] == "1")) || (!isset($_POST['can_login']) && ($fields['login_enabled'] == "1")) ) ? ' checked="checked"' : '' ; ?>> Yes</td></tr>

                            <tr><td align="center" colspan="2"><br /></td></tr>
                            <tr><td colspan="2" align="center" class="BgcolorDull"><b>Change Password</b></td></tr>
                            <tr><td align="right" class="BgcolorDull">Password:<br /><span class="FontBlackSmall"><em>(between 6 and 16 chars)</em></span></td>
                            <td class="BgcolorDull"><INPUT TYPE="text" name="passwd" value="<? echo stripslashes($_POST['passwd']); ?>" size="16" maxlength="16"> * <span class="FontBlackSmall">leave blank to keep current password</span></td></tr>
                            <tr><td align="center" colspan="2"><br /></td></tr>

                            <tr><td colspan="2" align="center" class="BgcolorNormal"><b>Optional Details</b></td></tr>
                            <tr><td align="right" class="BgcolorNormal">Address: </td>
                            <td class="BgcolorNormal"><INPUT TYPE="text" name="address_l1" value="<?= (($_POST['address_l1'])) ? stripslashes($_POST['address_l1']) : $fields['address_l1'] ; ?>" size="25" maxlength="90"></td></tr>
                            <tr><td align="right" class="BgcolorNormal">&nbsp;</td>
                            <td class="BgcolorNormal"><INPUT TYPE="text" name="address_l2" value="<?= (($_POST['address_l2'])) ? stripslashes($_POST['address_l2']) : $fields['address_l2'] ; ?>" size="25" maxlength="90"></td></tr>
                            <tr><td align="right" class="BgcolorNormal">Town/city: </td>
                            <td class="BgcolorNormal"><INPUT TYPE="text" name="address_town" value="<?= (($_POST['address_town'])) ? stripslashes($_POST['address_town']) : $fields['address_town'] ; ?>" size="25" maxlength="90"></td></tr>
                            <tr><td align="right" class="BgcolorNormal">County: </td>
                            <td class="BgcolorNormal"><INPUT TYPE="text" name="address_county" value="<?= (($_POST['address_county'])) ? stripslashes($_POST['address_county']) : $fields['address_county'] ; ?>" size="25" maxlength="90"></td></tr>
                            <tr><td align="right" class="BgcolorNormal">Postcode: </td>
                            <td class="BgcolorNormal"><INPUT TYPE="text" name="address_postcode" value="<?= (($_POST['address_postcode'])) ? stripslashes($_POST['address_postcode']) : $fields['address_postcode'] ; ?>" size="10" maxlength="90"></td></tr>
                            <tr><td align="right" class="BgcolorNormal">Tel (home): </td>
                            <td class="BgcolorNormal"><INPUT TYPE="text" name="tel_home" value="<?= (($_POST['tel_home'])) ? stripslashes($_POST['tel_home']) : $fields['phone_home'] ; ?>" size="16" maxlength="90"></td></tr>
                            <tr><td align="right" class="BgcolorNormal">Tel (work): </td>
                            <td class="BgcolorNormal"><INPUT TYPE="text" name="tel_work" value="<?= (($_POST['tel_work'])) ? stripslashes($_POST['tel_work']) : $fields['phone_work'] ; ?>" size="16" maxlength="90"></td></tr>
                            <tr><td align="right" class="BgcolorNormal">Tel (mobile): </td>
                            <td class="BgcolorNormal"><INPUT TYPE="text" name="tel_mobile" value="<?= (($_POST['tel_mobile'])) ? stripslashes($_POST['tel_mobile']) : $fields['phone_mobile'] ; ?>" size="16" maxlength="90"></td></tr>
                            <tr><td align="right" class="BgcolorNormal">Date of Birth: </td>
                            <td class="BgcolorNormal"><select name="dob_dd">
                                <option value=""> </option>
                                <?php
                                list( $dob_yyyy, $dob_mm, $dob_dd ) = explode( '-', $fields['dob'] ) ;

                                $defaultVal = $dob_dd ;
                                if ( isset( $_POST['dob_dd'] ) ) {
                                    $defaultVal = $_POST['dob_dd'] ;
                                }
                                for ( $i = 1 ; $i < 32 ; $i++ ) {
                                    $thisVal = str_pad( $i, 2, '0', STR_PAD_LEFT ) ;
                                    echo "\n\t" . '<option value="' . $thisVal . '"' ;
                                    if ( $thisVal == $defaultVal ) {
                                        echo ' selected="selected"' ;
                                    }
                                    echo '>' . $thisVal . '</option>' ;
                                }
                                ?>
                                </select>
                                <select name="dob_mm"><?php
                                $defaultVal = $dob_mm ;
                                if ( isset( $_POST['dob_mm'] ) ) {
                                    $defaultVal = $_POST['dob_mm'] ;
                                }
                                for ( $i = 1 ; $i < 13 ; $i++ ) {
                                    $thisVal = str_pad( $i, 2, '0', STR_PAD_LEFT ) ;
                                    echo "\n\t" . '<option value="' . $thisVal . '"' ;
                                    if ( $thisVal == $defaultVal ) {
                                        echo ' selected="selected"' ;
                                    }
                                    echo '>' . month_short_name( $i ) . '</option>' ;
                                }
                                ?>
                                </select>
                                <select name="dob_yyyy"><?php
                                $defaultVal = 1975 ;
                                if ( $dob_yyyy != "0000" ) {
                                    $defaultVal = $dob_yyyy ;
                                }
                                if ( isset( $_POST['dob_yyyy'] ) ) {
                                    $defaultVal = $_POST['dob_yyyy'] ;
                                }
                                $thisYear = date( 'Y' ) ;
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

                            <tr><td align="right" class="BgcolorNormal">Is Member: </td>
                            <td class="BgcolorNormal">
                              <INPUT TYPE="radio" name="ismember" value="0" onclick="toggleMemberEditingOptions('disable');"<?= ( (isset($_POST['ismember']) && ($_POST['ismember'] == "0")) || (!isset($_POST['ismember']) && ($fields['is_member'] == "0")) ) ? ' checked="checked"' : '' ; ?>>
                                No
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              <INPUT TYPE="radio" name="ismember" value="1" onclick="toggleMemberEditingOptions('enable');"<?= ( (isset($_POST['ismember']) && ($_POST['ismember'] == "1")) || (!isset($_POST['ismember']) && ($fields['is_member'] == "1")) ) ? ' checked="checked"' : '' ; ?>>
                                Yes
                            </td>
                            </tr>


                            <tr id="mem_type_tr"<?= ( (isset($_POST['ismember']) && ($_POST['ismember'] == "0")) || (!isset($_POST['ismember']) && ($fields['is_member'] == "0")) ) ? ' style="display:none;' : '' ; ?>">
                            </tr>

                            <tr id="credit_type_tr"<?= ( (isset($_POST['ismember']) && ($_POST['ismember'] == "0")) || (!isset($_POST['ismember']) && ($fields['is_member'] == "0")) ) ? ' style="display:none;' : '' ; ?>">
                            <td align="right" class="BgcolorNormal">Credit Type: </td>
                            <td class="BgcolorNormal"><select name="credit_type">
                                <?php
                                $defaultVal = $fields['credit_type_id'] ;
                                if ( isset( $_POST['credit_type'] ) ) {
                                    $defaultVal = $_POST['credit_type'] ;
                                }

                                $numCreditTypes = count( $_SESSION['CREDIT_TYPES'] ) ;
                                for ( $i = 0 ; $i < $numCreditTypes; $i++ ) {
                                    echo "\n\t" . '<option value="' . $_SESSION['CREDIT_TYPES'][$i]['credit_type_id'] . '"' ;
                                    if ( $defaultVal == $_SESSION['CREDIT_TYPES'][$i]['credit_type_id'] ) {
                                        echo ' selected="selected"' ;
                                    }
                                    echo '>' . $_SESSION['CREDIT_TYPES'][$i]['credit_type_name'] . ' - (' . $_SESSION['CREDIT_TYPES'][$i]['credit_type_booking_days'] . '  days)</option>' ;
                                }
                                ?>
                                </select>
                            </td></tr>

                            <tr><td align="right" class="BgcolorNormal">Receive Mailshots: </td>
                            <td class="BgcolorNormal"><INPUT TYPE="radio" name="optout" value="1"<?= ( (isset($_POST['optout']) && ($_POST['optout'] == "1")) || (!isset($_POST['optout']) && ($fields['mail_opt_out'] == "1")) ) ? ' checked="checked"' : '' ; ?>> No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <INPUT TYPE="radio" name="optout" value="0"<?= ( (isset($_POST['optout']) && ($_POST['optout'] == "0")) || (!isset($_POST['optout']) && ($fields['mail_opt_out'] == "0")) ) ? ' checked="checked"' : '' ; ?>> Yes</td></tr>

                            <tr><td align="right" class="BgcolorNormal">Gender: </td>
                            <td class="BgcolorNormal"><INPUT TYPE="radio" name="gender" value="male"<?= ( (isset($_POST['gender']) && ($_POST['gender'] == "male")) || (!isset($_POST['gender']) && ($fields['gender'] == "male")) ) ? ' checked="checked"' : '' ; ?>> Male &nbsp;&nbsp; <INPUT TYPE="radio" name="gender" value="female"<?= ( (isset($_POST['gender']) && ($_POST['gender'] == "female")) || (!isset($_POST['gender']) && ($fields['gender'] == "female")) ) ? ' checked="checked"' : '' ; ?>> Female</td></tr>

                            <tr><td align="center" colspan="2"><br />
                            <input type="hidden" name="groups" value="">
                            <input type="hidden" name="user_select" value="<?= $fields['user_id'] ; ?>">
                            <input type="hidden" name="register" value="yes">

                            <input type="submit" name="register" value="Remove User" class="ButtonStyle" onclick="return confirm( 'REMOVE USER\n\nThe user and all future bookings for this user will be removed if you proceed.\n\nAre you sure you want to remove this user?' );">
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="submit" name="register" value="Save Changes" class="ButtonStyle">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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

                            <?php
                        }
                    }
                } else {
                    echo $page_success_message ;
                }
            } else {
                echo '&nbsp;' ;
            }
            ?>
        </td>
    </tr>
</table>

<?
//} // end of if $reg_result

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>