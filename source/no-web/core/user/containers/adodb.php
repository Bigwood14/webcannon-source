<?PHP
require_once(dirname(__FILE__) .'/../../db/adodb/adodb.inc.php');

class user_container_adodb
{
    var $db;
    var $table_prefix;

    function user_container_adodb($options,$instance)
    {
        if(!is_null($instance))
        {
            $this->db = &$instance;
        }
        else
        {
            $this->db = &ADONewConnection($options['type']);
            //$this->db->debug = true;
            $this->db->Connect($options['host'],$options['username'],$options['password'],$options['database']);
        }
        $this->table_prefix = $options['table_prefix'];
    }

    function addUser($username,$password)
    {
        $username = $this->db->qstr($username,get_magic_quotes_gpc());
        $password = $this->db->qstr($password,get_magic_quotes_gpc());

        $sql = "INSERT INTO {$this->table_prefix}auth (username,password) VALUES ($username,$password);";

        if ($this->db->Execute($sql) === false)
			return false;

		return mysql_insert_id();
    }

    function chnagePassword($user_id,$password)
    {
        $user_id  = $this->db->qstr($user_id,get_magic_quotes_gpc());
        $password = $this->db->qstr($password,get_magic_quotes_gpc());

        $sql = "UPDATE {$this->table_prefix}auth SET password = $pasword WHERE user_id = $user_id";

        return ($this->db->Execute($sql) === false) ? false : true;
    }

    function removeUser($user_id)
    {
        $user_id = $this->db->qstr($user_id,get_magic_quotes_gpc());

        $sql = "DELETE FROM {$this->table_prefix}auth WHERE user_id = $user_id;";

        return ($this->db->Execute($sql) === false) ? false : true;
    }

    function getUser($username='',$user_id='')
    {
        if($username != '')
        {
            $username = $this->db->qstr($username,get_magic_quotes_gpc());
            $sql = "SELECT * FROM {$this->table_prefix}auth WHERE username = $username;";
        }
        else
        {
            $sql = "SELECT * FROM {$this->table_prefix}auth WHERE user_id = '$user_id';";
        }

        $rw = $this->db->GetRow($sql);

        //print_r($rw);

        return $rw;
    }

	function getGroup ($group_id)
	{
		$group_id 	= $this->db->qstr($group_id);
		$sql 		= "SELECT * FROM {$this->table_prefix}groups WHERE `group_id` = $group_id;";
		return $this->db->GetRow($sql);
	}

	function addGroup ($user_id, $group_id)
	{
		$user_id 	= $this->db->qstr($user_id);
		$group_id 	= $this->db->qstr($group_id);

		$sql 		= "INSERT INTO {$this->table_prefix}groups_2_users (`group_id` , `user_id`) VALUES ($group_id, $user_id);";

		return $this->db->Execute($sql);
	}

    function makeSession($session_id,$user_id,$last_activity,$duration)
    {
        $session_id = $this->db->qstr($session_id,get_magic_quotes_gpc());
        $user_id = $this->db->qstr($user_id,get_magic_quotes_gpc());
        $last_activity = $this->db->qstr($last_activity,get_magic_quotes_gpc());
        $duration = $this->db->qstr($duration,get_magic_quotes_gpc());

        $sql  = "INSERT INTO {$this->table_prefix}session (session_id,user_id,last_activity,duration)";
        $sql .= " VALUES ($session_id,$user_id,$last_activity,$duration);";

        return ($this->db->Execute($sql) === false) ? false : true;
    }

    function getSession($session_id)
    {
        $session_id = $this->db->qstr($session_id,get_magic_quotes_gpc());

        $sql = "SELECT * FROM {$this->table_prefix}session WHERE session_id = $session_id;";

        $rw = $this->db->GetRow($sql);

        //print_r($rw);

        return $rw;
    }

	function deleteSession($session_id)
	{
		$session_id = $this->db->qstr($session_id,get_magic_quotes_gpc());

		$sql = "DELETE FROM {$this->table_prefix}session WHERE session_id = $session_id;";

		return ($this->db->Execute($sql) === false) ? false : true;
	}

    function updateSession($session_id,$last_activity='',$duration='')
    {
        $sql  = "UPDATE {$this->table_prefix}session SET ";

        if($last_activity != '')
        {
            $sql .= "last_activity = '$last_activity' ";
        }

        if($duration != '')
        {
            $duration = $this->db->qstr($duration,get_magic_quotes_gpc());
            $sql .= "duration = $duration ";
        }

        $sql .=  "WHERE session_id = '$session_id';";

        return ($this->db->Execute($sql) === false) ? false : true;
    }

    function gc()
    {
        $sql = "DELETE FROM {$this->table_prefix}session WHERE last_activity+duration < '".mktime()."'";

        return ($this->db->Execute($sql) === false) ? false : true;
    }

    function getPermission($key,$user_id)
    {
        $sql = "SELECT * FROM {$this->table_prefix}permissions WHERE perm_key = '$key' AND id = '$user_id' AND id_type = 'u';";

        $rw = $this->db->GetRow($sql);

        //print_r($rw);

        return $rw;
    }

    function getGroups($user_id)
    {
        $user_id = $this->db->qstr($user_id,get_magic_quotes_gpc());
        $sql = "SELECT group_id FROM {$this->table_prefix}groups_2_users WHERE user_id = $user_id;";
        $rs = $this->db->Execute($sql);

        while($rw = $rs->FetchRow())
        {
            $groups[] = $rw['group_id'];
        }
        $rs->Close();

        return $groups;
    }

    function getGroupPermission($key,$groups)
    {

        if(is_array($groups))
        {
            $sql  = "SELECT * FROM {$this->table_prefix}permissions WHERE ";
            $sql .= "perm_key = '$key' AND id IN (".implode(",",$groups).") AND id_type = 'g';";

            $rw = $this->db->GetRow($sql);

            //print_r($rw);
        }
        return $rw;
    }

    function profileTextTable($big)
    {
        if($big == 0)
        {
            $table = "profile_text";
        }
        else
        {
            $table = "profile_big_text";
        }

        return $table;
    }

    function getProfileText($key,$user_id,$big,$scroll)
    {
        $table = $this->profileTextTable($big);
		$values = false;
        if($scroll == 0)
        {
            $user_id = $this->db->qstr($user_id,get_magic_quotes_gpc());
            $key     = $this->db->qstr($key,get_magic_quotes_gpc());

            $sql = "SELECT * FROM {$this->table_prefix}{$table} WHERE profile_key = $key AND user_id = $user_id";
            $rs = $this->db->Execute($sql);
            while($rw = $rs->FetchRow())
            {
				if (empty($values))
					$values = array();

                $values[] = $rw['text'];
            }
            $rs->Close();

            return $values;
        }
    }

    function addProfileText($key,$user_id,$text,$big)
    {
        $table = $this->profileTextTable($big);

        $key     = $this->db->qstr($key,get_magic_quotes_gpc());
        $user_id = $this->db->qstr($user_id,get_magic_quotes_gpc());
        $text    = $this->db->qstr($text,get_magic_quotes_gpc());

        $sql = "REPLACE INTO {$this->table_prefix}{$table} (user_id,profile_key,text) VALUES ($user_id,$key,$text);";

        return ($this->db->Execute($sql) === false) ? false : true;
    }

    function addPermission($key,$id,$level,$type,$has)
    {
        $key    = $this->db->qstr($key,get_magic_quotes_gpc());
        $id     = $this->db->qstr($id,get_magic_quotes_gpc());
        $level  = $this->db->qstr($level,get_magic_quotes_gpc());
        $type   = $this->db->qstr($type,get_magic_quotes_gpc());
        $has    = $this->db->qstr($has,get_magic_quotes_gpc());

        $sql  = "REPLACE INTO {$this->table_prefix}permissions ";
        $sql .= "(id,id_type,perm_key,level,has)";
        $sql .= " VALUES ";
        $sql .= "($id,$type,$key,$level,$has)";

        return ($this->db->Execute($sql) === false) ? false : true;
    }

    function removePermission($key = '',$id = '',$type = '', $level = '',$has = '')
    {
        $key    = $this->db->qstr($key,get_magic_quotes_gpc());
        $id     = $this->db->qstr($id,get_magic_quotes_gpc());
        $level  = $this->db->qstr($level,get_magic_quotes_gpc());
        $type   = $this->db->qstr($type,get_magic_quotes_gpc());
        $has    = $this->db->qstr($has,get_magic_quotes_gpc());

        $sql = "DELETE FROM {$this->table_prefix}permissions WHERE ";

        if($key != '')
        {
            $sql .= "perm_key = $key";
        }
        if($id != '')
        {
            $sql .= "id = $id";
        }
        if($level != '')
        {
            $sql .= "level = $level";
        }
        if($type != '')
        {
            $sql .= "type = $type";
        }
        if($key != '')
        {
            $sql .= "has = $has";
        }
    }
}
?>
