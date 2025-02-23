<?php
// check for running instance if so dont bother
$cmd = 'ps -Ao "%p|%a" | grep "SGDNE-get.php" | egrep -v "grep|null"';
exec($cmd, $output);

// one will be us - if there is two then exit
if (count($output) > 1)
	die;

set_time_limit(0);
DEFINE('CRON',1);
require_once(dirname(__FILE__) .'/../../core/include.php');


require_once(dirname(__FILE__) .'/../../core/include.php');
require_once(dirname(__FILE__) .'/../../core/functions-management.php');
require_once("Subscribe.php");

$sql = "SELECT * FROM sgdne";

$info = $db->GetRow($sql);

if(!$link = mysql_connect($info['hostname'],$info['username'],$info['password']))
{
	logMessage("sgdne","Invalid SGDNE credentials",0);
}
else
{
	$sql = "SELECT * FROM emails WHERE date >= '".$info['last_checkin']."' ORDER BY date LIMIT 0,1000000";
	mysql_select_db($info['database']);
	if(!$r = mysql_query($sql,$link))
	{
		logMessage("sgdne","Could not make get select (".mysql_error($link).")",0);
	}
	else
	{
		$i = -1;
		$j = 1;
		$k = 0;

		$unsubscribe		= new Unsubscribe();
		$unsubscribe->how   = 2;
		$unsubscribe->gdne  = true;

		while($rw = mysql_fetch_array($r))
		{
			if($k == 500)
			{
				print "SGDNE EMails Get: ".$k*$j."\n";
				$k = 0;
				$j ++;
			}

			$unsubscribe->setEmail(trim($rw['email']));
			$unsubscribe->doUnsub();
		   
			$unsubscribe->reset();

			$i ++;
			$k ++;
			$last = $rw;
		}

		if($i > 0)
		{
			logMessage("sgdne","Collected $i emails",0);

			$sql = "UPDATE sgdne SET last_checkin = '".$last['date']."', last_email = '".$last['email']."'";
			$db->Execute($sql);
		}
		else
		{
			logMessage("sgdne","Nothing new (get emails)",0);
		}
	}

	/* ------ Bounce Email ------ 
	$sql = "SELECT * FROM emails_bounce WHERE date >= '".$info['last_checkin_bounce']."' ORDER BY date LIMIT 0,1000000";

	if (!$r = mysql_query($sql,$link))
		logMessage("sgdne","Could not make get select (".mysql_error($link).")",0);
	else
	{
		$i = -1;
		$j = 1;
		$k = 0;

		$unsubscribe		= new Unsubscribe();
		$unsubscribe->how   = 3;
		$unsubscribe->gdne  = true;

		while($rw = mysql_fetch_array($r))
		{
			if($k == 500)
			{
				print "SGDNE Bounce EMails Get: ".$k*$j."\n";
				$k = 0;
				$j ++;
			}

			$unsubscribe->setEmail(trim($rw['email']));
			$unsubscribe->doUnsub();
		   
			$unsubscribe->reset();

			$i ++;
			$k ++;
			$last = $rw;
		}

		if($i > 0)
		{
			logMessage('sgdne', 'Collected '.$i.' bounce emails', 0);

			$sql = "UPDATE sgdne SET last_checkin_bounce = '".$last['date']."', last_email_bounce = '".$last['email']."'";
			$db->Execute($sql);
		}
		else
			logMessage('sgdne', 'Nothing new (get bounce emails)', 0);
	}*/

	/* ------ Domains ------- */
	$sql = "SELECT * FROM domains WHERE date >= '".$info['last_checkin_domain']."' ORDER BY date ASC LIMIT 0,100000";

	if(!$r = mysql_query($sql,$link))
	{
		logMessage("sgdne","Could not make get select (domain) (".mysql_error($link).")",0);
	}
	else
	{
		$i = -1;
		$j = 1;
		$k = 0;

		$unsubscribe_domain		= new Unsubscribe_Domain();
		$unsubscribe_domain->how   = 2;
		$unsubscribe_domain->gdne  = true;

		while($rw = mysql_fetch_array($r))
		{

			if($k == 500)
			{
				print "SGDNE Domain Get: ".$k*$j."\n";
				$k = 0;
				$j ++;
			}

			$unsubscribe_domain->setDomain(trim($rw['domain']));

			switch($unsubscribe_domain->doUnsub())
			{
				case 1:
				//$c['unsub_g'] ++;
				break;
				case 2:
				//$c['unsub_g'] ++;
				break;
				case 3:
				//$c['removed_sgdne'] ++;
				break;
				case -1:
				//$c['invalid'] ++;
				break;
			}

			$unsubscribe_domain->reset();

			$i ++;
			$k ++;
			$last = $rw;
		}

		if($i > 0)
		{
			logMessage("sgdne","Collected $i domains",0);

			$sql = "UPDATE sgdne SET last_checkin_domain = '".$last['date']."', last_domain = '".$last['domain']."'";
			$db->Execute($sql);
		}
		else
		{
			logMessage("sgdne","Nothing new (get domain)",0);
		}
	}

	/* ------ Words ------- */
	$sql = "SELECT * FROM words WHERE date >= '".$info['last_checkin_word']."' LIMIT 0,100000";

	if(!$r = mysql_query($sql,$link))
	{
		logMessage("sgdne","Could not make get select (word) (".mysql_error($link).")",0);
	}
	else
	{
		$i = -1;
		$j = 1;
		$k = 0;

		$unsubscribe_word		= new Unsubscribe_Word();
		$unsubscribe_word->how   = 2;
		$unsubscribe_word->gdne  = true;

		
		$unsubscribe_word->doUnsub();

		while($rw = mysql_fetch_array($r))
		{

			if($k == 500)
			{
				print "SGDNE Domain Get: ".$k*$j."\n";
				$k = 0;
				$j ++;
			}
			
			$unsubscribe_word->setWord($rw['word']);

			switch($unsubscribe_word->doUnsub())
			{
				case 1:
				//$c['unsub_g'] ++;
				break;
				case 2:
				//$c['unsub_g'] ++;
				break;
				case 3:
				//$c['removed_sgdne'] ++;
				break;
				case -1:
				//$c['invalid'] ++;
				break;
			}

			$unsubscribe_word->reset();

			$i ++;
			$k ++;
			$last = $rw;
		}

		if($i > 0)
		{
			logMessage("sgdne","Collected $i words",0);

			$sql = "UPDATE sgdne SET last_checkin_word = '".$last['date']."', last_word = '".$last['word']."'";
			$db->Execute($sql);
		}
		else
		{
			logMessage("sgdne","Nothing new (get word)",0);
		}
	}
}
?>
