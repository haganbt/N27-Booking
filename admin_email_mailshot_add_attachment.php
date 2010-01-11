<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Site Administration" ;
$page_title_bar = 'Please select a file to attach:' ;
$hide_navigation = true ;
$hide_footer = true ;

$page_error_message = '' ;

$body_attributes = 'style="margin: 2px; padding: 2px;"' ;
include_once("header.php");

function uploadFile( $tmpLocation, $fileName ) {
  // upload images and put them in sensible places
  // check images are to be uploaded

  //get some details about the current user
  $userID = get_user_id( $_SESSION['valid_user'] ) ;

  //the dir to upload the file to on the server
  //this dir must be writiable by php (chmod 757 ought to do it)
  //$dir = "./attachments/";
  if ( $tmpLocation ) {

    // check file name and make it unix friendly
    $pattern = '/[^a-zA-Z0-9_\.]/' ;
    $replacement = "_" ;
    $fileName = preg_replace($pattern, $replacement, $fileName) ;

    //$dest = $dir . $fileName ;
    $dest = DIR_FS_ATTACHMENTS . $fileName ;

    if ( copy( $tmpLocation, $dest ) ) {
      //it worked, now note this in the database
      $query = "INSERT INTO " . EMAILSHOT_ATTACHMENTS_TEMP . " ( attachment_id, user_id, filename ) VALUES ( '', '" . $userID . "', '" . mysql_real_escape_string( $fileName ) . "' );" ;
      //echo "<hr>$query" ;
      if ( $result = wrap_db_query($query) ) {
        //get the attachment_id (auto) for the entry just added to the temp attachments table
        //$thisAttachmentID = wrap_db_insert_id() ;

        //and finally, return the filesize to the item that called this function
        return FileSize($dest) ;
      }

      //if you get here then the db insert failed so
      echo "<!-- Insert to DB failed -->";
      return false;
    } else {
      echo "<!-- Copy to server failed -->";
      return false;
    }
  } else {
    echo "<!-- hmmm, somethings a bit dodgy... -->";
  }
}

if ( ($_FILES['file_to_attach']['error'] == "0") && ($_FILES['file_to_attach']['name'] != "") && ($_FILES['file_to_attach']['name'] != "none") ) {

  if ( $fileSize = uploadFile( $_FILES['file_to_attach']['tmp_name'], $_FILES['file_to_attach']['name'] ) ) {
    echo '<b>' . $_FILES['file_to_attach']['name'] . " uploaded.</b><br><br>Please close this window when you have finished attaching files." ;
    ?>
    <script language="javascript">
      //refresh the parent iframe so that the user sees the attachment listed
      window.opener.frames['email_attachments'].location.href='view_mailshot_attachments.php?rand='+Math.random();
      //close this popup window
      window.close();
    </script>
    <?php
  } else {
    echo '<b>There was a problem uploading your file.</b><br><br>Please try again.<br>' ;
  }
}
?>


<form name="attachment_form" method="post" action="admin_email_mailshot_add_attachment.php" enctype="multipart/form-data">
<br />
<table align="center">
  <tr>
    <td><input type="file" name="file_to_attach" size="50" /></td>
  </tr>
  <tr>
    <td align="right"><br /><input type="submit" class="ButtonStyle" value="Attach File" />
  </tr>
</table>
<input type="hidden" name="form_submitted" value="yes" />
<input type="hidden" name="MAX_FILE_SIZE" value="20000000"><!-- a 20MB PHP limit on file upload size (is set to 10MB by default in Apache) -->

</form>
<font color="gray"><b>Note</b><br />The selected file will be uploaded to the server when you click the &quot;Attach File&quot; button. This may take a while for larger files so please be patient. This window will close automatically when the file has been uploaded.</font>

<?php
include_once("footer.php");
include_once("application_bottom.php");
?>