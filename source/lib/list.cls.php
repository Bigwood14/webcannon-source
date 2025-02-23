<?php
class lists
{
	var $lists = array();
	var $db;
	var $email_tables = array('misc','0_9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	var $email_table_prefix = 'email_';

	static public function singleton ()
	{
		static $instance;
		if (!empty($instance))
			return $instance;

		return $instance = new lists();
	}

	/**
	 * Constructor grab all the lists and but them into lists array.
	 *
	 * @return void
	 */
	function lists()
	{
		$this->lists 	= array();
		$sql 			= "SELECT * FROM `list`";
		$result 		= query($sql);

		while($row = row($result))
		   $this->lists[$row['list_id']] = $row;
	}

	public function get ($list_id)
	{
		$list_id 	= esc($list_id);
		$sql 		= "SELECT * FROM `list` WHERE `list_id` = '$list_id';";

		return row(query($sql));
	}

	/**
	 * Fetch all information on the email from list.
	 *
	 * @param string $email Email address to search by.
	 * @param string $list  List name to look in.
	 */
	function getEmail($email, $list)
	{
		if(!$this->connectList($list))
		{
			return false;
		}

		$table = $this->whatTable($email);
		$email = Management::splitEmail($email);
		$sql = "SELECT * FROM `$table` WHERE local = '".$email['local']."' AND domain = '".$email['domain']."';";
		return $this->lists[$list]['db']->GetRow($sql);
	}

	/**
	 * Which table does this address belong to?
	 *
	 * @param string $email Email address to lookup
	 */
	function whatTable($email)
	{
		if(ereg("[0-9]",$email{0}))
		{
			return $this->email_table_prefix."0_9";
		}
		elseif(eregi("[a-z]",$email{0}))
		{
			return $this->email_table_prefix . strtolower($email{0});
		}
		else
		{
			return $this->email_table_prefix."misc";
		}
	}
	
	function whatTableID($id)
	{
		$two = substr($id, 0, 2);
		
		if($two == "ab")
		{
			return $this->email_table_prefix."0_9";
		}
		if($two == "aa")
		{
			return $this->email_table_prefix."misc";
		}
		
		return $this->email_table_prefix.substr($id, 0, 1);
	}
	
	function whatID($email)
	{
		if(ereg("[0-9]",$email{0}))
		{
			return "ab";
		}
		elseif(eregi("[a-z]",$email{0}))
		{
			return $email{0}.'z';
		}
		else
		{
			return "aa";
		}
	}

	function query_list ($list_name, $sql)
	{
		$return = false;

		foreach ($this->lists as $list_id => $list)
		{
			if ($list_name == $list['name'])
			{
				$sql 		= str_replace('{list}', 'celibero_list_'.$list['list_id'], $sql);
				$return 	= true;
				break;
			}
		}

		if (empty($return))
			return false;

		return query($sql);
	}

	function countEmails ($list_name, $where = '')
	{
		foreach ($this->lists as $list_id => $list)
		{
			if ($list_name == $list['name'])
			{
				return $this->count_emails($list_id, $where);
			}
		}

		return 0;
	}

	function count_emails($list_id, $where = '')
	{
	 	if (empty($this->lists[$list_id]))
			return false;

		$total 		= 0;
	  	$list_id 	= esc($list_id);
	   
		foreach($this->email_tables AS $table)
		{
			$tbl 	= $this->email_table_prefix.$table;
			$sql 	= "SELECT COUNT(`id`) AS `count` FROM `celibero_list_{$list_id}`.$tbl $where";
			$row 	= row(query($sql));
			$total += $row['count'];
		}

		return $total;
	}

	function buildListSelect($name = 'list_name', $selected = '', $extra = '')
	{
		$html = "<select name=\"$name\" $extra>";
		
		foreach($this->lists AS $list)
		{
			if($selected == $list['name'])
				$sel = " selected";
			else 
				$sel = "";

			$html .= "<option$sel>".$list['name']."</option>";
		}

		$html .= "</select>";

		return $html;
	}

	function delete ($list_id)
	{
		if (empty($this->lists[$list_id]))
			return false;

		$list_id 	= esc($list_id);

		$sql = "DROP DATABASE `celibero_list_$list_id`";
		query($sql);

		$sql = "DELETE FROM `list` WHERE `list_id` = '$list_id';";
		query($sql);

		unset($this->lists[$list_id]);
	}

	private function insert ($name, $remote_list_id, $remote_hostname, $remote_username, $remote_password)
	{
		$name 				= esc($name);
		$remote_list_id 	= esc($remote_list_id);
		$remote_hostname 	= esc($remote_hostname);
		$remote_username 	= esc($remote_username);
		$remote_password 	= esc($remote_password);

		$sql  = "INSERT INTO `list` (`name`, `remote_list_id`, `remote_hostname`, `remote_username`, `remote_password`) VALUES ";
		$sql .= "('$name', '$remote_list_id', '$remote_hostname', '$remote_username', '$remote_password')";

		if (!query($sql))
			return false;

		return mysql_insert_id();
	}

	public function create ($name, $remote_list_id = false, $remote_hostname = false, $remote_username = false, $remote_password = false)
	{
		if (!$list_id = $this->insert($name, $remote_list_id, $remote_hostname, $remote_username, $remote_password))
			return false;

		$sql = "CREATE DATABASE `celibero_list_$list_id`";
		
		if (!query($sql))
			return false;
		
		$tbl_sql = "CREATE TABLE [table_name] (
					`id` int(11) NOT NULL auto_increment,
					`local` varchar(64) NOT NULL default '',
					`domain` varchar(64) NOT NULL default '',
					`import_id` int(11) NOT NULL default '0',
					`mask` int(1) default '0',
					`first_name` varchar(30) NOT NULL default '',
					`last_name` varchar(30) NOT NULL default '',
					`ip` varchar(30) NOT NULL default '',
					`gender` varchar(6) NOT NULL default '',
					`dob` varchar(20) NOT NULL default '',
					`state` varchar(20) NOT NULL default '',
					`zip` varchar(12) NOT NULL default '',
					`city` varchar(30) NOT NULL default '',
					`postal` varchar(30) NOT NULL default '',
					`timestamp` varchar(50) NOT NULL default '',
					`phone` varchar(30) NOT NULL default '',
					`source` varchar(30) NOT NULL default '',
					`country` varchar(30) NOT NULL default '',
					PRIMARY KEY  (`id`),
					UNIQUE KEY `email` (`local`,`domain`),
					KEY `import_id` (`import_id`),
					KEY `mask` (`mask`),
					KEY `local` (`local`),
					KEY `domain` (`domain`)
			   ) TYPE=MyISAM;";
		
		foreach($this->email_tables as $table)
		{
			$tbl 	= "`celibero_list_{$list_id}`." .$this->email_table_prefix . $table;
			$sql 	= str_replace('[table_name]', $tbl, $tbl_sql);
			query($sql);
		}
		
		$sql = "CREATE TABLE `celibero_list_[list_id]`.`slog` (
					`ts` datetime default NULL,
					`local` varchar(64) NOT NULL default '',
					`domain` varchar(64) NOT NULL default '',
					`event` tinyint(1) NOT NULL default '0',
					`how` tinyint(1) NOT NULL default '0',
					`mask` tinyint(1) NOT NULL default '0',
					`id` varchar(11) NOT NULL default '0',
					`remote_sent` int(1) NOT NULL default '0',
					PRIMARY KEY  (`id`),
					UNIQUE KEY `email` (`local`,`domain`),
					KEY `mask` (`mask`),
					KEY `event` (`event`),
					KEY `local` (`local`),
					KEY `domain` (`domain`),
					KEY `how` (`how`),
					KEY `how_2` (`how`,`remote_sent`)
				) TYPE=MyISAM";
		
		$sql = str_replace('[list_id]', $list_id, $sql);
		query($sql);
		
		$sql = "ALTER TABLE `celibero_list_$list_id`.`slog`  MAX_ROWS=1000000000 AVG_ROW_LENGTH=100";
		query($sql);
		
		$sql = "CREATE TABLE `celibero_list_[list_id]`.`slog_data` (
					`id` varchar(11) NOT NULL default '0',
					`data` text NOT NULL,
					PRIMARY KEY  (`id`)
				) TYPE=MyISAM";
		
		$sql = str_replace('[list_id]', $list_id, $sql);
		query($sql);
		
		$sql = "ALTER TABLE `celibero_list_$list_id`.`slog_data`  MAX_ROWS=1000000000 AVG_ROW_LENGTH=300";
		query($sql);
	}

	/**
	 * Get the coloums that exists in a certain list.
	 *
	 * @param string $list The name of the list to get cols from.
	 */
	function getCols($list)
	{
		$tbl = $this->email_table_prefix . $this->email_tables[0];
		$sql = "SHOW COLUMNS FROM `{list}`.$tbl";

		if (!$result = $this->query_list($list, $sql))
			return false;

		return all_rows($result);
	}
	
	function getID()
	{
		
	}
}
?>
