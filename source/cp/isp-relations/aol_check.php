<?php
require '../../lib/control_panel.php';

set_time_limit(0);

$binary_path 	= $config->values['site']['path'] . 'no-web/celiberod/bin/iptest';

if (!is_file($binary_path))
	die('Checker not installed');

$sql 		= "SELECT * FROM `server_to_ip` ORDER BY `domain_group_id` ASC, INET_ATON(`ip`) ASC;";
$result 	= query($sql);
$ips 		= array();
$domains 	= array();

while ($row = row($result))
{
	$ips[$row['domain_group_id']][] = $row;	
	$domains[$row['ip']] = $row;
}

function digger ($cmd)
{
	$output = array();
	exec($cmd, $output);
	$mxs = array();

	$parts = $output;
	$at = false;
	foreach ($parts as $part)
	{
		$part = trim($part);	

		if ($at == true)
		{
			// end of answer section?
			if (empty($part))
				break;
			
			$bits = explode("\t", $part);

			// we only want the last bit
			$mxs[] = $bits[count($bits)-1];

			continue;
		}

		if (strpos($part, 'ANSWER SECTION') !== false)
			$at = true;
	}

	return $mxs;
}

// do checks
$results 	= array();
$config_2  	= getDBConfig('', 1);

if (isset($_POST['check']))
{
	$cmd_mx = '';
	$cmd 	= '/usr/bin/dig aol.com mx @localhost';
	$mxs 	= digger($cmd);

	if (!empty($mxs))
	{
		$cmd 	= '/usr/bin/dig '.$mxs[0].' @localhost';
		$a 		= digger($cmd);

		$mx_ip 	= $a[0];

		$long = ip2long($mx_ip);

		if ($long != -1 && $long !== false)
			$cmd_mx = " -m ".escapeshellarg($mx_ip)." ";
	}

	// ok now we need gather ALL the AOL IPs and fork an iptest on each one read and gather the results

	

	foreach ($_POST['ips'] as $ip)
	{
		// is a valid IP?
		$long = ip2long($ip);

		if ($long == -1 || $long === false)
			continue;

		$output = array();

		$lines 	= explode("\n", trim(@$config_2['AOL_IP_TEST_EMAIL']));
		$rand 	= rand(0, (count($lines)-1));

		// escape shell args
		$ip_arg 	= escapeshellarg($ip);
		$domain_arg = escapeshellarg($domains[$ip]['domain']);

		if (validEmail(trim(@$lines[$rand])))
			$cmd = "$binary_path -i $ip_arg{$cmd_mx} -d $domain_arg -r ".escapeshellarg(trim($lines[$rand]));
		else
	 		$cmd = "$binary_path -i $ip_arg{$cmd_mx} -d $domain_arg";

		exec($cmd, $output);

		$results[$ip] = '';

		foreach ($output as $line)
		{
			if (empty($line))
				continue;

			$results[$ip] .= ' '.$line;
		}		
	}
}

$sql 			= "SELECT * FROM `domain_group`";
$groups 		= $db->GetAll($sql);
$tpl->groups 	= array();

foreach ($groups as $group)
	$tpl->groups[$group['domain_group_id']] = $group;

$tpl->styles[]	= 'table.css';
$tpl->ips 		= $ips;
$tpl->results 	= $results;
show_cp_page('cp/isp-relations/aol_check.tpl.php');
?>
