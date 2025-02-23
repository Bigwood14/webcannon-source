<?php
function makeError($error)
{
	global $db;
	
	$sql = "SELECT * FROM sgdne";
	$info = $db->GetRow($sql);
	
	$link = mysql_connect($info['hostname'],$info['username'],$info['password']);
	mysql_select_db($info['database'], $link);
	
	$msg  = mysql_escape_string($error);
	$from = "Me";
	$db->Execute("INSERT INTO errors (`from`, `error`) VALUES ('$from', '$error');");
	
	
	$from = mysql_escape_string(getDefaultDomain());
	mysql_query("INSERT INTO errors (`from`, `error`) VALUES ('$from', '$error');", $link);
}

function showError($msg)
{
	print "<h1>Error</h1><p>$msg</p>";
	exit;
}

function checkCPAcces()
{
    global $permissions, $auth_is_admin;

	if ($auth_is_admin == true && $permissions->auth->user['mailer'] == 1)
	{
		die('No Access');
	}

    if($permissions->hasPermission("CP"))
    {
        $us = getDBConfig('VERSION',1);
        /* Check version 
        if($us['value'] < CELIBERO_VERSION)
        {
            header( 'refresh: 3; url=/install/upgrade/index.php' );
            echo "<a href=\"/install/upgrade/index.php\">redirecting to upgrade scripts.</a>";
            die();
        }*/
        return true;
    }
    else
    {
        header("Location: /");
		die;
    }
}

function checkAccess($key)
{
    global $permissions;

    if($permissions->hasPermission($key))
    {
        return true;
    }
    else
    {
        header("Location: /cp/index.php");
    }
}

function getCurrentVersion()
{
    $fp = @fopen("http://www.celibero.com/version.html","r");
    $contents = @fread($fp, 1024);
    // And we are a-float
    $cur = (float) $contents;
    return $cur;
}

function getEmailCols($hide = array())
{
    global $db,$config;

	$lists = lists::singleton();

    $sql = "SELECT * FROM `list` LIMIT 0,1;";

    $rw = $db->GetRow($sql);

    if($rw == false)
        return false;

    $email_cols = $lists->getCols($rw['name']);


    $fields = array();
    $fields[] = 'email';
    foreach($email_cols AS $col)
    {
        if(in_array($col['Field'],$config->values['mm_field_hide']) || ($col['Field'] == 'local') || ($col['Field'] == 'domain'))
        continue;
        $fields[] = $col['Field'];
    }
    return $fields;
}

function printEmailFieldsSelect($name, $extra = '', $selected = array())
{
    global $config;
    $cols = getEmailCols($config->values['mm_field_hide']);
    //print_r($selected);
    foreach($cols AS $v)
    {
        if(in_array($v,$selected))
        {
            $selected_2 = " selected";
        }
        else
        {
            $selected_2 = "";
        }
        $menu .= "<option value=\"$v\"$selected_2>$v</option>";
    }

    $select = "<select name=\"$name\" $extra>$menu</select>";

    return $select;
}

function countDB($adodb, $table, $where ='', $what = '*')
{
    $sql = "SELECT COUNT($what) AS `count` FROM $table $where;";
    $rw = $adodb->GetRow($sql);
    return $rw['count'];
}

function getLists()
{
    global $db;

    $sql = "SELECT * FROM `list`";
    $all = $db->GetAll($sql);
    //print_r($all);
    return $all;
}

function getDBInfo($db_name)
{
    global $db;
    $sql = "SELECT * FROM `list` WHERE username = '$db_name'";
    $info = $db->GetRow($sql);
    //print_r($info);
    return $info;
}

function calculatePercentage($small,$big,$places)
{
    if($big > 0)
    {
        return round(($small/$big)*100,$places);
    }
    else
    {
        return '100';
    }
}

function type($type = 0)
{
    if($type == "1")
    {
        return 'HTML Only';
    }
    elseif ($type == "2")
    {
        return 'HTML + Text';
    }
    else
    {
        return 'Text Only';
    }
}

function buildListSelect($name = "list_name", $selected = "", $extra = '')
{ 
	$lists = lists::singleton();

    return $lists->buildListSelect($name, $selected, $extra);
}

function getDBConfig($key = '',$global = 0)
{
    global $db2,$db;

    if($global != 0)
    {
        $db_l = &$db;
    }
    else
    {
        $db_l = &$db;
    }

    if($key == '')
    {
        $sql = "SELECT * FROM config";

        $config = $db_l->GetAll($sql);

        foreach($config AS $v)
        {
            $rtrn[$v['KEY']] = $v['value'];
        }
        return $rtrn;
    }
    else
    {
        $sql = "SELECT * FROM config WHERE `KEY` = '$key'";

        return $db_l->GetRow($sql);

    }

}

function getDefaultDomain()
{
    global $db;
    $sql = "SELECT * FROM server_to_ip WHERE `default` = '1'";
    $rw = $db->GetRow($sql);
    
    if($rw == false)
    {
        $sql = "SELECT * FROM server_to_ip LIMIT 0,1";
        $rw = $db->GetRow($sql);
    }
    return $rw['domain'];
}

function getDefaultIP()
{
    global $db;
    $sql = "SELECT * FROM server_to_ip WHERE `default` = '1'";
    $rw = $db->GetRow($sql);
    
    if($rw == false)
    {
        $sql = "SELECT * FROM server_to_ip LIMIT 0,1";
        $rw = $db->GetRow($sql);
    }
    return $rw['ip'];
}

function getDraft($id)
{
    global $db;

    $sql = "SELECT * FROM msg WHERE id = '$id'";

    return $db->GetRow($sql);
}

function writeMsgHeaders($body,$stuff)
{
    global $s;
    $lines=explode("\n",$stuff);
    for ($i=0;$i<sizeof($lines);$i++)
    {
        $line=$lines[$i];
        if (preg_match("/^[ \t\n]+$/",$line))
        continue;
        if (!$line) continue;
        $line=preg_replace("/##l/",$s->list_name,$line);
        $body.="$line\n";
    }
    return $body;
}

function validEmail($email)
{
    $regexp = "^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,3})$";

    if (!eregi($regexp, $email))
    {
        return false;
    }
    else
    {
        return true;
    }
}

function logMessage($type,$message,$code = '0')
{
    global $db;

    $type       = $db->qstr($type,get_magic_quotes_gpc());
    $message    = $db->qstr($message,get_magic_quotes_gpc());
    $code       = $db->qstr($code,get_magic_quotes_gpc());

    $sql = "INSERT INTO log (date,message,type,code) VALUES (NOW(),$message,$type,$code);";
    $db->Execute($sql);

    return true;
}

function getList($list, $id = 0)
{
    global $db;

    if($id == 0)
        $sql = "SELECT * FROM `list` WHERE `name` = '$list'";
    else
        $sql = "SELECT * FROM `list` WHERE `list_id` = '$id'";

    return $db->GetRow($sql);
}

function getState($id)
{
    if($id == 0)
    {
        return "Pending Prepare";
    }
    elseif($id == 1)
    {
        return "Preparing";
    }
    elseif($id == 2)
    {
        return "Transferring";
    }
    elseif($id == 3)
    {
        return "Pending Send";
    }
    elseif($id == 4)
    {
        return "Sending";
    }
    elseif($id == 5)
    {
        return "Retrying";
    }
    elseif($id == 6)
    {
        return "Cleanup";
    }
    elseif($id == 7)
    {
        return "Complete";
    }
    elseif($id == 8)
    {
        return "Pending Cancel";
    }
    elseif($id == 9)
    {
        return "Canceled";
    }
    elseif($id == 10)
    {
        return "Pending Pause";
    }
    elseif($id == 11)
    {
        return "Paused";
    }
    elseif($id == 12)
    {
        return "Pending Resume";
    }
    elseif($id == 13)
    {
        return "Error Please Cancel";
    }
}

function timespanFormat($seconds)
{
    $return_string = '';
    $days = floor($seconds / 86400);
    if ($days > 0) {
        $seconds -= $days * 86400;
    }
    $hours = floor($seconds / 3600);
    if ($days > 0 || $hours > 0) {
        $seconds -= $hours * 3600;
    }
    $minutes = floor($seconds / 60);
    if ($days > 0 || $hours > 0 || $minutes > 0) {
        $seconds -= $minutes * 60;
    }
    return sprintf("%s Days, %s hours, %s minutes, %s seconds", (string)$days, (string)$hours, (string)$minutes, (string)$seconds);
}


function getListSize($where = '')
{
    global $db2;

    if ($where != '')
    {
        if ($query_id)
        {
            $r=user_db_query("select total from stored_target where id=$query_id");
            return db_result($r,0);
        }

        $sql = "SELECT count(*) FROM email WHERE $where";
    }
    else
    {
        $sql = "SELECT count(*) FROM email";
    }

    $rw = $db2->GetRow($sql);
    return $rw[0];
}

function getDLog($id)
{
    global $db;
    $sql = "SELECT * FROM dlog WHERE id = '$id'";

    return $db->GetRow($sql);
}

function getEmail($email,$list = '')
{
    global $db2;
    //print_r($db2);
    $sql = "SELECT * FROM email WHERE address = '$email';";

    $rw = $db2->GetRow($sql);

    if(count($rw) > 1)
    {
        return $rw;
    }

    return false;
}

function getEmailFromID($email_id, $list)
{
	$lists = lists::singleton();

    if(!$lists->lists[$list])
    {
        return false;
    }

    $table = $lists->whatTableID($email_id);

    $sql = "SELECT * FROM `{list}`.`$table` WHERE id = '".substr($email_id, 2)."';";

    $rs = $lists->query_list($list, $sql);

    if($rs === false)
        return false;

    $rw = row($rs);

    if(is_array($rw))
        return $rw['local'].'@'.$rw['domain'];

    return false;
}

function getInfoFromID($email_id, $list)
{
	$lists = lists::singleton();

    $table 	= $lists->whatTableID($email_id);
    $sql 	= "SELECT * FROM `{list}`.`$table` WHERE id = '".substr($email_id, 2)."';";
    $rs 	= $lists->query_list($list, $sql);

    if($rs === false)
        return false;

    $rw = row($rs);

    if(is_array($rw))
        return $rw;

    return false;
}



function splitEmail($email)
{
    $parts = explode("@", $email);
    return array('local'=>$parts[0], 'domain'=>$parts[1]);
}

function pager($from, $limit, $numrows, $maxpages = false,$sliding = '5')
{
    ini_set("memory_limit","20M");
    if (empty($numrows) || ($numrows < 0)) {
        $a['sliding'] = array();
        return $a;
    }
    $from = (empty($from)) ? 0 : $from;

    if ($limit <= 0) {
        return PEAR::raiseError (null, 'wrong "limit" param', null,
        null, null, 'DB_Error', true);
    }

    // Total number of pages
    $pages = ceil($numrows/$limit);
    $data['numpages'] = $pages;

    // first & last page
    $data['firstpage'] = 1;
    $data['lastpage']  = $pages;

    // Build pages array
    $data['pages'] = array();
    for ($i=1; $i <= $pages; $i++) {
        $offset = $limit * ($i-1);
        $data['pages'][$i] = $offset;
        // $from must point to one page
        if ($from == $offset) {
            // The current page we are
            $data['current'] = $i;
        }
    }
    if (!isset($data['current'])) {
        return PEAR::raiseError (null, 'wrong "from" param', null,
        null, null, 'DB_Error', true);
    }

    // Limit number of pages (goole algoritm)
    if ($maxpages) {
        $radio = floor($maxpages/2);
        $minpage = $data['current'] - $radio;
        if ($minpage < 1) {
            $minpage = 1;
        }
        $maxpage = $data['current'] + $radio - 1;
        if ($maxpage > $data['numpages']) {
            $maxpage = $data['numpages'];
        }
        foreach (range($minpage, $maxpage) as $page) {
            $tmp[$page] = $data['pages'][$page];
        }
        $data['pages'] = $tmp;
        $data['maxpages'] = $maxpages;
    } else {
        $data['maxpages'] = null;
    }

    // Prev link
    $prev = $from - $limit;
    $data['prev'] = ($prev >= 0) ? $prev : null;

    // Next link
    $next = $from + $limit;
    $data['next'] = ($next < $numrows) ? $next : null;

    // Results remaining in next page & Last row to fetch
    if ($data['current'] == $pages) {
        $data['remain'] = 0;
        $data['to'] = $numrows;
    } else {
        if ($data['current'] == ($pages - 1)) {
            $data['remain'] = $numrows - ($limit*($pages-1));
        } else {
            $data['remain'] = $limit;
        }
        $data['to'] = $data['current'] * $limit;
    }
    $data['numrows'] = $numrows;
    $data['from']    = $from + 1;
    $data['limit']   = $limit;
    $data['sliding'] = pagerSlidingLinks($data['numpages'],$data['current'],$sliding);
    return $data;
}

function pagerSlidingLinks($pages,$current,$sliding)
{
    $data = array();
    //echo $current."-".$sliding."-".$pages."-";
    if($pages <= $sliding) {$start=1;$end=$pages;}
    elseif($current == 1) {$start=1;$end=$sliding;}
    else
    {
        $o = $sliding;
        while(1)
        {
            $ender = ($current + (round($o - $sliding)));
            if($ender > $pages)
            {
                $addon = $ender - $pages;
            }
            $starter = ($current - floor(($sliding+$addon)/2));

            if($starter < 1)
            {
                $sliding --;
            }
            else
            {
                $sliding = floor($sliding/2);
                break;
            }
        }
        //print $o."-".$sliding;
        $start = $starter;
        $end   = $current + (round($o - $sliding));

        if($end > $pages)
        {
            $end = $pages;
        }
    }
    //echo $start."-".$end;
    for($start; $start<=$end; $start++)
    {
        $data[] = $start;
    }

    return $data;
}

function clean($line,$delim = "")
{
    if($delim != "\"")
    {
        $line = str_replace("\"","",$line);
    }
    if($delim != ",")
    {
        $line = str_replace(",","",$line);
    }
    if($delim != "|")
    {
        $line = str_replace("|","",$line);
    }
    $line = str_replace("\r","",$line);
    $line = str_replace("\n","",$line);
    $line = trim($line);
    return $line;
}

function partTypeSelect($name = 'type',$selected = '',$extra = '')
{
    $types = array('email');
    $cols = getEmailCols(array_merge($types,array('address','mask','import_id','id')));
    $types = array_merge($types,$cols);
    $menu = "<select name='$name' size='1' $extra>";

    foreach($types AS $type)
    {
        if($selected == $type)
        {
            $menu .= "<option selected>$type</option>";
        }
        else
        {
            $menu .= "<option>$type</option>";
        }
    }

    $menu .= "</select>";

    return $menu;
}

function exportState($id)
{
    switch ($id)
    {
        case 0:
        return "Pending";
        break;
        case 1:
        return "Exporting";
        break;
        case 2:
        return "Complete";
        break;
        case 3:
        return "Cancelled";
        break;
    }
}

function getImportState($id)
{
    if($id == 0)
    {
        return "Pending";
    }
    elseif($id == 1)
    {
        return "Importing";
    }
    elseif($id == 2)
    {
        return "Completed";
    }
    elseif($id == 3)
    {
        return "Cancelled";
    }
}

function makeScriptRequestURL()
{
    //print_r($_SERVER);
    $cur_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    if(strrpos($cur_url, "?"))
    {
        $cur_url .= "&";
    }
    else
    {
        $cur_url .= "?";
    }

    return $cur_url;
}
?>
