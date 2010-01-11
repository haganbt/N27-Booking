<?php
// Common Display Functions

//send_mail function requires the PEAR MIME classes
//the 2 PEAR files below should be automatically available everywhere on the server
include( 'Mail.php' ) ;
include( 'Mail/mime.php' ) ;


function do_html_bar_heading($heading, $width = '100%')
{
  // print heading bar
?>

<table cellspacing="1" cellpadding="1" width="<?=$width?>" border="0">
  <tr><td align="left" class="SectionHeaderStyle"><?=$heading?>:</td></tr>
</table>

<?php
}


function do_html_right_nav_bar_top($width = 140)
{
  // start right navigation bar
?>
<table border="0" cellpadding="0" cellspacing="0" width="<?=$width?>">
<tr><td align="left" valign="top" class="BgcolorHard"><img
   src="<?=DIR_WS_IMAGES?>spacer.gif" width="<?=$width?>" height="1" alt="" /></td></tr>
<tr><td align="left" valign="top" class="BgcolorNormal">
<table border="0" cellpadding="0" cellspacing="10" width="100%" class="BgcolorNormal"><tr><td
align="left" valign="top">

<?php
}


function do_html_right_nav_bar_bottom($width = 140)
{
  // end right navigation bar
?>
</td></tr></table>
</td></tr>
<tr><td align="left" valign="top" class="BgcolorHard"><img
   src="<?=DIR_WS_IMAGES?>spacer.gif" width="<?=$width?>" height="1" alt="" /></td></tr>
</table>

<?php
}


function overlib_escape ($content) {
	// Escape
	$patterns = array ("/'/", "/#/");
	$replacements = array ("\'", "\#");
	$content = preg_replace($patterns, $replacements, $content);
	return $content;
}


/**
 * Original send_mail function
 *
 * Now deprecated and replaced with PEAR Mail and Mail_Mime classes
 **

 function send_mail($myname = false, $myemail = false,
					$contactname, $contactemail, $subject = "None", $message = "None",
					$wrap = '1', $add_footer = "0", $priority = "Normal" ) {

  // MAIL_MYNAME and MAIL_MYEMAIL should be defined constants!
  if ( ( trim( $myname ) == '' ) || ( $myname == false ) ) {
      $myname = MAIL_MYNAME ;
  }
  if ( ( trim( $myemail ) == '' ) || ( $myemail == false ) ) {
      $myemail = MAIL_MYEMAIL ;
  }

  $xPriority = '1' ; //default to High priority
  if ( $priority == 'Normal' ) {
    $xPriority = '3' ;
  }

  $toHeaders = "To: ".$contactname." <".$contactemail.">\r\n";
  $toHeaders .= "Reply-To: ".$myname." <".$myemail.">\r\n";
  if ( ( trim( $contactname ) == '' ) || ( $contactname == false ) ) {
    $toHeaders = "To: ".$contactemail."\r\n";
    $toHeaders .= "Reply-To: ".$myemail."\r\n";
  }

  $headers .= "MIME-Version: 1.0\r\n";
  // Next Line Removed for Wrapping Effect, HTML Not Needed
  //$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
  $headers .= "Content-type: text/plain; charset=us-ascii\r\n";
  $headers .= "From: ".$myname." <".$myemail.">\r\n";
  $headers .= $toHeaders ;
  $headers .= "X-Priority: " . $xPriority . "\r\n";  //1 UrgentMessage, 3 Normal
  $headers .= "X-MSMail-Priority: " . $priority . "\r\n";  // High, Normal
  $headers .= "X-Mailer: PHP";  //mailer

  if ($wrap == '1') { $message = wordwrap($message, 70); }

  if ($add_footer == "1") {
$message .= "\n\n" . do_email_divider_line() . "This message was sent automatically by the Web Calendar script.\n" . do_email_divider_line();
  } // end of if $add_footer

  if (mail($contactemail, $subject, $message, $headers)) {
		return true;
  } else {
		return false;
  }

} // end of send_mail
*/


function send_mail( $myname = false, $myemail = false,
                    $contactname, $contactemail, $subject = "None", $message = "None",
                    $wrap = '1', $add_footer = "0", $priority = "Normal", $attachments_array = false ) {

  // MAIL_MYNAME and MAIL_MYEMAIL should be defined constants!
  if ( ( trim( $myname ) == '' ) || ( $myname == false ) ) {
      $myname = MAIL_MYNAME ;
  }
  if ( ( trim( $myemail ) == '' ) || ( $myemail == false ) ) {
      $myemail = MAIL_MYEMAIL ;
  }

  $xPriority = '1' ; //default to High priority
  if ( $priority == 'Normal' ) {
    $xPriority = '3' ;
  }

  $toValue = $contactname . ' <' . $contactemail . '>' ;
  $replyToValue = $myname . ' <' . $myemail . '>' ;
  if ( ( trim( $contactname ) == '' ) || ( $contactname == false ) ) {
    $toValue = $contactemail ;
    $replyToValue = $myemail ;
  }

  $crlf = "\n";
  $hdrs = array( 'From' => $myname . ' <' . $myemail . '>',
                 'Reply-To' => $replyToValue,
                 'Subject' => $subject,
                 'X-Priority' => $xPriority,
                 'X-MSMail-Priority' => $priority ) ;

  if ( $wrap == '1' ) {
    $message = wordwrap( $message, 70 ) ;
  }
  if ( $add_footer == '1' ) {
    $message .= "\n\n" . do_email_divider_line() . "This message was sent automatically by the Web Calendar script.\n" . do_email_divider_line() ;
  }

  $mime = new Mail_mime( $crlf ) ;

  $mime->setTXTBody( $message ) ;
  //$mime->setHTMLBody( '<html><body>HTML version of msg were html e-mailing ever to be required</body></html>' ) ;

  if ( ( $attachments_array !== false ) && is_array( $attachments_array ) ) {
    $numAttachments = count( $attachments_array ) ;
    for ( $a = 0 ; $a < $numAttachments ; $a++ ) {
      $mime->addAttachment( DIR_FS_ATTACHMENTS . $attachments_array[$a] ) ;
    }
  }

  $body = $mime->get();
  $hdrs = $mime->headers( $hdrs ) ;

  $mail =& Mail::factory( 'mail' ) ;

  $mailSendResult =& $mail->send( $toValue, $hdrs, $body ) ;
  if( PEAR::isError( $mailSendResult ) ) {
    // output the error
    //echo "<b>PEAR error 5:</b> " . $mailSendResult->getMessage() ;
    return false ;
  } else {
    return true ;
  }

  //if you get here then something went wrong so:
  return false ;
}


function do_email_divider_line() {
  return ('~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~'."\n");
}


?>