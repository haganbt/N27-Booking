<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Modify User Groups";
$page_title_bar = "Modify User Groups";

//check for form submission
if ( $_POST['submitted'] == 'submitted' ) {

  //make sure a valid group_id value was supplied
  if ( trim( $_POST['group_id'] ) != '' ) {

    //delete all existing $_POST['group_id'] entries in db
    $sql = 'DELETE FROM ' . BOOKING_USER_GROUPS_TABLE . ' WHERE group_id="' . mysql_real_escape_string ( $_POST['group_id'] ) . '"' ;
    $res = wrap_db_query( $sql ) ;


    //now insert all our new entries... foreach on $_POST['user_ids']
    if ( ( is_array( $_POST['user_ids'] ) ) && ( sizeof( $_POST['user_ids'] ) > 0 ) ) {
      foreach( $_POST['user_ids'] as $user_id ) {
        $sql = 'INSERT INTO ' . BOOKING_USER_GROUPS_TABLE . ' ( user_id , group_id , created ) VALUES ( "' . mysql_real_escape_string( $user_id ) . '" , "' . mysql_real_escape_string ( $_POST['group_id'] ) . '" , NOW() ) ' ;
        $res = wrap_db_query( $sql ) ;
      }
    }

    //note changes were made okay
    $page_info_message = 'Changes to group membership saved successfully' ;
  }
}

//get users in any specified group id
if ( isset( $_REQUEST['group_id'] ) && ( trim( $_REQUEST['group_id'] ) > 0 ) ) {
  $sql = 'SELECT * FROM ' . BOOKING_USER_GROUPS_TABLE . ' WHERE group_id=' . $_REQUEST['group_id'] ;
  $res = wrap_db_query( $sql ) ;
  if ( $res && ( wrap_db_num_rows( $res ) > 0 ) ) {
    while ( $row = wrap_db_fetch_array( $res ) ) {
      $thisGroupsUsers[] = $row['user_id'] ;
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

//get all our current users
$sql = 'SELECT user_id , lastname , firstname , username FROM ' . BOOKING_USER_TABLE . ' ORDER BY lastname , firstname , username ASC';
$res = wrap_db_query( $sql ) ;
if ( $res ) {
  while ( $row = wrap_db_fetch_array( $res ) ) {
    $users[] = $row ;
  }
}

include_once("header.php");

?>
<br />
Select a user group from the list below, then place a tick next to all users belonging in that group.<br>
To remove a user from a group simply remove the tick next to their name.<br />
<br />

<form name="form1" method="post" action="<?= FILENAME_ADMIN_MODIFY_USER_GROUPS ;?>">
<b>Edit Users Within a Group</b>
<br>
<?php

//output all our existing groups with links to edit / delete
$numGroups = sizeof( $groups ) ;
$numUsers = sizeof( $users ) ;
if ( ( is_array( $groups ) ) && ( $numGroups > 0 ) ) {

  ?><br />
  <br />
  <table border="0" cellpadding="0" cellspacing="2">
    <tr>
      <th width="100">Group :</th>
      <td>
        <select name="group_id" onchange="if( this.value != '' ) { document.location.href=('<?= FILENAME_ADMIN_MODIFY_USER_GROUPS ;?>?group_id=' + this.value); } else { document.location.href=('<?= FILENAME_ADMIN_MODIFY_USER_GROUPS ;?>'); }">
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
      <th valign="top">Users :</th>
      <td><div class="scrollingDivBox"><?php

      for ( $i = 0 ; $i < $numUsers ; $i++ ) {

        ?><input type="checkbox" name="user_ids[]" value="<?= $users[$i]['user_id'] ;?>"<?php
        if ( is_array( $thisGroupsUsers ) ) {
          if ( in_array( $users[$i]['user_id'] , $thisGroupsUsers ) ) {
            echo ' checked="checked"' ;
          }
        }
        ?> /><?= stripslashes( $users[$i]['lastname'] ) . ', ' . stripslashes( $users[$i]['firstname'] ) . ' (' . stripslashes( $users[$i]['username'] ) . ')' ;?><br /><?php
      }
      ?></div></td>
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
<input type="hidden" name="previousUsersThisGroup" value="<?= $numUsers ; ?>">
<input type="hidden" name="submitted" value="submitted">
</form>
<br>
<br>
<?php
include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>