<?PHP
// Includes
require '../../lib/control_panel.php';
require_once('functions-scheduling.php');
require 'link_tracking.php';
require 'extra_content.php';
require 'draft.cls.php';
require_once 'link_tracking.cls.php';
// Access
checkCPAcces();
// Prep
srand(time());

$msg_id = esc($_GET['msg_id']);
$msg    = getDraft($msg_id);

if($msg['state'] != '0')
{
	$sql = "SELECT * FROM schedule WHERE msg_id = '{$msg['id']}'";
	$paused = $db->GetRow($sql);
	if($paused['state'] != 11)
	{
		die('Cannot schedule same draft twice');
	}
	elseif($paused['state'] == 11)
	{
		$id = $paused['id'];
		$is_paused = 1;
	}
}
// Get vars outta config that we need
$config_2  		= getDBConfig('', 1);

$draft 			= new draft(array('test' => false));
$link_tracking 	= new link_tracking();

$draft->load_draft($_GET['msg_id']);
$bodies 		= $draft->build();
$body 			= $bodies['main'];
$aol_body 		= $bodies['aol'];
$yahoo_body 	= $bodies['yahoo'];

// store the rotations info
$rotations 		= $draft->rotations_get($body.$aol_body.$yahoo_body);

foreach ($rotations as $content_id => $name)
{
	$name 	= esc($name);
	$sql 	= "INSERT INTO `msg_to_rotated` (`msg_id`, `rotated_id`, `name`) VALUES ('$msg_id', '$content_id', '$name');";
	query($sql);
}

// Mail Merge Stuff
$draft_personalization 	= new draft_personalization();
$fields 				= $draft_personalization->parse($msg_id);
$sql_extra 				= '';
foreach ($fields as $index=>$field)
{
	$index 	= $index+6;
	if ($index < 10)
		$index = '0'.$index;
	
	$index 	= (string) '{{'.$index.'}}';

	$replace 	= '{'.$field.'}';
	$body 		= str_replace($replace, $index, $body);

	if ($field == 'email')
		continue;

	$sql_extra 	.= $field.',';
}

$sql_extra = rtrim($sql_extra, ',');

// Timing setup

// NOW!
if ($_POST['when'] == 1)
{
	$scheduled_time = "NOW()";
}
// At this time (selects)
elseif ($_POST['when'] == 2)
{
	if ($_POST['ampm'] == "am")
	{
		if ($_POST['hour'] == 12) $_POST['hour'] = 0;
	}
	else
	{
		if ($_POST['hour'] < 12) $_POST['hour'] += 12;
	}

	$matches        = explode("-",$_POST['day']);
	//print ($_POST['hour'] .":". $_POST['minute'] .":0 ". $matches[1] ."-". $matches[2] ."-". $matches[0]);
	$time_stamp     = mktime($_POST['hour'], $_POST['minute'], 0, $matches[1], $matches[2], $matches[0]);

	$server_offset = (int) date('Z');
	$server_offset = 0 - $server_offset;
	
	// now get selected zones offset to GMT
	$datetimezone   = new DateTimeZone($_POST['timezone']);
	$datetime       = new DateTime('now', $datetimezone);
	
	$user_offset    = $datetimezone->getOffset($datetime);
	
	$offset         = $server_offset + $user_offset;
	$calc_offset    = $offset;
	$scheduled_time = "'".date("Y-m-d H:i:00", ($time_stamp-$calc_offset))."'";
}
// At this time (calendar)
else
{
	$split  = explode(" ",$_POST['cal-field-1']);
	$split2 = explode("/",$split[0]);
	$split3 = explode(":",$split[1]);



	if ($split[2] == "AM")
	{
		if ($split3[0] == 12) $split3[0] = 0;
	}
	else
	{
		if ($split3[0] < 12) $split3[0] += 12;
	}

	$time_stamp     = mktime($split3[0],$split3[1],0,$split2[0],$split2[1],$split2[2]);
	$scheduled_time = "'".date("Y-m-d H:i:00",$time_stamp)."'";
}




$sql    = "SELECT * FROM msg_to_list WHERE msg_id = '".$msg['id']."'";
$rs     = $db->Execute($sql);

$total_emails_list = 0;

while ($lt = $rs->FetchRow())
{
	$sql = "SELECT * FROM `list` WHERE `list_id` = '".$lt['list_id']."'";
	$lst = $db->GetRow($sql);

	if(isset($lst['name']))
	{
		$lists[] 			= $lst;
		$total_emails_list += $Lists->countEmails($lst['name']);
	}
}

// Default all
if ($msg['max_recipients'] == 0)
{
	$total_emails   = $total_emails_list - $msg['start_recipient'];
}
// Send to first x recips
elseif($msg['send_type'] == 1)
{
	$send_to_first = floor($msg['max_recipients']);
}
// Send to last xx recips
elseif($msg['send_type'] == 2)
{
	//$last_email_id  = $msg['start_recipient'];
	$send_to_last = $msg['start_recipient'];
}
// Skip first xx send to max xx
elseif($msg['send_type'] == 3)
{
	//$last_email_id  = $msg['start_recipient'];
	//$total_emails   = $msg['max_recipients'];
	$skip_first = $msg['start_recipient'];
	$max_of     = $msg['max_recipients'];
}

// Sanity checks
if ($total_emails < 0)
{
	$total_emails = 0;
}
if($total_emails > $total_emails_list)
{
	$total_emails = $total_emails_list;
}
if(@$last_email_id < 0)
{
	$last_email_id = 0;
}

$max_speed  = $_POST['max_speed']/5;
$max_threads = round(600*($max_speed/100));
if($max_threads < 1)
{
	$max_threads = 600;
}

// Count the from lines, subject lines and domains.
$subject_lines  = countDB($db, 'msg_to_subject', "WHERE msg_id = '".$msg['id']."'");
$from_lines     = countDB($db, 'msg_to_from', "WHERE msg_id = '".$msg['id']."'");
$domains        = countDB($db, 'msg_to_domain', "WHERE msg_id = '".$msg['id']."'");

if($domains < 1)
{
	$rotations = $db->GetRow("SELECT * FROM rotations WHERE server_id = '{$msg['server_id']}';");
	$count = 0;

	if($rotations['per_mailing'] < 1)
	{
		$rotations['per_mailing'] = 2;
	}
	$rs = $db->Execute("SELECT * FROM server_to_ip WHERE server_id = '{$msg['server_id']}' AND ip > '{$rotations['last_id']}' ORDER BY ip ASC");
	$rots = array();
	$m = 0;
	while($rw = $rs->FetchRow())
	{
		$rots[] = $rw['domain'];
		$m ++;
		if($m >= $rotations['per_mailing']) break;
	}
	// still not got enough start again
	if($m < $rotations['per_mailing'])
	{
		$rs = $db->Execute("SELECT * FROM server_to_ip WHERE server_id = '{$msg['server_id']}' ORDER BY ip ASC");
		while($rw = $rs->FetchRow())
		{
			if(!in_array($rw['domain'], $rots))
			{
				$rots[] = $rw['domain'];
				$m ++;
				if($m >= $rotations['per_mailing']) break;
			}
		}
	}
	$rs->Close();
	$db->Execute("UPDATE rotations SET last_id = '{$rw['ip']}' WHERE server_id = '{$msg['server_id']}';");
	$domains = 0;
	foreach($rots AS $rot)
	{
		$db->Execute("INSERT INTO msg_to_domain (msg_id, domain) VALUES ('{$msg['id']}', '{$rot}');");
		$domains ++;
	}
}
$retries = $_POST['retries'];
$sql = "INSERT INTO schedule (
          msg_id, 
          state, 
          scheduled_time, 
          total_emails,
          server_id,
          subject_lines,
          from_lines,
          domains,
          sql_extra,
          max_threads,
          skip_first,
          send_to_first,
          send_to_last,
          max_of,
          retry_level,
          retries
        ) VALUES (
          '".$msg['id']."',
          '0',
          $scheduled_time,
          '$total_emails',
          '".$msg['server_id']."',
          '$subject_lines',
          '$from_lines',
          '$domains',
          '$sql_extra',
          '1000',
          '$skip_first',
          '$send_to_first',
          '$send_to_last',
          '$max_of',
          '$retries',
          '$retries')";


//$db->debug = 1;
// Lock the draft
$db->Execute("UPDATE msg SET state = '1' WHERE id = '".$msg['id']."'");

// if we have a specific from domain enter this in the for the from lines & qmail locals
if (!empty($msg['from_domain']))
{
	$db->Execute("UPDATE msg_to_from SET `from_domain` = '{$msg['from_domain']}' WHERE `from_domain` = '{{dn}}' AND `msg_id` = '{$msg['id']}';");
	$domain 	= $msg['from_domain'];
	$command 	= mysql_escape_string("echo '$domain' >> /var/qmail/control/locals; echo '$domain' >> /var/qmail/control/rcpthosts;/usr/local/bin/svc -h /service/qmail-send;/usr/local/bin/svc -h /service/qmail-smtpd");
	$sq 		= "INSERT INTO commands (`command`, `date`, `state`) VALUES ('$command', NOW(), '0');";
	$db->Execute($sq);
}

// Enter the schedule
if($is_paused != 1)
{
	$db->Execute($sql);
	$id = $db->Insert_ID();
	$db->Execute("INSERT INTO schedule_log (schedule_id,time,message) VALUES ('".$id."',NOW(),'Draft Inputted for schedule');");
}

// Take care of the LF bare line feeds issue (http://cr.yp.to/docs/smtplf.html)
$body       = str_replace("\r", "", $body);
$aol_body   = str_replace("\r", "", $aol_body);
$yahoo_body = str_replace("\r", "", $yahoo_body);
$body       = str_replace("\n", "\r\n", $body);
$aol_body   = str_replace("\n", "\r\n", $aol_body);
$yahoo_body = str_replace("\n", "\r\n", $yahoo_body);

// Reshuffle
$body       = str_replace("{{dn}}", "{{04}}", $body);
$aol_body   = str_replace("{{dn}}", "{{04}}", $aol_body);
$yahoo_body = str_replace("{{dn}}", "{{04}}", $yahoo_body);
// And back again
$body       = str_replace("{{04}}", "{{dn}}", $body);
$aol_body   = str_replace("{{04}}", "{{dn}}", $aol_body);
$yahoo_body = str_replace("{{04}}", "{{dn}}", $yahoo_body);

// if we have a certain from fomain replace the domain with this one
if (!empty($msg['from_domain']))
{
	$body 		= str_replace('{{dn}}', $msg['from_domain'], $body);
	$aol_body 	= str_replace('{{dn}}', $msg['from_domain'], $aol_body);
	$yahoo_body = str_replace('{{dn}}', $msg['from_domain'], $yahoo_body);
}

// copy book snippet
$sql 	= "SELECT `content` FROM `content_book` ORDER BY rand() LIMIT 0,1;";
$rw 	= row(query($sql));
$con 	= $rw['content'];

$body 		= str_replace('{{book_content}}', $con, $body);
$aol_body 	= str_replace('{{book_content}}', $con, $aol_body);
$yahoo_body = str_replace('{{book_content}}', $con, $yahoo_body);


//$body       = str_replace("{{sl}}", "{{05}}", $body);
//$aol_body   = str_replace("{{sl}}", "{{05}}", $aol_body);

//$body       = str_replace("{{fl}}", "{{06}}", $body);
//$aol_body   = str_replace("{{fl}}", "{{06}}", $aol_body);
//print $body;


// Write the message to file
$file   = $config->values['site']['path'] . 'no-web/celiberod/body/' . $id;
$fh     = fopen($file, "w+");
fwrite($fh, $body);
fclose($fh);

// Write the aol message to file
$file   = $config->values['site']['path'] . 'no-web/celiberod/body/' . $id . '_aol';
$fh     = fopen($file, "w+");
fwrite($fh, $aol_body);
fclose($fh);

// Write the yahoo message to file
$file   = $config->values['site']['path'] . 'no-web/celiberod/body/' . $id . '_yahoo';
$fh     = fopen($file, "w+");
fwrite($fh, $yahoo_body);
fclose($fh);

$tpl->sent = 1;

$tpl->template = "cp/scheduling/schedule.php";
$tpl->display('cp/layout.php');
?>
