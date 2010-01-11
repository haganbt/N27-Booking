<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Site Administration" ;
$page_title_bar = '' ;
$hide_navigation = true ;
$hide_footer = true ;

$page_error_message = '' ;

//get some details about the current user
//$user_info = get_user( get_user_id( $_SESSION['valid_user'] ) ) ;

$body_attributes = 'style="margin: 2px; padding: 2px;"' ;
include_once("header.php");

//see if the user is trying to remove any attachments
if ( $_GET['del_attach_id'] != '' ) {
  $sql = 'SELECT filename FROM ' . EMAILSHOT_ATTACHMENTS_TEMP . ' WHERE attachment_id=' . $_GET['del_attach_id'] . ' LIMIT 0,1' ;
  //echo "<hr>$sql" ;
  $res = wrap_db_query( $sql ) ;
  $numAttachments = wrap_db_num_rows( $res ) ;
  if ( $numAttachments > 0 ) {
    $row = wrap_db_fetch_array( $res ) ;

    //delete the item from the db
    $sql = 'DELETE FROM ' . EMAILSHOT_ATTACHMENTS_TEMP . ' WHERE attachment_id=' . $_GET['del_attach_id'] . ' LIMIT 1' ;
    $res = wrap_db_query( $sql ) ;

    //check if this attachment is still linked into any stored mailshots in the sent items table, if so we must not delete it
    $sql2 = 'SELECT attachment_id FROM ' . EMAILSHOT_ATTACHMENTS . ' WHERE filename="' . $row['filename'] . '" LIMIT 0,1' ;
    $res2 = wrap_db_query( $sql2 ) ;
    if ( wrap_db_num_rows( $res2 ) < 1 ) {
      //delete each file from the file system
      unlink( DIR_FS_ATTACHMENTS . $row['filename'] ) ;
    }
  }
}

//echo time() . '<br>';

//output all unattached temp files belonging to the current user with a link to remove them from this e-mail in case they are no longer wanted
$currentUserID = get_user_id( $_SESSION['valid_user'] ) ;
$sql = 'SELECT attachment_id, user_id, filename FROM ' . EMAILSHOT_ATTACHMENTS_TEMP . ' WHERE user_id=' . $currentUserID . ' ORDER BY attachment_id ASC' ;
//echo "<hr>$sql" ;
$res = wrap_db_query( $sql ) ;
$numAttachments = wrap_db_num_rows( $res ) ;
if ( $numAttachments > 0 ) {
  while ( $row = wrap_db_fetch_array( $res ) ) {
    echo htmlentities( $row['filename'] ) . ' [<a href="view_mailshot_attachments.php?del_attach_id=' . $row['attachment_id'] . '">remove</a>]<br />' ;
  }
} else {
  echo '(none)' ;
}


include_once("footer.php");
include_once("application_bottom.php");
?>