<?php
require_once('public.php');
require_once('ip.cls.php');
require_once('link_tracking.cls.php');

require_once('delivery_configuration.cls.php');

class draft
{
	protected $draft_id;
	protected $draft_row;
	protected $options;
	protected $fields;
	protected $open_html 		= '<img src="http://{{dn}}/t/o/{{03}}/{{02}}/%d.gif" />';
	protected $bodies 			= array();
	protected $our_rotations 	= array();

	public function __construct ($options = array())
	{
		// setup default options
		$default_options 					= array();
		$default_options['link_tracking'] 	= true;
		// set this to true for a dummy run dont track links etc
		$default_options['test']			= true;
		$default_options['headers_footers'] = true;

		$options  							= array_merge($default_options, $options);

		$this->options 				= $options;

		$this->fields 	= array();
		$this->fields[] = 'server_id';
		$this->fields[] = 'user_id';
		$this->fields[] = 'delivery_configuration_id';
		$this->fields[] = 'title';
		$this->fields[] = 'state';
		$this->fields[] = 'size';
		$this->fields[] = 'max_recipients';
		$this->fields[] = 'start_recipient';
		$this->fields[] = 'query';
		$this->fields[] = 'send_type';
		$this->fields[] = 'content';
		$this->fields[] = 'body';
		$this->fields[] = 'html_body';
		$this->fields[] = 'aol_body';
		$this->fields[] = 'yahoo_body';
		$this->fields[] = 'yahoo_date';
		$this->fields[] = 'yahoo_date_original';
		$this->fields[] = 'comments';
		$this->fields[] = 'seeds';
		$this->fields[] = 'link_tracking';
		$this->fields[] = 'sup_list_id';
		$this->fields[] = 'header';
		$this->fields[] = 'footer';
		$this->fields[] = 'threads';
		$this->fields[] = 'thread_wait';
		$this->fields[] = 'from_domain';
		$this->fields[] = 'aol_rotate';
		$this->fields[] = 'aol_check_total';
		$this->fields[] = 'aol_check_hits';
		$this->fields[] = 'open_action';
		$this->fields[] = 'open_list_id';
		$this->fields[] = 'embed_images';
		$this->fields[] = 'seed_rotate';
		$this->fields[] = 'max_per_ip';
	}

	public function get ($draft_id)
	{
		$sql = "SELECT * FROM `msg` WHERE `id` = '$draft_id';";
		$row = row(query($sql));
		return $row;
	}

	public function get_froms ($single = false)
	{
		$limit 	= '';
		if ($single !== false)
			$limit = ' LIMIT 0,1';

		$sql 	= "SELECT * FROM `msg_to_from` WHERE `msg_id` = '{$this->draft_id}' {$limit};";
	
		if ($single !== false)
			$rows 	= row(query($sql));
		else
			$rows 	= all_rows(query($sql));

		return $rows;
	}

	public function get_subjects ($single = false)
	{
		$limit 	= '';
		if ($single !== false)
			$limit = ' LIMIT 0,1';

		$sql 	= "SELECT * FROM `msg_to_subject` WHERE `msg_id` = '{$this->draft_id}' {$limit};";
	
		if ($single !== false)
			$rows 	= row(query($sql));
		else
			$rows 	= all_rows(query($sql));

		return $rows;
	}

	public function load_draft ($draft_id)
	{
		if (!($data = $this->get($draft_id)))
			return false;

		$this->draft_row 		= $data;
		$this->draft_id 		= $draft_id;
		$this->our_rotations 	= array();
		return $data;
	}

	public function build ()
	{
		if (empty($this->draft_row))
			return false;

		if ($this->options['link_tracking'] !== false)
			$this->link_tracking();

		if ($this->options['headers_footers'] !== false)
			$this->header_footer();

		$this->headers();
		$this->build_content();
		$this->replace_macros();
		return $this->bodies;	
	}

	public function link_tracking ($body = false)
	{
		require_once('link_tracking.cls.php');

		$link_tracking 	= new link_tracking();	
	
		$sql 			= "SELECT * FROM `tracked_link` WHERE `draft_id` = '{$this->draft_id}';";
		$result 		= query($sql);
		// sort in to strlen order biggest first
		$link_lengths 	= array();
		$links 			= array();
		while ($row = row($result))
		{
			$links[$row['tracked_link_id']] 		= $row;
			$link_lengths[$row['tracked_link_id']] 	= strlen($row['url']);
		}
	
		ksort($link_lengths, SORT_NUMERIC);
	
		foreach ($link_lengths as $tracked_link_id => $len)
		{
			$link_format 	= $link_tracking->format($tracked_link_id);

			if ($body === false)
			{
				$this->draft_row['body'] 		= str_replace($links[$tracked_link_id]['url'], $link_format, $this->draft_row['body']);
				$this->draft_row['html_body'] 	= str_replace($links[$tracked_link_id]['url'], $link_format, $this->draft_row['html_body']);
				$this->draft_row['aol_body'] 	= str_replace($links[$tracked_link_id]['url'], $link_format, $this->draft_row['aol_body']);
				$this->draft_row['yahoo_body'] 	= str_replace($links[$tracked_link_id]['url'], $link_format, $this->draft_row['yahoo_body']);
			}
			else
				$body = str_replace($links[$tracked_link_id]['url'], $link_format, $body);
		}

		if ($body === false)
			return true;
		else
			return $body;
	}

	protected function header_footer ()
	{
		require_once('extra_content.php');

		if ($this->draft_row['header'] && $head = get_content_data($this->draft_row['header']))
		{
			if (!empty($this->draft_row['body']))
				$this->draft_row['body'] 		= $head['text']['data'] . $this->draft_row['body'];
			if (!empty($this->draft_row['html_body']))
				$this->draft_row['html_body'] 	= $head['html']['data'] . $this->draft_row['html_body'];
			 if (!empty($this->draft_row['aol_body']))
				$this->draft_row['aol_body'] 	= $head['text']['data'] . $this->draft_row['aol_body'];
			if (!empty($this->draft_row['yahoo_body']))
				$this->draft_row['yahoo_body'] 	= $head['text']['data'] . $this->draft_row['yahoo_body'];
		}
		
		// opener pixel - do we want this on a test?
		//$this->draft_row['html_body'] .= sprintf($this->open_html, $this->draft_row['id']);
		
		if ($this->draft_row['footer'] && $foot = get_content_data($this->draft_row['footer']))
		{
			if (!empty($this->draft_row['body']))
				$this->draft_row['body'] 		.= $foot['text']['data'];
			if (!empty($this->draft_row['html_body']))
				$this->draft_row['html_body'] 	.= $foot['html']['data'];
			if (!empty($this->draft_row['aol_body']))
				$this->draft_row['aol_body'] 	.= $foot['text']['data'];
			if (!empty($this->draft_row['yahoo_body']))
				$this->draft_row['yahoo_body'] 	.= $foot['text']['data'];
		}
	}

	protected function headers ()
	{
		$delivery_configuration = new delivery_configuration();
		$configuration 			= $delivery_configuration->get($this->draft_row['delivery_configuration_id']);
		$headers 				= $configuration['header'];

		/*srand(time());
		$config  	= getDBConfig('', 1);
		$headers 	= $config['HEADERS'];
		$mi         = substr(md5(rand() % 1000000), 0, 20) . date("YmdHis");
		$date_now   = date("r");
		$headers 	= str_replace('{{header_mi}}', $mi, $headers);
		$headers 	= str_replace('{{header_date}}', $date_now, $headers);*/

		$headers 	= str_replace("\r", "", $headers);
		$headers 	= str_replace("\n\n", "\n", $headers);

		$headers  	= rtrim($headers, "\n");
		$headers 	.= "\n";

		$this->draft_row['headers'] = $headers;
	}

	protected function build_content ()
	{
		if (!empty($this->draft_row['body']) && !empty($this->draft_row['html_body']))
			$this->build_content_both();
		elseif (!empty($this->draft_row['body']))
			$this->build_content_text();
		else
			$this->build_content_html();
	}

	protected function build_content_text ($return = false, $use_body = false)
	{
		$body 	= rtrim($this->draft_row['headers']);

		$body 	.= "\n";
		$body 	.= 'Content-Type: text/plain; '."\n\t".'charset="iso-8859-1"';
		$body 	.= "\n\n";

		if ($use_body != false)
			$body 	.= $use_body;
		else
			$body 	.= $this->draft_row['body'];

		$body 	.= "\n";

		if ($return === true)
			return $body;

		$this->bodies['main'] 	= $body;
	
		if (empty($this->draft_row['aol_body']))
			$this->bodies['aol'] 	= $body;
		else
			$this->bodies['aol'] 	= $this->build_content_text(true, $this->draft_row['aol_body']);

		if (empty($this->draft_row['yahoo_body']))
			$this->bodies['yahoo'] 	= $body;
		else
			$this->bodies['yahoo'] 	= $this->build_content_text(true, $this->draft_row['yahoo_body']);
	}

	protected function http_retrieve ($url)
	{ 
		$return['url'] 	= $url; 
		$return['body'] = file_get_contents($url);
		
		# Set content type depending on file extension
		$file_ext = strtolower(substr(strrchr($url,'.'),1));
		
		if ($file_ext == "jpg")
		  $return['headers']['Content-Type'] = 'image/jpg';
		else if ($file_ext == "gif")
		  $return['headers']['Content-Type'] = 'image/gif';
		else
		  $return['headers']['Content-Type'] = 'image/unknown';
		
		return $return; 
	}

	protected function build_content_html ()
	{
		global $config;

		$body 	= rtrim($this->draft_row['headers']);
		$images = array();

		if (!empty($this->draft_row['embed_images']))
		{
			$i 			= 0;
			$link 		= new link_tracking();
			$images 	= $link->find('', $this->draft_row['html_body'], '', true);
			$img_data 	= array();

			foreach ($images as $image)
			{
				if (strpos($image, '{{dn}}') !== false)
				{
					$dir 	= $config->values['site']['path'].'img/';
					$path 	= $dir.str_replace('http://{{dn}}/img/', '', $image); 
				}
				else
					$path = $image;

				$img_data[$i] 			= $this->http_retrieve($path);
				$img_data[$i]['body'] 	= chunk_split(base64_encode($img_data[$i]['body'])); 
				$img_data[$i]['url'] 	= $image;
				$i++;
			}
		}

		$body 	.= "\n";
		
		if (!empty($images))
		{
			$boundary 	= '----=_Part_'.(rand() % 1000000);
			$body 	.= 'Content-Type: multipart/related; boundary="'.$boundary.'"';
			$body 	.= "\n\n";
			$body 	.= "--{$boundary}\n";
			$body 	.= "Content-Type: text/html; charset=ISO-8859-1\nContent-Transfer-Encoding: 7bit\nContent-Disposition: inline\n\n";
			$body 	.= $this->draft_row['html_body'];
			$body 	.= "\n\n";

			foreach ($img_data as $idx => $img)
			{
				$body .= "--{$boundary}\n";
				$body .= "Content-Type: {$img['headers']['Content-Type']}\nContent-Disposition: inline;\nContent-Transfer-Encoding: base64\nContent-ID: <cid".$idx.">\n\n";
				$body .= $img['body']."\n";

				$body = str_replace($img['url'], "cid:cid{$idx}", $body);
			}

			$body 	.= "--{$boundary}--\n";
		}
		else
		{
			$body   .= 'Content-type: text/html; charset="us-ascii"';
			$body 	.= "\n\n";
			$body 	.= $this->draft_row['html_body'];
			$body 	.= "\n";
		}

		$this->bodies['main'] 	= $body;

		if (empty($this->draft_row['aol_body']))
			$this->bodies['aol'] 	= $body;
		else
			$this->bodies['aol'] 	= $this->build_content_text(true, $this->draft_row['aol_body']);

		if (empty($this->draft_row['yahoo_body']))
			$this->bodies['yahoo'] 	= $body;
		else
			$this->bodies['yahoo'] 	= $this->build_content_text(true, $this->draft_row['yahoo_body']);
	}

	protected function build_content_both ()
	{
		$boundary 	= ('mg_boundary-' . (rand() % 1000000));
		$boundary 	.= '-';
		$boundary 	.= (rand() % 1000000);

		$body 		= rtrim($this->draft_row['headers']);

		$body 		.= "\n";
		$body 		.= 'Content-Type: multipart/alternative;'."\n	".' boundary="'.$boundary.'"';
		$body 		.= "\n\n";

		//Plain Text Alternative
		$body 		.= '--'.$boundary;
		$body 		.= "\n";
		$body 		.= 'Content-Type: text/plain; charset="iso-8859-1"';
		$body 		.= "\n\n";
		$body 		.= $this->draft_row['body'];
		$body 		.= "\n\n";

		//HTML Content
		$body 		.= '--'.$boundary;
		$body 		.= "\n";
		$body 		.= 'Content-Type: text/html; charset="iso-8859-1"';
		$body 		.= "\n\n";
		$body 		.= $this->draft_row['html_body'];
		$body 		.= "\n";

		$body 		.= '--'.$boundary.'--';
		$body 		.= "\n";

		$this->bodies['main'] 	= $body;

		if (empty($this->draft_row['aol_body']))
			$this->bodies['aol'] 	= $this->build_content_text(true);
		else
			$this->bodies['aol'] 	= $this->build_content_text(true, $this->draft_row['aol_body']);

		if (empty($this->draft_row['yahoo_body']))
			$this->bodies['yahoo'] 	= $body;
		else
			$this->bodies['yahoo'] 	= $this->build_content_text(true, $this->draft_row['yahoo_body']);
	}

	protected function replace_macros ()
	{
		if ($this->options['test'] === true)
			$this->replace_macros_test();
		else
			$this->replace_macros_real();
	}

	public function get_random_ip ($draft_id = false)
	{
		if (empty($this->ip))
			$this->ip = new ip();

		if (empty($draft_id))
			$draft_id = $this->draft_id;

		$ips 		= $this->ip->draft_get($draft_id);
		$ip_list 	= array();

		foreach ($ips as $ip)
		{
			$ip_info 	= $this->ip->get($ip['ip_id']);
			$ip_list[] 	= $ip_info;
		}

		$rand 	= rand(0, count($ip_list)-1);
		return $ip_list[$rand];
	}

	protected function replace_macros_test ()
	{
		$this->bodies['main'] 		= $this->replace_macros_test_rotations($this->bodies['main']);
		$this->bodies['aol'] 		= $this->replace_macros_test_rotations($this->bodies['aol']);
		$this->bodies['yahoo'] 		= $this->replace_macros_test_rotations($this->bodies['yahoo']);

		$from_line 					= $this->get_froms(true);
		$from 						= $from_line['from']." <{$from_line['from_local']}@{$from_line['from_domain']}>";
		$this->bodies['main'] 		= str_replace('{{fl}}', $from, $this->bodies['main']);
		$this->bodies['aol'] 		= str_replace('{{fl}}', $from, $this->bodies['aol']);
		$this->bodies['yahoo'] 		= str_replace('{{fl}}', $from, $this->bodies['yahoo']);

		if (!empty($this->draft_row['from_domain']))
			$domain = $this->draft_row['from_domain'];
		else
			$domain = getDefaultDomain();

		// open tracking
		$pixel = '<img src="http://{{dn}}/i/{{m0}}{{02}}c{{03}}" />';

		$this->bodies['main']	= str_replace('{{open_img}}', $pixel, $this->bodies['main']);
		$this->bodies['aol']	= str_replace('{{open_img}}', $pixel, $this->bodies['aol']);
		$this->bodies['yahoo']	= str_replace('{{open_img}}', $pixel, $this->bodies['yahoo']);
	
		$this->bodies['main']		= str_replace('{email}', '{{01}}', $this->bodies['main']);
		$this->bodies['aol']		= str_replace('{email}', '{{01}}', $this->bodies['aol']);
		$this->bodies['yahoo']		= str_replace('{email}', '{{01}}', $this->bodies['yahoo']);
		
		$this->bodies['main'] 		= str_replace('{{dn}}', $domain, $this->bodies['main']);
		$this->bodies['aol'] 		= str_replace('{{dn}}', $domain, $this->bodies['aol']);
		$this->bodies['yahoo'] 		= str_replace('{{dn}}', $domain, $this->bodies['yahoo']);
	
		$user_id 			= chr((97+rand(0,25))).'z'.rand(60,99);
		$this->bodies['aol'] 		= str_replace('{{02}}', $user_id, $this->bodies['aol']);
		$this->bodies['yahoo'] 		= str_replace('{{02}}', $user_id, $this->bodies['yahoo']);
		$this->bodies['main'] 		= str_replace('{{02}}', $user_id, $this->bodies['main']);
	
		$list_id 			= rand(32,99);	
		$this->bodies['aol'] 		= str_replace('{{03}}', $list_id, $this->bodies['aol']);
		$this->bodies['yahoo'] 		= str_replace('{{03}}', $list_id, $this->bodies['yahoo']);
		$this->bodies['main'] 		= str_replace('{{03}}', $list_id, $this->bodies['main']);
	
		$this->bodies['aol'] 		= str_replace('{{m0}}', $this->draft_id, $this->bodies['aol']);
		$this->bodies['yahoo'] 		= str_replace('{{m0}}', $this->draft_id, $this->bodies['yahoo']);
		$this->bodies['main'] 		= str_replace('{{m0}}', $this->draft_id, $this->bodies['main']);

			
		$subject_line 				= $this->get_subjects(true);
		$this->bodies['main'] 		= str_replace('{{sl}}', $subject_line['subject'], $this->bodies['main']);
		$this->bodies['aol']		= str_replace('{{sl}}', $subject_line['subject'], $this->bodies['aol']);
		$this->bodies['yahoo']		= str_replace('{{sl}}', $subject_line['subject'], $this->bodies['yahoo']);

		$this->bodies['main'] 		= $this->replace_macros_test_random_string($this->bodies['main']);
		$this->bodies['aol'] 		= $this->replace_macros_test_random_string($this->bodies['aol']);
		$this->bodies['yahoo'] 		= $this->replace_macros_test_random_string($this->bodies['yahoo']);

		$this->bodies['main'] 		= $this->replace_macros_test_strftime($this->bodies['main']);
		$this->bodies['aol'] 		= $this->replace_macros_test_strftime($this->bodies['aol']);
		$this->bodies['yahoo'] 		= $this->replace_macros_test_strftime($this->bodies['yahoo']);

		$this->bodies['main'] 		= $this->replace_macros_test_fields($this->bodies['main']);
		$this->bodies['aol'] 		= $this->replace_macros_test_fields($this->bodies['aol']);
		$this->bodies['yahoo'] 		= $this->replace_macros_test_fields($this->bodies['yahoo']);

		$ip 						= getDefaultIP();
		$this->bodies['main'] 		= str_replace('{{ip}}', $ip, $this->bodies['main']);
		$this->bodies['aol'] 		= str_replace('{{ip}}', $ip, $this->bodies['aol']);
		$this->bodies['yahoo'] 		= str_replace('{{ip}}', $ip, $this->bodies['yahoo']);

		$date 						= date('D, j M Y h:i:s +0000');
		$this->bodies['main'] 		= str_replace('{{date}}', $date, $this->bodies['main']);
		$this->bodies['aol'] 		= str_replace('{{date}}', $date, $this->bodies['aol']);
		$this->bodies['yahoo'] 		= str_replace('{{date}}', $date, $this->bodies['yahoo']);

		$sql 			= "SELECT `content` FROM `content_book` ORDER BY rand() LIMIT 0,1;";
		$rw 			= row(query($sql));
		$book_content 	= @$rw['content'];
	
		$this->bodies['main'] 		= str_replace('{{book_content}}', $book_content, $this->bodies['main']);
		$this->bodies['aol'] 		= str_replace('{{book_content}}', $book_content, $this->bodies['aol']);
		$this->bodies['yahoo'] 		= str_replace('{{book_content}}', $book_content, $this->bodies['yahoo']);
	}

	protected function replace_macros_test_fields ($body)
	{
		$draft_personalization 	= new draft_personalization();
		$defaults 				= $draft_personalization->get_defaults();
		$fields 				= $draft_personalization->find_fields($body);

		foreach ($fields as $field)
			$body = str_replace('{'.$field.'}', @$defaults[$field], $body);

		return $body;
	}

	public function rotations_get ($body)
	{
		$rotations 	= array();

		preg_match_all('|{ro{(.*)}}|U', $body, $matches);

		foreach ($matches[1] as $index => $match)
		{
			if (!in_array($match, $rotations))
			{
				$content_info = get_content_info(false, $match);

				if (!empty($content_info))
					$rotations[$content_info['id']] = $content_info['name'];
			}
		}

		return $rotations;
	}

	protected function replace_macros_test_rotations ($body)
	{
		preg_match_all('|{ro{(.*)}}|U', $body, $matches);

		foreach ($matches[1] as $index => $match)
		{
			if (empty($our_rotations[$match]))
			{
				$this->our_rotations[$match] = rotation_get_random($match);
			}

			$body = str_replace($matches[0][$index], $this->our_rotations[$match], $body);
		}

		return $body;
	}

	protected function replace_macros_test_strftime ($body)
	{
		preg_match_all('|{sft{(.*)}}|U', $this->bodies['main'], $matches);

		foreach ($matches[0] as $index => $match)
		{
			$string 	= strftime($matches[1][$index]);
			$body 		= str_replace($match, $string, $body);
		}

		return $body;
	 }

	protected function replace_macros_test_random_string ($body)
	{
		preg_match_all('|{{y([0-9][0-9])([0-9][0-9])}}|U', $body, $matches);
		
		foreach ($matches[0] as $index => $match)
		{
			$low 	= (int)$matches[1][$index];
			$high 	= (int)$matches[2][$index];

			$length = rand($low, $high);
			$string = '';
			for ($i=1;$i<=$length;$i++)
			{
				$string .= chr((97+rand(0,25)));
			}

			$body 	= str_replace($match, $string, $body);
		}

		return $body;
	}

	protected function replace_macros_real ()
	{
		$this->bodies['main']	= str_replace('{email}', '{{01}}', $this->bodies['main']);
		$this->bodies['aol']	= str_replace('{email}', '{{01}}', $this->bodies['aol']);
		$this->bodies['yahoo']	= str_replace('{email}', '{{01}}', $this->bodies['yahoo']);
 
		$this->bodies['main']	= str_replace('{{sl}}', '{{05}}', $this->bodies['main']);
		$this->bodies['aol']	= str_replace('{{sl}}', '{{05}}', $this->bodies['aol']);
		$this->bodies['yahoo']	= str_replace('{{sl}}', '{{05}}', $this->bodies['yahoo']);
		
		// open tracking
		$pixel = '<img src="http://{{dn}}/i/{{m0}}{{02}}c{{03}}" />';

		$this->bodies['main']	= str_replace('{{open_img}}', $pixel, $this->bodies['main']);
		$this->bodies['aol']	= str_replace('{{open_img}}', $pixel, $this->bodies['aol']);
		$this->bodies['yahoo']	= str_replace('{{open_img}}', $pixel, $this->bodies['yahoo']);
	}

	public function insert ($values)
	{
		$sql_fields = '';
		$sql_values = '';

		$i = 0;
		foreach ($this->fields as $field)
		{
			if (isset($values[$field]))
			{
				$i++;

				$value 		= esc($values[$field]);
				$sql_fields .= "`$field`,";
				$sql_values .= "'$value',";
			}
		}

		if ($i == 0)
			return false;

		$sql_fields 	= rtrim($sql_fields, ',');
		$sql_values 	= rtrim($sql_values, ',');
		$sql 			= "INSERT INTO `msg` ($sql_fields) VALUES ($sql_values);";
		return insert($sql);
	}

	public function update ($draft_id, $values)
	{
		$draft_id 	= esc($draft_id);
		$sql_update = '';

		$i = 0;

		foreach ($this->fields as $field)
		{
			if (isset($values[$field]))
			{
				$i++;

				$value 		= esc($values[$field]);
				$sql_update .= "`$field`='$value',";
			}
		}

		if ($i == 0)
			return false;

		$sql_update = rtrim($sql_update, ',');
		$sql 		= "UPDATE `msg` SET $sql_update WHERE `id` = '$draft_id';";
		return query($sql);
	}
}

class draft_personalization
{
	protected $draft;
	protected $draft_subject;
	protected $draft_row;

	public function __construct ()
	{
		$this->draft 			= new draft();
		$this->draft_subject 	= new draft_subject();
	}

	public function parse ($draft_id)
	{
		if (($this->draft_row = $this->draft->get($draft_id)) == false)
			return false;

		$text 	= $this->munge_bits();
		$fields = $this->find_fields($text);

		return $fields;
	}

	private function munge_bits ()
	{
		if (empty($this->draft_row))
			return false;
		
		$munge 	= $this->draft_row['body'].$this->draft_row['html_body'].$this->draft_row['aol_body'].$this->draft_row['yahoo_body'];
		
		$subjects = $this->draft_subject->get($this->draft_row['id']);

		foreach ($subjects as $subject)
			$munge .= $subject['subject'];
	
		return $munge;	
	}

	public function find_fields ($text)
	{	
		global $config;

		$valid_cols = getEmailCols($config->values['mm_field_hide']);
		preg_match_all('/\{([0-9a-z_]+)\}(?!\})/iUu', $text, $matches);
		
		$fields 	= array();

		foreach ($matches[1] as $match)
		{
			if (!in_array($match, $fields) && in_array($match, $valid_cols))
				$fields[] = $match;
		}	

		return array_values($fields);
	}

	public function get_defaults ()
	{
		$sql 	= "SELECT * FROM `mm_defaults`;";
		$row 	= row(query($sql));
		return $row;
	}
}

class draft_domain
{
	public function get_groups ()
	{
		$sql 	= "SELECT * FROM `domain_group`;";
		$result = query($sql);
		$groups = array();

		while ($row = row($result))
			$groups[$row['domain_group_id']] = $row;

		return $groups;
	}

	public function get ($draft_id, $invert = 0)
	{
		$draft_id 	= esc($draft_id);
		$sql 		= "SELECT * FROM `msg_to_domain_2` WHERE `msg_id` = '$draft_id' AND `invert` = '$invert';";

		return all_rows(query($sql));
	}

	public function create ($draft_id, $domain, $invert = 0, $delete_old = true)
	{
		if ($delete_old === true)
		{
			if (!$this->delete($draft_id, $invert))
				return false;
		}

		if (!is_array($domain))
			$domain = array($domain);

		foreach ($domain as $dom)
		{
			$dom = trim($dom);
			if (empty($dom))
				continue; 

			$this->insert($draft_id, $dom, $invert);
		}
	}

	public function delete ($draft_id, $invert = 0)
	{
		$draft_id 	= esc($draft_id);
		$sql 		= "DELETE FROM `msg_to_domain_2` WHERE `msg_id` = '$draft_id' AND `invert` = $invert";

		return query($sql);
	}

	private function insert ($draft_id, $domain, $invert)
	{
		$draft_id 		= esc($draft_id);
		$domain 		= esc($domain);
		$invert 		= esc($invert);

		$sql 			= 'INSERT INTO `msg_to_domain_2` ';
		$sql 			.= '(`msg_id`, `domain`, `invert`)';
		$sql 			.= ' VALUES '; 
		$sql 			.= "('$draft_id', '$domain', '$invert');";

		return query($sql);
	}
}

class draft_from
{
	public function get ($draft_id)
	{
		$draft_id 	= esc($draft_id);
		$sql 		= "SELECT * FROM `msg_to_from` WHERE `msg_id` = '$draft_id';";

		return all_rows(query($sql));
	}

	public function create ($draft_id, $from, $delete_old = true)
	{
		if ($delete_old === true)
		{
			if (!$this->delete($draft_id))
				return false;
		}

		if (!is_array($from))
			$from = array($from);

		foreach ($from as $from_line)
		{
			$from_line = trim($from_line);
			if (!$parts = $this->validate($from_line))
				continue;

			list($from, $local, $domain) = $parts;
			$this->insert($draft_id, $from, $local, $domain);
		}
	}

	public function validate ($from_line)
	{
		$regexp_email 	= '([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*)@([A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[a-z]{2,4}))';
		$regexp_dn 		= '([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*)@({{dn}})';
		
		preg_match_all('/(.*) <'.$regexp_email.'>/U', $from_line, $matches);
		
		if (!empty($matches[0]))
		{
			$return = array();
			$return[0] = $matches[1][0];
			$return[1] = $matches[2][0];
			$return[2] = $matches[4][0];
			return $return;	
		}
		else
		{
			preg_match_all('/(.+) <'.$regexp_dn.'>/U', $from_line, $matches);

			if (!empty($matches[0]))
			{
				$return = array();
				$return[0] = $matches[1][0];
				$return[1] = $matches[2][0];
				$return[2] = $matches[4][0];
				return $return;	
			}
		}

		return false;
	}

	public function delete ($draft_id)
	{
		$draft_id 	= esc($draft_id);
		$sql 		= "DELETE FROM `msg_to_from` WHERE `msg_id` = '$draft_id';";

		return query($sql);
	}

	private function insert ($draft_id, $from_name, $from_local, $from_domain)
	{
		$draft_id 		= esc($draft_id);
		$from_name 		= esc($from_name);
		$from_local 	= esc($from_local);
		$from_domain 	= esc($from_domain);

		$sql 			= 'INSERT INTO `msg_to_from` ';
		$sql 			.= '(`msg_id`, `from`, `from_local`, `from_domain`)';
		$sql 			.= ' VALUES '; 
		$sql 			.= "('$draft_id', '$from_name', '$from_local', '$from_domain');";

		return query($sql);
	}
}

class draft_subject
{
	public function get ($draft_id)
	{
		$draft_id 	= esc($draft_id);
		$sql 		= "SELECT * FROM `msg_to_subject` WHERE `msg_id` = '$draft_id';";

		return all_rows(query($sql));
	}

	public function create ($draft_id, $subject, $delete_old = true)
	{
		if ($delete_old === true)
		{
			if (!$this->delete($draft_id))
				return false;
		}

		if (!is_array($subject))
			$subject = array($subject);

		foreach ($subject as $subject)
		{
			if (empty($subject))
				continue;

			$this->insert($draft_id, $subject);
		}
	}

	public function delete ($draft_id)
	{
		$draft_id 	= esc($draft_id);
		$sql 		= "DELETE FROM `msg_to_subject` WHERE `msg_id` = '$draft_id';";

		return query($sql);
	}

	private function insert ($draft_id, $subject)
	{
		$draft_id 		= esc($draft_id);
		$subject 		= esc($subject);
		$sql 			= "INSERT INTO `msg_to_subject` (`msg_id`, `subject`) VALUES ('$draft_id', '$subject');";
		
		return query($sql);
	}
}

class draft_suppression
{
	public function get ($draft_id)
	{
		$draft_id 	= esc($draft_id);
		$sql 		= "SELECT * FROM `msg_to_suppression` WHERE `msg_id` = '$draft_id';";

		return all_rows(query($sql));
	}

	public function create ($draft_id, $suppression, $delete_old = true)
	{
		if ($delete_old === true)
		{
			if (!$this->delete($draft_id))
				return false;
		}

		if (!is_array($suppression))
			$suppression = array($suppression);

		foreach ($suppression as $suppression)
		{
			if (empty($suppression))
				continue;

			$this->insert($draft_id, $suppression);
		}
	}

	public function delete ($draft_id)
	{
		$draft_id 	= esc($draft_id);
		$sql 		= "DELETE FROM `msg_to_suppression` WHERE `msg_id` = '$draft_id';";

		return query($sql);
	}

	private function insert ($draft_id, $suppression)
	{
		$draft_id 		= esc($draft_id);
		$suppression 	= esc($suppression);
		$sql 			= "INSERT INTO `msg_to_suppression` (`msg_id`, `suppression_list_id`) VALUES ('$draft_id', '$suppression');";
		
		return query($sql);
	}
}
?>
