<?php
require_once('../../lib/control_panel.php');
require_once('public.php');

class aol_fb_domain 
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

			if (!empty($_POST['fl'][$domain]))
				$fl = 1;
			else
				$fl = 0;

			$sql 	= "UPDATE `server_to_ip` SET `aol_fl_code` = '$code', `aol_fl_date` = '$date', `aol_fl` = '$fl' WHERE `domain` = '$domain';";
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

			$domains[] = $row;
		}

		$this->tpl->domains 	= $domains;
		$this->tpl->template 	= 'cp/isp-relations/aol_fb_domain.tpl.php';
		$this->tpl->display('cp/layout.php');
	}
}

$controller = new aol_fb_domain();
$controller->index();
?>
