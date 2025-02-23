#!/usr/bin/php -q
<?PHP
set_time_limit(0);
$inc_path = dirname(__FILE__) . '/';

require_once($inc_path . '../../core/include.php');
require_once("Subscribe.php");
require_once('public.php');
require_once('link_tracking.cls.php');
require_once('list_db.cls.php');
require_once('complaint.cls.php');

$c = getDBConfig("COMPLAINT_EMAIL",1);
$c_email = $c['value'];

// read from stdin
$fd 		= fopen('php://stdin', 'r');
$email_c 	= '';

while (!feof($fd))
    $email_c .= fread($fd, 1024);

fclose($fd);

$complaint = new complaint();
$complaint->parse($email_c);

$parts = explode('@', $c_email);

if(isset($parts[1]))
{
	$body  = "To: $c_email\nFrom: complaints@".getDefaultDomain()."\nSubject: Complaint Response\nDate: ".date("r")."\n\n";
	$body .= "This is a complaint that has been processed see report below\n".$complaint->report."\n\n";
	$body .= "Below is a copy of the orignal email\n\n$email_c\n.\n";

	$file 	= $config->values['site']['path'] . 'cp/scheduling/test/complaint';
	$fh 	= fopen($file, "w+");

	fwrite($fh, str_replace("\n", "\r\n", str_replace("\r", "", $body)));
	fclose($fh);

	exec("cd ".$config->values['site']['path']."cp/scheduling/test/; /var/qmail/bin/qmail-remote ".$parts[1]." complaints@".getDefaultDomain()." $c_email < complaint", $o, $r);
}
?>
