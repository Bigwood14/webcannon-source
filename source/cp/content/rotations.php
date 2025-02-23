<?php
require_once('../../lib/control_panel.php');
require_once('public.php');
require_once('extra_content.php');


class ctl_content_rotations
{
	public function __construct ()
	{
		global $tpl;

		$this->tpl 				= $tpl;
		$this->tpl->scripts[] 	= 'jquery.js';
		$this->tpl->scripts[] 	= 'rotations.js';
	}

	public function create ()
	{
		
	}

	private function create_set ($name)
	{
		return content_add_rotated($name);
	}

	private function content_get ($id)
	{
		$id 	= esc($id);
		$sql 	= "SELECT * FROM `extra_content_data` WHERE `content_id` = $id;";

		return all_rows(query($sql));
	}

	private function content_create ($content_id, $name, $content)
	{
		return add_content_data($content_id, $name, $content);
	}

	private function content_delete ($content_id)
	{
		$content_id 	= esc($content_id);
		$sql 			= "DELETE FROM `extra_content_data` WHERE `id` = $content_id;";

		return query($sql);
	}

	public function index ()
	{
		if (!empty($_GET['delete']) && !empty($_GET['content_id']))
		{
			$this->content_delete($_GET['content_id']);
		}

		if (!empty($_POST['name']) && isset($_POST['add_content']))
		{
			$this->content_create($_POST['content_id'], $_POST['name'], $_POST['content']);
		}

		if (!empty($_POST['name']) && isset($_POST['new_set']))
		{
			$this->create_set($_POST['name']);
		}

		$sql 	= "SELECT * FROM `extra_content` WHERE `content_type` = 'rotated';";

		$sets 	= all_rows(query($sql));
		$this->tpl->sets = $sets;

		if (isset($_GET['view_content']))
		{
			$content = $this->content_get($_GET['view_content']);
			$this->tpl->content = $content;
		}

		$this->tpl->template 		= 'cp/content/rotations.tpl.php';
		$this->tpl->display('cp/layout.php');
	}

	public function setup_form ($defaults = array())
	{
		$form = new form();
		// name
		$form->add_element('text', 'name', 'Name:');
		$form->set_default('name', @$defaults['name']);
		$form->add_rule('name', 'Please supply a name', 'required');

		// 
	}
}

$ctl 	= new ctl_content_rotations();
switch (@$_GET['action'])
{
	case 'create':
		$ctl->create();
		break;
	case 'edit':
		$ctl->edit();
		break;
	default:
		$ctl->index();
		break;
}
?>
