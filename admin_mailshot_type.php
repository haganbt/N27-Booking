<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Site Administration";
$page_title_bar = "Site Administration";

//get some details about the current user
$user_info = get_user( get_user_id( $_SESSION['valid_user'] ) ) ;

//check for link click
if ( ( isset( $_GET['action'] ) && ( $_GET['action'] != '' ) ) && ( isset( $_GET['mail_id'] ) && ( trim( $_GET['mail_id'] ) != '' ) ) ) {

  if ( $_GET['action'] == 'delete' ) {
    //delete the mail from the sent mails table
    $sql = 'DELETE FROM ' . EMAILSHOT_SENT_EMAILS . ' WHERE email_id=' . $_GET['mail_id'] . ' LIMIT 1' ;
    if ( $res = wrap_db_query( $sql ) ) {
      //clearup details of membership of this group (housekeeping to keep the db tidy - like a cascading delete)
      $sql = 'DELETE FROM ' . EMAILSHOT_SENT_TO_GROUPS . ' WHERE email_id=' . $_GET['mail_id'] ;
      $res = wrap_db_query( $sql ) ;
      $sql = 'DELETE FROM ' . EMAILSHOT_SENT_TO_USERS . ' WHERE email_id=' . $_GET['mail_id'] ;
      $res = wrap_db_query( $sql ) ;

      //delete any file attachments used in this mailshot
      $sqlAttach = 'SELECT attachment_id, filename FROM ' . EMAILSHOT_ATTACHMENTS . ' WHERE mailshot_id=' . $_GET['mail_id'] ;
      //echo "<hr>$sqlAttach" ;
      $res = wrap_db_query( $sqlAttach ) ;
      $numAttachments = wrap_db_num_rows( $res ) ;
      if ( $numAttachments > 0 ) {
        while( $row = wrap_db_fetch_array( $res ) ) {
          //delete each file from the file system
          unlink( DIR_FS_ATTACHMENTS . $row['filename'] ) ;
        }

        //now delete all of this attachments belinging to this mailshot from the db
        //delete the item from the db
        $sql = 'DELETE FROM ' . EMAILSHOT_ATTACHMENTS . ' WHERE mailshot_id=' . $_GET['mail_id'] ;
        $res = wrap_db_query( $sql ) ;
      }
    }

    $page_info_message = 'E-mail deleted successfully.' ;
  }
}

//check for email mailshot form submission
if ( $_POST['send_mail'] == 'yes' ) {

  //store the mail in the db
  $query = "INSERT INTO " . EMAILSHOT_SENT_EMAILS . " ( email_id, sent_by_user_id, from_name, from_email, cc_me, subject, body, sent ) VALUES ( '', '" . $user_info['user_id'] . "', '" . mysql_real_escape_string( $_POST['email_from_name'] ) . "', '" . mysql_real_escape_string( $_POST['email_from_email'] ) . "', '" . mysql_real_escape_string( $_POST['email_cc_me'] ) . "', '" . mysql_real_escape_string( $_POST['email_subject'] ) . "', '" . mysql_real_escape_string( $_POST['email_body'] ) . "', NOW() );" ;
  //echo "<hr>$query" ;
  if ( $result = wrap_db_query($query) ) {
    //get the email_id (auto) for the mail just added to the sent emails table
    $thisEmailID = wrap_db_insert_id() ;

    //link in all the groups this mail is being sent to
    $numGroupIDs = count( $_POST['group_ids'] ) ;
    for ( $g = 0 ; $g < $numGroupIDs ; $g++ ) {
      $query = "INSERT INTO " . EMAILSHOT_SENT_TO_GROUPS . " ( id, email_id, group_id ) VALUES ( '', '" . $thisEmailID . "', '" . mysql_real_escape_string( $_POST['group_ids'][$g] ) . "' );" ;
      //echo "<hr>$query" ;
      $result = wrap_db_query($query);
    }

    //link in all the users this mail is being sent to
    $numUserIDs = count( $_POST['user_ids'] ) ;
    for ( $u = 0 ; $u < $numUserIDs ; $u++ ) {
      $query = "INSERT INTO " . EMAILSHOT_SENT_TO_USERS . " ( id, email_id, user_id ) VALUES ( '', '" . $thisEmailID . "', '" . mysql_real_escape_string( $_POST['user_ids'][$u] ) . "' );" ;
      //echo "<hr>$query" ;
      $result = wrap_db_query($query);
    }

    //link in any attachments and map these into an attacments array at the same time ready for passing to the send_mail function
    $sqlAttach = 'SELECT attachment_id, filename FROM ' . EMAILSHOT_ATTACHMENTS_TEMP . ' WHERE user_id=' . $user_info['user_id'] ;
    //echo "<hr>$sql" ;
    $resAttach = wrap_db_query( $sqlAttach ) ;
    $numAttachments = wrap_db_num_rows( $resAttach ) ;
    $attachmentsArray = false ; //empty holder for attachments
    if ( $numAttachments > 0 ) {
      while( $rowAttach = wrap_db_fetch_array( $resAttach ) ) {
        $queryAttach2 = "INSERT INTO " . EMAILSHOT_ATTACHMENTS . " ( attachment_id, mailshot_id, filename ) VALUES ( '', '" . $thisEmailID . "', '" . mysql_real_escape_string( $rowAttach['filename'] ) . "' );" ;
        //echo "<hr>$query" ;
        if ( $resultAttach2 = wrap_db_query($queryAttach2) ) {
          //add this filename to an array of files to be sent
          $attachmentsArray[] = $rowAttach['filename'] ;
        }
      }

      //now delete all of this users temp attachments from the db
      $sqlAttach3 = 'DELETE FROM ' . EMAILSHOT_ATTACHMENTS_TEMP . ' WHERE user_id=' . $user_info['user_id'] ;
      $resAttach3 = wrap_db_query( $sqlAttach3 ) ;
    }

//    $numUserIDs = count( $_POST['user_ids'] ) ;
//    for ( $u = 0 ; $u < $numUserIDs ; $u++ ) {
//      $query = "INSERT INTO " . EMAILSHOT_SENT_TO_USERS . " ( id, email_id, user_id ) VALUES ( '', '" . $thisEmailID . "', '" . mysql_real_escape_string( $_POST['user_ids'][$u] ) . "' );" ;
//      //echo "<hr>$query" ;
//      $result = wrap_db_query($query);
//    }


    //the mail should now be saved. Let's find out who it should get sent to by:
    // - looking up all the users in the selected groups
    // - merging this list with the selected individual user e-mail addresses
    // - remove any duplicates (a user may be in multiple of the selected groups or in a group and selected again individually)
    // - removing all opted-out addresses

    $groupMailAddresses = null ;
    if ( $numGroupIDs > 0 ) {
      $sql = 'SELECT DISTINCT u.email FROM ' . BOOKING_USER_TABLE . ' AS u, ' . BOOKING_USER_GROUPS_TABLE . ' AS ug WHERE u.user_id=ug.user_id AND u.mail_opt_out=\'0\' AND ( ug.group_id=' . $_POST['group_ids'][0] ;
      for ( $g = 1 ; $g < $numGroupIDs ; $g++ ) {
        $sql .= ' OR ug.group_id=' . $_POST['group_ids'][$g] ;
      }
      $sql .= ' )' ;
      $res = wrap_db_query( $sql ) ;
      if ( $res ) {
        while ( $row = wrap_db_fetch_array( $res ) ) {
          $groupMailAddresses[] = $row['email'] ;
        }
      }
    }

    $userMailAddresses = null ;
    if ( $numUserIDs > 0 ) {
      $sql = 'SELECT DISTINCT email FROM ' . BOOKING_USER_TABLE . ' WHERE mail_opt_out=\'0\' AND ( user_id=' . $_POST['user_ids'][0] ;
      for ( $u = 1 ; $u < $numUserIDs ; $u++ ) {
        $sql .= ' OR user_id=' . $_POST['user_ids'][$u] ;
      }
      $sql .= ' )' ;
      $res = wrap_db_query( $sql ) ;
      if ( $res ) {
        while ( $row = wrap_db_fetch_array( $res ) ) {
          $userMailAddresses[] = $row['email'] ;
        }
      }
    }

    //check that we actually have 2 arrays worth of addresses to merge
    $allMailAddresses = null ;
    if( is_array( $groupMailAddresses ) && is_array( $userMailAddresses ) ) {
      //both are arrays, so merge them
      $allMailAddresses = array_merge( $groupMailAddresses, $userMailAddresses ) ;
    } else if ( is_array( $groupMailAddresses ) ) {
      //only the groups is an array
      $allMailAddresses = $groupMailAddresses ;
    } else if ( is_array( $userMailAddresses ) ) {
      //only the users is an array
      $allMailAddresses = $userMailAddresses ;
    }

    if ( $allMailAddresses != null ) {
      //strip out any duplicates
      $mailToAddresses = array_unique( $allMailAddresses ) ;

      //define the text for the remove link which gets bolted onto the end of the e-mails
      $removeLinkText = "\n\n\nIf you have received this mail in error or would like to remove yourself from this list you may do so at any time simply by visiting " . DOMAIN_NAME . "/remove.php\nPlease note that " . SITE_NAME . " will not give your email address to any other companies.\n\n" ;

      //strip any slashes out of the posted strings
      $_POST['email_from_name'] = stripslashes( $_POST['email_from_name'] ) ;
      $_POST['email_from_email'] = stripslashes( $_POST['email_from_email'] ) ;
      $_POST['email_subject'] = stripslashes( $_POST['email_subject'] ) ;
      $_POST['email_body'] = stripslashes( $_POST['email_body'] ) ;

      //now let's send the mailshot to these users
      $numPeopleToMail = count( $mailToAddresses ) ;
      for ( $m = 0 ; $m < $numPeopleToMail ; $m++ ) {
        send_mail( $_POST['email_from_name'], $_POST['email_from_email'], '', $mailToAddresses[$m], $_POST['email_subject'], ( $_POST['email_body'] . $removeLinkText ), '1', false, 'Normal', $attachmentsArray ) ;
      }

      //see if the user wants a copy CC'd to themselves
      if ( $_POST['email_cc_me'] == '1' ) {
          send_mail( $_POST['email_from_name'], $_POST['email_from_email'], $_POST['email_from_name'], $_POST['email_from_email'], $_POST['email_subject'], ( $_POST['email_body'] . $removeLinkText ), '1', false, 'Normal', $attachmentsArray ) ;
      }

      //let the user know how many people their e-mail was sent to
      $page_info_message = 'Your mailshot has been sent to ' . $numPeopleToMail . ' opted-in users by e-mail.' ;
    }
  }
}

$show_admin_site_admin_menu = true ;
include_once("header.php");
?>
<br>
<b>Please select the mailshot type:</b><br>
<br>
<br>

- Create a new e-mail mailshot: <input type="button" class="ButtonStyle" value="GO" name="newMailButton" onclick="document.location.href='<?= FILENAME_ADMIN_EMAIL_MAILSHOT ; ?>'" style="margin-left: 20px;"><br>

<br>
<br>

<?php
//output all previously sent emails with links to edit / delete

$currentUserID = get_user_id( $_SESSION['valid_user'] ) ;
$sql = 'SELECT email_id, subject, DATE_FORMAT( sent, \'%d/%m/%Y %H:%i\' ) AS sent_time FROM ' . EMAILSHOT_SENT_EMAILS . ' WHERE sent_by_user_id=' . $currentUserID . ' ORDER BY sent DESC' ;
//echo "<hr>$sql" ;
$res = wrap_db_query( $sql ) ;
$numMails = wrap_db_num_rows( $res ) ;

if ( $numMails > 0 ) {
  ?>
  - Edit or delete a previous e-mail mailshot:<br>
  <br>

  <table border="0" cellpadding="4" cellspacing="2" style="margin-left: 10px;">
    <tr>
      <th class="BgcolorDull2" width="150">Subject</th>
      <th class="BgcolorDull2">Sent</th>
      <th class="BgcolorDull2">Control</th>
    </tr>
    <?php
    $i = 0 ;
    while ( $row = wrap_db_fetch_array( $res ) ) {

      $class = 'BgcolorNormal' ;
      if ( $i % 2 == 1 ) {
        $class = 'BgcolorBody' ;
      }
      ?><tr>
        <td align="left" class="<?= $class ;?>"><?= $row['subject'] ; ?></td>
        <td align="left" class="<?= $class ;?>"><?= $row['sent_time'] ; ?></a>
        <td align="center" class="<?= $class ;?>"><a href="<?= FILENAME_ADMIN_EMAIL_MAILSHOT ; ?>?mail_id=<?= $row['email_id'] ; ?>">edit mail</a> | <a href="<?= FILENAME_ADMIN_MAILSHOT_TYPE ;?>?action=delete&mail_id=<?= $row['email_id'] ;?>" onclick="return confirm('Are you sure you wish to delete this mail?\n\nThis action is permanent and cannot be undone.');">delete</a></td>
      </tr><?php
      $i++ ;
    }
  ?>
  </table><?php
}
//else {
//  <br>
//  <br>
//  <b>No sent mails currently exist.</b>
//}
?>

<br>
<br>

<?php
include_once("footer.php");
include_once("application_bottom.php");
?>