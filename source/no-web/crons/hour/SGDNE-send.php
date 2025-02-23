<?php
set_time_limit(0);
require_once(dirname(__FILE__) .'/../../core/include.php');
require_once(dirname(__FILE__) .'/../../core/functions-management.php');


$sql = "SELECT * FROM sgdne";
$info = $db->GetRow($sql);

if(!$link = mysql_connect($info['hostname'],$info['username'],$info['password']))
{
    logMessage("sgdne","Invalid SGDNE credentials",0);
}
else
{
    mysql_select_db($info['database']);
    //print mysql_error($link);
    
	$sql 	= "SELECT * FROM global_unsub WHERE global_action = '1' LIMIT 0,100000";
    $rs 	= $db->Execute($sql);
    $i 		= 0;
    $server = getDefaultDomain();

    while ($rw = $rs->FetchRow())
    {
        $sql = "INSERT INTO incoming_email (`email`,`server`,`ts`) VALUES ('".$rw['address']."','$server',NOW())";
        mysql_query($sql,$link);
        print mysql_error($link);
        $sql = "UPDATE global_unsub SET global_action = '2' WHERE address = '".$rw['address']."'";
        $db->Execute($sql);
        
        $i ++;
    }

    logMessage("sgdne","Sent emails $i",0);

	// send clicks
	$sql 	= "SELECT * FROM `clicks` WHERE `sent` = 0 LIMIT 0, 10000";
	$rs 	= $db->Execute($sql);
	$i 		= 0;
	$server = getDefaultDomain();

	while ($rw = $rs->FetchRow())
	{
		$sql 		= "INSERT INTO `incoming_clicks` (`email`, `server`, `ts`) VALUES ('".$rw['email']."', '$server', NOW());";
		$result 	= mysql_query($sql, $link);

		/*if (!$result)
		{
			print mysql_error();
			continue;
		}*/

		$sql 	= "UPDATE `clicks` SET `sent` = 1 WHERE `email` = '".$rw['email']."';";
		$db->Execute($sql);
		$i++;
	}

}
?>
