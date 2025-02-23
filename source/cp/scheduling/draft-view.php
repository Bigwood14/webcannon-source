<?PHP
require_once('../../lib/control_panel.php');
require_once('../../no-web/core/include.php');
require_once('functions-scheduling.php');

require_once('draft.cls.php');
require_once('ip.cls.php');
require_once('link_tracking.cls.php');

checkCPAcces();
$config2 = getDBConfig('',1);

$draft_domain = new draft_domain();

if(isset($_GET['sent']))
    $tpl->sent = 1;

if(!$_GET['msg_id'])
    die("No ID given");

$msg_id = mysql_real_escape_string($_GET['msg_id']);

$sql 	= "SELECT * FROM msg WHERE id = '$msg_id';";

$draft = $db->GetRow($sql);
if(!is_array($draft))
    die("Bad ID Given");

// Server
$sql = "SELECT * FROM servers WHERE server_id = '".$draft['server_id']."';";
$server = $db->GetRow($sql);

if($server == false)
{
    $tpl->schedule_disabled = ' disabled';
    $tpl->no_server = true;
}
else
{
    $draft['server']['name'] = $server['name'];
}

// Domains
$ip 	= new ip();
$ips 	= $ip->draft_get($msg_id);

foreach ($ips as $ip_row)
{
	$ip_info = $ip->get($ip_row['ip_id']);
    $main_ip = $ip_info['ip'];

    $add['domain'] 		= $ip_info['domain'];
    $add['ip'] 			= $main_ip;
    $draft['domains'][] = $add;
}

// Lists
$sql = "SELECT * FROM msg_to_list WHERE msg_id = '$msg_id'";
$rs = $db->Execute($sql);
$i = 0;
while ($lt = $rs->FetchRow())
{
    $sql = "SELECT * FROM list WHERE list_id = '".$lt['list_id']."'";
    $lst = $db->GetRow($sql);
    if(isset($lst['name']))
    {
        $draft['lists'][] = array('name' => $lst['name'], 'skip' => $lt['skip'], 'max' => $lt['max']);
        $i ++;
    }
}
if($i < 1)
{
    $tpl->schedule_disabled = ' disabled';
    $tpl->no_list           = true;
}

// timezones list
$timezones 		= DateTimeZone::listIdentifiers();
$tpl->timezones = $timezones;

// build an array of the lists
$list_data 			= array();
$sql 				= 'SELECT * FROM list';
$rows 				= all_rows(query($sql));

foreach ($rows as $row)
	$list_data[$row['list_id']] = $row['name'];

$draft['list_data'] = $list_data; 

// From Names
$sql = "SELECT * FROM msg_to_from WHERE msg_id = '$msg_id'";
$rs = $db->Execute($sql);
while ($fn = $rs->FetchRow())
{
    $draft['froms'][] = $fn['from']." <{$fn['from_local']}@{$fn['from_domain']}>";
}

// Domain NOT and Domain ONLY
$draft['domain_only'] 	= array();
$draft['domain_not'] 	= array();

$data 	= $draft_domain->get($msg_id);
foreach ($data as $row)
	$draft['domain_only'][] = $row['domain'];

$data 	= $draft_domain->get($msg_id, 1);
foreach ($data as $row)
	$draft['domain_not'][] = $row['domain'];

// Subject Lines
$sql = "SELECT * FROM msg_to_subject WHERE msg_id = '$msg_id'";
$rs = $db->Execute($sql);
while ($sl = $rs->FetchRow())
{
    $draft['subjects'][] = $sl['subject'];
}

// Suppression List
$sql 				= 'SELECT * FROM `supression_lists`;';
$result 			= query($sql);
$suppression_lists 	= array();
while ($row = row($result))
	$suppression_lists[$row['sup_list_id']] = $row['title'];


$draft['suppression_list'] 	= array();
$draft_suppression 			= new draft_suppression();
$data 						= $draft_suppression->get($msg_id);
foreach ($data as $row)
{
	if (empty($suppression_lists[$row['suppression_list_id']]))
		continue;

	$draft['suppression_list'][] = $suppression_lists[$row['suppression_list_id']];
}

// Catgories
$sql = "SELECT * FROM msg_to_category WHERE msg_id = '$msg_id'";
$rs = $db->Execute($sql);
$draft['cats'] = array();
while ($cy = $rs->FetchRow())
{
    $sql = "SELECT * FROM categories WHERE category_id = '".$cy['category_id']."'";
    $cat = $db->GetRow($sql);
    $draft['cats'][] = $cat['title'];
}

// Sup List
$sql = "SELECT * FROM supression_lists WHERE sup_list_id = '".$draft['sup_list_id']."'";
$rs = $db->Execute($sql);
while ($sl = $rs->FetchRow())
{
    $draft['sup_name'] = $sl['title'];
}

// Default Test Emails
$tests = $config2['DEFAULT_TESTS'];
$m = explode("\n", $tests);
foreach($m AS $ad)
{
    $t[] = $ad;
}
$seeds = explode("\n",$draft['seeds']);
foreach($seeds AS $seed)
{
    if(!in_array($seed, $t))
    {
        $t[] = $seed;
    }
}

$tpl->default_test = implode("\n",$t);

// link tracking
$sql 	= "SELECT * FROM `tracked_link` WHERE `draft_id` = '$msg_id';";
$rows 	= all_rows(query($sql));
$draft['tracked_links'] = array();
foreach ($rows as $row)
{
	$draft['tracked_links'][] = $row;
}
$link_tracking = new link_tracking();
$draft['link_tracking_actions'] = $link_tracking->actions;



// Headers and Footers
require_once('extra_content.php');
$text_header = '';
$text_footer = '';
if (!empty($draft['footer']))
{
	$data = get_content_data($draft['footer']);
	$text_footer = $data['text']['data'];
}
if (!empty($draft['header']))
{
	$data = get_content_data($draft['header']);
	$text_header = $data['text']['data'];
}

$draft_personalization = new draft_personalization();
$draft_personalization->parse($_GET['msg_id']);

$draft_o 	= new draft();
$draft_o->load_draft($_GET['msg_id']);

if (!empty($draft['body']))
{
	$draft['body']		= $draft_o->link_tracking($draft['body']);
	// Put on the headers and footers
	$draft['body']      = $text_header.$draft['body'].$text_footer;
	// Domain Replace
	$rand 				= rand(0, count($draft['domains'])-1);
	$domain 			= $draft['domains'][$rand]['domain'];
	
	if (!empty($draft['from_domain']))
		$domain = $draft['from_domain'];

	$draft['body'] 		= str_replace("{{dn}}", $domain, $draft['body']);
}

// Paused?
if($draft['state'] != 0)
{
	$sql = "SELECT * FROM schedule WHERE msg_id = '{$draft['id']}'";
    $paused = $db->GetRow($sql);
    if($paused['state'] == 11)
    {
    	$tpl->is_paused = 1;
    }
}


$tpl->scripts[]		= 'draft.js';
$tpl->calendar      = 1;
$tpl->js_collapse   = true;
$tpl->draft         = $draft;
$tpl->template      = "cp/scheduling/draft-view.php";
$tpl->display('cp/layout.php');
?>
