<?php
require '../../lib/control_panel.php';

class tracked_link
{
	public function __construct ()
	{
		global $tpl;

		$this->tpl = $tpl;
	}

	private function update ($tracked_link_id, $link)
	{
		$link 	= esc($link);

		$sql 	= "UPDATE `tracked_link` SET `url` = '$link' WHERE `tracked_link_id` = '$tracked_link_id';";
		query($sql);
	}

	public function edit ()
	{
		$tracked_link_id = esc(@$_GET['tracked_link_id']);

		if (isset($_POST['link']))
			$this->update($tracked_link_id, $_POST['link']);	

		
		$sql = "SELECT * FROM `tracked_link` WHERE `tracked_link_id` = '$tracked_link_id';";
		$row = row(query($sql));

		if (empty($row))
			die('Not found');

		$this->tpl->row 		= $row;
		$this->tpl->template 	= 'cp/management/tracked_link/edit.tpl.php';
		$this->tpl->display('cp/layout-pop.php');
	}
}

$controller = new tracked_link;
$controller->edit();
?>
