<?php
require_once('../../lib/control_panel.php');
require_once('public.php');

class aol_removal 
{
	public function __construct ()
	{
		global $tpl;

		$this->tpl 				= $tpl;
		$this->tpl->styles[]	= 'table.css';
	}

	private function lookup ()
	{
		$cmd = '/usr/bin/nslookup -q=mx aol.com';
		exec($cmd, $output);
		$this->tpl->lookup = implode("\n", $output);
	}

	private function telnet ($domain)
	{
		global $config; 

		$binary_path 	= $config->values['site']['path'] . 'no-web/celiberod/bin/iptest';
		$config_2  		= getDBConfig('', 1);

		$lines 	= explode("\n", trim(@$config_2['AOL_IP_TEST_EMAIL']));
		$rand 	= rand(0, (count($lines)-1));

		$ip_arg 	= escapeshellarg($domain['ip']);
		$domain_arg = escapeshellarg($domain['domain']);

		if (validEmail(trim(@$lines[$rand])))
			$cmd = "$binary_path -i $ip_arg -d $domain_arg -l -r ".escapeshellarg(trim($lines[$rand]));
		else
			$cmd = "$binary_path -i $ip_arg -d $domain_arg -l";

		exec($cmd, $output);
	
		$this->tpl->telnet 	= implode("\n", $output);
	}

	public function index ()
	{
		if (!empty($_POST['nslookup']))
			$this->lookup();

		/* Groups list */
		$sql 		= "SELECT * FROM `domain_group`";
		$groupss 	= all_rows(query($sql));
		$groups 	= array();

		foreach ($groupss as $group)
			$groups[$group['domain_group_id']] = $group;

		$sql 		= "SELECT * FROM server_to_ip ORDER BY `domain_group_id` ASC, INET_ATON(`ip`) ASC";
		$result 	= query($sql);
		$options 	= array();
		$domains 	= array();

		while($rw = row($result))
		{
			$options[$rw['ip']] = @$groups[$rw['domain_group_id']]['name'].' - '.$rw['ip'].' - '.$rw['domain'];
			$domains[$rw['ip']] = $rw;
		}

		if (!empty($_POST['telnet']))
			$this->telnet($domains[$_POST['ip']]);

		$this->tpl->options 	= $options;
		$this->tpl->template 	= 'cp/isp-relations/aol_removal.tpl.php';
		$this->tpl->display('cp/layout.php');
	}
}

$controller = new aol_removal();
$controller->index();
?>
