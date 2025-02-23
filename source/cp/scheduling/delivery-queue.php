<?PHP
require_once('../../lib/control_panel.php');
require_once('public.php');

if (!empty($_POST['speed_update']))
{
	$mailing_speed 	= (int) round($_POST['speed'], 1);

	$sql 			= "REPLACE INTO `config` (`KEY`, `value`) VALUES ('ENGINE_MAILING_WAIT', '$mailing_speed');";
	$db->Execute($sql);
}

$config2 = getDBConfig('',1);

if (empty($config2['ENGINE_MAILING_WAIT']))
	$mailing_speed = 0;
else
	$mailing_speed = $config2['ENGINE_MAILING_WAIT'];

// Pause
if(@$_GET['action'] == 'pause')
{
    $sql = "UPDATE schedule SET state = '10' WHERE id = '".mysql_real_escape_string($_GET['id'])."';";
    $db->Execute($sql);
    print mysql_error();
    header("Location: /cp/scheduling/delivery-queue.php");exit;
}

// Unpause
if(@$_GET['action'] == 'unpause')
{
    $sql = "UPDATE schedule SET state = '12' WHERE id = '".mysql_real_escape_string($_GET['id'])."';";
    $db->Execute($sql);
    print mysql_error();
    header("Location: /cp/scheduling/delivery-queue.php");exit;
}

//Pager
$num_rows       = countDB($db, 'schedule', '', 'id');
$rows_per_page  = 20;

$page_num       = (int) (empty($_GET['page_num'])) ? 1 : $_GET['page_num'];
$paging_from    = ($page_num - 1) *  $rows_per_page;
$tpl->pager = pager($paging_from, $rows_per_page, $num_rows);

$user_sql 		= '';

//if ($permissions->auth->user['mailer'] == 1)
//	$user_sql .= " AND `msg`.`user_id` = '{$permissions->auth->user['user_id']}' ";

$sql = "SELECT *,  schedule.state AS st,
        schedule.id AS sid,
        UNIX_TIMESTAMP(start_time) AS start_timestamp,
        UNIX_TIMESTAMP(scheduled_time) AS scheduled_timestamp,
        UNIX_TIMESTAMP(end_time) AS end_timestamp  
        FROM schedule,msg WHERE schedule.msg_id = msg.id AND (schedule.state = 7 OR schedule.state = 9) {$user_sql} ORDER BY scheduled_timestamp DESC LIMIT $paging_from,$rows_per_page;";


$rs = query($sql);

$q = gather_mailings($rs);

$sql = "SELECT *,  schedule.state AS st,
        schedule.id AS sid,
        UNIX_TIMESTAMP(start_time) AS start_timestamp,
        UNIX_TIMESTAMP(scheduled_time) AS scheduled_timestamp,
        UNIX_TIMESTAMP(end_time) AS end_timestamp  
        FROM schedule,msg WHERE schedule.msg_id = msg.id AND (schedule.state != 7 AND schedule.state != 9) {$user_sql} ORDER BY scheduled_timestamp DESC;";


$rs = query($sql);

$data = gather_mailings($rs);


function gather_mailings ($rs)
{
	global $db;

	$q = array();

	while($rw = row($rs))
	{
	    $rw['start_date']     = $rw['start_timestamp'] > 0 ? date("M d H:i:s",$rw['start_timestamp']) : ".";
	    $rw['end_date']       = $rw['end_timestamp'] > 0 ? date("M d H:i:s",$rw['end_timestamp']) : ".";
	    $rw['scheduled_date'] = $rw['scheduled_timestamp'] > 0 ? date("M d H:i:s",$rw['scheduled_timestamp']) : ".";
	    //print_r($rw);
	    // Server
	    $sql = "SELECT * FROM servers WHERE server_id = '".$rw['server_id']."';";
	    $rw['server'] = $db->GetRow($sql);
	
	    $rw['total_tried'] = $rw['success'] + $rw['failure'] + $rw['deferral'];
	    $rw['bounced'] = $rw['failure'] + $rw['deferral'];
	
	    // Work out % in small / big * 100
	    if($rw['total_emails'] != 0)
	    {
	        $percentage = round(($rw['total_tried'] / $rw['total_emails']) * 100);
	        $blocks = round($percentage / 10);
	        $rw['blocks'] = $blocks;
	        $rw['percent'] = $percentage;
	    }
	
	    // Lists
	    $sql    = "SELECT * FROM msg_to_list WHERE msg_id = '".$rw['msg_id']."'";
	    $rs2     = $db->Execute($sql);
	    $rw['lists'] = array();
	    while ($lt = $rs2->FetchRow())
	    {
	        $sql = "SELECT * FROM list WHERE list_id = '".$lt['list_id']."'";
	        $lst = $db->GetRow($sql);
	
	        if(isset($lst['name']))
	        {
	            $rw['lists'][] = $lst;
	        }
	        else 
	        {
	            $rw['lists'][] = '{deleted}';
	        }
	    }
	
	
		// AOL Complaint Ratio
		$sql 	= "SELECT SUM(`count`) AS `count` FROM `msg_complaint` WHERE `msg_id` = '".$rw['msg_id']."';";
		$rs2 	= $db->Execute($sql);
		$rw2 	= $rs2->FetchRow();
		
		$rw['aol_count'] 	= $rw2['count'];
		
		if ($rw['total_tried'] != 0)
			$rw['aol_ratio'] 	= ($rw2['count'] / $rw['success'])*100;
		else
			$rw['aol_ratio'] = 0;
	    $q[] = $rw;
	}

	return $q;
}

//print_r($draft);
$tpl->mailing_speed = $mailing_speed;
/*$tpl->scripts[] 	= 'jquery.js';*/
//$tpl->scripts[] 	= 'slider.js';
$tpl->scripts[] 	= 'yui/dom/dom-min.js';
$tpl->scripts[] 	= 'yui/event/event-min.js';
$tpl->scripts[] 	= 'yui/dragdrop/dragdrop-min.js';
$tpl->scripts[] 	= 'yui/element/element-beta-min.js';
$tpl->scripts[] 	= 'yui/button/button-min.js';
$tpl->scripts[] 	= 'yui/slider/slider-min.js';

header('Refresh: 190; url=/cp/scheduling/delivery-queue.php');

$tpl->q 	= $q;
$tpl->data 	= $data;
$tpl->template = "cp/scheduling/delivery-queue.php";
$tpl->display('cp/layout.php');
?>
