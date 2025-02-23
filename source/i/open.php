<?php
require '../lib/public.php';
require_once '../lib/filter.cls.php';
require_once('functions-management.php');

require 'link_tracking.cls.php';
require 'list_db.cls.php';

define('OUTSIDE', 1);
require_once('../no-web/core/include.php');
require_once('Subscribe.php');

$parts = explode('.', $_GET['url']);
$link_tracking 	= new link_tracking();
$lists 			= new list_db();

$values 		= $link_tracking->parse($parts[0]);

$mailing_id 	= @esc($values['link_id']);
$table 			= @$values['table'];
$user_id 		= @$values['user_id'];
$list_id 		= @$values['list_id'];

if (!empty($mailing_id))
{
	// log a count
	$sql = "UPDATE `schedule` SET `opens` = `opens`+1 WHERE `id` = '$mailing_id';";
	query($sql, true);

	if (!empty($table) && !empty($user_id) && !empty($list_id))
	{
		$id 		= $table.$user_id;
		$list_data 	= $lists->get($list_id);

		$email 		= getInfoFromID($id, $list_data['name']);

		if (!empty($email))
		{
			$email 		= $email['local'].'@'.$email['domain'];

			$mailing_id = esc($mailing_id);
			$sql 		= "INSERT INTO `opens` (`schedule_id`, `email`, `date`) VALUES ('$mailing_id', '$email', NOW());";
			query($sql, true);

			$sql 		= "SELECT `m`.`open_action`, `m`.`open_list_id` FROM `msg` `m`, `schedule` `s` WHERE `m`.`id` = `s`.`msg_id` AND `s`.`id` = '$mailing_id';";
			if ($res = query($sql, true))
			{
				$row = row($res);
				if (!empty($row))
				{
					if ($row['open_action'] > 1)
					{
						$target_list_info 	= $lists->get($row['open_list_id']);
						$target_list 		= $target_list_info['name'];

						if (!empty($target_list))
						{
							if ($row['open_action'] == 2)
							{
								$sub = new Subscribe;
						        $sub->setEmail($email);
						        $sub->setList($target_list);
						        $sub->doSub();

								if ($target_list_info['remote_list_id'] > 0)
								{
									$sql = "INSERT INTO `clicks` (`email`, `remote_list_id`) VALUES ('$email', '{$target_list_info['remote_list_id']}');";
									query($sql, true);
								}
							}
							else if ($row['open_action'] == 3)
							{
								$unsub = new Unsubscribe;
						        $unsub->setEmail($email);
								$unsub->setList($target_list);
								$unsub->doUnsub();
							}
						}
					}
				}
			}
		}
	}
}

header('Content-Type: image/gif');
header('Content-Length: ' . filesize('trans.gif'));
$fp = fopen('trans.gif', 'rb');
fpassthru($fp);
exit;
?>
