<?php
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
require_once('list.cls.php');

/**
  * Master Subscribe Class
  *
  * To handle subscribing emails to lists.
  *
  * @package   Subscribe
  * @author	Celibero Team
  * @version   $Id: Subscribe.php,v 1.17 2006/01/18 17:58:10 tom Exp $
  * @since	 4.1
  * @copyright Integraclick inc.
  */
class Subscribe
{
	/**
	 * Email guess  what it holds?
	 * @var string An email address.
	 */
	var $email;
	/**
	 * The lists class
	 * @var object The global lsits class.
	 */
	var $lists;
	/**
	 * DB object
	 * @var object The global master DB object. 
	 */
	var $db;
	/**
	 * List name we are using
	 * @var string List name.
	 */
	var $list;
	/**
	 * el local
	 * @var string Local part of email address.
	 */
	var $local;
	/**
	 * Domain
	 * @var string Domain part of email address.
	 */
	var $domain;
	/**
	 * Filtered words
	 * @var array An array of filtered words to check against.
	 */
	var $words;
	/**
	 * Tabla
	 * @var string The table for the email address.
	 */
	var $table;
	/**
	 * How
	 * @var int The how code for the slog bitches.
	 * 
	 * 5 = Soft Bounce Remove
	 * 6 = Hard Bounce Remove
	 */
	var $format;
	var $parts;
	var $custom_sql_fields;
	var $custom_sql_values;
	var $import_id;
	var $overwrite 	= 0;
	var $how 		= 0;
	var $dedupe;

	/**
	 * The mighty constructor!
	 *
	 * Set up some vars (globals mostly)
	 *
	 * @return void.
	 */
	function Subscribe()
	{
		global $db;

		$this->lists = lists::singleton();
		$this->db	= &$db;
	}

	/**
	 * Sets the email address var
	 *
	 * @return void
	 */
	function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * Sets how var
	 *
	 * @return void
	 */
	function setHow($how)
	{
		$this->how = $how;
	}

	/**
	 * Yup sets list var
	 *
	 * @return void
	 */
	function setList($list)
	{
		$this->list = $list;
	}

	/**
	 * Reset the current object for reuse but can save some varibales
	 * for a nifty performance gain.
	 *
	 * @param  bool $save_list  Shall we save the list name? Not much peformance gain here.
	 * @param  bool $save_words Shall we save the words array? Sweet peformance gain here.
	 * @return void
	 */
	function reset($save_list = true, $save_words = true)
	{
		$this->email	= '';
		$this->domain   = '';
		$this->local	= '';
		$this->table	= '';
		$this->word	 	= '';
		$this->s_word   = '';

		if($save_list != true)
			$this->list	 = '';

		$this->custom_sql_fields = '';
		$this->custom_sql_values = '';
	}

	/**
	 * Do the unsubscribe of email address we loaded to list we loaded!
	 *
	 * @return mixed True if successfully done else an int which ties to why it was not added.
	 */
	function doSub()
	{
		$ret = $this->test4Sub();
		// Do the sub test dance
		if($ret != 1)
			return $ret;

		// All good? Lets add this biatch
		$local  = mysql_escape_string($this->local);
		$domain = mysql_escape_string($this->domain);
		$how	= mysql_escape_string($this->how);

		$sql = "INSERT INTO `{list}`.`$this->table` (`local`,`domain`) VALUES ('$local','$domain');";
		$this->lists->query_list($this->list, $sql);

		/*$sql = "INSERT INTO `slog` (ts,local,domain,event,how) VALUES (NOW(),'$local','$domain','1','$how');";
		$this->lists->query_list($this->list, $sql);*/

		return true;
	}

	function doCustomSub()
	{
		$ret = $this->test4Sub();
		// Do the sub test dance
		if($ret != 1)
			return $ret;

		$local  = mysql_escape_string($this->local);
		$domain = mysql_escape_string($this->domain);
		$how	= mysql_escape_string($this->how);

		foreach($this->format AS $k => $format)
		{
			if($format == 'email')
			{
				$this->custom_sql_fields .= "`local`,`domain`,";
				$this->custom_sql_values .= "'$local','$domain',";
				continue;
			}
		
			$this->custom_sql_fields .= "`$format`,";
			$this->custom_sql_values .= "'".mysql_escape_string($this->parts[$k])."',";
		}
		$this->custom_sql_fields = rtrim($this->custom_sql_fields, ',');
		$this->custom_sql_values = rtrim($this->custom_sql_values, ',');


		// All sorted lets add
		$sql 	= "INSERT INTO `{list}`.`$this->table` ($this->custom_sql_fields,import_id) VALUES ($this->custom_sql_values,$this->import_id);";
		$rs 	= $this->lists->query_list($this->list, $sql);

		if(!$rs)
			print mysql_error()." ".$sql;

		if($this->overwrite > 0)
		{
			$sql = "DELETE FROM `{list}`.`slog` WHERE `local` = '$local' AND `domain` = '$domain';";
			$rs = $this->lists->query_list($this->list, $sql);
		}
		//$sql = "INSERT INTO `slog` (ts,local,domain,event,how) VALUES (NOW(),'$local','$domain','1','$how');";
		//$sql = "INSERT INTO `slog` (ts,local,domain,event,how) VALUES (NOW(),'$local','integraclick.net','1','$how');";
		//$this->lists->query_list($this->list, $sql);

		return true;
	}

	/**
	 * Test to see if there is a reason not to add the loaded address.
	 *
	 * @return int Positive int if its cool to go else a negative int.
	 */
	function test4Sub()
	{
		if(!$this->isValid())
			return -1;

		if($this->inList())
			return -2;

		if(is_array($this->dedupe))
		{
			foreach($this->dedupe AS $list)
			{
				if($this->inList($list))
					return -2;
			}
		}

		if($this->overwrite < 1)
		{
			if($this->getSLog(true) > 0)
				return -3;
		}

		if($this->isGlobalUnsub())
			return -4;

		if($this->isGlobalUnsubDomain())
			return -5;

		if($this->isGlobalWord())
			return -6;

		return 1;
	}

	/**
	 * Test to see if an email address is valid.
	 *
	 * @return bool True if email is valid false otherwise.
	 */
	function isValid()
	{
		$regexp = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";

		if (!eregi($regexp, $this->email))
			return false;
		else
			return true;
	}

	/**
	 * Test to see if the email is already inside list.
	 *
	 * @return bool True if in list false if not.
	 */
	function inList($list = '')
	{
		$tbl 			= $this->lists->whatTable($this->email);
		$this->table 	= $tbl;
		$e_parts 		= splitEmail($this->email);

		if($list == '')
			$list = $this->list;

		$sql 	= "SELECT COUNT(id) AS `count` FROM `{list}`.`$tbl` WHERE `local` = '".$e_parts['local']."' AND `domain` = '".$e_parts['domain']."';";
	 	$rs 	= $this->lists->query_list($list, $sql);

		if($rs)
			$rw = row($rs);

		if($rw['count'] > 0)
			return true;
		else
			return false;
	}


	/**
	 * Get an slog record for email address.
	 *
	 * @param bool $rtn_action True if we want it to return the action. Instead of the row.
	 * @return mixed int If return action is set else and array of row values.
	 */
	function getSLog($rtn_action = true)
	{
		$this->gotSplit();

		$sql 	= "SELECT event FROM `{list}`.`slog` WHERE `local` = '".$this->local."' AND `domain` = '".$this->domain."'";
		$rs 	= $this->lists->query_list($this->list, $sql);

		print mysql_error();

		if($rs)
			$rw = row($rs);

		if ($rtn_action === true)
			return $rw['event'];
		else
			return $rw;
	}

	/**
	 * Is this address in the global unsub?
	 *
	 * @return bool True its in false its not!
	 */
	function isGlobalUnsub()
	{
		$sql = "SELECT COUNT(*) AS `count` FROM global_unsub WHERE address = '".$this->email."'";
		$row = row(query($sql));

		if($row['count'] > 0)
			return true;
		else
			return false;
	}

	/**
	 * Is this address in the global unsub domain?
	 *
	 * @return bool True its in false its not!
	 */
	function isGlobalUnsubDomain()
	{
		$this->gotSplit();

		$sql = "SELECT COUNT(*) AS `count` FROM global_unsub_domain WHERE domain = '".$this->domain."'";
		$row = row(query($sql));

		if($row['count'] > 0)
			return true;
		else
			return false;
	}

	/**
	 * Is global word
	 *
	 * @return bool True its in false its not!
	 */
	function isGlobalWord()
	{
		if(!is_array($this->words))
		{
			$words 	= array();
			$sql 	= "SELECT * FROM global_words";
			$result = query($sql);

			while ($row = row($result))
				$words[] = $row['word'];

			$this->words = $words;
		}

		$email = strtolower($this->email);
		$words = $this->words;

		foreach($words AS $word)
		{
			if($word != '')
			{
				if(strpos($email, $word) !== false)
					return true;
			}
		}

		return false;
	}

	/**
	 * Have we already split the email? No ok then go it.
	 *
	 * @return void
	 */
	function gotSplit()
	{
		if(!$this->local)
		{
			$e_parts 		= splitEmail($this->email);
			$this->local 	= $e_parts['local'];
			$this->domain 	= $e_parts['domain'];
		}
	}

	/**
	 * Have we got the table name this email belongs to? No? Get it.
	 *
	 * @return void
	 */
	function gotTable()
	{
		if(!$this->table)
		{
			$tbl 			= $this->lists->whatTable($this->email);
			$this->table 	= $tbl;
		}
	}
}

/**
  * Unsubscribe class
  *
  * Extends Subscribe class to provide unsubscribe functions.
  *
  * @package   Subscribe
  * @author	Celibero Team
  * @version   $Id: Subscribe.php,v 1.17 2006/01/18 17:58:10 tom Exp $
  * @since	 4.1
  * @copyright Integraclick inc.
  */
class Unsubscribe extends Subscribe
{
	/**
	 * Global Do Not Email
	 * @var bool We wanna gdne?
	 */
	var $gdne  = false;
	/**
	 * Super Global Do Not Email
	 * @var bool We wanna sgdne?
	 */
	var $sgdne = false;

	/**
	 * The mighty constructor!
	 *
	 * Set up some vars (globals mostly)
	 *
	 * @return void.
	 */
	function Unsubscribe()
	{
		global $db;

		$this->lists 	= lists::singleton();
		$this->db 		= &$db;
	}

	/**
	 * Test to see if this meet unsubscribe criteria.
	 *
	 * @return int Positive if it does neg if it doesn't.
	 */
	function test4Unsub()
	{
		if(!$this->isValid())
			return -1;

		return 1;
	}

	/**
	 * Unsub the loaded email.
	 *
	 * @return mixed True if unsub else int tied to msg.
	 */
	function doUnsub()
	{
		$ret = $this->test4Unsub();
		$this->gotTable();
		$this->gotSplit();

		if($ret != 1)
			return $ret;

		if($this->gdne == true || $this->sgdne == true)
		{
			foreach($this->lists->lists AS $list => $info)
			{
				$this->unsubFromList($info['name']);
			}
		}
		else
		{
			if($this->list == '')
			{
				foreach($this->lists->lists as $list => $info)
					$this->unsubFromList($info['name']);
			}
			else
				$this->unsubFromList($this->list);

			return 1;
		}

		// Still going ... we are a (s)gdne
		if($this->sgdne == true)
		{
			$action = 1;
			$rtn 	= 3;
		}
		else
		{
			$action = 0;
			$rtn 	= 2;
		}

		$sql = "INSERT INTO global_unsub (ts,address,how,global_action) VALUES (NOW(),'$this->email','$this->how','$action');";
		query($sql, true);

		return $rtn;
	}

	/**
	 * Unsubscribe loaded email from specified list.
	 *
	 * @return bool True or False.
	 */
	function unsubFromList($list)
	{
		$sql 	= "SELECT * FROM `{list}`.`$this->table` WHERE `local` = '$this->local' AND `domain` = '$this->domain';";
		$rs 	= $this->lists->query_list($list, $sql);
		
		if (!$rs)
			return false;

		$rw = row($rs);

		if(is_array($rw))
		{
			$sql = "DELETE FROM `{list}`.$this->table WHERE `local` = '$this->local' AND `domain` = '$this->domain';";
			$this->lists->query_list($list, $sql);

			$sql = "DELETE FROM `{list}`.slog WHERE `local` = '$this->local' AND `domain` = '$this->domain';";
			$this->lists->query_list($list, $sql);

			$table_id = $this->lists->whatID($this->local);

			$sql = "INSERT INTO
						`{list}`.slog 
						(ts,local,domain,event,how,id) 
					VALUES 
						(NOW(),'$this->local','$this->domain','2','$this->how','".$table_id.$rw['id']."');";
			$this->lists->query_list($list, $sql);
		   
			$data = serialize($rw);
			$sql = "INSERT INTO
						`{list}`.slog_data
						(id, `data`) 
					VALUES 
						('".$table_id.$rw['id']."', '".mysql_escape_string($data)."');";
			$this->lists->query_list($list, $sql);

			print mysql_error();
		}
	}
}

/**
  * Unsubscribe Domain class
  *
  * Extends Subscribe class to provide unsubscribe domain functions.
  *
  * @package   Subscribe
  * @author	Celibero Team
  * @version   $Id: Subscribe.php,v 1.17 2006/01/18 17:58:10 tom Exp $
  * @since	 4.1
  * @copyright Integraclick inc.
  */
class Unsubscribe_Domain extends Subscribe
{
	/**
	 * Global Do Not Email
	 * @var bool We wanna gdne?
	 */
	var $gdne  = false;
	/**
	 * Super Global Do Not Email
	 * @var bool We wanna sgdne?
	 */
	var $sgdne = false;

	/**
	 * The mighty constructor!
	 *
	 * Set up some vars (globals mostly)
	 *
	 * @return void.
	 */
	function Unsubscribe_Domain()
	{
		global $Lists,$db;

		$this->lists = &$Lists;
		$this->db	= &$db;
	}

	/**
	 * Sets the domain no longer private as we are Unsubbing by domain!
	 *
	 * @return void
	 */
	function setDomain($domain)
	{
		$this->domain = $domain;
	}

	/**
	 * Test to see if this meet unsubscribe criteria.
	 *
	 * @return int Positive if it does neg if it doesn't.
	 */
	function test4Unsub()
	{
		if (!$this->isValidDomain())
			return -1;
		else if ($this->gotDomain())
			return -2;

		return 1;
	}


	function gotDomain()
	{
		$sql 	= "SELECT COUNT(*) AS `count` FROM global_unsub_domain WHERE domain = '$this->domain'";
		$row 	= row(query($sql));

		if($row['count'] > 0)
			return true;

		return false;
	}

	function isValidDomain()
	{
		$reg = "[_a-z0-9-]+(\\.[_a-z0-9-]+)*(\\.([a-z]{2,3}))+$";

		if (eregi($reg, $this->domain))
			return 1;
		else
			return -1;
	}

	/**
	 * Unsub the loaded domain.
	 *
	 * @return mixed True if unsub else int tied to msg.
	 */
	function doUnsub()
	{
		$ret = $this->test4Unsub();

		if($ret != 1)
			return $ret;

		if($this->gdne == true || $this->sgdne == true)
		{
			foreach($this->lists->lists AS $list => $info)
				$this->unsubFromList($info['name']);
		}
		else
		{
			$this->unsubFromList($this->list);
			return 1;
		}

		// Still going ... we are a (s)gdne
		if($this->sgdne == true)
		{
			$action = 1;
			$rtn 	= 3;
		}
		else
		{
			$action = 0;
			$rtn 	= 2;
		}

		$sql = "INSERT INTO global_unsub_domain (ts,domain,how,global_action) VALUES (NOW(),'$this->domain','$this->how','$action');";
		query($sql, true);

		return $rtn;
	}

	/**
	 * Unsubscribe loaded domain from specified list.
	 *
	 * @return bool True or False.
	 */
	function unsubFromList($list)
	{
		foreach($this->lists->email_tables AS $table)
		{
			$tbl = $this->lists->email_table_prefix.$table;

			$sql = "SELECT * FROM `{list}`.`$tbl` WHERE `domain` = '$this->domain';";
			$rs = $this->lists->query_list($list, $sql);
		
			if (!$rs)
				print $sql.'-'.$list.'-'.$table."\n";
			
			while($rw = row($rs))
			{
				$sql = "DELETE FROM `{list}`.$tbl WHERE `local` = '".$rw['local']."' AND `domain` = '".$rw['domain']."';";
				$this->lists->query_list($list, $sql);

				$table_id = $this->lists->whatID($rw['local']);

				$sql = "INSERT INTO
						`{list}`.slog 
						(ts,local,domain,event,how,id) 
					VALUES 
						(NOW(),'".$rw['local']."','".$rw['domain']."','2','$this->how','".$table_id.$rw['id']."');";
				$this->lists->query_list($list, $sql);

				$data = serialize($rw);
				$sql = "INSERT INTO
						`{list}`.slog_data
						(id, `data`) 
					VALUES 
						('".$table_id.$rw['id']."', '".mysql_escape_string($data)."');";
				$this->lists->query_list($list, $sql);
			}
		}
	}
}

/**
  * Unsubscribe Word class
  *
  * Extends Subscribe class to provide unsubscribe word functions.
  *
  * @package   Subscribe
  * @author	Celibero Team
  * @version   $Id: Subscribe.php,v 1.17 2006/01/18 17:58:10 tom Exp $
  * @since	 4.1
  * @copyright Integraclick inc.
  */
class Unsubscribe_Word extends Subscribe
{
	/**
	 * Global Do Not Email
	 * @var bool We wanna gdne?
	 */
	var $gdne  = false;
	/**
	 * Super Global Do Not Email
	 * @var bool We wanna sgdne?
	 */
	var $sgdne = false;

	var $word;
	var $s_word;
	var $atPos;

	/**
	 * The mighty constructor!
	 *
	 * Set up some vars (globals mostly)
	 *
	 * @return void.
	 */
	function Unsubscribe_Word()
	{
		global $Lists,$db;

		$this->lists = &$Lists;
		$this->db	= &$db;
	}

	/**
	 * Sets the domain no longer private as we are Unsubbing by domain!
	 *
	 * @return void
	 */
	function setWord($word)
	{
		$pos 	= strpos($word, '@');
		$s_word = $word;

		if($pos !== false)
		{
			$s_word = str_replace('@', '', $word);

			if($pos == 0)
				$this->atPos = 1;
			else
				$this->atPos = 2;
		}

		$this->s_word 	= $s_word;
		$this->word 	= $word;
	}

	/**
	 * Test to see if this meet unsubscribe criteria.
	 *
	 * @return int Positive if it does neg if it doesn't.
	 */
	function test4Unsub()
	{
		if ($this->word == '')
			return -1;
		else if ($this->gotWord())
			return -2;

		return 1;
	}

	function gotWord()
	{
		$sql = "SELECT COUNT(*) AS `count` FROM global_words WHERE word = '$this->word'";
		$row = row(query($sql));

		if ($row['count'] > 0)
			return true;

		return false;
	}

	/**
	 * Unsub the loaded domain.
	 *
	 * @return mixed True if unsub else int tied to msg.
	 */
	function doUnsub()
	{
		$ret = $this->test4Unsub();

		if($ret != 1)
			return $ret;

		if($this->gdne == true || $this->sgdne == true)
		{
			foreach($this->lists->lists AS $list => $info)
				$this->unsubFromList($list);
		}
		else
		{
			$this->unsubFromList($this->list);
			return 1;
		}

		// Still going ... we are a (s)gdne
		if($this->sgdne == true)
		{
			$action = 1;
			$rtn 	= 3;
		}
		else
		{
			$action = 0;
			$rtn 	= 2;
		}

		$sql = "INSERT INTO global_words (ts,word) VALUES (NOW(),'$this->word');";
		query($sql, true);

		return $rtn;
	}

	/**
	 * Unsubscribe loaded domain from specified list.
	 *
	 * @return bool True or False.
	 */
	function unsubFromList($list)
	{
		foreach($this->lists->email_tables AS $table)
		{
			$tbl = $this->lists->email_table_prefix.$table;

			if($this->atPos == 1)
			{
				$where = "(`domain` LIKE '%$this->s_word%')";
			}
			elseif($this->atPos == 2)
			{
				$where = "(`local` LIKE '%$this->s_word%')";
			}
			else
			{
				$where = "(`domain` LIKE '%$this->word%') OR (`local` LIKE '%$this->word%')";
			}

			$sql 	= "SELECT * FROM `{list}`.`$tbl` WHERE $where;";
			$rs 	= $this->lists->query_list($list, $sql);

			while ($rw = row($rs))
			{
				$sql = "DELETE FROM `{list}`.$tbl WHERE `local` = '".$rw['local']."' AND `domain` = '".$rw['domain']."';";
				$this->lists->query_list($list, $sql);

				$table_id 	= $this->lists->whatID($this->local);
				$sql 		= "DELETE FROM `{list}`.slog WHERE `local` = '".$rw['local']."' AND `domain` = '".$rw['domain']."';";
				$this->lists->query_list($list, $sql);

				$sql = "INSERT INTO
						`{list}`.slog 
						(ts,local,domain,event,how,id,data) 
					VALUES 
						(NOW(),'".$rw['local']."','".$rw['domain']."','2','$this->how','".$table_id.$rw['id']."','".serialize($rw)."');";
				$this->lists->query_list($list, $sql);
			}
		}
	}
}

/**
  * Find class
  *
  * Extends Subscribe class to provide search/finding functions.
  *
  * @package   Subscribe
  * @author	Celibero Team
  * @version   $Id: Subscribe.php,v 1.17 2006/01/18 17:58:10 tom Exp $
  * @since	 4.1
  * @copyright Integraclick inc.
  */
class Subscribe_Find extends Subscribe
{
	var $id;
	var $sub_id;
	var $rtn_one;

	function Subscribe_Find()
	{
		global $Lists,$db;

		$this->lists = &$Lists;
		$this->db	= &$db;
	}

	function setID($id)
	{
		$this->id = $id;
		$this->gotID();
	}

	function gotID()
	{

		$code 			= $this->id{0};
		$code 			.= $this->id{1};
		$this->sub_id 	= substr($this->id, 2);

		if($code == "aa")
			$this->table = 'misc';
		else if ($code == "ab")
			$this->table = '0_9';
		else
			$this->table = $this->id{0};
	}

	function find($where = '', $fields = '*')
	{
		$sql = "SELECT $fields FROM `{list}`.[table_name] $where";

		// We already know the table dont search em all crrrazynesss!
		if(isset($this->table))
		{
			$tbl = $this->lists->email_table_prefix . $this->table;
			$sql2 = str_replace('[table_name]', $tbl, $sql);

			$rs = $this->lists->query_list($this->list, $sql2);

			if ($this->rtn_one == true)
				return row($rs);
		}
		// Bah well search em all then stupied domain search
		else
		{

		}
	}

	function findSLog($where = '')
	{
		$sql 	= "SELECT * FROM `{list}`.slog $where";
		$rs 	= $this->lists->query_list($this->list, $sql);

		return row($rs);
	}

	function findSLogData($where = '')
	{
		$sql 	= "SELECT data FROM `{list}`.slog_data $where";
		$rs 	= $this->lists->query_list($this->list, $sql);

		return row($rs);
	}
}
?>
