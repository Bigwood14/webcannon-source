<?php
require 'lib/public.php';
require_once 'lib/filter.cls.php';
require_once('functions-management.php');

function index_page ()
{
	header("HTTP/1.1 404 Not Found");

	// else show this
	print '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL '.htmlentities($_SERVER['REDIRECT_URL']).' was not found on this server.</p>
<hr>
<address>'.$_SERVER['SERVER_SOFTWARE'].' Server at '.$_SERVER['SERVER_NAME'].' Port 80</address>
</body></html>';
	die;	
}

$sql = "SELECT `value` FROM `config` WHERE `KEY` = 'INDEX_PAGE';";
$res = query($sql, true);

if ($res)
{
	$rw = row($res);

	if (@$rw['value'] == 'no' && empty($_GET['url']) && empty($_GET['e']))
	{
		/*header("HTTP/1.0 404 Not Found");
		print("<html><body>HTTP 404 - Not Found</body></html>");
		exit;*/
		index_page();
	}
}

require 'link_tracking.cls.php';
require 'list_db.cls.php';

$sql 	= "SELECT * FROM `pages`";
$result = query($sql, true);
$pages 	= array();

while ($row = row($result))
	$pages[$row['url']] = $row;

if (isset($pages[$_GET['url']]))
{
	$row = $pages[$_GET['url']];

	$email = (empty($_POST['email'])) ? @$_GET['e'] : $_POST['email'];

	$email = filter::remove_xss($email);
	
	if($email != '')
	{
		$i = 0;
		require_once('Subscribe.php');
	
		$unsubscribe = new Unsubscribe();
	
		$unsubscribe->how   = 8;
		$unsubscribe->gdne = true;
	
		$email = trim($email);
	
		$unsubscribe->setEmail(mysql_escape_string($email));
		$good = 0;
		switch($unsubscribe->doUnsub())
		{
			case 1:
			$error = "Email address $email removed.";
			$good = 1;
			break;
			case 2:
			$error = "Email address $email removed.";
			$good = 1;
			break;
			case 3:
			$error = "Email address $email removed.";
			$good = 1;
			break;
			case -1:
			$error = "Error: Invalid email address.";
			break;
		}
	}
	$content = $row['content'];
	$content = str_replace('{error}', @$error, $content);	
	$content = str_replace('{email}', $email, $content);	

	print $content;
	die;
}

define('OUTSIDE', 1);
require_once('no-web/core/include.php');
require_once('Subscribe.php');

$link_tracking 	= new link_tracking();
$lists 			= new list_db();

$values 		= $link_tracking->parse($_GET['url']);
$link_data 		= $link_tracking->get($values['link_id']);

if (empty($link_data))
{
	if (isset($_GET['e']) || isset($_POST['email']))
	{
		require_once('unsub.php');
		exit;
	}
	else
	{
		index_page();
		/*header("location: /unsub.php");
		exit;*/
	}
}

$email 			= false;

// update click count
$sql 		= "UPDATE `tracked_link` SET `count` = `count`+1 WHERE `tracked_link_id` = '{$values['link_id']}';";
query($sql);

if (!empty($values['table']) && !empty($values['user_id']))
{
	$id 		= $values['table'].$values['user_id'];
	$list_data 	= $lists->get($values['list_id']);

	$email 		= getInfoFromID($id, $list_data['name']);
}

if (!empty($email))
{
	$mask = 0;
	if($email['mask'] == 1) $mask = 3;
	if($email['mask'] == 0) $mask = 2;
    
	if($mask != 0)
	{
		$tbl = $Lists->whatTableID($values['table']);
		$sql = "UPDATE `{list}`.`$tbl` SET mask = '$mask' WHERE id = '".substr($id, 2)."';";
		$Lists->query_list($list_data['username'], $sql);
	}

	$email = $email['local'].'@'.$email['domain'];

	$sql 		= "INSERT INTO `clicks` (`email`) VALUES ('$email');";
	query($sql, true);

	$ip 	= esc(@$_SERVER['REMOTE_ADDR']);
	$ref 	= esc(@$_SERVER['HTTP_REFERER']);

	$sql = "INSERT INTO `tracked_link_click` (`tracked_link_id`, `email`, `datetime`, `ip`, `ref`) VALUES ('{$values['link_id']}', '$email', NOW(), '$ip', '$ref');";
	query($sql, true);

	if (!empty($link_data['list_id']))
	{
		$target_list_info 	= $lists->get($link_data['list_id']);
		$target_list 		= $target_list_info['name'];
	}

	// subscribe
	if ($link_data['action'] == 2)
	{
		$sub = new Subscribe;
        $sub->setEmail($email);
        $sub->setList($target_list);
        $sub->doSub();

		// update click list
		if ($target_list_info['remote_list_id'] > 0)
		{
			$sql = "INSERT INTO `clicks` (`email`, `remote_list_id`) VALUES ('$email', '{$target_list_info['remote_list_id']}');";
			query($sql, true);
		}
	}
	elseif ($link_data['action'] == 3)
	{
		$unsub = new Unsubscribe;
        $unsub->setEmail($email);
        $unsub->setList($target_list);
        $unsub->doUnsub();
	}
}
$link_data['url'] = str_replace('{{email}}', @$email, $link_data['url']);
redirect($link_data['url']);
die;
?>
