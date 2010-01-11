<?
  /////
  //
  // Function    : validate_email
  //
  // Arguments   : email   email address to be checked
  //
  // Return      : true  - valid email address
  //               false - invalid email address
  //
  // Description : function for validating email address that conforms to RFC 822 specs
  //
  //               This function is converted from a JavaScript written by 
  //               Sandeep V. Tamhankar (stamhankar@hotmail.com). The original JavaScript
  //               is available at http://javascript.internet.com
  //
  // Sample Valid Addresses:
  //
  //    first.last@host.com
  //    firstlast@host.to
  //    "first last"@host.com
  //    "first@last"@host.com
  //    first-last@host.com
  //    first.last@[123.123.123.123]
  //
  // Invalid Addresses:
  //
  //    first last@host.com
  //    
  //
  /////
  function validate_email($email) {
    $valid_address = true;
    
    $mail_pat = '^(.+)@(.+)$';
    $valid_chars = "[^] \(\)<>@,;:\.\\\"\[]";
    $atom = "$valid_chars+";
    $quoted_user='(\"[^\"]*\")';
    $word = "($atom|$quoted_user)";
    $user_pat = "^$word(\.$word)*$";
    $ip_domain_pat='^\[([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\]$';
    $domain_pat = "^$atom(\.$atom)*$";
    
    if (eregi($mail_pat, $email, $components)) {
    
      $user = $components[1];
      $domain = $components[2];

      // validate user  
      if (eregi($user_pat, $user)) {
        // validate domain
        if (eregi($ip_domain_pat, $domain, $ip_components)) {
          // this is an IP address
      	  for ($i=1;$i<=4;$i++) {
      	    if ($ip_components[$i] > 255) {
      	      $valid_address = false;
      	      break;
      	    }
          }
        }
        else {
          // Domain is symbolic name
          if (eregi($domain_pat, $domain)) {
  
            /* domain name seems valid, but now make sure that it ends in a
               three-letter word (like com, net, org, gov, edu, int) or a two-letter word,
               representing country (ca, uk, nl), and that there's a hostname preceding 
               the domain or country. */
  
            $domain_components = explode(".", $domain);          
  
            // Make sure there's a host name preceding the domain.
            if (sizeof($domain_components) < 2)
              $valid_address = false;
            else {
              $top_level_domain = strtolower($domain_components[sizeof($domain_components)-1]);
              if (strlen($top_level_domain) < 2 || strlen($top_level_domain) > 3)
                $valid_address = false;
              elseif (strlen($top_level_domain) == 3) {
                switch ($top_level_domain) {
                  case 'com':
                  case 'net':
                  case 'org':
                  case 'gov':
                  case 'edu':
                  case 'int':
                    break;
                  default:
                    $valid_address = false;
                    break;
                }
              }
            }
          }
          else {
      	    $valid_address = false;
      	  }
      	}
      }
      else {
        $valid_address = false;
      }
    }
    else
      $valid_address = false;

    if ($valid_address && ENTRY_EMAIL_ADDRESS_CHECK == 1) {
      if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
        $valid_address = false;
      }
    }
    
    return $valid_address;
  }



  //////////


function filled_out($form_vars)
{
  // test that each required field has a value
  $type = gettype($form_vars); // associative array or string
  //echo "<h1>$type</h1>";
  if ($type == "array") { // array
     foreach ($form_vars as $key => $value)
     {
        if (!isset($key) || $value == "")
           return false;
     }
  } elseif ($type == "string") { // string
     if ($form_vars == "")
        return false;
  }
  return true;
}


function at_least_one_filled_out($form_vars)
{
  // test if each required field has a value
  // expects associative array
  $at_least_one_set = false;
  foreach ($form_vars as $key => $value)
  {
     if (isset($key) && ($value == ""))
        $at_least_one_set = true;
  } 
  if ($at_least_one_set)
    return true;
  else
    return false;
}


?>