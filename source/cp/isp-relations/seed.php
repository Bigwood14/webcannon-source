<?php
require_once('../../lib/control_panel.php');
require_once('public.php');

class seed_account 
{
	public function __construct ()
	{
		global $tpl;

		$this->tpl 				= $tpl;
		$this->tpl->styles[]	= 'table.css';
	}


	protected function add ()
	{
		$username 	= esc($_POST['seed_username']);
		$password 	= esc($_POST['seed_password']);

		$sql = "INSERT INTO `seed_account` (`username`, `password`) VALUES ('$username', '$password');";
		query($sql);
	}

	protected function delete ($id)
	{
		$id = esc($id);

		$sql = "DELETE FROM `seed_account` WHERE `seed_account_id` = '$id';";
		query($sql);
	}

	public function index ()
	{
		if (!empty($_POST['add']))
			$this->add();

		if (!empty($_GET['delete']))
			$this->delete($_GET['delete']);

		$sql 		= "SELECT * FROM `seed_account`;";
		$result 	= query($sql);
		$seeds 		= array();

		while ($row = row($result))
			$seeds[] = $row;

		$this->tpl->seeds		= $seeds;
		$this->tpl->template 	= 'cp/isp-relations/seed.tpl.php';
		$this->tpl->display('cp/layout.php');
	}
}

$controller = new seed_account();
$controller->index();
?>
