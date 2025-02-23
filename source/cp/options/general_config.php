<?PHP
require_once('../../no-web/core/include.php');

checkCPAcces();

$emails = array('POSTMASTER', 'FEEDBACK');

if(isset($_POST['submit']))
{
    //print_r($_POST);
	foreach ($emails as $email)
	{
		$content = '';
		
		if (validEmail($_POST['cfg'][$email.'_EMAIL']))
			$content .= "&{$_POST['cfg'][$email.'_EMAIL']}\n";
	   
	   	if ($_POST['cfg'][$email.'_COMPLAINT'] == 'yes')
			$content .= "|/www/celibero/no-web/celiberod/bin/complaint.php";
	  
	  	$content 	= trim($content);
		$file 		= '/var/qmail/alias/.qmail-'.strtolower($email);
	   	$cmd 		= "echo '$content' > $file";
	    $cmd 		= mysql_real_escape_string($cmd);
		$sql 		= "INSERT INTO commands (`command`, `date`, `state`) VALUES ('$cmd', NOW(), '0');";
		$db->Execute($sql);
	}

    foreach($_POST['cfg'] AS $k => $v)
    {
		$k = mysql_real_escape_string($k);
        $sql = "REPLACE INTO config SET `value` = '".mysql_real_escape_string(stripslashes($v))."', `KEY` = '$k';";
        $db->Execute($sql);
        print mysql_error();
    }
    
    $tpl->complete = 1;
}

$config2 = getDBConfig('',1);
//print_r($config2);
$tpl->emails 	= $emails;
$tpl->config2 	= $config2;
$tpl->template 	= "cp/options/general_config.php";
$tpl->display('cp/layout.php');
?>
