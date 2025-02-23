<?php
require_once('../../lib/control_panel.php');
require_once('public.php');
require_once('campaign.cls.php');
require_once('draft.cls.php');

class campaign_stats
{
	public function __construct ()
	{
		global $tpl;

		$campaign_id = esc(@$_GET['campaign_id']);

		$this->campaign 		= new campaign;
		$this->draft 			= new draft;
		$this->tpl 				= $tpl;
		$this->tpl->styles[]	= 'table.css';
		$this->tpl->scripts[] 	= 'jquery.js';
		$this->tpl->scripts[] 	= 'campaign.js';

		if (!$this->row = $this->campaign->get($campaign_id))
			die('Could not find campaign');

		$this->draft_row = $this->draft->get($this->row['msg_id']);
	}

	public function open_download ()
	{
		global $config, $permissions;

		if (!empty($permissions->auth->user['mailer']))
			die('Access');	


		$campaign_id 	= esc(@$_GET['campaign_id']);
		$sql 			= "SELECT * FROM `opens` WHERE `schedule_id` = '{$campaign_id}';";

		$path 			= $config->values['site']['path'] . 'cp/scheduling/test/'.mktime().'_'.$campaign_id.'_open.log';
		$fh  			= fopen($path, 'w+');

		$result 		= query($sql);

		while ($row = row($result))
		{
			fwrite($fh, $row['email']."\n");
		}

		fclose($fh);

		$name 	= "open_log_{$campaign_id}.txt";
		$fh 	= fopen($path, 'r');
		$date 	= date('D M j G:i:s T Y');

		//@ob_clean();
		header("Last-Modified: " . gmdate("D, d M Y H:i:s"));
		header("Pragma: no-cache");
		header("Expires: -1");
		header('Date: ' . $date, true);
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: plain/text; name=\"$name\"\n", true);
		header("Content-Disposition: attachment; filename=\"$name\"\n", true);
		header("Content-length: ".(string)filesize($path));
		header("Connection: close");
		fpassthru($fh);
		fclose($fh);
		unlink($path);
		exit();
	}

	public function clicks_download ()
	{
		global $config, $permissions;

		if (!empty($permissions->auth->user['mailer']))
			die('Access');	

		$tracked_link_id 	= (int) esc(@$_GET['tracked_link_id']);
		$sql 				= "SELECT * FROM `tracked_link_click` WHERE `tracked_link_id` = '{$tracked_link_id}';";

		$path 				= $config->values['site']['path'] . 'cp/scheduling/test/'.mktime().'_'.realpath($tracked_link_id).'_click.log';
		$fh  				= fopen($path, 'w+');

		$result 			= query($sql);

		while ($row = row($result))
		{
			fwrite($fh, $row['email']."\n");
		}

		fclose($fh);

		$name 	= "click_log_{$tracked_link_id}.txt";
		$fh 	= fopen($path, 'r');
		$date 	= date('D M j G:i:s T Y');

		//@ob_clean();
		header("Last-Modified: " . gmdate("D, d M Y H:i:s"));
		header("Pragma: no-cache");
		header("Expires: -1");
		header('Date: ' . $date, true);
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: plain/text; name=\"$name\"\n", true);
		header("Content-Disposition: attachment; filename=\"$name\"\n", true);
		header("Content-length: ".(string)filesize($path));
		header("Connection: close");
		fpassthru($fh);
		fclose($fh);
		unlink($path);
		exit();
	}

	public function clicks_top ()
	{
		$tracked_link_id 	= esc(@$_GET['tracked_link_id']);
		$sql 				= "SELECT * FROM `tracked_link_click` WHERE `tracked_link_id` = '{$tracked_link_id}';";

		$result 			= query($sql);
		$clicks 			= array();

		while ($row = row($result))
		{
			$parts 	= explode('@', $row['email']);

			if (empty($parts[0]) || empty($parts[1]))
				continue;

			$local 	= $parts[0];
			$domain = $parts[1];

			$parts 	= explode('.', $domain);

			if (@$parts[1] == 'rr')
				$domain = 'rr.com';

			if (!empty($clicks[$domain]))
			{
				$clicks[$domain]++;
				continue;
			}
			
			$clicks[$domain] = 1;
		}

		arsort($clicks);

		$clicks = array_slice($clicks, 0, 20);

		$this->tpl->css 		= '';
		$this->tpl->clicks 		= $clicks;
		$this->tpl->template 	= 'cp/reporting/campaign/click_top.tpl.php';
		$this->tpl->display('cp/layout-pop.php');
	}
}

$cs = new campaign_stats;

switch (@$_GET['action'])
{
	case 'top_click':
		$cs->clicks_top();
		break;
	case 'click_download':
		$cs->clicks_download();
		break;
	case 'open_download':
		$cs->open_download();
		break;
	default:
		$cs->index();
		break;
}
?>
