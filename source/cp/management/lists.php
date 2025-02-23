<?php
set_time_limit(0);
require '../../lib/control_panel.php';
require_once('list.cls.php');
require_once('form.cls.php');
require_once('prime_api.cls.php');

class lists_ctl
{
	public function __construct ()
	{
		global $tpl, $permissions;

		$this->tpl 	= $tpl;
		$this->list = lists::singleton();
		$this->api 	= new prime_api();

		$this->tpl->mailer = $permissions->auth->user['mailer'];
	}

	private function get_local ()
	{
		$sql 	= "SELECT * FROM `list` WHERE `remote_list_id` = 0";
		$result = query($sql);

		$lists 	= array();

		while ($row = row($result))
		{
			$row['count'] 	= $this->list->count_emails($row['list_id']);
			$lists[] 		= $row;
		}

		return $lists;
	}

	private function get_remote ()
	{
		$sql 	= "SELECT * FROM `list` WHERE `remote_list_id` != 0";
		$result = query($sql);

		$lists 	= array();

		while ($row = row($result))
		{
			$row['count'] 	= $this->list->count_emails($row['list_id']);
			$lists[] 		= $row;
		}

		return $lists;
	}

	private function add ($name)
	{
		$this->list->create($name);
	}

	private function delete ($selected)
	{
		if ($this->tpl->mailer == '1')
			die('Mailer');

		if (!is_array($selected))
			$selected = array($selected);

		foreach ($selected as $list_id)
			$this->list->delete($list_id);
	}

	private function remote_get ()
	{
		$this->api->set_hostname($_POST['remote_host'], false);

		if (!$this->api->login($_POST['remote_user'], $_POST['remote_pass']))
			die(' An error occured could not login to remote server.');

		if (!$lists = $this->api->list_list())
			die('Error getting the lists');

		$this->tpl->remote_listing 	= $lists;
		$this->tpl->remote_host 	= $_POST['remote_host'];
		$this->tpl->remote_user 	= $_POST['remote_user'];
		$this->tpl->remote_pass 	= $_POST['remote_pass'];
	}

	private function remote_subscribe ($selected)
	{
		if ($this->tpl->mailer == '1')
			die('Mailer');

		$this->api->set_hostname($_POST['remote_host'], false);

		if (!$this->api->login($_POST['remote_user'], $_POST['remote_pass']))
			die(' An error occured could not login to remote server.');

		if (!$lists = $this->api->list_list())
			die('Error getting the lists');

		foreach ($selected as $list_id)
		{
			foreach ($lists as $list)
			{
				if ($list['list_id'] == $list_id)
					$this->list->create($list['name'], $list_id, $_POST['remote_host'], $_POST['remote_user'], $_POST['remote_pass']);
			}
		}
	}

	private function remote_unsubscribe ($selected)
	{
		if ($this->tpl->mailer == '1')
			die('Mailer');

		if (!is_array($selected))
			$selected = array($selected);

		foreach ($selected as $list_id)
			$this->list->delete($list_id);
	}

	public function setting ()
	{
		$list_id 	= esc($_GET['list_id']);
		$list 		= $this->list->get($list_id);

		if (isset($_POST['update']))
		{
			$remote_hostname 	= esc($_POST['remote_hostname']);
			$remote_username 	= esc($_POST['remote_username']);
			$remote_password 	= esc($_POST['remote_password']);
			$send_unsubs 		= esc(@$_POST['send_unsubs']);

			$sql 				= "UPDATE `list` SET `remote_hostname` = '$remote_hostname', `remote_username` = '$remote_username', `remote_password` = '$remote_password', `send_unsubs` = '$send_unsubs' WHERE `list_id` = '$list_id';";
			query($sql);

			$list 				= $this->list->get($list_id);

			$this->tpl->updated = true;
		}

		$form = new form();

		$form->add_element('text', 'remote_hostname', 'Hostname:');
		$form->set_default('remote_hostname', $list['remote_hostname']);

		$form->add_element('text', 'remote_username', 'Username:');
		$form->set_default('remote_username', @$list['remote_username']);
	
		$form->add_element('text', 'remote_password', 'Password:');
		$form->set_default('remote_password', @$list['remote_password']);

		$form->add_element('checkbox', 'send_unsubs', 'Send Unsubs', '1');
		$form->set_default('send_unsubs', @$list['send_unsubs']);

		$this->tpl->list 		= $list;
		$this->tpl->form 		= $form;
		$this->tpl->template 	= 'cp/management/list/setting.tpl.php';
		$this->tpl->display('cp/layout.php');
	}

	public function log ()
	{
		$list_id 	= esc($_GET['list_id']);
		
		$sql 		= "SELECT * FROM `list_log` WHERE `list_id` = '$list_id' ORDER BY `date` DESC LIMIT 0, 100;";
		$result 	= query($sql);
		$log 		= array();	

		while ($row = row($result))
			$log[] = $row;

		$this->tpl->log 		= $log;
		$this->tpl->template 	= 'cp/management/list/log.tpl.php';
		$this->tpl->display('cp/layout.php');
	}

	public function index ()
	{
		if (isset($_POST['add_local']))
			$this->add($_POST['name']);

		if (isset($_POST['delete']))
			$this->delete($_POST['selected']);

		if (isset($_POST['remote_get']))
			$this->remote_get();

		if (isset($_POST['subscribe']))
			$this->remote_subscribe($_POST['selected']);

		if (isset($_POST['unsubscribe']))
			$this->remote_unsubscribe($_POST['selected']);

		$this->tpl->remote_lists 	= $this->get_remote();
		$this->tpl->local_lists 	= $this->get_local();
		$this->tpl->template = 'cp/management/lists.php';
		$this->tpl->display('cp/layout.php');
	}
}
$controller = new lists_ctl();

switch (@$_GET['action'])
{
	case 'log':
		$controller->log();
		break;
	case 'setting':
		$controller->setting();
		break;
	default;
		$controller->index();
		break;
}
?>
