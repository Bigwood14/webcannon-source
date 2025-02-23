<?PHP
$auth_is_admin = true;
require_once('../../no-web/core/include.php');
require_once('HTML/Layout.php');
checkCPAcces();

if(empty($_GET['server_id']))
    $server_id = 1;

if(isset($_POST['rotations']))
{
    //$server_id   = mysql_escape_string($_POST['rot_server_id']);
    $per_mailing = mysql_real_escape_string($_POST['ip_num']);
    
    if(countDB($db, 'rotations', "WHERE server_id = '$server_id'") > 0)
    {
        $db->Execute("UPDATE rotations SET per_mailing = '$per_mailing';");
    }
    else 
    {
        $db->Execute("INSERT INTO rotations (`server_id`, `per_mailing`) VALUES ('$server_id', '$per_mailing')");
    }
    
    print mysql_error();
}

/* Add group */
if (isset($_POST['group_add']) && !empty($_POST['name']))
{
	$name 	= mysql_real_escape_string($_POST['name']);
	$sql 	= "INSERT INTO `domain_group` (`name`) VALUES ('$name');";
	$db->Execute($sql);
}
/* Delete group */
if (isset($_GET['group_delete']))
{
	$domain_group_id 	= mysql_real_escape_string($_GET['group_delete']);
	$sql 				= "DELETE FROM `domain_group` WHERE `domain_group_id` = '$domain_group_id';";
	$db->Execute($sql);
}
/* Groups list */
$sql 			= "SELECT * FROM `domain_group`";
$groups 		= $db->GetAll($sql);
$tpl->groups 	= array();

foreach ($groups as $group)
{
	$tpl->groups[$group['domain_group_id']] = $group;
}

if(isset($_POST['action_submit']))
{
	if ($_POST['action'] == 'delete')
	{
		foreach ($_POST['selected'] as $ip)
		{
			$ip = mysql_real_escape_string($ip);

		    $sql = "SELECT * FROM server_to_ip WHERE ip = '{$ip}';";
		
		    if(!$rw = $db->GetRow($sql))
		    {
		        die("Bad id for domain/ip delete??");
		    }
		
		    $do = $rw['domain'];
		
		    $sql = "DELETE FROM server_to_ip WHERE ip = '{$ip}';";
		    $db->Execute($sql);
		
		    $d = "/var/qmail/control";
		    $command  = "cd $d;cp locals locals.tmp; sed -e '/^$do$/d' locals.tmp > locals;";
		    $command .= "cp rcpthosts rcpthosts.tmp; sed -e '/^$do$/d' rcpthosts.tmp > rcpthosts;svc -h /service/qmail-send;";
		
		    $sql = "INSERT INTO commands (`command`, `date`, `state`) VALUES ('".mysql_real_escape_string($command)."', NOW(), '0');";
		    $db->Execute($sql);
		    print mysql_error();
		}
	}
	else if (substr($_POST['action'], 0, 6) == 'group_')
	{
		$domain_group_id = (int)str_replace('group_', '', $_POST['action']);

		foreach ($_POST['selected'] as $ip)
		{
			$ip = mysql_real_escape_string($ip);
			$sql = "UPDATE `server_to_ip` SET `domain_group_id` = '{$domain_group_id}' WHERE `ip` = '$ip';";
			$db->Execute($sql);
		}
	}
}
/* Wants to some domains / ips (row stylie) */
if(isset($_POST['add_domain']) && isset($_POST['domain']))
{
    foreach($_POST['domain'] AS $k => $domain)
    {
        $ip     = trim($_POST['ip'][$k]);
        $domain = trim($domain);

        if($domain == '' || $ip == '')
        {
            continue;
        }

        $long_ip = ip2long($ip);

        if(long2ip($long_ip) != $ip)
        {
            $tpl->error[] = "Invalid: $ip - $domain";
        }
        else
        {
			$ip 	= mysql_real_escape_string($ip);
			$domain = mysql_real_escape_string($domain);

            $sql = "INSERT server_to_ip (server_id, domain, ip) VALUES ('$server_id', '$domain', '$ip');";
            $db->Execute($sql);

			$domain 	= escapeshellarg($domain);
            $command 	= mysql_escape_string("echo $domain >> /var/qmail/control/locals; echo $domain >> /var/qmail/control/rcpthosts;svc -h /service/qmail-send;");

            $sql 		= "INSERT INTO commands (`command`, `date`, `state`) VALUES ('$command', NOW(), '0');";
            $db->Execute($sql);
        }
    }


    print mysql_error();
}
if(isset($_POST['add_domain_textarea']))
{
    //print_r($_POST);
    $rows = explode("\n", $_POST['domain_textarea']);

    foreach($rows AS $row)
    {
        $parts = explode($_POST['delim'], $row);

        if($_POST['order'] == '1')
        {
            $ip     = trim($parts[0]);
            $domain = trim($parts[1]);
        }
        else
        {
            $ip     = trim($parts[1]);
            $domain = trim($parts[0]);
        }

        if($domain == '' || $ip == '')
        {
            continue;
        }

        $long_ip = ip2long($ip);

        if(long2ip($long_ip) != $ip)
        {
            $tpl->error[] = "Invalid: $ip - $domain";
        }
        else
        {
			$ip 	= mysql_real_escape_string($ip);
			$domain = mysql_real_escape_string($domain);

            $sql = "INSERT server_to_ip (server_id, domain, ip) VALUES ('$server_id', '$domain', '$ip');";
            $db->Execute($sql);

			$domain 	= escapeshellarg($domain);
            $command 	= mysql_escape_string("echo $domain >> /var/qmail/control/locals; echo $domain >> /var/qmail/control/rcpthosts;svc -h /service/qmail-send;");
            $sql 		= "INSERT INTO commands (`command`, `date`, `state`) VALUES ('$command', NOW(), '0');";

            $db->Execute($sql);
        }
    }
}

/* Set new default domain */
if(isset($_GET['make_default']))
{
    /* Update so no default */
    $sql = "UPDATE server_to_ip SET `default` = '0';";
    $db->Execute($sql);
    /* Update specific domain to default */
    $id = (int) mysql_real_escape_string($_GET['id']);
    $sql = "UPDATE server_to_ip SET `default` = '1' WHERE `domain` = '$id' AND `server_id` = '$server_id';";
    $db->Execute($sql);

    $domain = $db->GetRow("SELECT * FROM server_to_ip WHERE `default` = '1';");
    /* Tell qmail its new master */
    $command = mysql_escape_string("echo '{$domain['domain']}' > /var/qmail/control/me;svc -h /service/qmail-send;");
    $sql = "INSERT INTO commands (`command`, `date`, `state`) VALUES ('$command', NOW(), '0');";
    $db->Execute($sql);
}

$data = array();

$sql = "SELECT * FROM server_to_ip ORDER BY `domain_group_id` ASC, INET_ATON(`ip`) ASC";
$rs = $db->Execute($sql);
while($rw = $rs->FetchRow())
{
	$sql 	= "SELECT * FROM `aol_ratio` WHERE `ip` = '".$rw['domain']."' ORDER BY `date` DESC LIMIT 0,1;";
	$rows	= $db->GetAll($sql);

	if (!empty($rows[0]))
		$rw['ratio'] = $rows[0]['ratio'].'%';

    $data[] = $rw;
}

$sql = "SELECT * FROM servers WHERE `type` != '2'";
$rs = $db->Execute($sql);
while($rw = $rs->FetchRow())
{
    $s_list[$rw['server_id']] = $rw['name'].' (#'.$rw['server_id'].')';
}
$rs->Close();

$sql = "SELECT * FROM rotations";
$rs = $db->Execute($sql);
while($rw = $rs->FetchRow())
{
    $r_list[$rw['server_id']] = $rw;
}
$rs->Close();

$tpl->rotations  = @$r_list;
$tpl->servers    = $s_list;
$tpl->domains    = $data;
$tpl->template   = "cp/isp-relations/domains.php";
$tpl->display('cp/layout.php');
?>
