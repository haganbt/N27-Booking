<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Site Administration";
$page_title_bar = "Site Administration";

$page_error_message = '' ;

// Site Settings Form Submit
if ($_POST['save_changes'] == 'yes') {
    //check we have a valid value for allow_new_reg
    if ( ( $_POST['booking_conf_email'] == '0' ) || ( $_POST['booking_conf_email'] == '1' ) ) {

        //update the value in the db
        $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . $_POST['booking_conf_email'] . "' WHERE name = 'send_booking_conf_email' LIMIT 1 ;" ;
        $result = wrap_db_query($query);

        //update the value in the session
        $new_sess_val = false ;
        if ($_POST['booking_conf_email'] == '1') {
            $new_sess_val = true ;
        }
        //update the value in the session so that this change takes immediate effect
        $_SESSION['BOOKING_CONF_EMAILS_SEND'] = $new_sess_val ;

        //if booking confim e-mailing is being enabled then see about updating any of the other fields.
        //The other fields should not be saved if we are disabling the confirm e-mails.
        if ($_POST['booking_conf_email'] == '1') {
            //new from name submitted
            if ( isset( $_POST['booking_email_from_name'] ) ) {
                if ( trim( $_POST['booking_email_from_name'] ) != '' ) {
                    $_POST['booking_email_from_name'] = str_replace( "\'\'", '\"', $_POST['booking_email_from_name'] ) ; //swap double single quotes with proper speech marks
                    $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . mysql_real_escape_string( $_POST['booking_email_from_name'] ) . "' WHERE name = 'send_booking_conf_email_from_name' LIMIT 1 ;" ;
                    wrap_db_query($query);
                    //no need to check if it got added, the user will see this for themselves soon enough :)
                    $_SESSION['BOOKING_CONF_EMAILS_FROM_NAME'] = $_POST['booking_email_from_name'] ;
                } else {
                    $page_error_message .= "- The 'Subject' field cannot be left blank<br>" ;
                }
            }

            //new from address submitted
            if ( isset( $_POST['booking_email_from'] ) ) {
                if ( validate_email( $_POST['booking_email_from'] ) ) {
                    $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . mysql_real_escape_string( $_POST['booking_email_from'] ) . "' WHERE name = 'send_booking_conf_email_from' LIMIT 1 ;" ;
                    wrap_db_query($query);
                    //no need to check if it got added, the user will see this for themselves soon enough :)
                    $_SESSION['BOOKING_CONF_EMAILS_FROM'] = $_POST['booking_email_from'] ;
                } else {
                    $page_error_message .= "- The 'From' e-mail address is not a valid e-mail address<br>" ;
                }
            }

            //new subject line submitted
            if ( isset( $_POST['booking_email_subject'] ) ) {
                if ( trim( $_POST['booking_email_subject'] ) != '' ) {
                    $_POST['booking_email_subject'] = str_replace( "\'\'", '\"', $_POST['booking_email_subject'] ) ; //swap double single quotes with proper speech marks
                    $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . mysql_real_escape_string( $_POST['booking_email_subject'] ) . "' WHERE name = 'send_booking_conf_email_subject' LIMIT 1 ;" ;
                    wrap_db_query($query);
                    //no need to check if it got added, the user will see this for themselves soon enough :)
                    $_SESSION['BOOKING_CONF_EMAILS_SUBJECT'] = $_POST['booking_email_subject'] ;
                } else {
                    $page_error_message .= "- The 'Subject' field cannot be left blank<br>" ;
                }
            }

            //new message body submitted
            if ( isset( $_POST['booking_email_body'] ) ) {
                if ( trim( $_POST['booking_email_body'] ) != '' ) {
                    $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . mysql_real_escape_string( $_POST['booking_email_body'] ) . "' WHERE name = 'send_booking_conf_email_body' LIMIT 1 ;" ;
                    wrap_db_query($query);
                    //no need to check if it got added, the user will see this for themselves soon enough :)
                    $_SESSION['BOOKING_CONF_EMAILS_BODY'] = $_POST['booking_email_body'] ;
                } else {
                    $page_error_message .= "- The 'Message' field cannot be left blank<br>" ;
                }
            }

            //the CC field may or may not have been submitted. If not, assume no CC to be sent
            if ( isset( $_POST['booking_email_cc_me'] ) && isset( $_POST['booking_email_cc'] ) ) {
                if ( validate_email( $_POST['booking_email_cc'] ) ) {
                    $query = "UPDATE " . SETTINGS_TABLE . " SET function_value ='" . mysql_real_escape_string( $_POST['booking_email_cc'] ) . "' WHERE name = 'send_booking_conf_email_cc' LIMIT 1 ;" ;
                    wrap_db_query($query);
                    //no need to check if it got added, the user will see this for themselves soon enough :)
                    $_SESSION['BOOKING_CONF_EMAILS_CC'] = $_POST['booking_email_cc'] ;
                } else {
                    $page_error_message .= "- The e-mail address for the 'CC' (copy of the e-mail to be sent to you) is not a valid e-mail address<br>" ;
                }
            } else {
                //disable the cc sending option
                $query = "UPDATE " . SETTINGS_TABLE . " SET function_value = '' WHERE name = 'send_booking_conf_email_cc' LIMIT 1 ;" ;
                wrap_db_query($query);
                //no need to check if it got added, the user will see this for themselves soon enough :)
                $_SESSION['BOOKING_CONF_EMAILS_CC'] = false ;
            }
        }
    }
}

$show_admin_site_admin_menu = true ;
include_once("header.php");
?>
<br>
<form method="POST" action="<?=FILENAME_ADMIN_EMAIL_OPTIONS?>" name="email_conf_form" onSubmit="return checkRequiredFields(this);">
<b>Booking Confirmation E-mail Settings:</b><br>
<br>
Enable booking confirmation e-mail: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <INPUT TYPE="radio" name="booking_conf_email" onclick="toggleEmailEditingOptions('disable');" value="0"<?= ( $_SESSION['BOOKING_CONF_EMAILS_SEND'] === false ) ? ' checked="true"' : '' ; ?>> No &nbsp;&nbsp;&nbsp;&nbsp; <INPUT TYPE="radio" name="booking_conf_email" onclick="toggleEmailEditingOptions('enable');" value="1"<?= ( $_SESSION['BOOKING_CONF_EMAILS_SEND'] !== false ) ? ' checked="true"' : '' ; ?>> Yes<br>
<br>
<br>

<table cellpadding="2" cellspacing="0" border="0">
<tr>
    <td id="booking_email_table" style="filter: progid:DXImageTransform.Microsoft.Alpha(opacity=<?= ($_SESSION['BOOKING_CONF_EMAILS_SEND']) ? '100' : '50' ; ?>);">
        <b>Edit Booking Confirmation E-mail:</b><br>
        <br>

        <table cellpadding="2" cellspacing="0" border="0">
        <tr>
            <td width="120">E-mail From Name:</td>
            <td width="20">&nbsp;</td>
            <td><INPUT TYPE="text" size="40" name="booking_email_from_name" value="<?= ( isset( $_POST['booking_email_from_name'] ) && ( $_POST['booking_conf_email'] == '1' ) ) ? str_replace( '"', "''", stripslashes( $_POST['booking_email_from_name'] ) ) : str_replace( '"', "''", stripslashes( $_SESSION['BOOKING_CONF_EMAILS_FROM_NAME'] ) ) ; ?>"<?= ( $_SESSION['BOOKING_CONF_EMAILS_SEND'] === false ) ? ' readonly' : '' ; ?>></td>
            <td rowspan="5" valign="top" style="padding-left: 20px;"><font color="gray"><b>Variables for Subject and Message fields</b><br>
                <br>
                The following variables can be entered into the message subject or body and will be replaced with the actual values when a booking is made. For example: 'Dear %firstname% %lastname%, you have just made %slots% bookings' will get e-mailed to the user as 'Dear John Smith, you have just made 2 bookings'.<br>
                <br>
                <b>%firstname%</b> = First name of person booking is for.<br>
                <b>%lastname%</b> = Surname of person booking is for.<br>
                <b>%sitename%</b> = Name of web site (as defined when first set-up).<br>
                <b>%bookingtimes%</b> = Comma separated list of dates and times of bookings just made. Eg. 23/11/2005 at 14:30, 30/11/2005 at 14:30<br>
                <b>%bookingtimesvertical%</b> = List of dates and times of bookings just made, each on a new line. Eg.<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;23/11/2005 at 14:30<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;30/11/2005 at 14:30<br>
                <b>%period%</b> = Duration of a single booking slot (as defined when first set-up).<br>
                <b>%location%</b> = Location of booked event.<br>
                <b>%slots%</b> = Total number of slots used by this boooking(s).<br>
                <b>%briefdesc%</b> = Brief description of booking. (Fixed for most users).<br>
                <b>%fulldesc%</b> = User entered description/details for booking.<br>
                <b>%options%</b> = List of booking options selected, or 'none' if none selected.<br></font></td>
        </tr>
        <tr>
            <td>E-mail From E-mail:</td>
            <td width="20">&nbsp;</td>
            <td><INPUT TYPE="text" size="40" name="booking_email_from" value="<?= ( isset( $_POST['booking_email_from'] ) && ( $_POST['booking_conf_email'] == '1' ) ) ? stripslashes( $_POST['booking_email_from'] ) : $_SESSION['BOOKING_CONF_EMAILS_FROM'] ; ?>"<?= ( $_SESSION['BOOKING_CONF_EMAILS_SEND'] === false ) ? ' readonly' : '' ; ?>></td>
        </tr>
        <tr>
            <td>E-mail Subject:</td>
            <td width="20">&nbsp;</td>
            <td><INPUT TYPE="text" size="40" name="booking_email_subject" value="<?= ( isset( $_POST['booking_email_subject'] ) && ( $_POST['booking_conf_email'] == '1' ) ) ? str_replace( '"', "''", stripslashes( $_POST['booking_email_subject'] ) ) : str_replace( '"', "''", stripslashes( $_SESSION['BOOKING_CONF_EMAILS_SUBJECT'] ) ) ; ?>"<?= ( $_SESSION['BOOKING_CONF_EMAILS_SEND'] === false ) ? ' readonly' : '' ; ?>></td>
        </tr>
        <tr>
            <td valign="top">E-mail Message:</td>
            <td width="20">&nbsp;</td>
            <td><textarea rows="15" cols="50" name="booking_email_body"<?= ( $_SESSION['BOOKING_CONF_EMAILS_SEND'] === false ) ? ' readonly' : '' ; ?>><?= ( isset( $_POST['booking_email_body'] ) && ( $_POST['booking_conf_email'] == '1' ) ) ? stripslashes( $_POST['booking_email_body'] ) : $_SESSION['BOOKING_CONF_EMAILS_BODY'] ; ?></textarea></td>
        </tr>
        <tr>
            <td>Send me a copy:</td>
           <td width="20">&nbsp;</td>
            <?php
            //see if box should be checked
            $checkBox = false ;
            if ( $_SESSION['BOOKING_CONF_EMAILS_CC'] !== false ) {
                $checkBox = true ;
            }
            $checkValue = $_SESSION['BOOKING_CONF_EMAILS_CC'] ;
            if ( ( $_SESSION['BOOKING_CONF_EMAILS_CC'] == false ) || ( trim( $_SESSION['BOOKING_CONF_EMAILS_CC'] ) == '' ) ) {
                //there is no previously saved value for the cc address so default to using the same as the From address.
                $checkValue = $_SESSION['BOOKING_CONF_EMAILS_FROM'] ;
            }
            //see if any POST values exist that take precendence
            if ( isset( $_POST['save_changes'] ) ) {
                //form submitted
                $checkBox = isset( $_POST['booking_email_cc_me'] ) ;
                if ( isset( $_POST['booking_email_cc_me'] ) ) {
                    if ( $_POST['booking_conf_email'] == '1' ) {
                        $checkValue = $_POST['booking_email_cc'] ;
                    } else {
                        $checkValue = $_SESSION['BOOKING_CONF_EMAILS_CC'] ;
                    }
                }
            }
            ?>
            <td><INPUT TYPE="checkbox" name="booking_email_cc_me" value="1"<?= ( $checkBox ) ? ' checked' : '' ; ?> onclick="if( this.checked ){ booking_email_cc.style.visibility='visible' ; } else { booking_email_cc.style.visibility='hidden' ; }"<?= ( $_SESSION['BOOKING_CONF_EMAILS_SEND'] === false ) ? ' disabled' : '' ; ?>>
                <input type="text" size="37" name="booking_email_cc" value="<?= $checkValue ; ?>"<?= ( $checkBox == false ) ? ' style="visibility: hidden;"' : '' ; ?><?= ( $_SESSION['BOOKING_CONF_EMAILS_SEND'] === false ) ? ' readonly' : '' ; ?>></td>
        </tr>
        </table>
    </td>
</tr>
</table>

<br>
<input type="hidden" name="save_changes" value="yes">
<input type="submit" name="register" value="Save Settings" class="ButtonStyle" style="margin-left: 150px;">

</form>

<script language="javascript">
<!--
function toggleEmailEditingOptions( toggleTo ) {
    var emailTable = document.getElementById( 'booking_email_table' ) ;

    if( toggleTo == 'disable' ) {
        if (document.all) {
            emailTable.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 50 ;
        } else {
            emailTable.style.MozOpacity=0.5;
        }
        document.forms.email_conf_form.booking_email_from_name.readOnly=true;
        document.forms.email_conf_form.booking_email_from.readOnly=true;
        document.forms.email_conf_form.booking_email_cc_me.disabled=true;
        document.forms.email_conf_form.booking_email_cc.readOnly=true;
        document.forms.email_conf_form.booking_email_subject.readOnly=true;
        document.forms.email_conf_form.booking_email_body.readOnly=true;
    } else {
        if (document.all) {
            emailTable.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100 ;
        } else {
            emailTable.style.MozOpacity=1.0;
        }
        document.forms.email_conf_form.booking_email_from_name.readOnly=false;
        document.forms.email_conf_form.booking_email_from.readOnly=false;
        document.forms.email_conf_form.booking_email_cc_me.disabled=false;
        document.forms.email_conf_form.booking_email_cc.readOnly=false;
        document.forms.email_conf_form.booking_email_subject.readOnly=false;
        document.forms.email_conf_form.booking_email_body.readOnly=false;
    }
}

function checkRequiredFields(input) {
    //see if the user even wants to send confirmation e-mails
    var checkOtherFields = '0' ;
    for( var j = 0 ; j < input.booking_conf_email.length ; j++ ) {
        if( input.booking_conf_email[j].checked ) {
            checkOtherFields = input.booking_conf_email[j].value ;
            break ; //only one radio option can be selected at once there is so no point looking at any remaining options
        }
    }

    if ( checkOtherFields == '0' ) {
        return confirm( 'Any ammendments made to the booking confirmation details below will not be checked\nor saved since you have selected not to send any confirmation e-mails.\n\nAre you sure you do not want to send confirmation e-mails for bookings?' ) ;
    } else {
        var requiredFields = new Array("booking_email_from_name",
                                       "booking_email_from",
                                       "booking_email_subject",
                                       "booking_email_body");

        var fieldNames = new Array("E-mail From name",
                                   "E-mail From address",
                                   "E-mail Subject",
                                   "E-mail Message");

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
            if ( !(emlCheckExpr.test(input.booking_email_from.value) ) ) {
                alert ( "The From e-mail address supplied is invalid.\nPlease check the e-mail address entered." ) ;
                input.booking_email_from.focus();
                return false ;
            }

            //see if the send a copy option is ticked
            if ( input.booking_email_cc_me.checked == true ) {
                //is is so test the associated e-mail address
                if ( !(emlCheckExpr.test(input.booking_email_cc.value) ) ) {
                    alert ( "The 'Send me a copy' e-mail address supplied is invalid.\nPlease check the e-mail address entered." ) ;
                    input.booking_email_cc.focus();
                    return false ;
                }
            }

        	//all seems okay so let the user continue
        	return true;

        } else {
            alert(fieldsNeeded);
            return false;
        }
    }
}
// -->
</script>
<?php
include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>