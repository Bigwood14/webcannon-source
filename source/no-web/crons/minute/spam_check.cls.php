<?php
class spam_check
{
	private $account_count;
	private $accounts;

	private $types 		= array('aol');
	private $protocols 	= array('imap', 'pop', 'http');

	public function __construct ()
	{
		$this->accounts 		= array();
		$this->account_count 	= 0;
	}

	public function account ($type, $username, $password, $protocol, $options = array())
	{
		if (!in_array($type, $this->types))
			return -1;

		if (!in_array($protocol, $this->protocols))
			return -2;


		$id 	= $this->account_count;

		$account = array(
			'type' 		=> $type,
			'protocol' 	=> $protocol,
			'username' 	=> $username,
			'password' 	=> $password,
			'options' 	=> $options
		);

		$this->accounts[$id] = $account;

		$this->accounts++;

		return $id;
	}

	public function add_check ($account_id, $mailing_id, $link_ids, $ips, $ids = array())
	{
		if (empty($this->accounts[$account_id]))
			return -1;

		if (empty($link_ids))
			return -2;

		if (empty($ips))
			return -3;

		$check 	= array(
			'mailing_id'=> $mailing_id,
			'ips' 		=> $ips,
			'link_ids' 	=> $link_ids,
			'ids' 		=> $ids
		);

		$this->accounts[$account_id]['check'][] = $check;

		return true;
	}

	public function check ()
	{
		$errors = array();

		foreach ($this->accounts as $account_id => $account)
		{
			if (empty($account['check']))
				continue;

			if ($account['protocol'] == 'imap')
			{
				$obj = new spam_check_imap();
				
				if (!$obj->connect('imap.aol.com', $account['username'], $account['password']))
				{
					$errors[] = 'Can not connect';
					continue;
				}

				$obj->check($account['check']);
				$this->accounts[$account_id] = $account;
			}
		}

		return $this->accounts;
	}

	public function find_links ($body_text = '', $body_html = '', $body_aol = '', $images = false)
	{
		$image_links 	= array();

		if (!empty($body_html))
			$image_links 	= array_merge($image_links, $this->find_html_links($body_html, true));
		if (!empty($body_aol))
			$image_links 	= array_merge($image_links, $this->find_html_links($body_aol, true));

		$image_links 	= array_unique($image_links);

		if ($images === true)
			return $image_links;

		$normal_links 	= array();

		foreach (array($body_text, $body_html, $body_aol) as $body)
			$normal_links = array_merge($normal_links, $this->find_normal_links($body));

		foreach ($normal_links as $index => $normal_link)
		{
			if (in_array($normal_link, $image_links))
				unset($normal_links[$index]);
		}

		$normal_links 	= array_unique($normal_links);

		return array_values($normal_links);
	}

	protected function find_html_links ($body, $img = true)
	{
		$links 	= array();
		preg_match_all("/<([^>]*)>/i", $body, $matches);

		if (empty($matches[1]))
			return $links;

		foreach ($matches[1] as $match)
		{
			if ($img === true)
			{
				if (!eregi("src[\n\r ]*=", $match) && !eregi("img", $match))
					continue;
			}

			$reg_exp 	= "/(http(s)?:\/\/([^\\n\\r\"'\s>]*))[\"'\s>]?/i";
            preg_match($reg_exp, $match, $matches2);
			
			if (!empty($matches2[1]))
				$links[] = $matches2[1];
		}

		return array_unique($links);
	}

	protected function find_normal_links ($body)
	{
		$links = array();
		preg_match_all("/(http(s)?:\/\/([^\\n\\r\"'\s>]*))[\"'\s>]?/i", $body, $matches);

		if (empty($matches[1]))
			return $links;

		foreach($matches[1] as $match)
		{
			$match 		= str_replace("</a", "", $match);
			$links[] 	= $match;
		}

		return array_unique($links);
	}

	public function parse_link ($link)
	{
		$parts  = explode('/', $link);
		
		// Must be last part and must exist
		if (!empty($parts[4]) || empty($parts[3]))
			return false;
		
		// Must be only alpha numeric
		if (!eregi('^[a-zA-Z0-9]+$', $parts[3]))
			return false;

		preg_match('/([0-9]+)[^0-9]/iU', $parts[3], $matches);
		
		if (empty($matches[1]))
			return false;

		return array('link_id' => $matches[1]);	
	}
}

class spam_check_imap extends spam_check
{
	public $mbox = false;

	public function __construct ()
	{

	}

	public function connect ($hostname, $username, $password, $box = 'Spam', $port = '143')
	{
		$this->mbox = imap_open('{'.$hostname.':'.$port.'/imap}'.$box, $username, $password);

		if (!$this->mbox)
			return false;

		return true;
	}

	public function check (&$checks)
	{
		if (!$this->mbox)
			return false;

		$num_msgs = imap_num_msg($this->mbox);
		for ($i=1;$i<=$num_msgs;$i++)
		{
			$uid 	= imap_uid($this->mbox, $i);
				
			$this->check_message($uid, $checks);
		}
	}

	protected function check_message ($uid, &$checks)
	{
		$body 		= false;
		$link_ids 	= false;
		$ips 		= false;

		foreach ($checks as $k => $check)
		{
			$match_ip 	= false;
			$match_link = false;

			if (in_array($uid, $check['ids']))
				continue;

			if ($body === false)
			{
				if (!$body = imap_fetchbody($this->mbox, $uid, '', FT_UID))
					continue;
			}

			$checks[$k]['ids'][] = $uid;

			if ($ips === false)
			{
				preg_match_all("/Received:(.*)\[([0-9\.]*)\](.*)/Ui", $body, $matches);

				if (empty($matches[2]))
					continue;

				$found 	= false;
				$ips 	= $matches[2];				
			}
		
			foreach ($ips as $ip)
			{
				if (in_array($ip, $check['ips']))
				{
					$match_ip 	= $ip;
					$found 		= true;
					break;
				}
			}

			if ($found == false)
				continue;
			
			if ($link_ids === false)
			{
				$link_ids = array();

				if (!$links = $this->find_links($body, $body, ''))
					continue;

				foreach ($links as $link)
				{
					//print $link."\n";
					$values = $this->parse_link($link);

					if (empty($values))
						continue;

					$links_ids[] = $values['link_id'];
				}
			}

			$found = false;

			foreach ($links_ids as $link_id)
			{
				if (in_array($link_id, $check['link_ids']))
				{
					$match_link = $link_id;
					$found 		= true;
					break;
				}
			}

			if ($found == false)
				continue;

			@$checks[$k]['hits'][$match_ip]++;
		}
	}
}
?>
