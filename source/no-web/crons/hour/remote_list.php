#!/usr/bin/php -q
<?php
set_time_limit(0);

// check for running instance if so dont bother
$cmd = 'ps -Ao "%p|%a" | grep "remote_list.php" | egrep -v "grep|null"';
exec($cmd, $output);

// one will be us - if there is two then exit
if (count($output) > 1)
	die;

require_once(dirname(__FILE__) .'/../../core/include.php');
require_once('Subscribe.php');
require_once('prime_api.cls.php');

function api_log ($list_id, $message)
{
	$list_id 	= esc($list_id);
	$message 	= esc($message);

	$sql 		= "INSERT INTO `list_log` (`list_id`, `date`, `message`) VALUES ('$list_id', NOW(), '$message');";
	query($sql);
}


function get_mem ()
{
	$pid = getmypid();
	exec("ps -eo%mem,rss,pid | grep $pid", $output);
	//$output = explode("  ", $output[0]);
	$output = preg_split('/[\s]+/', $output[0]);
	//rss is given in 1024 byte units
	$bytes = ($output[2] * 1024); 
	$kb 	= $bytes/1024;
	$mb 	= $kb/1024;

	return $mb;
}

for($loops=0;$loops<50;$loops++)
{
	$got 	= false;

	/* Get subs & unsubs */

	$sql 	= "SELECT * FROM `list` WHERE `remote_list_id` != 0;";
	$result = query($sql);
	$rlists = array();

	while ($row = row($result))
		$rlists[] = $row;

	foreach ($rlists as $row)
	{
		echo "Memcheck 1: ". number_format(get_mem(true)) . " - {$row['name']}\n";
		if (!empty($api))
			unset($api);

		$api = new prime_api($row['remote_hostname'], false);
		
		if (!$api->login($row['remote_username'], $row['remote_password']))
		{
			api_log($row['list_id'], $api->error_detail());
			continue;
		}
	
		if (!$data = $api->list_get_file($row['remote_list_id'], $row['remote_position']))
		{
			api_log($row['list_id'], $api->error_detail());
			continue;
		}
		
		echo "Memcheck 1.1: ". number_format(get_mem(true)) . " - {$row['name']}\n";
		$i = 0;
		if (!empty($subscribe))
			unset($subscribe);

		$subscribe 			= new Subscribe();
	    $subscribe->list  	= $row['name'];
	    $subscribe->how 	= 8;
		$c 					= array();

		if (!empty($unsubscribe))
			unset($unsubscribe);

		$unsubscribe 		= new Unsubscribe();
	    $unsubscribe->list  = $row['name'];
	    $unsubscribe->how   = 8;
		$cu					= array();

		$count = count($data['r']->recipients->recipient);

		//foreach ($data['r'] as $recip)
		for ($i=0;$i<$count;$i++)
		{
			$recip 		= array();
			$recip['email'] = (string)$data['r']->recipients->recipient[$i]->email;
			$recip['act']   = (string)$data['r']->recipients->recipient[$i]['a'];

			$i++;

			$email 	= trim($recip['email']);

			if ($recip['act'] == 1)
			{	
				$subscribe->email = $email;
				
				switch ($subscribe->doSub())
				{
					case 1:
						@$c['add'] ++;
						break;
					case -1:
						@$c['invalid'] ++;
						break;
					case -2:
						@$c['dup'] ++;
						break;
					case -3:
						@$c['unsub'] ++;
						break;
					case -4:
						@$c['unsub_g'] ++;
						break;
					case -5:
						@$c['unsub_g_d'] ++ ;
						break;
					case -6:
						@$c['unsub_g_w'] ++ ;
						break;
				}
	
				@$c['total']++;	
		        $subscribe->reset();
		
				//print "GOT: {$recip['email']}\n";
			}
			else
			{
				$unsubscribe->setEmail($email);
	        
				switch($unsubscribe->doUnsub())
				{
					case 1:
						@$cu['removed'] ++;
						break;
					case 2:
						@$cu['removed_gdne'] ++;
						break;
					case 3:
						@$cu['removed_sgdne'] ++;
						break;
					case -1:
						@$cu['invalid'] ++;
						break;
				}
		
				@$cu['total']++;		
		        $unsubscribe->reset();
		
				
				//print "GOT UNSUB: {$recip['email']}\n";
			}

			$got = true;
		}

		echo "Memcheck 2: ". number_format(get_mem(true)) . "\n";

		$position 	= esc(@$data['p']);
		$sql 		= "UPDATE `list` SET `remote_position` = '$position' WHERE `list_id` = '{$row['list_id']}';";
		query($sql);
	
		$msg 		= @"Added [{$c['add']}], Invalid [{$c['invalid']}], Dup [{$c['dup']}], Unsub [{$c['unsub']}], Unsub G [{$c['unsub_g']}], Unsub G D [{$c['unsub_g_d']}], Unsub G W [{$c['unsub_g_w']}] ";
		api_log(@$row['list_id'], @"Got [{$c['total']}] emails.\n$msg\n Position [$position].");
	
		$msg = @"Removed: [{$cu['removed']}], Invalid [{$cu['invalid']}]";	
		api_log(@$row['list_id'], @"Got [{$cu['total']}] unsub emails.\n$msg\n Position [$position].");

		unset($data);
			
		if ($row['send_unsubs'] > 0)
		{
			$sql 	= "SELECT * FROM `celibero_list_{$row['list_id']}`.`slog` WHERE `remote_sent` = 0 AND `how` != 8 LIMIT 0, 50000;";
			$result = query($sql);
	
			$emails = array();
			$i 		= 0;
	
			while ($slog = row($result))
			{
				switch ($slog['how'])
				{
					case 4:
						$how = 1;
						break;
					case 3:
					case 5:
					case 6:
						$how = 3;
						break;
					default:
						$how = 2;
						break;
				}
	
				$emails[] =	$slog['local'].'@'.$slog['domain'].':'.$how;
				$i++;

				if ($i >= 50000)
				{
					$got = true;
					break;
				}
			}
	
			if (!$api->list_send_unsub($row['remote_list_id'], $emails))
			{
				api_log($row['list_id'], $api->error_detail());
			}

			foreach ($emails as $email)
			{
				$parts 	= explode(':', $email);
				$email 	= $parts[0];
				//print "Sent Unsub $email {$row['remote_list_id']}\n";
				list($local, $domain) 	= explode('@', $email);
				$sql 					= "UPDATE `celibero_list_{$row['list_id']}`.`slog` SET `remote_sent` = 1 WHERE `local` = '$local' AND `domain` = '$domain';";
				query($sql);
			}
	
			api_log($row['list_id'], "Sent [$i] unsub emails.\n.");
		}
		
		echo "Memcheck 3: ". number_format(get_mem(true)) . "\n";
	
		// send subs
		$sql  	= "SELECT * FROM `clicks` WHERE `remote_list_id` = '{$row['remote_list_id']}' AND `remote_sent` = 0;";
		$result = query($sql);
		$emails = array();
		$i  	= 0;
		while ($clicks = row($result))
		{
			// do 200 a time		
			$emails[] = $clicks['email'];
			
			$i++;

			if ($i >= 5000)
			{
				$got = true;
				break;
			}
		}

		if (!$api->list_send_sub($row['remote_list_id'], $emails))
			api_log($row['list_id'], $api->error_detail());
		else
			api_log($row['list_id'], "Sent [$i] sub emails.\n.");

		foreach ($emails as $email)
		{
			$sql = "UPDATE `clicks` SET `remote_sent` = 1 WHERE `email` = '$email' AND `remote_list_id` = '{$row['remote_list_id']}';";
			query($sql);
		}

		echo "Memcheck 4: ". number_format(get_mem(true)) . "\n";

	}

	if ($got == false)
		break;
}
?>
