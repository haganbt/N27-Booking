<? include_once("./includes/application_top.php"); ?>
<?
//make sure the user is an administrator
require_once('admin_security.php') ;

$page_title = "Modify Groups";
$page_title_bar = "Modify Groups";

//value of action
$actionValue = 'add' ;

//check for form submission
if ( $_POST['submitted'] == 'submitted' ) {

  //make sure the new name is not blank
  if ( trim( $_POST['group_name'] ) != '' ) {

    //check our action
    switch( $_POST['action'] ) {

      case 'add' :
        $sql = 'INSERT INTO ' . BOOKING_GROUPS_TABLE . ' ( group_name ) VALUES ( "' . mysql_real_escape_string( $_POST['group_name'] ) . '" ) ' ;
        $theMsg = 'New group added successfully' ;
        break;

      case 'edit' :
        $sql = 'UPDATE ' . BOOKING_GROUPS_TABLE . ' SET group_name="' . mysql_real_escape_string( $_POST['group_name'] ) . '" WHERE group_id=' . $_POST['group_id'] . ' LIMIT 1' ;
        $theMsg = 'Changes to group name saved successfully' ;
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
    //the user tried to add a group with no name or to change the name to a blank string
    $page_error_message = 'The group name cannot be blank.' ;
  }
}

//check for link click
if ( ( isset( $_GET['action'] ) && ( $_GET['action'] != '' ) ) && ( isset( $_GET['group_id'] ) && ( trim( $_GET['group_id'] ) != '' ) ) ) {

  //check our action
  switch( $_GET['action'] ) {

    case 'edit' :
      $actionValue = 'edit' ;
      $sql = 'SELECT * FROM ' . BOOKING_GROUPS_TABLE . ' WHERE group_id=' . $_GET['group_id'] . ' LIMIT 1' ;
      $res = wrap_db_query( $sql ) ;
      if ( $res ) {

        while ( $row = wrap_db_fetch_array( $res ) ) {

          $chosenGroup = $row ;
        }
      }
      $page_info_message = 'New group created successfully.' ;
      break;

    case 'delete' :
      //delete the group
      $sql = 'DELETE FROM ' . BOOKING_GROUPS_TABLE . ' WHERE group_id=' . $_GET['group_id'] . ' LIMIT 1' ;
      if ( $res = wrap_db_query( $sql ) ) {
        //  Delete any reference to the group from the BOOKING_USER_GROUPS_TABLE
        $sql = 'DELETE FROM ' . BOOKING_USER_GROUPS_TABLE . ' WHERE group_id=' . $_GET['group_id'];
        $res = wrap_db_query( $sql ) ;
		//  Delete any reference from the BOOKING_PRODUCT_GROUPS table
        $sql2 = 'DELETE FROM ' . BOOKING_PRODUCT_GROUPS . ' WHERE group_id=' . $_GET['group_id'];
        $res2 = wrap_db_query( $sql2 ) ;		
	  }

      $page_info_message = 'Group deleted successfully.' ;
      break;

    default:
      break;
  }
}

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

include_once("header.php");

?>
<br />
Use the controls below to add/edit or delete user groups (as used when sending mailshots).<br />
<br />
<form name="form1" method="post" action="<?= FILENAME_ADMIN_MODIFY_GROUPS ;?>">
<input type="hidden" name="action" value="<?= $actionValue ;?>" />
<input type="hidden" name="group_id" value="<?= $chosenGroup['group_id'] ;?>" />
<b><?= ucwords( $actionValue ) ;?> Group</b>
<table>
  <tr>
    <td><input type="text" name="group_name" value="<?= stripslashes( $chosenGroup['group_name'] ) ;?>" /></td>
    <td><input type="submit" name="submit" value="Save" class="ButtonStyle" /></td>
  </tr>
</table>
<?php
//output a hidden field containing a tracker to let us know when the form has been submitted
?>
<input type="hidden" name="submitted" value="submitted">
</form>

<br>
<br>

<?php

//output all our existing groups with links to edit / delete
$numGroups = sizeof( $groups ) ;
if ( ( is_array( $groups ) ) && ( $numGroups > 0 ) ) {
  ?>
  <b>Current Groups</b>
  <table border="0" cellpadding="4" cellspacing="2">
    <tr>
      <th class="BgcolorDull2" width="150">Name</th>
      <th class="BgcolorDull2">Members</th>
      <th class="BgcolorDull2">Control</th>
    </tr>
    <?php
  for ( $i = 0 ; $i < $numGroups ; $i++ ) {

    $class = 'BgcolorNormal' ;
    if ( $i % 2 == 1 ) {
      $class = 'BgcolorBody' ;
    }
    ?><tr>
      <td align="left" class="<?= $class ;?>"><?= stripslashes( $groups[$i]['group_name'] ) ; ?></td>
      <td align="center" class="<?= $class ;?>"><a href="<?= FILENAME_ADMIN_MODIFY_USER_GROUPS ;?>?group_id=<?= $groups[$i]['group_id'] ;?>"><?= $groups[$i]['num_members'] ; ?></a>
      <td align="center" class="<?= $class ;?>"><a href="<?= FILENAME_ADMIN_MODIFY_GROUPS ;?>?action=edit&group_id=<?= $groups[$i]['group_id'] ;?>">edit name</a> | <a href="<?= FILENAME_ADMIN_MODIFY_GROUPS ;?>?action=delete&group_id=<?= $groups[$i]['group_id'] ;?>" onclick="return confirm('Are you sure you wish to delete this group?\n\nThis action is permanent and cannot be undone.');">delete</a></td>
    </tr><?php
  }
  ?>
  </table><?php
} else {
  ?>
  <b>No groups currently exist.</b>
  <?php
}

include_once("footer.php");
?>
<? include_once("application_bottom.php"); ?>