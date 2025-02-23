<?php
define('AOL_RATIO', 1);
define('AOL_CONFIRM', 2);
define('AOL_COMPLAINT', 3);

class complaint
{
	private $is_aol 	= false;
	private $email 		= '';
	private $ids_got 	= array();
	public 	$report 	= '';

	public function __construct ()
	{
		
	}

	public function parse ($email)
	{
		$this->email = $email;

		if (empty($this->email))
			return false;

		if ($this->is_aol())
		{
			if ($this->is_aol == AOL_CONFIRM)
			{
				$this->aol_confirm();
				return true;
			}

			if ($this->is_aol == AOL_RATIO)
			{
				$this->aol_ratio();
				return true;
			}
		}

		$this->find_emails();
	}

	protected function report ($msg)
	{
		$this->report .= $msg;
		print $msg;
	}

	protected function is_aol ()
	{
		// AOL Ratio Report
		if (strpos($this->email, 'AOL email concerns for') !== false)
		{
			$this->is_aol = AOL_RATIO;
			return AOL_RATIO;
		}

		// AOL Confirm Link
		if (strpos($this->email, 'Subject: Request Confirmation') !== false)
		{
			$this->is_aol = AOL_CONFIRM;
			return AOL_CONFIRM;
		}

		// A Complaint
		if (strpos($this->email, 'postmaster.aol.com') !== false)
		{
			$this->is_aol = AOL_COMPLAINT;
			return AOL_COMPLAINT;
		}

		return false;
	}

	protected function aol_confirm ()
	{
		$lines 	= explode("\n", $this->email);

		$link 		= false;
		$ip 		= false;
		$whitelist 	= false;

		foreach ($lines as $k => $line)
		{
			if (strpos($line, 'confirm_request.pl') !== false)
			{
				$link = trim($line);
				
				if ($link{strlen($link)-1} == '=')
				{
					$link = rtrim($link, '=');
					$link = $link.trim($lines[$k+1]);
					$link = str_replace('=3D', '=', $link);
				}

				$link = esc($link);
			}
			else if (strpos($line, 'Requested IP') !== false)
			{
				$ip = esc(trim($lines[$k+1]));

				if (strstr($ip, '[') !== false)
				{
					$ip_parts 	= explode('.', $ip);
					$parts 		= explode('-', $ip_parts[3]);
					$ip_parts[3]= str_replace(array('[', ']'), '' , $parts[0]);
					$ip 		= implode('.', $ip_parts);
				}

				break;
			}
		}

		if (empty($link) || empty($ip))
		{
			$this->report("AOL confirm link email but no link or IP found\n");
			return false;
		}

		if (strpos($this->email, 'Whitelist') !== false)
		{
			$whitelist = true;
			$sql = "UPDATE `server_to_ip` SET `aol_link` = '$link' WHERE `ip` = '$ip';";
		}
		else
			$sql = "UPDATE `server_to_ip` SET `aol_fl_link` = '$link' WHERE `ip` = '$ip';";

		$this->report("A confirm link was found, link = [$link], ip = [$ip], whitelist = [$whitelist]\n");

		query($sql);
	}

	protected function aol_ratio ()
	{
		$domain_regexp = "(([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4}))";

		$site 	= false;
		$ratio 	= false;
		$lines 	= explode("\n", $this->email);
	
		foreach ($lines as $line)
		{
			if (strpos($line, 'Subject: AOL email concerns for ') !== false)
			{
				preg_match($domain_regexp, strtolower($line), $matches);
	
				if ($matches[1] != '')
					$site = $matches[0];
			}
	
			if (strpos($line, 'Complaint rate:') !== false)
			{
				$string = preg_replace('/[^0-9\.]/' , '' , $line);
				$ratio 	= $string;
			}
		}
	
		if (!empty($ratio) && !empty($site))
		{
			$site 		= esc($site);
			$ratio 		= esc($ratio);
			$email_c 	= esc($this->email);
	
			$sql 	= "INSERT INTO `aol_ratio` (`ip`, `date`, `ratio`, `message`, `read`) VALUES ('$site', NOW(), '$ratio', '$email_c', '0');";
			query($sql);

			$this->report("An AOL ratio email domain = [$site], ratio = [$ratio]\n");
		}
		else
			$this->report("A AOL ratio email found but could not find site and/or ratio. site = [$site], ratio = [$ratio]\n");
	}

	protected function find_emails ()
	{
		$this->find_ids();
	}

	protected function find_ids ()
	{
		$this->link_tracking 	= new link_tracking();
		$lists 					= new list_db();
		$links 					= $this->link_tracking->find($this->email, $this->email);
		$complaint 				= false;

		foreach ($links as $link)
		{
			if ($parts = $this->id_link($link))
			{
				$list_data = $lists->get($parts['list_id']);

				if (empty($list_data))
				{
					$this->report("Found ID but list does not exist table = [{$parts['table']}], user_id = [{$parts['user_id']}], list_id = [{$parts['list_id']}].");
					continue;
				}

				if ($this->is_aol == AOL_COMPLAINT)
				{
					$link_data = $this->link_tracking->get($parts['link_id']);

					if (!empty($link_data))
					{
						$this->add_complaint($parts['domain'], $link_data['draft_id']);
						$complaint = true;
					}
				}

				if ($email = $this->find_id_email($parts['table'].$parts['user_id'], $list_data['name']))
				{
					$this->unsubscribe($email);
				}
			}
			else if ($email = $this->unsub_link($link))
			{
				$this->unsubscribe($email);	

				if ($this->is_aol == AOL_COMPLAINT && $complaint == false && !empty($this->got_draft))
					$this->add_complaint($this->got_draft['domain'], $this->got_draft['draft_id']);
			}
		}
	}

	private function add_complaint ($domain, $draft_id)
	{
		$ip 		= '';
		$date_sent 	= '';

		// try to parse out the IP
		if (preg_match('|Subject: Email Feedback Report for IP (.*)|i', $this->email, $matches))
			$ip = $matches[1];

		if (preg_match_all('|Date: (.*)|i', $this->email, $matches))
		{
			if (!empty($matches[1][1]))
				$date_sent = date('Y-m-d G:i:s', strtotime($matches[1][1]));
		}


		$domain 	= esc($domain);
		$ip 		= esc($ip);

		$sql 		= "INSERT INTO `msg_complaint_log` (`msg_id`, `ip`, `domain`, `date`, `date_sent`) VALUES ('$draft_id', INET_ATON('$ip'), '$domain', NOW(), '$date_sent');";
		query($sql);

		$sql 		= "SELECT * FROM `msg_complaint` WHERE `msg_id` = '{$draft_id}' AND `ip` = '$domain';";
		$row 		= row(query($sql));
	
		if (empty($row))
			$sql = "INSERT INTO `msg_complaint` (`msg_id`, `ip`, `count`) VALUES ('{$draft_id}', '$domain', '1');";
		else
			$sql = "UPDATE `msg_complaint` SET `count` = `count` + 1 WHERE `msg_id` = '{$draft_id}' AND `ip` = '$domain';";

		// query
		query($sql);
	}

	protected function id_link ($link)
	{
		$parts 	= explode('/', $link);
		$domain = $parts[2];

		// Must be last part and must exist
		if (!empty($parts[4]) || empty($parts[3]))
			return false;

		// Must be only alpha numeric
		if (!eregi('^[a-zA-Z0-9]+$', $parts[3]))
			return false;

		$parts 		= $this->link_tracking->parse($parts[3]);

		if (empty($parts))
			return false;
	
		$hash 		= $parts['table'].$parts['user_id'].$parts['list_id'];
		
		if (in_array($hash, $this->ids_got))
			return false;
		
		$parts['domain'] = $domain;
		$this->ids_got[] = $hash;
		
		return $parts;
	}

	protected function find_id_email ($id, $list)
	{
		$find 			= new Subscribe_Find();
		$find->rtn_one 	= true;

		$find->setList($list);
		$find->setID($id);
			
		$rw = $find->find("WHERE `id` = '$find->sub_id'");
	
		if(is_array($rw))
		{
			$email = $rw['local'] . '@' . $rw['domain']; 
		}
		else
		{
			$rw = $find->findSLog("WHERE `id` = '$find->id'");

			if(empty($rw['data']))
			{
				$data = $find->findSLogData("WHERE `id` = '$find->id'");
				$data = $data['data'];
			}
			else 
			{
				$data = $rw['data'];
			}
			
			if($data)
			{
				$data = unserialize($data);
				$email = $data['local'] . '@' . $data['domain'];
			}
			else 
			{
				$this->report("I found the ID ($id) and list ($list) but could not locate the email in lists.\n");
				return false;
			}
		}

		return $email;
	}

	protected function unsub_link ($link)
	{
		$parts = parse_url($link);
		if (!isset($parts['query']))
			return false;

		parse_str($parts['query'], $array);

		if (empty($array['e']))
			return false;

		if (!empty($array['m']))
			$this->got_draft = array('domain' => $parts['host'], 'draft_id' => $array['m']);

		return $array['e'];
	}

	public function unsubscribe ($email)
	{
		$unsubscribe 		= new Unsubscribe();
		$unsubscribe->list 	= '';
		$unsubscribe->sgdne = 1;
		$unsubscribe->gdne 	= 1;
		$unsubscribe->how 	= 4;

		$unsubscribe->setEmail($email);
		$unsubscribe->doUnsub();

		$this->report("I identified the email address to be $email this user has been removed from lists and added to GDNE & SGDNE\n");
	}
}
?>
