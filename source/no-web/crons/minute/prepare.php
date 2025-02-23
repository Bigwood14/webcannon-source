<?PHP
set_time_limit(0);
require_once(dirname(__FILE__) .'/../../core/include.php');
require_once('draft.cls.php');
prepare();

function prepare()
{
	global $db,$config,$Lists;

	$draft_suppression 	= new draft_suppression();

	$sql = "SELECT COUNT(*) AS count FROM schedule WHERE state > '0' AND state < '3'";
	$info = $db->GetRow($sql);

	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	if($info['count'] > 0)
	{
		//Prepare already running let it finish
		return;
	}

	$sql = "SELECT COUNT(*) AS count FROM schedule WHERE state > '2' AND state < '7'";
	$info = $db->GetRow($sql);

	if($info['count'] > 1)
	{
		// Already one in the pipe no prepare until its done otherwise
		// miss unsubscribes.
		echo "Prepare exit waiting mailing finish\n";
		return;
	}


	$sql = "SELECT * FROM schedule WHERE state = '0' AND scheduled_time < NOW() ORDER BY scheduled_time ASC LIMIT 0,1;";
	$info = $db->GetRow($sql);

	if(isset($info['id']))
	{
		$db->Execute("INSERT INTO schedule_log (schedule_id,time,message) VALUES ('".$info['id']."',NOW(),'Started Prepare');");
	
		$prepare_file = '/www/celibero/no-web/celiberod/bin/prepare';

		if (!is_file($prepare_file))
			$prepare_file = '/home/celibero/bin/prepare';

		$cmd = $prepare_file.' '.$info['id'];

		$sql 	= "UPDATE `schedule` SET state = '1' WHERE `id` = '".$info['id']."';";
		$db->Execute("UPDATE `schedule` SET state = '1' WHERE `id` = '".$info['id']."';");
		print $db->ErrorMsg();
		exec($cmd, $output, $return);	
		var_dump($cmd, $output, $return);
	/*	if($r === false)
		{
			logMessage('schedule',"Could not update table to trasnfering Id: ".$info['id']." (".$db->ErrorMsg().") ($sql).");
			return;
		}*/

		$db->Execute("INSERT INTO schedule_log (schedule_id,time,message) VALUES ('".$info['id']."',NOW(),'Ended Prepare');");


	}
}
?>
