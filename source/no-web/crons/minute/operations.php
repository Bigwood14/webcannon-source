<?php
// Dont want to timeout!
set_time_limit(0);
// Main files prob already there
require_once(dirname(__FILE__) .'/../../core/include.php');
require_once("Subscribe.php");
do_operations();

function do_operations()
{
	global $db, $config, $Lists;

	$sql = "SELECT count(*) as count FROM operations WHERE state = 'processing'";
	$info = $db->GetRow($sql);

	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	// We have runners exit
	if($info['count'] > 0)
	{
		return false;
	}

	$sql = "SELECT * FROM operations WHERE state = '0' ORDER BY date_in ASC LIMIT 0,1;";
	$info = $db->GetRow($sql);

	if(!isset($info['operation_id']))
	{
		return false;
	}

	if($info['type'] == 'reset_bounce')
	{
		$lists2 = unserialize($info['data']);

		foreach($lists2 as $list_id)
		{
			$list_info 			= $db->GetRow("SELECT * FROM `list` WHERE list_id = '$list_id';");
			$sql 				= "SELECT local, domain FROM `{list}`.slog WHERE how = '5';";
			$rs   				= $Lists->query_list($list_info['username'], $sql);
			$subscribe        	= new Subscribe();
			$subscribe->list  	= $list_info['username'];

			while($rw = $rs->FetchRow())
			{
				$email 				= $rw['local'] . '@' . $rw['domain'];
				$email 				= trim($email);
				$subscribe->email 	= $email;

				$subscribe->doSub();
				$subscribe->reset();
			}
		}
	}

	$sql = "UPDATE operations SET state = 'completed', date_end = NOW() WHERE operation_id = '".$info['operation_id']."'";
	$r = $db->Execute($sql);
}
?>
