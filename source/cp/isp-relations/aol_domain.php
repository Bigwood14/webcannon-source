<?php
require_once('../../lib/control_panel.php');
require_once('public.php');

class aol_domain 
{
	public function __construct ()
	{
		global $tpl;

		$this->tpl 				= $tpl;
		$this->tpl->styles[]	= 'table.css';
	}

	private function update ()
	{
		foreach ($_POST['code'] as $domain => $code)
		{
			$code 	= esc($code);
			$date 	= esc(@$_POST['date'][$domain]);
			$domain = esc($domain);
			$ratio 	= esc(@$_POST['ratio'][$domain]);
			$deny 	= esc(@$_POST['deny'][$domain]);

			if (!empty($_POST['whitelist'][$domain]))
				$whitelist = 1;
			else
				$whitelist = 0;

			$sql 	= "UPDATE `server_to_ip` SET `aol_confirmation_code` = '$code', `aol_date` = '$date', `aol` = '$whitelist', `aol_ratio` = '$ratio', `aol_deny` = '$deny' WHERE `domain` = '$domain';";
			query($sql);
		}
	}

	public function index ()
	{
		if (!empty($_POST['update']))
			$this->update();

		$sql 		= "SELECT * FROM `server_to_ip` ORDER BY INET_ATON(`ip`) ASC;";
		$result 	= query($sql);
		$domains 	= array();

		while ($row = row($result))
		{
			$sql 	= "SELECT * FROM `aol_ratio` WHERE `ip` = '".$row['domain']."' ORDER BY `date` DESC LIMIT 0,1;";
			$rw		= row(query($sql));

			if (!empty($rw))
				$row['ratio'] = $rw['ratio'].'%';

			$domains[] = $row;
		}

		$this->tpl->domains 	= $domains;
		$this->tpl->template 	= 'cp/isp-relations/aol_domain.tpl.php';
		$this->tpl->display('cp/layout.php');
	}
}

$controller = new aol_domain();
$controller->index();
?>
