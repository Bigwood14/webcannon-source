<?php
require_once('../../lib/control_panel.php');
require_once('public.php');

class aol_complaint
{
	public function __construct ()
	{
		global $tpl;

		$this->tpl 				= $tpl;
		$this->tpl->styles[]	= 'table.css';
	}

	public function index ()
	{
		$start 		= mktime()-(86400*10);
		$end 		= mktime();

		$days 		= array();

		for($i=$start;$i<=$end;$i+=86400)
		{
			//print date('Y-m-d', $i)."<br />";	
			$days[] = $i;
		}

		$sql 		= "SELECT * FROM `server_to_ip` ORDER BY INET_ATON(`ip`) ASC;";
		$result 	= query($sql);
		$data 		= array();

		while ($row = row($result))
		{
			foreach ($days as $day)
			{
				$from 	= date('Y-m-d', $day);
				$to 	= date('Y-m-d', ($day+86400));

				$sql 	= "SELECT SUM(`count`) AS `count` FROM `del_success_stats` WHERE `type` = 1 AND `date` > '$from' AND `date` < '$to' AND `ip` = INET_ATON('".$row['ip']."')";
				$rw 	= row(query($sql));
				$del 	= $rw['count'];

				if (empty($del))
					$del = 0;
	
				$sql 	= "SELECT COUNT(*) AS `count` FROM `msg_complaint_log` WHERE `date` > '$from' AND `date` < '$to' AND `ip` = INET_ATON('".$row['ip']."');";	
				$rw 	= row(query($sql));
				$com 	= $rw['count'];

				$data[$row['ip']][$day] = array('del' => $del, 'com' => $com);
			}
		}

		$this->tpl->days 		= $days;
		$this->tpl->data 		= $data;
		$this->tpl->template 	= 'cp/isp-relations/aol_complaint.tpl.php';
		$this->tpl->display('cp/layout.php');
	}
}

$controller = new aol_complaint();
$controller->index();
?>
