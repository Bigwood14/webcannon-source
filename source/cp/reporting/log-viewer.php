<?PHP
require_once('../../no-web/core/include.php');

checkCPAcces();

$path 	= '/www/celibero/no-web/celiberod/results/';
$id 	= (int) $_GET['id'];

switch ($_GET['log'])
{
	case 'deferral':
		$file = 'deferral';
		break;
	case 'failure':
		$file = 'failure';
		break;
	case 'success':
		$file = 'success';
		break;
	default:
		$file = 'success';
		break;
}

$log = $path."$id.$file";

if (isset($_GET['download']))
{
	if (!empty($permissions->auth->user['mailer']))
		die('Access');	

	$name 	= basename($log).".txt";
	$fh 	= fopen($log, 'r');
	$date 	= date('D M j G:i:s T Y');

	//@ob_clean();
	header("Last-Modified: " . gmdate("D, d M Y H:i:s"));
	header("Pragma: no-cache");
	header("Expires: -1");
	header('Date: ' . $date, true);
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: plain/text; name=\"$name\"\n", true);
	header("Content-Disposition: attachment; filename=\"$name\"\n", true);
	header("Content-length: ".(string)filesize($log));
	header("Connection: close");
	fpassthru($fh);
	fclose($fh);
	exit();
}

$r = exec("tail -n1000 $log",$o);
$o = array_reverse($o);
$tpl->mailer 	= $permissions->auth->user['mailer'];
$tpl->r 		= $o;
$tpl->template 	= "cp/reporting/log_viewer.tpl.php";
$tpl->display('cp/reporting/log_viewer.tpl.php');
?>
