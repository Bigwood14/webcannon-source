<?php
// check for running instance if so dont bother
$cmd = 'ps -Ao "%p|%a" | grep "spam_check.php" | egrep -v "grep|null"';
exec($cmd, $output);

// one will be us - if there is two then exit
if (count($output) > 1)
	die('Die');

set_time_limit(0);
DEFINE('CRON',1);
require_once(dirname(__FILE__) .'/../../core/include.php');
require_once('spam_check.cls.php');

$sql 	= "SELECT * FROM `seed_account`;";
$res 	= query($sql);
$accnts = array();

while ($row = row($res))
	$accnts[$row['username']] = $row;

$sql  		= "SELECT `s`.*, `m`.`seed_rotate`, `m`.`seeds` ";
$sql 		.= "FROM `schedule` `s`, `msg` `m`  WHERE `m`.`seed_rotate` > 0 AND `s`.msg_id = `m`.`id` AND ((s.`state` = 4 OR s.`state` = 5) OR (DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= `s`.`end_time`))";
$res  		= query($sql);
$mailings 	= array();

while ($row = row($res))
{
	// see which account applies to mailing
	$seeds = explode("\n", $row['seeds']);

	foreach ($seeds as $seed)
	{
		$parts = explode(':', $seed);
		$email = $parts[0];

		if (!validEmail($email))
			continue;

		if (!isset($accnts[$email]))
			continue;

		// find the link ids and ips to check for
		$sql 		= "SELECT * FROM `tracked_link` WHERE `draft_id` = '{$row['msg_id']}';";
		$r 			= query($sql);
		$link_ids 	= array();

		while ($rw = row($r))
			$links_ids[] = $rw['tracked_link_id'];
			
		$sql 		= "SELECT `si`.`ip`, `si`.`ip_id`, `mi`.`spam_seed_count` FROM `msg_to_ip` `mi`, `server_to_ip` `si` WHERE `mi`.`draft_id` = '{$row['msg_id']}' AND `si`.`ip_id` = `mi`.`ip_id`;";
		$r 			= query($sql);
		$ips 		= array();
		$m_ips 		= array();

		while ($rw = row($r))
		{
			$ips[] 				= $rw['ip'];
			$m_ips[$rw['ip']] 	= $rw;
		}

		$sql 		= "SELECT * FROM `msg_seed_uid` WHERE `msg_id` = '{$row['msg_id']}';";
		$r 			= query($sql);
		$uids 		= array();

		while ($rw = row($r))
			$uids[] = $rw['uid'];

		$mailing = array(
			'mailing_id' 	=> $row['msg_id'],
			'link_ids' 		=> $links_ids,
			'ips' 			=> $ips,
			'uids' 			=> $uids
		);

		$m					= $row;
		$m['ips'] 				= $m_ips;

		$mailings[$row['msg_id']] = $m;

		@$accnts[$email]['mailing'][] 	= $mailing;
	}
}
print_r($mailings);
// now loop the accounts and check each one
foreach ($accnts as $account)
{
	if (empty($account['mailing']))
		continue;

	$spam 		= new spam_check();
	$account_id = $spam->account('aol', $account['username'], $account['password'], 'imap');
	
	if ($account_id < 0)
		die("Account ID {$account_id}\n");
	
	foreach ($account['mailing'] as $check)
	{
		if (!$spam->add_check($account_id, $check['mailing_id'], $check['link_ids'], $check['ips'], $check['uids']))
			die("Add Check\n");
	}

	$accounts = $spam->check();

	foreach ($accounts as $account)
	{
		foreach ($account['check'] as $check)
		{
			$msg_id = $check['mailing_id'];

			// update counts
			if (!empty($check['hits']))
			{
				foreach ($check['hits'] as $ip => $count)
				{
					$ip_id = $mailings[$msg_id]['ips'][$ip]['ip_id'];
					
					$mailings[$msg_id]['ips'][$ip]['spam_seed_count'] += $count;

					$sql 	= "UPDATE `msg_to_ip` SET `spam_seed_count` = `spam_seed_count` +{$count} WHERE `ip_id` = '$ip_id' AND `draft_id` = '$msg_id';";
					query($sql);

					if ($mailings[$msg_id]['ips'][$ip]['spam_seed_count'] >= $mailings[$msg_id]['seed_rotate'])
					{
						$sql = "UPDATE `msg_to_ip` SET `removed` = 1 WHERE `ip_id` = '$ip_id' AND `draft_id` = '$msg_id';";
						query($sql);
					}

				}
			}

			foreach ($check['ids'] as $id)
			{
				$sql = "INSERT INTO `msg_seed_uid` (`msg_id`, `uid`) VALUES ('$msg_id', '$id');";
				query($sql, true);
			}
		}
	}
}
?>
