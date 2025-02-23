<?php
require_once('../no-web/core/include.php');
checkCPAcces();
header('Location: /cp/scheduling/delivery-queue.php');
die;

$tpl->msg = '';

$now = mktime();
//$db->debug = 1;
// Check Minute Cron
$min_time = date('Y-m-d h:i:s', ($now - 120));
$sql = "SELECT COUNT(*) AS count FROM crons WHERE name = 'minute' AND last_checkin < '$min_time'";
$test_m = $db->GetRow($sql);

if($test_m['count'] > 0)
{
	$tpl->msg[] = 'Error minute cron not running';
}

// Check Hour Cron
$hour_time = date('Y-m-d h:i:s', ($now - 7200));
$sql = "SELECT COUNT(*) AS count FROM crons WHERE name = 'hour' AND last_checkin < '$hour_time'";
$test_m = $db->GetRow($sql);

if($test_m['count'] > 0)
{
	$tpl->msg[] = 'Error hour cron not running';
}

// Check Day Cron
$day_time = date('Y-m-d h:i:s', ($now - 172800));
$sql = "SELECT COUNT(*) AS count FROM crons WHERE name = 'day' AND last_checkin < '$day_time'";
$test_m = $db->GetRow($sql);

if($test_m['count'] > 0)
{
	$tpl->msg[] = 'Error day cron not running';
}

// Check for errors
$sql = "SELECT * FROM errors WHERE state = 'n';";
$rs = $db->Execute($sql);
while($rw = $rs->FetchRow())
{
	$tpl->bad_errors[] = $rw;
	if(!$db->Execute("UPDATE errors SET state = 'd' WHERE error_id = '".$rw['error_id']."';"))
	{
		showError(mysql_error());
	}
}

// User has decided to run upgrade, a wise choice.
if(isset($_GET['run_upgrade']))
{
	//$db->debug = 1;
	// Se if already set to run! User might have refreshed or is being stupid or summtin fooked up
	$sql = "SELECT COUNT(command_id) AS `count` FROM commands WHERE command LIKE '%rsync.sh%' AND `state` = '0'";
	$rw = $db->GetRow($sql);

	// Yup its running let em know
	if($rw['count'] > 0)
	{
		$tpl->msg[] = 'Upgrade already set to run within one minute please contact support if it has been over 24hours.';
	}
	// Not running make command and put in table
	else
	{
		$path = $config->values['site']['path'];
		$command  = "cd " . $path . "install/shell/;";
		$command .= $path . "install/shell/rsync.sh " . $path;
		$sql = "INSERT INTO commands (`command`, `date`, `state`) VALUES ('$command', NOW(), '0');";
		$db->Execute($sql);
		$tpl->msg[] = 'Upgrade is set to run within 1 minute.';
	}
}

/*
$us = getDBConfig('VERSION',1);

if($us['value'] < CELIBERO_VERSION)
{
	header( 'refresh: 3; url=/install/upgrade/index.php' );
	echo "<a href=\"/install/upgrade/index.php\">redirecting to upgrade scripts.</a>";
	die();
}*/

/*if(($cur = (float) getCurrentVersion()) > $us['value'])
{
$tpl->msg[] = "You are running an outdated Celibero version (".$us['value'].") current is $cur. Do you want to run <a href=\"/cp/index.php?run_upgrade=1\">upgrade?</a>";
}*/



$tpl->template = "cp/index.php";
$tpl->display('cp/layout.php');
?>
