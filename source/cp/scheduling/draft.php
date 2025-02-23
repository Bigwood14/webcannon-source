<?php
require_once('../../lib/control_panel.php');
require_once('public.php');
require_once('extra_content.php');
require_once('link_tracking.php');

require_once('draft.cls.php');
require_once('form.cls.php');
require_once('ip.cls.php');
require_once('list_db.cls.php');
require_once('link_tracking.cls.php');
require_once('validation.cls.php');

require_once('functions-scheduling.php');

class ctl_draft
{
	public function __construct ()
	{
		global $tpl;

		$this->draft 				= new draft();
		$this->link_tracking 		= new link_tracking();
		$this->draft_from 			= new draft_from();
		$this->draft_domain 		= new draft_domain();
		$this->draft_subject 		= new draft_subject();
		$this->draft_suppression	= new draft_suppression();
		$this->ip 					= new ip();
		$this->list 				= new list_db();

		$this->tpl 				= $tpl;

		$this->tpl->scripts[] 	= 'tabs/tabs.js';
		$this->tpl->scripts[] 	= 'draft.js';
		$this->tpl->styles[] 	= 'tabs.css';
		$this->tpl->styles[] 	= 'draft.css';
	}

	public function links_find ()
	{
		if (@$_POST['links_find_images'] == '1')
			$links = $this->link_tracking->find(@$_POST['body_text'], @$_POST['body_html'], @$_POST['body_aol'], true);
		else
			$links = $this->link_tracking->find(@$_POST['body_text'], @$_POST['body_html'], @$_POST['body_aol']);

		print json_encode($links);
		die;
	}

	public function create ()
	{
		global $permissions;

		if (!empty($_GET['draft_id']))
			$defaults  				=  $this->load_draft($_GET['draft_id']);

		if (empty($defaults))
		{
			$defaults 					= array();
			$defaults['from'][0] 		= 'Name <name@{{dn}}>';
			$defaults['list'] 			= array();
			$defaults['domain'] 		= array();
			$defaults['threads'] 		= 0;
			$defaults['thread_wait'] 	= 1;
		}

		$form 						= $this->setup_form($defaults);

		if (isset($_POST['submit']))
		{
			if ($form->validate($_POST))
			{
				$values 					= array();
				$values['body'] 			= str_replace("\r", '', @$_POST['body_text']);	
				$values['html_body']	 	= str_replace("\r", '', @$_POST['body_html']);	
				$values['aol_body'] 		= str_replace("\r", '', @$_POST['body_aol']);
				$values['yahoo_body'] 		= str_replace("\r", '', @$_POST['body_yahoo']);

				$values['size'] 			= strlen($values['body'])+strlen($values['html_body']);
				$values['send_type']	 	= 0;

				$values['footer'] 			= $_POST['footer_id'];
				$values['header'] 			= $_POST['header_id'];
				$values['link_tracking'] 	= '0';
				$values['from_domain'] 		= $_POST['from_domain'];
				$values['user_id'] 			= $permissions->auth->user['user_id'];

				// make yahoo date email date
				$_POST['yahoo_date_original'] = $_POST['yahoo_date']; 
				if (!empty($_POST['yahoo_date']))
				{
					$stamp 					= strtotime($_POST['yahoo_date']);
					$_POST['yahoo_date'] 	=  date('r', $stamp);
				}
				else
					$_POST['yahoo_date'] = '';

				$values 				= array_merge($_POST, $values);

				$draft_id 				= $this->draft->insert($values);
				$this->save_multiple($draft_id);

				header('location: draft-view.php?msg_id='.$draft_id);
				die;
			}
			else
			{
				$this->tpl->error 		= 1;
				$this->tpl->errors 		= $form->messages;
			}
		}

		$this->tpl->form 			= $form;
		$this->tpl->submit 			= 'Create';
		$this->tpl->template 		= 'cp/scheduling/draft_form.tpl.php';
		$this->tpl->display('cp/layout.php');
	}

	public function edit ()
	{
		global $permissions;

		if (empty($_GET['draft_id']))
			die('No draft ID');

		$draft_id 				= $_GET['draft_id'];
		$draft 					= $this->load_draft($draft_id);

		$draft['yahoo_date'] 	= $draft['yahoo_date_original'];

		$form 				= $this->setup_form($draft);

		if (isset($_POST['submit']))
		{
			if ($form->validate($_POST))
			{
				$values 					= array();
				$values['body'] 			= str_replace("\r", '', @$_POST['body_text']);	
				$values['html_body']	 	= str_replace("\r", '', @$_POST['body_html']);	
				$values['aol_body'] 		= str_replace("\r", '', @$_POST['body_aol']);
				$values['yahoo_body'] 		= str_replace("\r", '', @$_POST['body_yahoo']);

				$values['size'] 			= strlen($values['body'])+strlen($values['html_body']);
				$values['send_type']	 	= 0;

				$values['footer'] 			= $_POST['footer_id'];
				$values['header'] 			= $_POST['header_id'];
				$values['link_tracking'] 	= '0';
				$values['from_domain'] 		= $_POST['from_domain'];
				$values['user_id'] 			= $permissions->auth->user['user_id'];

				if (empty($_POST['embed_images']))
					$_POST['embed_images'] = 0;

				// make yahoo date email date
				$_POST['yahoo_date_original'] = $_POST['yahoo_date']; 
				if (!empty($_POST['yahoo_date']))
				{
					$stamp 					= strtotime($_POST['yahoo_date']);
					$_POST['yahoo_date'] 	=  date('r', $stamp);
				}
				else
					$_POST['yahoo_date'] = '';

				$values 				= array_merge($_POST, $values);

				$this->draft->update($draft_id, $values);
				$this->save_multiple($draft_id);

				header('location: draft-view.php?msg_id='.$draft_id);
				die;
			}
			else
			{
				$this->tpl->error 		= 1;
				$this->tpl->errors 		= $form->messages;
			}
		}


		$this->tpl->form 		= $form;
		$this->tpl->submit 		= 'Update';
		$this->tpl->template 	= 'cp/scheduling/draft_form.tpl.php';
		$this->tpl->display('cp/layout.php');
	}

	private function load_draft ($draft_id)
	{
		if (!$draft = $this->draft->get($draft_id))
			die('Bad draft ID');

		// lists
		$data 	= $this->list->draft_get($draft_id);
		foreach ($data as $row)
		{
			$draft['list'][] 						= $row['list_id'];
			$draft['list_skip'][$row['list_id']]	= $row['skip'];
			$draft['list_max'][$row['list_id']]		= $row['max'];
		}

		// domains
		$data 	= $this->ip->draft_get($draft_id);
		foreach ($data as $row)
			$draft['domain'][] = $row['ip_id'];

		// links
		$data 							= $this->link_tracking->draft_get($draft_id);
		$draft['tracked_link'] 			= $data['link'];
		$draft['tracked_link_action'] 	= $data['action'];
		$draft['tracked_link_target'] 	= $data['target'];

		// froms
		$data 	= $this->draft_from->get($draft_id);
		foreach ($data as $row)
			$draft['from'][] = $row['from']." <{$row['from_local']}@{$row['from_domain']}>";

		// domain only
		$draft['domain_only'] = '';
		$data 	= $this->draft_domain->get($draft_id);
		foreach ($data as $row)
			$draft['domain_only'] .= $row['domain']."\n";

		// domain not 
		$draft['domain_not'] = '';
		$data 	= $this->draft_domain->get($draft_id, 1);
		foreach ($data as $row)
			$draft['domain_not'] .= $row['domain']."\n";

		// subjects
		$data 	= $this->draft_subject->get($draft_id);
		foreach ($data as $row)
			$draft['subject'][] = $row['subject'];

		// suppression lists
		$data 	= $this->draft_suppression->get($draft_id);
		foreach ($data as $row)
			$draft['suppression_list'][] = $row['suppression_list_id'];

		// activate drafts
		if (!empty($draft['body']))
			$draft['body_text_check'] = 1;
		if (!empty($draft['html_body']))
			$draft['body_html_check'] = 1;
		if (!empty($draft['aol_body']))
			$draft['body_aol_check'] = 1;
		if (!empty($draft['yahoo_body']))
			$draft['body_yahoo_check'] = 1;

		$draft['header_id'] 			= $draft['header'];
		$draft['footer_id'] 			= $draft['footer'];
		
		return $draft;
	}

	private function save_multiple ($draft_id)
	{
		$this->save_multiple_link($draft_id);
		$this->draft_from->create($draft_id, $_POST['from']);
		$this->draft_subject->create($draft_id, $_POST['subject']);
		$this->draft_suppression->create($draft_id, $_POST['suppression_list']);
		$this->draft_domain->create($draft_id, explode("\n", $_POST['domain_only']));
		$this->draft_domain->create($draft_id, explode("\n", $_POST['domain_not']), 1);
		$this->ip->draft_create($draft_id, $_POST['domain']);
		$this->list->draft_create($draft_id, $_POST['list'], @$_POST['list_skip'], @$_POST['list_max']);
	}

	private function save_multiple_link ($draft_id)
	{
		if (!$this->link_tracking->delete($draft_id))
			return false;

		if (!is_array(@$_POST['tracked_link']))
			return false;

		foreach ($_POST['tracked_link'] as $index => $url)
		{
			if (empty($url))
				continue;

			$action = @$_POST['tracked_link_action'][$index];
			$target = @$_POST['tracked_link_target'][$index];

			if (empty($action))
				$action = 1;

			$this->link_tracking->insert($draft_id, $url, $action, $target);
		}
	}

	private function setup_form ($defaults = array())
	{
		global $permissions;

		$ips_parts 	= explode("\n", $permissions->auth->user['ips']);
		$ips 		= array();
		foreach ($ips_parts as $ip)
			$ips[] = trim($ip);

		$form = new form();
		// title
		$form->add_element('text', 'title', 'Title:');
		$form->set_default('title', @$defaults['title']);
		$form->add_rule('title', 'Please supply a title', 'required');

		// from domain
		$form->add_element('text', 'from_domain', 'Domain:');
		$form->set_default('from_domain', @$defaults['from_domain']);

		// server id
		$row 		= row(query("SELECT `server_id` FROM servers WHERE `type` != '2'"));
		$form->add_element('hidden', 'server_id', $row['server_id']);

		// delivery config
		$rows 		= all_rows(query('SELECT `delivery_configuration_id`, `name` FROM `delivery_configuration`;'));
		$options 	= array();
		foreach ($rows as $row)
			$options[$row['delivery_configuration_id']] = $row['name'];
		
		$form->add_element('select', 'delivery_configuration_id', 'Configuration:', $options, 2);
		$form->set_default('delivery_configuration_id', @$defaults['delivery_configuration_id']);
		$form->add_rule('delivery_configuration_id', 'Please select a delivery configuration', 'required');

		// aol rotate
		$form->add_element('text', 'aol_rotate', 'IP Rotate', array('size' => 3));
	
		if (empty($defaults['aol_rotate']))
			$defaults['aol_rotate'] = '0';

		$form->set_default('aol_rotate', @$defaults['aol_rotate']);

		// max per IP
		$form->add_element('text', 'max_per_ip', 'Max Per IP', array('size' => 6));
	
		if (empty($defaults['max_per_ip']))
			$defaults['max_per_ip'] = '0';

		$form->set_default('max_per_ip', @$defaults['max_per_ip']);


		// aol check total
		$form->add_element('text', 'aol_check_total', 'Pre check IPs this many times', array('size' => 3));
		$form->set_default('aol_check_total', @$defaults['aol_check_total']);
		$form->add_element('text', 'aol_check_hits', 'for this many hits', array('size' => 3));
		$form->set_default('aol_check_hits', @$defaults['aol_check_hits']);

		// ip domains
		$groups 					= $this->draft_domain->get_groups();

		$rows 						= all_rows(query('SELECT * FROM `server_to_ip` ORDER BY INET_ATON(`ip`) ASC'));
		$form->counts['domains'] 	= 0;
		foreach ($rows as $row)
		{
			/*if (!empty($ips))
			{
				if (!in_array($row['ip'], $ips))
					continue;
			}*/

			if ($row['aol'] == 1)
				$label = "[{$row['ip']}] <strong>{$row['domain']} (AOL)</strong>";
			else
				$label = '['.$row['ip'].'] '.$row['domain'];


			$form->add_element('checkbox', 'domain['.$form->counts['domains'].']', $label, $row['ip_id'], array('class' => 'checkbox'));
			if ($form->counts['domains'] == 0)
				$form->add_rule('domain['.$form->counts['domains'].']', 'Please select at least one domain', 'callback', array('class' => $this, 'method' => 'form_validate_domains'));

			$checked = false;
			if (@in_array($row['ip_id'], $defaults['domain']))
			{
				$checked = true;
				$form->set_default('domain['.$form->counts['domains'].']', $row['ip_id']);
			}

			if (!empty($groups))
			{
				if (!empty($row['domain_group_id']) && !empty($groups[$row['domain_group_id']]))
				{
					$groups[$row['domain_group_id']]['ips'][] = 'domain['.$form->counts['domains'].']';
					@$groups[$row['domain_group_id']]['count']++;

					if ($checked == true)
						@$groups[$row['domain_group_id']]['checked']++;
				}
				else
				{
					$groups[0]['ips'][] = 'domain['.$form->counts['domains'].']';
					@$groups[0]['count']++;

					if ($checked == true)
						@$groups[0]['checked']++;
				}
			}

			$form->counts['domains']++;
		}

		if (!empty($groups))
			$groups[0]['name'] = 'Ungrouped';

		foreach ($groups as $id => $group)
		{
			if (empty($group['ips']))
				unset($groups[$id]);
		}

		$form->group = $groups;

		// lists
		$rows 						= all_rows(query('SELECT * FROM `list`'));
		$form->counts['lists'] 	= 0;
		$form->list_ids 		= array();

		foreach ($rows as $row)
		{
			$form->add_element('checkbox', 'list['.$form->counts['lists'].']', $row['name'], $row['list_id'], array('class' => 'checkbox', 'style' => 'padding:0;margin:0 !important;'));

			$form->list_ids[$form->counts['lists']] = $row['list_id'];
			$form->add_element('text', 'list_skip['.$row['list_id'].']', 'skip', array('size' => 7));
			$form->add_element('text', 'list_max['.$row['list_id'].']', 'max', array('size' => 7));

			if (!empty($defaults['list_skip'][$row['list_id']]))
				$form->set_default('list_skip['.$row['list_id'].']', $defaults['list_skip'][$row['list_id']]);
			else
				$form->set_default('list_skip['.$row['list_id'].']', 0);

			if (!empty($defaults['list_max'][$row['list_id']]))
				$form->set_default('list_max['.$row['list_id'].']', $defaults['list_max'][$row['list_id']]);
			else
				$form->set_default('list_max['.$row['list_id'].']', 0);


			if ($form->counts['lists'] == 0)
				$form->add_rule('list['.$form->counts['lists'].']', 'Please select at least one list', 'callback', array('class' => $this, 'method' => 'form_validate_lists'));

			if (in_array($row['list_id'], $defaults['list']))
				$form->set_default('list['.$form->counts['lists'].']', $row['list_id']);

			$form->counts['lists']++;
		}

		// link track
		$rows 				= all_rows(query('SELECT `list_id`, `name` FROM `list`;'));
		$target_options 	= array('' => '-- none --');

		foreach ($rows as $row)
			$target_options[$row['list_id']] = $row['name'];

		$action_options 	= $this->link_tracking->actions;
		foreach ($rows as $row)
			$options[$row['list_id']] = $row['name'];
	
		if (!empty($_POST['tracked_link']))
			$defaults['tracked_link'] = $_POST['tracked_link'];

		if (empty($defaults['tracked_link']))
			$defaults['tracked_link'] = array('');
	
		if (!empty($_POST['tracked_link_action']))
			$defaults['tracked_link_action'] = $_POST['tracked_link_action'];

		if (!empty($_POST['tracked_link_target']))
			$defaults['tracked_link_target'] = $_POST['tracked_link_target'];

		$form->counts['tracked_links'] = 0;
		
		foreach ($defaults['tracked_link'] as $index => $url)
		{
			if (empty($url))
				continue;
			
			$form->add_element('text', 'tracked_link['.$form->counts['tracked_links'].']', 'URL:', array('class' => 'text-long'));
			$form->set_default('tracked_link['.$form->counts['tracked_links'].']', $url, false);

			$form->add_element('select', 'tracked_link_action['.$form->counts['tracked_links'].']', 'Action:', $action_options, 2);
			$form->set_default('tracked_link_action['.$form->counts['tracked_links'].']', @$defaults['tracked_link_action'][$index], false);

			$form->add_element('select', 'tracked_link_target['.$form->counts['tracked_links'].']', 'Target:', $target_options, 2);
			$form->set_default('tracked_link_target['.$form->counts['tracked_links'].']', @$defaults['tracked_link_target'][$index], false);

			$form->counts['tracked_links']++;
		}
		// link track clone
		$form->add_element('text', 'tracked_link[]', 'URL:', array('class' => 'text-long'));
		$form->add_rule('tracked_link[]', 'You have selected that a link subscribe but no list for it', 'callback', array('class' => $this, 'method' => 'form_validate_links'));
		// link track action clone
		$form->add_element('select', 'tracked_link_action[]', 'Action:', $action_options, 2);
		// link track target clone
		$form->add_element('select', 'tracked_link_target[]', 'Target:', $target_options, 2);

		// opens
		$form->add_element('select', 'open_action', 'Action:', $action_options, 2);
		$form->set_default('open_action', @$defaults['open_action']);

		$form->add_element('select', 'open_list_id', 'Target:', $target_options, 2);
		$form->set_default('open_list_id', @$defaults['open_list_id']);
		// froms
		$i = 0;

		if (!empty($_POST['from']))
		{
			foreach ($_POST['from'] as $k=>$v)
			{
				if (empty($v))
					unset($_POST['from'][$k]);
			}
			
			$_POST['from'] 		= array_values($_POST['from']); 
			$defaults['from'] 	= $_POST['from'];
		}
		
		foreach ($defaults['from'] as $from)
		{
			if (empty($from) && $i>0)
				continue;

			$form->add_element('text', 'from['.$i.']', 'From:', array('class' => 'text-long'));
			$form->set_default('from['.$i.']', $from);
			
			if ($i == 0)
			{
				$form->add_rule('from['.$i.']', 'Please supply a from line', 'required');
				$form->add_rule('from['.$i.']', 'Please supply valid from lines', 'callback', array('class' => $this, 'method' => 'form_validate_from'));
			}

			$i++;
		}
		$form->counts['froms'] = $i;
		// from clone
		$form->add_element('text', 'from[]', 'From:', array('class' => 'text-long'));

		// subjects
		$i = 0;

		if (!empty($_POST['subject']))
		{
			foreach ($_POST['subject'] as $k=>$v)
			{
				if (empty($v))
					unset($_POST['subject'][$k]);
			}
			
			$_POST['subject'] 		= array_values($_POST['subject']); 
			$defaults['subject'] 	= $_POST['subject'];
		}

		if (empty($defaults['subject']))
			$defaults['subject'] = array('');
		
		foreach ($defaults['subject'] as $subject)
		{
			if (empty($subject) && $i>0)
				continue;

			$form->add_element('text', 'subject['.$i.']', 'Subject:', array('class' => 'text-long'));
			$form->set_default('subject['.$i.']', $subject);
			
			if ($i == 0)
			{
				$form->add_rule('subject['.$i.']', 'Please supply a subject line', 'required');
				$form->add_rule('subject['.$i.']', 'Subject line should be between 1 & 98 characters long', 'callback', array('class' => $this, 'method' => 'form_validate_subject'));
			}

			$i++;
		}
		$form->counts['subjects'] = $i;
		// subject clone
		$form->add_element('text', 'subject[]', 'Subject:', array('class' => 'text-long'));
		
		// embed images checkbox
		$form->add_element('checkbox', 'embed_images', 'Embed Images', 1, array('class' => 'checkbox'));
		$form->set_default('embed_images', @$defaults['embed_images']);

		// text checkbox
		$form->add_element('checkbox', 'body_text_check', 'Activate text part', 1, array('class' => 'checkbox'));
		$form->set_default('body_text_check', @$defaults['body_text_check']);

		// text body
		$form->add_element('textarea', 'body_text', 'Text:', array('rows' => 25, 'cols' => 100));
		$form->set_default('body_text', @$defaults['body']);
		$form->add_rule('body_text', 'Please supply at least an HTML or Text body', 'callback', array('class' => $this, 'method' => 'form_validate_bodies'));

		// html checkbox
		$form->add_element('checkbox', 'body_html_check', 'Activate HTML part', 1, array('class' => 'checkbox'));
		$form->set_default('body_html_check', @$defaults['body_html_check']);

		// html body
		$form->add_element('textarea', 'body_html', 'HTML:', array('rows' => 25, 'cols' => 100));
		$form->set_default('body_html', @$defaults['html_body']);

		// aol checkbox
		$form->add_element('checkbox', 'body_aol_check', 'Activate AOL part', 1, array('class' => 'checkbox'));
		$form->set_default('body_aol_check', @$defaults['body_aol_check']);

		// aol body
		$form->add_element('textarea', 'body_aol', 'AOL:', array('rows' => 25, 'cols' => 100));
		$form->set_default('body_aol', @$defaults['aol_body']);

		// yahoo checkbox
		$form->add_element('checkbox', 'body_yahoo_check', 'Activate yahoo part', 1, array('class' => 'checkbox'));
		$form->set_default('body_yahoo_check', @$defaults['body_yahoo_check']);

		// yahoo body
		$form->add_element('textarea', 'body_yahoo', 'Yahoo:', array('rows' => 25, 'cols' => 100));
		$form->set_default('body_yahoo', @$defaults['yahoo_body']);
		
		// yahoo date
		$form->add_element('text', 'yahoo_date', 'Date:', array('size' => 25));
		$form->set_default('yahoo_date', @$defaults['yahoo_date']);

		// domain only
		$form->add_element('textarea', 'domain_only', 'Only:', array('rows' => 8, 'cols' => 50));
		$form->set_default('domain_only', @$defaults['domain_only']);

		// domain not
		$form->add_element('textarea', 'domain_not', 'Not:', array('rows' => 8, 'cols' => 50));
		$form->set_default('domain_not', @$defaults['domain_not']);

		// header
		$rows 		= all_headers();
		$options 	= array('' => '-- none --');
		foreach ($rows as $row)
			$options[$row['id']] = $row['name'];
		
		$form->add_element('select', 'header_id', 'Header:', $options, 2);
		$form->set_default('header_id', @$defaults['header_id']);

		// footer
		$rows 		= all_footers();
		$options 	= array('' => '-- none --');
		foreach ($rows as $row)
			$options[$row['id']] = $row['name'];
		
		$form->add_element('select', 'footer_id', 'Footer:', $options, 2);
		$form->set_default('footer_id', @$defaults['footer_id']);

		// seed rotate
		$form->add_element('text', 'seed_rotate', 'Seed Rotate:', array('size' => 5));
		$form->set_default('seed_rotate', @$defaults['seed_rotate']);

		// seeds
		$form->add_element('textarea', 'seeds', 'Seeds:', array('rows' => 5, 'cols' => 50));
		$form->set_default('seeds', @$defaults['seeds']);
		$form->add_rule('seeds', 'Please supply valid email addresses for seeds', 'callback', array('class' => $this, 'method' => 'form_validate_seeds'));

		// threads
		$form->add_element('text', 'threads', 'Threads:', array('size' => 4));
		$form->set_default('threads', @$defaults['threads']);

		// thread wait;
		$form->add_element('text', 'thread_wait', 'Wait:', array('size' => 4));
		$form->set_default('thread_wait', @$defaults['thread_wait']);

		// suppression list 
		$rows 		= all_rows(query('SELECT `sup_list_id`, `title` FROM `supression_lists`;'));
		$options 	= array('' => '-- none --');
		foreach ($rows as $row)
			$options[$row['sup_list_id']] = $row['title'];

		$i = 0;

		if (!empty($_POST['suppression_list']))
			$defaults['suppression_list'] = $_POST['suppression_list'];

		if (empty($defaults['suppression_list']))
			$defaults['suppression_list'] = array('');
		
		foreach ($defaults['suppression_list'] as $suppression_list)
		{
			if (empty($suppression_list) && $i>0)
				continue;

			$form->add_element('select', 'suppression_list['.$i.']', 'Suppression List:', $options, 2);
			$form->set_default('suppression_list['.$i.']', $suppression_list);
			
			$i++;
		}
		$form->counts['suppressions'] = $i;
		// suppression clone
		$form->add_element('select', 'suppression_list[]', 'Suppression List:', $options, 2);

		return $form;
	}

	public function form_validate_links ($value, $element, $values, $args, $elements)
	{
		if (!is_array(@$_POST['tracked_link']))
			return true;

		foreach ($_POST['tracked_link'] as $index => $url)
		{
			if (empty($url))
				continue;

			$action = @$_POST['tracked_link_action'][$index];
			$target = @$_POST['tracked_link_target'][$index];

			if (empty($action))
				$action = 1;

			if ($action > 1 && empty($target))
				return false;
		}

		return true;
	}

	public function form_validate_from ($value, $element, $values, $args, $elements)
	{
		if (!is_array($values['from']))
			return false;
		
		foreach ($values['from'] as $from)
		{
			if (empty($from))
				continue;

			$from = trim($from);

			if (!$this->draft_from->validate($from))
				return false;
		}

		return true;
	}

	public function form_validate_subject ($value, $element, $values, $args, $elements)
	{
		if (!is_array($values['subject']))
			return false;
		
		foreach ($values['subject'] as $subject)
		{
			if (empty($subject))
				continue;

			$subject = trim($subject);

			if (strlen($subject) > 98)
				return false;
		}

		return true;
	}


	public function form_validate_domains ($value, $element, $values, $args, $elements)
	{
		if (!is_array(@$values['domain']))
			return false;

		$i = 0;
		foreach ($values['domain'] as $domain)
			$i++;

		if ($i > 0)
			return true;
		else
			return false;
	}

	public function form_validate_lists ($value, $element, $values, $args, $elements)
	{
		if (!is_array(@$values['list']))
			return false;

		$i = 0;
		foreach ($values['list'] as $list)
			$i++;

		if ($i > 0)
			return true;
		else
			return false;
	}

	public function form_validate_bodies ($value, $element, $values, $args, $elements)
	{
		if (empty($values['body_text']) && empty($values['body_html']))
			return false;

		return true;
	}

	public function form_validate_seeds ($value, $element, $values, $args, $elements)
	{
		if (empty($values['seeds']))
			return true;

		$emails 	= explode("\n", $values['seeds']);

		foreach ($emails as $email)
		{
			$parts = explode(':', $email);
			$email = trim($parts[0]);

			if (empty($email))
				continue;

			if (!validation::is_email($email))
				return false;
		}

		return true;
	}
}

$ctl 	= new ctl_draft();
switch (@$_GET['action'])
{
	case 'links_find':
		$ctl->links_find();
		break;
	case 'edit':
		$ctl->edit();
		break;
	default:
		$ctl->create();
		break;
}
?>
