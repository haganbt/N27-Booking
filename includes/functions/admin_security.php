<?php
//a security function to be included on any page requiring
//access to be limited to administrators only.
//
//to use, simply require this file

if (!wrap_session_is_registered("admin_user")) {
    // user is NOT an admin. Reject access and close page nicely
    $page_title = "Access Error";
    $page_title_bar = "Access Error";
    include_once("header.php");
	echo "<br><b>Error</b><br><br>Sorry, admin users only.<br>";
    include_once("footer.php");
    include_once("application_bottom.php");
    //terminate processing
    exit ;
}

//if we get this far then the user is an administrator so
//return true and allow script execution as per usual.
return true ;
?>