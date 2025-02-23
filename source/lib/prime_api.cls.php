<?php
class prime_api
{
	private $hostname;
	private $secure = true;
	private $token;
	private $error;
	private $method;
	private $url;

	public $debug = false;
	//public $debug = true;

	public function __construct ($hostname = 'api.prime.webcannon.com', $secure = false)
	{
		$this->hostname = $hostname;
		$this->secure 	= $secure;
	}

	public function set_hostname ($hostname, $secure = true)
	{
		$this->hostname = $hostname;
		$this->secure 	= $secure;
	}

	private function make_url ($page, $args= array())
	{
		if ($this->secure == true)
			$url = 'https://';
		else
			$url = 'http://';

		$url .= $this->hostname.$page.'?';

		if (!empty($args))
		{
			$str = '';

			foreach ($args as $key => $value)
				$str .= $key.'='.urlencode($value).'&';
				 
			$url 	.= $str;
		}

		if (!empty($this->token))
			$url 	.= 'token='.$this->token;

		return rtrim($url, '&?');
	}

	public function get_error ()
	{
		return $this->error;
	}

	public function get_method ()
	{
		return $this->method;
	}

	public function get_url()
	{
		return $this->url;
	}

	public function error_detail ()
	{
		$str = "Error: $this->error\nMethod: $this->method\nURL: $this->url\n";

		return $str;
	}

	public function login ($username, $password)
	{
		$url 	= $this->make_url('/login/', array('username' => $username, 'password' => $password));
		
		if (!($xml = $this->get($url)))
			return false;

		if (empty($xml->token))
		{
			$this->error = 'No login token found.';
			return false;
		}

		$this->token = $xml->token;

		return true;
	}

	public function list_list ()
	{
		$url 	= $this->make_url('/list/', array());

		if (!($xml = $this->get($url)))
			return false;

		$lists 	= array();
		
		foreach ($xml->lists->list as $list)
		{
			$l 				= array();
			$l['name']		= (string)$list->name;
			$l['list_id']	= (int)$list['id'];
			$l['count'] 	= (int)$list['count'];

			$lists[] = $l;
		}

		return $lists;
	}

	public function list_get ($list_id, $pos_str = '')
	{
		$url 	= $this->make_url('/list/get/', array('list_id' => $list_id, 'pos' => $pos_str));
		
		if (!($xml = $this->get($url)))
			return false;

		$recipients = array();

		foreach ($xml->recipients->recipient as $recipient)
		{
			$recip 				= array();
			$recip['email'] 	= (string)$recipient->email;
			$recipients[] 	 	= $recip;
		}

		$postition_str = (string)$xml->position['str'];

		return array('r' => $recipients, 'p' => $postition_str);
	}

	public function list_get_file ($list_id, $pos_str = '')
	{
		$url 	= $this->make_url('/list/get_file/', array('list_id' => $list_id, 'pos' => $pos_str));

		if (!($xml = $this->get($url, false, false, false)))
			return false;

		//echo "Memcheck2 1: ". number_format(get_mem(true)) . "\n";

		$recipients = array();

		//$count = count($xml->recipients->recipient);

		/*foreach ($xml->recipients->recipient as $recipient)
		{
			$recip 				= array();
			$recip['email'] 	= (string)$recipient->email;
			$recip['act'] 		= (string)$recipient['a'];
			$recipients[] 	 	= $recip;
			unset($recip);
		}*/

		/*for ($i=0;$i<$count;$i++)
		{
			$recip 				= array();
			$recip['email'] 	= (string)$xml->recipients->recipient[$i]->email;
			$recip['act'] 		= (string)$xml->recipients->recipient[$i]['a'];
			$recipients[] 	 	= $recip;
			unset($recip);
		}*/

		//echo "Memcheck2 2: ". number_format(get_mem(true)) . "\n";

		$postition_str = (string)$xml->position['str'];

		//echo "Memcheck2 2: ". number_format(get_mem(true)) . "\n";

		return array('r' => $xml, 'p' => $postition_str);
	}

	public function list_send_unsub ($list_id, $emails)
	{
		if (empty($emails))
			return true;

		$url 	= $this->make_url('/list/send_unsub/', array('list_id' => $list_id));
	
		$str 	= 'e[]='.implode('&e[]=', $emails);
		//$url 	.= '&'.$str;
		
		if (!($xml = $this->get($url, $str)))
			return false;

		return true;
	}

	public function list_send_sub ($list_id, $emails)
	{
		if (empty($emails))
			return true;

		$url 	= $this->make_url('/list/send_sub/', array('list_id' => $list_id));
	
		$str 	= 'e[]='.implode('&e[]=', $emails);
		//$url 	.= '&'.$str;
		
		if (!($xml = $this->get($url, $str)))
			return false;

		return true;
	}

	public function list_get_unsub ($list_id, $pos_str = '')
	{
		$url 	= $this->make_url('/list/get_unsub/', array('list_id' => $list_id, 'pos' => $pos_str));
		
		if (!($xml = $this->get($url)))
			return false;

		$recipients = array();

		foreach ($xml->recipients->recipient as $recipient)
		{
			$recip 				= array();
			$recip['email'] 	= (string)$recipient->email;
			$recipients[] 	 	= $recip;
		}

		$postition_str = (string)$xml->position['str'];

		return array('r' => $recipients, 'p' => $postition_str);
	}

	private function get ($url, $post = false, $wget = false, $test = false)
	{
		if ($this->debug == true)
			print "\n".$url."\n";

		$this-> url = $url;

		if ($wget == true)
			$ret = $this->send_wget($url);
		/*else if (function_exists('curl_init'))
			$ret = $this->send_curl($url);*/
		else
			$ret = $this->send_native($url, $post);	

		//echo "Memcheck3 1: ". number_format(get_mem(true)) . "\n";


		if ($this->debug == true)
			print "\n\n$ret\n\n";

		if (!($xml = simplexml_load_string($ret)))
		{
			$this->error = 'Could not load XML string ('.$ret.')';
			return false;
		}

		if ($xml['status'] != 'ok')
		{
			$this->error = 'A bad status was returned. Error was: '.$xml->error;
			return false;
		}

		//echo "Memcheck3 2: ". number_format(get_mem(true)) . "\n";		

		if ($test != false)
			return false;

		return $xml;
	}

	private function send_wget ($url)
	{
		$this->method 	= 'wget';
		$file 			= md5(uniqid());
		$cmd 			= "/usr/bin/wget -q -O $file $url";
		$fh 			= fopen($file, 'r');
		
		return $fh;	
	}

	private function send_native ($url, $post = false)
	{
		$opt 						= array();
		$opt['http'] 				= array();
		$opt['http']['timeout'] 	= 1800;

		if ($post != false)
		{
			$opt['http']['method'] 		= 'POST';
			$opt['http']['header'] 		= 'Content-type: application/x-www-form-urlencoded';
			$opt['http']['content'] 	= $post;
		}

		$ctx  			= stream_context_create($opt); 
		$this->method 	= 'native';
		$contents 		= file_get_contents($url, 0, $ctx);

		if (empty($contents))
		{
			$this->error = 'File get contents failed.';
			return false;
		}

		return $contents;
	}

	private function send_curl ($url)
	{
		$this->method 	= 'curl';
		$ch 			= curl_init($url);

		if (@$postargs !== false)
		{
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postargs);
		}
		
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, '');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, '');

		$response = curl_exec($ch);
		
		$this->responseInfo = curl_getinfo($ch);
		curl_close($ch);
		
		if(intval($this->responseInfo['http_code'])==200)
			return $response;	
		else
			return false;
	}
}
?>
