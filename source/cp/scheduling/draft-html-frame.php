<?PHP
require_once('../../lib/control_panel.php');
require_once('../../no-web/core/include.php');
require_once('../../no-web/core/functions-scheduling.php');
require_once('draft.cls.php');
require_once('ip.cls.php');

checkCPAcces();

$config2 = getDBConfig('',1);

if(!$_GET['msg_id'])
    die('No ID given');

$type = mysql_real_escape_string(@$_GET['type']);

if($type != "html" && $type != "aol")
{
    $type = 'html';
}

$sql = "SELECT * FROM `msg` WHERE `id` = '".mysql_real_escape_string($_GET['msg_id'])."';";

$draft = $db->GetRow($sql);

// Headers and Footers
require_once('extra_content.php');
$html_header = '';
$text_header = '';
$html_footer = '';
$text_footer = '';

if (!empty($draft['footer']))
{
	$data = get_content_data($draft['footer']);
	$html_footer = $data['html']['data'];
	$text_footer = $data['text']['data'];
}
if (!empty($draft['header']))
{
	$data = get_content_data($draft['header']);
	$html_header = $data['html']['data'];
	$text_header = $data['text']['data'];
}

$draft_o 	= new draft();
$draft_o->load_draft($_GET['msg_id']);
$draft['html_body']	= $draft_o->link_tracking($draft['html_body']);
$draft['aol_body']	= $draft_o->link_tracking($draft['aol_body']);

$body = ($type == 'html') ? $draft['html_body'] : $draft['aol_body'];

if ($type == 'html')
	$draft['html_body'] = $html_header.$body.$html_footer;
else
	$draft['html_body'] = nl2br($text_header).nl2br($body).nl2br($text_footer);


// Domain Replace
$ip 	= $draft_o->get_random_ip();

if (!empty($draft['from_domain']))
	$ip['domain'] = $draft['from_domain'];

$html = str_replace('{{dn}}', $ip['domain'], $draft['html_body']);

$sql 	= "SELECT `content` FROM `content_book` ORDER BY rand() LIMIT 0,1;";
$rw 	= row(query($sql));
$con 	= @$rw['content'];

$html = str_replace('{{book_content}}', $con, $html);

$tpl->html = $html;
$tpl->display('cp/scheduling/draft-html-frame.php');
?>
