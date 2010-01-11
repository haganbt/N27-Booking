<?
// handle magic_quotes_gpc turned off.

  if (!get_magic_quotes_gpc()) {
    if (is_array($_GET)) {
      while (list($var, $val) = each($_GET)) {
        $_GET[$var] = addslashes($val);
      }
    }
    if (is_array($_POST)) {
      while (list($var, $val) = each($_POST)) {
        //if an array style item is posted, leave it alone or we'll scre up the array
        if ( !is_array( $val ) ) {
            $_POST[$var] = addslashes($val);
        }
      }
    }
    if (is_array($_COOKIE)) {
      while (list($var, $val) = each($_COOKIE)) {
        $_COOKIE[$var] = addslashes($val);
      }
    }
    if (is_array($_REQUEST)) {
      while (list($var, $val) = each($_REQUEST)) {
        //if an array style item is posted, leave it alone or we'll scre up the array
        if ( !is_array( $val ) ) {
            $_REQUEST[$var] = addslashes($val);
        }
      }
    }
  }
?>