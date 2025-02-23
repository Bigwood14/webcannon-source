<?php
// check for running instance if so dont bother
$cmd = 'ps -Ao "%p|%a" | grep "engine_launch.php" | egrep -v "grep|null"';
exec($cmd, $output);

// one will be us - if there is two then exit
if (count($output) > 1)
	die;

set_time_limit(0);
DEFINE('CRON',1);
require_once(dirname(__FILE__) .'/../../core/include.php');

$sql 	= "SELECT `s`.*, `m`.`threads`, `m`.`thread_wait`, `m`.`aol_check_total`, `m`.`aol_check_hits`, `m`.`id` AS `draft_id`, `m`.`from_domain` ";
$sql 	.= "FROM `schedule` `s`, `msg` `m`  WHERE (s.`state` = 3 OR s.`state` = 4 OR s.`state` = 5) AND `s`.msg_id = `m`.`id`";

$rs 		= $db->Execute($sql);
$runners 	= array();

while($rw = $rs->FetchRow())
{
	if ($rw['threads'] == 0)
	{
		$sql = "SELECT COUNT(*) AS `count` FROM `msg_to_ip` WHERE `draft_id` = '{$rw['msg_id']}';";		
		$rs2 = $db->Execute($sql);
		$rw2 = $rs2->FetchRow();

		$threads = floor(500/$rw2['count']);

		if ($threads < 1)
			$threads = 1;

		$rw['threads'] = $threads;
	}

	$id 		= $rw['id'];
	$list_file 	= "/www/celibero/no-web/celiberod/list/$id";

	/*if (!is_file($list_file))
	{
		$db->Execute("UPDATE `schedule` SET `state` = 7 WHERE `id` = '$id';");
		continue;
	}*/

	$runners[$rw['id']] = $rw;
}

$output = array();
$cmd 	= 'ps -Ao "%p|%a" | grep "bin/celiberod"';

exec($cmd, $output);

foreach ($output as $line)
{
	$parts = explode("|", $line);
	$pid 	= $parts[0];
	$args 	= explode(' ', $parts[1]);
	$id 	= $args[1];
	
	if (!isset($runners[$id]) && $id != '-c' && $id != 'celiberod')
	{
		$cmd = 'kill '.$pid;
		exec($cmd);
		continue;
	}
	else
		unset($runners[$id]);

}

foreach ($runners as $id => $runner)
{
	// Do AOL IP Test
	if (($runner['aol_check_total'] > 0 && $runner['aol_check_hits'] > 0) && ($runner['aol_check_hits'] <= $runner['aol_check_total']))
	{
		$binary_path 	= $config->values['site']['path'] . 'no-web/celiberod/bin/iptest';
		$sql 			= "SELECT * FROM `msg_to_ip` `mi`, `server_to_ip` `si`  WHERE `draft_id` = '{$runner['draft_id']}' AND `si`.`ip_id` = `mi`.`ip_id`;";
		$result 		= query($sql);
		$ips 			= array();

		while ($row = row($result))
		{
			$row['hit_count'] 	= 0;
			$ips[] 				= $row;	
		}

		$forked = 0;
		
		$config_2  		= getDBConfig('', 1);
		$lines 			= explode("\n", trim(@$config_2['AOL_IP_TEST_EMAIL']));
		$rand 			= rand(0, (count($lines)-1));
		$extra 			= '';

		if (validEmail(trim(@$lines[$rand])))
			$extra = ' -r '.$lines[$rand];

		foreach ($ips as $key => $ip)
		{
			$pid = pcntl_fork();

			if ($pid === -1)
			     die('could not fork');

			if (!$pid)
			{
				$report = '';

				for ($i=0;$i<=$runner['aol_check_total'];$i++)
				{
				
					$output = array();
					
					if (!empty($runner['from_domain']))
						$domain = $runner['from_domain'];
					else
						$domain = $ip['domain'];
	
			 		$cmd 	= "$binary_path -i {$ip['ip']} -d {$domain}".$extra;
	
					exec($cmd, $output);
					
					$string = '';
	
					foreach ($output as $line)
					{
						if (!empty($line))
							$string .= $line;
					}
	
					if (strpos($string, "{$ip['ip']} OK") === false)
						$ip['hit_count']++;
				}

				for ($i=0;$i<10;$i++)
				{
					if (!mysql_connect('localhost', 'root', 'cheese'))
					{
						sleep(5);
						continue;
					}

					mysql_select_db('celibero');
					$report = '';	
					if ($ip['hit_count'] >= $runner['aol_check_hits'])
					{
						$report .= "Removed {$ip['ip']} - Hits = {$ip['hit_count']}\n";
						$sql 	= "DELETE FROM `msg_to_ip` WHERE `draft_id` = '{$runner['draft_id']}' AND `ip_id` = '{$ip['ip_id']}';";
						mysql_query($sql);
					}
					else
						$report .= "Good {$ip['ip']} - Hits = {$ip['hit_count']}\n";

					$sql = "INSERT INTO schedule_log (schedule_id, time, message) VALUES ('$id', NOW(), '".esc($report)."');";
					mysql_query($sql);
					mysql_close();
					die;
				}

				die;
			}
			else
				$forked++;

		}

		$returned = 0;

		while (1)
		{
			pcntl_wait($status);
			$returned++;

			if ($returned >= $forked)
				break;
		}
		
		@mysql_close();

		mysql_connect('localhost', 'root', 'cheese');
		mysql_select_db('celibero');

		// count the ips we have left (if any)
		$sql 			= "SELECT * FROM `msg_to_ip` `mi`, `server_to_ip` `si`  WHERE `draft_id` = '{$runner['draft_id']}' AND `si`.`ip_id` = `mi`.`ip_id`;";
		$result 		= mysql_query($sql);
		$ips 			= array();

		while ($row = row($result))
			$ips[] 				= $row;	
	
		if ((count($ips)) < 1)
		{
			// all ips were duff cancel mailing
			$sql = "UPDATE `schedule` SET `state` = '8' WHERE `id` = '{$id}';";
			mysql_query($sql);
			$report = "All IPs failed canceling mailing\n";

			$sql = "INSERT INTO schedule_log (schedule_id, time, message) VALUES ('$id', NOW(), '".esc($report)."');";
			mysql_query($sql);
		}
		/*else
		{
			$sql = "INSERT INTO schedule_log (schedule_id, time, message) VALUES ('$id', NOW(), '".esc($report)."');";
			query($sql);
		}*/
	}
	$cmd = "/www/celibero/no-web/celiberod/bin/celiberod $id {$runner['threads']} {$runner['thread_wait']} >/dev/null  >/dev/null 2>&1 &";
	exec($cmd);
}

$sql = "UPDATE `schedule` SET `state` = '9' WHERE `state` = '8'";
mysql_query($sql);
?>
