<?php
/*
 * test_smtp_message.php
 *
 * @(#) $Header: /celibero/celibero/no-web/core/mail/mimemessage/test_smtp_message.php,v 1.1.1.1 2005/05/02 18:23:19 tom Exp $
 *
 */

	require("email_message.php");
	require("smtp_message.php");
	require("smtp.php");
	/* Uncomment when using SASL authentication mechanisms */
	/*
	require("sasl.php");
	*/

	$from_name=getenv("USERNAME");
	$from_address="me@address.com";
	$reply_name=$from_name;
	$reply_address=$from_address;
	$reply_address=$from_address;
	$error_delivery_name=$from_name;
	$error_delivery_address=$from_address;
	$to_name="Manuel Lemos";
	$to_address="mlemos@acm.org";
	$subject="Testing Manuel Lemos' Email SMTP sending PHP class";
	$message="Hello ".strtok($to_name," ").",\n\nThis message is just to let you know that your SMTP e-mail sending class is working as expected.\n\nThank you,\n$from_name";
	$email_message=new smtp_message_class;

	/* This computer address */
	$email_message->localhost="localhost";

	/* SMTP server address, probably your ISP address */
	$email_message->smtp_host="localhost";

	/* Deliver directly to the recipients destination SMTP server */
	$email_message->smtp_direct_delivery=0;

	/* In directly deliver mode, the DNS may return the IP of a sub-domain of
	 * the default domain for domains that do not exist. If that is your
	 * case, set this variable with that sub-domain address. */
	$email_message->smtp_exclude_address="";

	/* If you use the direct delivery mode and the GetMXRR is not functional,
	 * you need to use a replacement function. */
	/*
	$_NAMESERVERS=array();
	include("rrcompat.php");
	$email_message->smtp_getmxrr="_getmxrr";
	*/

	/* authentication user name */
	$email_message->smtp_user="";

	/* authentication realm or Windows domain when using NTLM authentication */
	$email_message->smtp_realm="";

	/* authentication workstation name when using NTLM authentication */
	$email_message->smtp_workstation="";

	/* authentication password */
	$email_message->smtp_password="";

	/* if you need POP3 authetntication before SMTP delivery,
	 * specify the host name here. The smtp_user and smtp_password above
	 * should set to the POP3 user and password*/
	$email_message->smtp_pop3_auth_host="";

	/* Output dialog with SMTP server */
	$email_message->smtp_debug=0;

	/* if smtp_debug is 1,
	 * set this to 1 to make the debug output appear in HTML */
	$email_message->smtp_html_debug=1;

	$email_message->SetEncodedEmailHeader("To",$to_address,$to_name);
	$email_message->SetEncodedEmailHeader("From",$from_address,$from_name);
	$email_message->SetEncodedEmailHeader("Reply-To",$reply_address,$reply_name);
	$email_message->SetHeader("Return-Path",$error_delivery_address);
	$email_message->SetEncodedEmailHeader("Errors-To",$error_delivery_address,$error_delivery_name);
	$email_message->SetEncodedHeader("Subject",$subject);
	$email_message->AddQuotedPrintableTextPart($email_message->WrapText($message));
	$error=$email_message->Send();
	for($recipient=0,Reset($email_message->invalid_recipients);$recipient<count($email_message->invalid_recipients);Next($email_message->invalid_recipients),$recipient++)
		echo "Invalid recipient: ",Key($email_message->invalid_recipients)," Error: ",$email_message->invalid_recipients[Key($email_message->invalid_recipients)],"\n";
	if(strcmp($error,""))
		echo "Error: $error\n";
	echo "Done.\n";
?>