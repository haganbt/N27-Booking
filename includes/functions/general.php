<?
// Some of these functions are from TEP/Oscommerce

function wrap_exit()
{
	if (EXIT_AFTER_REDIRECT == 1) {
		wrap_session_close();
		return exit();
	}
}


function href_link($page = '', $parameters = '', $connection = 'NONSSL')
{
	global $link;
	
	if ($page == '') {
		die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><b>Error!</b></font><br /><br /><b>Unable to determine the page link!<br /><br />');
	}
	if ($connection == 'NONSSL') {
		$link = HTTP_SERVER . DIR_WS_SCRIPTS;
	} elseif ($connection == 'SSL') {
		if (ENABLE_SSL == 1) {
			$link = HTTPS_SERVER . DIR_WS_SCRIPTS;
		} else {
			$link = HTTP_SERVER . DIR_WS_SCRIPTS;
		}
	} else {
		die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><b>Error!</b></font><br /><br /><b>Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL</b><br /><br />');
	}
	// Put the session in the URL if we are we are using cookies and changing to SSL
	// Otherwise, we loose the cookie and our session
	if (!SID && !getenv(HTTPS) && $connection=='SSL') {
		$sess = wrap_session_name() . '=' . wrap_session_id();
	} else {
		$sess = SID;
	}
	if ($parameters == '') {
		$link = $link . $page . '?' . $sess;
	} else {
		$link = $link . $page . '?' . $parameters . '&' . $sess;
	}
	
	while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);
	
	return $link;
}


function make_hidden_fields($fields = array())
{
  // Used to track hidden fields.
  if (is_array($fields)) { reset ($fields); } else { $fields = array ($fields); }
  $hidden_fields = '';
  foreach ($fields as $field) {
	if (@$_REQUEST[$field] != "") { $hidden_fields .= '<input type="hidden" name="' . $field . '" value="' . htmlspecialchars(stripslashes($_REQUEST[$field])) . '" />' ."\n"; }
  }
  //echo "Hidden Fields: <br />" .  htmlspecialchars($hidden_fields);
  return ($hidden_fields);
} # End of make_hidden_fields()


function make_hidden_fields_workstring($fields = array())
{
  // Used to track hidden fields.
  if (is_array($fields)) { reset ($fields); } else { $fields = array ($fields); }
  $hidden_fields_workstring = '';
  foreach ($fields as $field) {
	if (@$_REQUEST[$field] != "") { $hidden_fields_workstring .= '&' . $field . '=' . urlencode(stripslashes($_REQUEST[$field])); }
  }
  # Remove the leading ampersand
  $hidden_fields_workstring = preg_replace("/^\&/", "", $hidden_fields_workstring);
  //echo "Want Workstring: " .  htmlspecialchars($hidden_fields_workstring);
  return ($hidden_fields_workstring);
} # End of make_hidden_fields_workstring


function get_all_get_params($exclude_array = '')
{
	global $HTTP_GET_VARS;
	if ($exclude_array == '') $exclude_array = array();
	$get_url = '';
	if (is_array($HTTP_GET_VARS)) {
		reset($HTTP_GET_VARS);
		while (list($key, $value) = each($HTTP_GET_VARS)) {
			if (($key != session_name()) && ($key != 'error') && (!in_array($key, $exclude_array))) $get_url .= $key . '=' . rawurlencode(StripSlashes($value)) . '&';
		}
	}
	return $get_url;
}


// Used to subtract one array from another array.
function array_minus_array($a, $b) {
	$c=array_diff($a,$b);
	$c=array_intersect($c, $a);
	return $c;
}

?>
