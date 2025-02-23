<?PHP
require_once('../../no-web/core/include.php');

checkCPAcces();
require_once('HTML/Layout.php');
require_once('link_tracking.cls.php');

$campaign_id = mysql_real_escape_string($_GET['id']);

$sql = "SELECT *,UNIX_TIMESTAMP(start_time) AS start_stamp,UNIX_TIMESTAMP(end_time) AS end_stamp  FROM schedule WHERE id = '$campaign_id';";
$campaign = $db->GetRow($sql);

$sql = "SELECT * FROM msg WHERE id = '".$campaign['msg_id']."';";
$msg = $db->GetRow($sql);

if (!empty($msg['body']) && !empty($msg['html_body']))
	$msg['content'] = 'HTML + Text';
elseif (!empty($msg['html_body']))
	$msg['content'] = 'HTML Only';
else
	$msg['content'] = 'Text Only';

$sql = "SELECT * FROM tracked_link WHERE draft_id = '".$campaign['msg_id']."';";
$clicks = $db->GetAll($sql);

$campaign['total_tried'] = $campaign['success'] + $campaign['failure'] + $campaign['deferral'];

$link 	= new link_tracking();
$links 	= $link->find('', $msg['html_body'], $msg['aol_body'], true);
$images = array();

foreach ($links as $link)
{
	if (strpos($link, '{{dn}}') === false)
		continue;

	$images[] = $link;
}

$sql = "SELECT * FROM `msg_complaint` WHERE `msg_id` = '{$campaign['msg_id']}';";

$aol_complaint = $db->GetAll($sql);

$sql = "SELECT `si`.`ip`, `mi`.* FROM `server_to_ip` `si`, `msg_to_ip` `mi` WHERE `si`.`ip_id` = `mi`.`ip_id` AND `mi`.`draft_id` = '{$campaign['msg_id']}';";
$ips = array();
$res = query($sql);

while ($row = row($res))
	$ips[] = $row;

$tpl->scripts[] 	= 'jquery.js';
$tpl->scripts[] 	= 'campaign.js';
$tpl->scripts[]		= 'draft.js';

$tpl->mailer = $permissions->auth->user['mailer'];
$tpl->stats = array('campaign' => $campaign,'msg' => $msg,'clicks' => $clicks, 'images' => $images, 'aol' => $aol_complaint, 'ips' => $ips);
$tpl->template = "cp/reporting/campaign-breakdown.php";
$tpl->display('cp/layout.php');
?>
