<?php
$auth_is_admin = true;
require_once('../../lib/control_panel.php');
require_once('public.php');

class user 
{
	public function __construct ()
	{
		global $tpl, $permissions;

		$this->permissions 		= $permissions;

		$this->tpl 				= $tpl;
		$this->tpl->styles[]	= 'table.css';
	}

	public function edit ()
	{
		$user_id  	= esc(@$_GET['user_id']);

		if (!$user = $this->permissions->auth->getUser($user_id))
			die('No User');
		
		if ($user['username'] == 'admin')
			die('Cant edit admin');

		if (!empty($_POST['submit']))
		{
			$access = 0;
			if ($_POST['access'] != 'admin')
				$access = 1;

			$ips 	= esc($_POST['ips']);
	
			$sql = "UPDATE `users_auth` SET `mailer` = '$access', `ips` = '$ips' ";
	
			if (!empty($_POST['user_password']))
			{
				$sql .= ",`password` = md5('".mysql_real_escape_string($_POST['user_password'])."') ";
	
				query("DELETE FROM `users_session` WHERE `user_id` = '$user_id';");
			}
	
			$sql .= " WHERE `user_id` = '$user_id';";
			query($sql);
	
			$user = $this->permissions->auth->getUser($user_id);
	
			$this->tpl->msg 		= 'User had been updated.';
		}

		$this->tpl->user 		= $user;
		$this->tpl->template 	= 'cp/options/user/edit.tpl.php';
		$this->tpl->display('cp/layout.php');
	}

	private function add_user ()
	{
		$username 	= esc($_POST['user_username']);
		$password 	= esc($_POST['user_password']);
		$access		= $_POST['access'];

		if (empty($username) || empty($password))
		{
			$this->tpl->error = 'Please enter a username and password';
			return false;
		}
	
		$sql 		= "SELECT * FROM `users_auth` WHERE `username` = '$username';";
		
		if (row(query($sql)))
		{
			$this->tpl->error = 'User already exists';
			return false;
		}

		$user_id = $this->permissions->auth->addUser($username, $password);

		$sql 		= "SELECT * FROM `users_groups`;";
		$result 	= query($sql);

		while ($row = row($result))
			$this->permissions->auth->addGroup($user_id, $row['group_id']);

		if ($access != 'admin')
			query("UPDATE `users_auth` SET `mailer` = 1 WHERE `user_id` = '$user_id';");

		return true;
	}

	private function delete_users ($users)
	{
		if (empty($users) || !is_array($users))
			return false;

		foreach ($users as $user_id)
		{
			$user = $this->permissions->auth->getUser($user_id);
			if ($user['username'] == 'admin')
				continue;

			$user_id 	= esc($user_id);
			$sql 		= "DELETE FROM `users_auth` WHERE `user_id` = '$user_id';";
			query($sql);
		}
	}

	public function index ()
	{
		if (isset($_POST['submit']))
			$this->add_user();

		if (isset($_POST['delete']))
			$this->delete_users($_POST['users']);

		$sql 		= "SELECT * FROM `users_auth` ORDER BY `username` ASC;";
		$result 	= query($sql);
		$users 		= array();

		while ($row = row($result))
		{
			if (!empty($row['mailer']))
				$row['type'] = 'Emailer';
			else
				$row['type'] = 'Admin';

			$users[] 	= $row;
		}

		$this->tpl->users 		= $users;
		$this->tpl->template 	= 'cp/options/user/list.tpl.php';
		$this->tpl->display('cp/layout.php');
	}
}

$controller = new user();

switch (@$_GET['action'])
{
	case 'edit':
		$controller->edit();
		break;
	default:
		$controller->index();
		break;
}
?>
