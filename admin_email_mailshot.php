<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Site Administration";
$page_title_bar = "Site Administration";

$page_error_message = '' ;

//get some details about the current user
$user_info = get_user( get_user_id( $_SESSION['valid_user'] ) ) ;

//delete any old temp attachments associated with this user
$sql = 'SELECT attachment_id, filename FROM ' . EMAILSHOT_ATTACHMENTS_TEMP . ' WHERE user_id=' . $user_info['user_id'] ;
//echo "<hr>$sql" ;
$res = wrap_db_query( $sql ) ;
$numAttachments = wrap_db_num_rows( $res ) ;
if ( $numAttachments > 0 ) {
  while( $row = wrap_db_fetch_array( $res ) ) {
    //see if each temp attachment name also exists in the sent items table, if so we must not delete it
    $sql2 = 'SELECT attachment_id FROM ' . EMAILSHOT_ATTACHMENTS . ' WHERE filename="' . $row['filename'] . '" LIMIT 0,1' ;
    $res2 = wrap_db_query( $sql2 ) ;
    if ( wrap_db_num_rows( $res2 ) < 1 ) {
      //delete each file from the file system
      unlink( DIR_FS_ATTACHMENTS . $row['filename'] ) ;
    }
  }

  //now delete all of this users temp attachments from the db
  //delete the item from the db
  $sql = 'DELETE FROM ' . EMAILSHOT_ATTACHMENTS_TEMP . ' WHERE user_id=' . $user_info['user_id'] ;
  $res = wrap_db_query( $sql ) ;
}


//see if we need to re-load any existing e-mail content
if ( isset( $_GET['mail_id'] ) && ( trim( $_GET['mail_id'] ) != '' ) ) {
  $oldMailSQL = 'SELECT * FROM ' . EMAILSHOT_SENT_EMAILS . ' WHERE email_id=' . $_GET['mail_id'] . ' LIMIT 0,1' ;
  $oldMailRes = wrap_db_query( $oldMailSQL ) ;
  if ( $oldMailRes ) {
    $oldMailContent = wrap_db_fetch_array( $oldMailRes ) ;

    //get the id's of all the groups this mail was previously sent to
    $sentToGroupRes = wrap_db_query( 'SELECT DISTINCT group_id FROM ' . EMAILSHOT_SENT_TO_GROUPS . ' WHERE email_id=' . $_GET['mail_id'] ) ;
    while ( $sentToGroupRows = wrap_db_fetch_array( $sentToGroupRes ) ) {
      $sentToGroupIDs[] = $sentToGroupRows['group_id'] ;
    }
    //get the id's of all the individuals this mail was previously sent to
    $sentToUserRes = wrap_db_query( 'SELECT DISTINCT user_id FROM ' . EMAILSHOT_SENT_TO_USERS . ' WHERE email_id=' . $_GET['mail_id'] ) ;
    while ( $sentToUserRows = wrap_db_fetch_array( $sentToUserRes ) ) {
      $sentToUserIDs[] = $sentToUserRows['user_id'] ;
    }

    //add the previous attachments to the temp db table so that the originals are never deleted
    $sqlAttach = 'SELECT filename FROM ' . EMAILSHOT_ATTACHMENTS . ' WHERE mailshot_id=' . $_GET['mail_id'] ;
    //echo "<hr>$sqlAttach" ;
    $resAttach = wrap_db_query( $sqlAttach ) ;
    $numAttachments = wrap_db_num_rows( $resAttach ) ;
    if ( $numAttachments > 0 ) {
      while( $rowAttach = wrap_db_fetch_array( $resAttach ) ) {
        $queryAttach2 = "INSERT INTO " . EMAILSHOT_ATTACHMENTS_TEMP . " ( attachment_id, user_id, filename ) VALUES ( '', '" . $user_info['user_id'] . "', '" . mysql_real_escape_string( $rowAttach['filename'] ) . "' );" ;
        //echo "<hr>$queryAttach2" ;
        $resultAttach2 = wrap_db_query($queryAttach2) ;
      }
    }
  }
}

$show_admin_site_admin_menu = true ;
include_once("header.php");

//get all our current groups
$sql = 'SELECT group_id, group_name FROM ' . BOOKING_GROUPS_TABLE . ' ORDER BY group_name ASC' ;
//it would be neater to include a count of the number of members at the same time but it is impossible to get groups with 0 members to be returned this way, hence the extra query for each group done later in the loop.
//$sql = 'SELECT g.group_id, g.group_name, COUNT(m.user_group_id) AS num_members FROM ' . BOOKING_GROUPS_TABLE . ' AS g, ' . BOOKING_USER_GROUPS_TABLE . ' AS m WHERE m.group_id=g.group_id GROUP BY g.group_id ORDER BY g.group_name ASC' ;
$res = wrap_db_query( $sql ) ;
if ( $res ) {
  while ( $row = wrap_db_fetch_array( $res ) ) {
    $membershipSql = 'SELECT COUNT(user_group_id) AS numMembers FROM ' . BOOKING_USER_GROUPS_TABLE . ' WHERE group_id=' . $row['group_id'] ;
    if( $membershipRes = wrap_db_query( $membershipSql ) ) {
      if ( $membershipRow = wrap_db_fetch_array( $membershipRes ) ) {
        $row['num_members'] = $membershipRow['numMembers'] ;
      }
    }
    $groups[] = $row ;
  }
}
$numGroups = count( $groups ) ;

//get all our current users
$sql = 'SELECT user_id , lastname , firstname , username FROM ' . BOOKING_USER_TABLE . ' ORDER BY lastname , firstname , username ASC';
$res = wrap_db_query( $sql ) ;
if ( $res ) {
  while ( $row = wrap_db_fetch_array( $res ) ) {
    $users[] = $row ;
  }
}
$numUsers = count( $users ) ;

?>
<br>
<form method="POST" action="<?= FILENAME_ADMIN_MAILSHOT_TYPE ; ?>" name="email_mailshot" onSubmit="return checkRequiredFields(this);">
<b>Prepare your mail:</b><br>
<br>

<table>
  <tr>
    <td width="80">&nbsp;</td>
    <td align="center"><b>Groups</b><br>
      Group Name (# members)</td>
    <td>&nbsp;</td>
    <td align="center"><b>Individual Users</b><br>
      Surname, Firstname (username)</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
   <td valign="top">To:</td>
    <td valign="top"><div class="scrollingDivBox"><?php
    $checkBoxCount = 0 ;
    for ( $i = 0 ; $i < $numGroups ; $i++, $checkBoxCount++ ) {

      ?><input type="checkbox" name="group_ids[]" id="cb_<?= $checkBoxCount ; ?>" value="<?= $groups[$i]['group_id'] ;?>"<?php
      if ( is_array( $sentToGroupIDs ) ) {
        if ( in_array( $groups[$i]['group_id'], $sentToGroupIDs ) ) {
          echo ' checked="checked"' ;
        }
      }
      ?> /><?= stripslashes( $groups[$i]['group_name'] ) . ' (' . stripslashes( $groups[$i]['num_members'] ) . ')' ;?><br /><?php
    }
    ?></div></td>
    <td width="20">&nbsp;</td>
    <td valign="top"><div class="scrollingDivBox"><?php

    for ( $i = 0 ; $i < $numUsers ; $i++, $checkBoxCount++ ) {

      ?><input type="checkbox" name="user_ids[]" id="cb_<?= $checkBoxCount ; ?>" value="<?= $users[$i]['user_id'] ;?>"<?php
      if ( is_array( $sentToUserIDs ) ) {
        if ( in_array( $users[$i]['user_id'], $sentToUserIDs ) ) {
          echo ' checked="checked"' ;
        }
      }
      ?> /><?= stripslashes( $users[$i]['lastname'] ) . ', ' . stripslashes( $users[$i]['firstname'] ) . ' (' . stripslashes( $users[$i]['username'] ) . ')' ;?><br /><?php
    }
    ?></div></td>
    <td rowspan="6" valign="top" style="padding-left: 20px;"><font color="gray"><b>Note</b><br>
    <br>
      If an individual exists in multiple selected groups, or is selected in a group and as an individual user they will only be sent 1 copy of the e-mail.<br>
      <br>
      Users who have opted not to receive these e-mails will not be sent a copy of this e-mail regardless of whether or not they are selected in these dialogs.</font></td>
  </tr>

  <tr>
    <td>Subject:</td>
    <td colspan="3"><INPUT TYPE="text" size="40" name="email_subject" value="<?= stripslashes( $oldMailContent['subject'] ) ; ?>"></td>
 </tr>

  <tr>
    <td>From Name:</td>
    <td colspan="3"><INPUT TYPE="text" size="40" name="email_from_name" value="<?= ( isset( $oldMailContent ) ) ? stripslashes( $oldMailContent['from_name'] ) : stripslashes( $user_info['firstname'] . ' ' . $user_info['lastname'] ) ; ?>"></td>
  </tr>

  <tr>
    <td>From Email:</td>
    <td colspan="3"><INPUT TYPE="text" size="40" name="email_from_email" value="<?= ( isset( $oldMailContent ) ) ? stripslashes( $oldMailContent['from_email'] ) : stripslashes( $user_info['email'] ) ; ?>"></td>
  </tr>

  <tr>
    <td>CC me?:</td>
    <td colspan="3"><INPUT TYPE="radio" name="email_cc_me" value="0"<?= ( ( $oldMailContent['cc_me'] ) == '0' ) ? ' checked="true"' : '' ; ?>> No &nbsp;&nbsp;&nbsp;&nbsp; <INPUT TYPE="radio" name="email_cc_me" value="1"<?= ( ( $oldMailContent['cc_me'] ) != '0' ) ? ' checked="true"' : '' ; ?>> Yes &nbsp;&nbsp; <font color="gray">(this will be sent to the 'From Email' address)</td>
  </tr>

  <tr>
    <td valign="top">Attachments:</td>
    <td colspan="3" valign="top"><iframe name="email_attachments" class="form_item" src="view_mailshot_attachments.php" width="250" height="60" frameborder="0"></iframe><br />
       <input type="button" class="ButtonStyle" value="Add Attachment" onclick="popUpAttachWindow();" /></td>
  </tr>

  <tr>
    <td valign="top">Message:</td>
    <td colspan="3"><textarea rows="15" cols="50" name="email_body"><?= stripslashes( $oldMailContent['body'] ) ; ?></textarea></td>
  </tr>

  <tr>
    <td>&nbsp;</td>
    <td><input type="hidden" name="send_mail" value="yes">
      <br>
      <input type="submit" name="submit_button" value="Send E-mail" class="ButtonStyle"></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>

</table>

</form>

<script language="javascript">
<!--

function checkRequiredFields(input) {
    var requiredFields = new Array("email_subject",
                                   "email_from_name",
                                   "email_from_email",
                                   "email_body");

    var fieldNames = new Array("E-mail Subject",
                               "E-mail From Name",
                               "E-mail From Address",
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
        if ( !(emlCheckExpr.test(input.email_from_email.value) ) ) {
            alert ( "The From e-mail address supplied is invalid.\nPlease check the e-mail address entered." ) ;
            input.email_from_email.focus();
            return false ;
        }

        <?php
        //check that the form contains any checkboxes at all
        if ( ( $numGroups > 0 ) || ( $numUsers > 0 ) ) {

          ?>var numCheckBoxes = <?= $checkBoxCount ; ?> ;
          for ( c = 0 ; c < numCheckBoxes ; c++ ) {

            box = document.getElementById ( 'cb_' + c ) ;
            if ( box.checked === true ) {

            	//all seems okay so let the user continue
            	return true;
            }
          }
          alert ( 'Please ensure you have selected at least one group or individual for the mail to be sent to!' ) ;
          return false ;
          <?php
        } else {
          ?>
          alert ( 'There are no users or groups to choose from, please create a user before attempting to send mail!' ) ;
          return false ;<?php
        }
        ?>
    } else {
        alert(fieldsNeeded);
        return false;
    }
}

function popUpAttachWindow() {
  eval( "attachWindow = window.open( 'admin_email_mailshot_add_attachment.php', 'attachWindow', 'toolbar=0,scrollbars=0,location=0,statusbar=1,menubar=0,resizable=0,width=440,height=180,left=400,top=400');");
}

// -->
</script>
<?php
include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>