<?php
$auth_is_admin = true;
require_once('../../lib/control_panel.php');
require_once('delivery_configuration.cls.php');
require_once('form.cls.php');

class ctl_delivery_configuration
{
	protected $delivery_configuration;

	public function __construct ()
	{
		global $tpl;

		$this->delivery_configuration 	= new delivery_configuration();
		$this->tpl 						= $tpl;
	}

	public function index ()
	{
		global $tpl;
		
		$sql 			= 'SELECT `delivery_configuration_id`, `name` FROM `delivery_configuration`';
		$rows 			= all_rows(query($sql));
		$tpl->rows 		= $rows;
		$tpl->template 	= 'cp/options/delivery_configuration_list.tpl.php';
		$tpl->display('cp/layout.php');
	}

	public function create ()
	{
		$this->tpl->form 			= $this->setup_form();

		if (!empty($_POST['submit']))
		{
			if (!$this->tpl->form->validate($_POST))
			{
				$this->tpl->error 		= 1;
				$this->tpl->errors 		= $form->messages;
				$this->tpl->template 	= 'cp/options/delivery_configuration_form.tpl.php';
				$this->tpl->display('cp/layout.php');
				return;
			}

			if (!$this->delivery_configuration->insert($_POST['name'], $_POST))
				die('An error occured');

			header('refresh: 5; url=/cp/options/delivery_configuration.php');
			$this->tpl->message 	= 'Configuration Created';
			$this->tpl->template 	= 'cp/message_success.tpl.php';
			$this->tpl->display('cp/layout.php');
			return;
		}

		$this->tpl->template 	= 'cp/options/delivery_configuration_form.tpl.php';
		$this->tpl->display('cp/layout.php');
	}

	public function update ()
	{
		$delivery_configuration_id 	= $_GET['delivery_configuration_id'];
		$delivery_configuration 	= $this->delivery_configuration->get($_GET['delivery_configuration_id']);
		$this->tpl->form 			= $this->setup_form($delivery_configuration);

		if (!empty($_POST['submit']))
		{
			if (!$this->tpl->form->validate($_POST))
			{
				$this->tpl->error 		= 1;
				$this->tpl->errors 		= $form->messages;
				$this->tpl->template 	= 'cp/options/delivery_configuration_form.tpl.php';
				$this->tpl->display('cp/layout.php');
				return;
			}

			if (!$this->delivery_configuration->update($delivery_configuration_id, $_POST))
				die('An error occured');

			header('refresh: 5; url=/cp/options/delivery_configuration.php');
			$this->tpl->message 	= 'Configuration Updated';
			$this->tpl->template 	= 'cp/message_success.tpl.php';
			$this->tpl->display('cp/layout.php');
			return;
		}

		$this->tpl->template 	= 'cp/options/delivery_configuration_form.tpl.php';
		$this->tpl->display('cp/layout.php');
	}

	private function setup_form ($defaults = array())
	{
		$form = new form();
		
		$form->add_element('text', 'name', 'Name:');
		$form->set_default('name', @$defaults['name']);
		$form->add_rule('name', 'Please supply a name', 'required');

		$form->add_element('textarea', 'header', 'Headers:', array('rows' => 10, 'cols' => 50));
		$form->set_default('header', @$defaults['header']);
		$form->add_rule('header', 'Please supply headers', 'required');

		$form->add_element('text', 'encoding_html', 'HTML:');
		$form->set_default('encoding_html', @$defaults['encoding_html']);
		$form->add_rule('encoding_html', 'Please supply encoding HTML', 'required');

		$form->add_element('text', 'encoding_text', 'Text:');
		$form->set_default('encoding_text', @$defaults['encoding_text']);
		$form->add_rule('encoding_text', 'Please supply encoding text', 'required');

		$form->add_element('text', 'encoding_aol', 'AOL:');
		$form->set_default('encoding_aol', @$defaults['encoding_aol']);
		$form->add_rule('encoding_aol', 'Please supply encoding AOL', 'required');

		$form->add_element('text', 'charset_head', 'Head:');
		$form->set_default('charset_head', @$defaults['charset_head']);
		$form->add_rule('charset_head', 'Please supply charset head', 'required');

		$form->add_element('text', 'charset_text', 'Text:');
		$form->set_default('charset_text', @$defaults['charset_text']);
		$form->add_rule('charset_text', 'Please supply charset text', 'required');

		$form->add_element('text', 'charset_html', 'HTML:');
		$form->set_default('charset_html', @$defaults['charset_html']);
		$form->add_rule('charset_html', 'Please supply charset html', 'required');

		$form->add_element('text', 'charset_aol', 'AOL:');
		$form->set_default('charset_aol', @$defaults['charset_aol']);
		$form->add_rule('charset_aol', 'Please supply charset aol', 'required');
	
		$form->add_element('text', 'boundry_prefix', 'Prefix:');
		$form->set_default('boundry_prefix', @$defaults['boundry_prefix']);
		$form->add_rule('boundry_prefix', 'Please supply boundry prefix', 'required');

		$form->add_element('text', 'boundry_postfix', 'Postfix:');
		$form->set_default('boundry_postfix', @$defaults['boundry_postfix']);
		//$form->add_rule('boundry_postfix', 'Please supply boundry postfix', 'required');
	
		return $form;
	}
}

$controller = new ctl_delivery_configuration();

switch ($_GET['action'])
{
	case 'create':
		$controller->create();
		break;
	case 'update':
		$controller->update();
		break;
	default:
		$controller->index();
		break;
}
?>
