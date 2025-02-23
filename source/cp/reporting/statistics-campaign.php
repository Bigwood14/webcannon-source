<?PHP
require_once('../../no-web/core/include.php');
require_once('HTML/Time_Date.php');

checkCPAcces();

$data = array();
$stats = array();
$sql = "SELECT *,UNIX_TIMESTAMP(scheduled_time) AS s_date,s.state AS state1,s.id AS s_id,s.state AS st
        FROM schedule AS s,msg AS m";
$sql .= " WHERE s.msg_id = m.id";
if(isset($_GET['display']))
{
	if($_GET['option'] == "between")
	{
		$date1 = mysql_real_escape_string($_GET['year_1']."-".$_GET['month_1']."-".$_GET['day_1']);
		$date2 = mysql_real_escape_string($_GET['year_2']."-".$_GET['month_2']."-".$_GET['day_2']);


		$sql .= " AND  `scheduled_time` >= '$date1' AND `scheduled_time` <= '$date2'";
	}
	else
	{
		if($_GET['range'] == '1M')
		{
			$sql .= " AND `scheduled_time` > '".date("Y-m-d",mktime(0,0,0,date('m')-1,date('d'),date('Y')))."'";
		}
		elseif($_GET['range'] == '6M')
		{
			$sql .= " AND `scheduled_time` > '".date("Y-m-d",mktime(0,0,0,date('m')-6,date('d'),date('Y')))."'";
		}
		else
		{
			$sql .= " AND `scheduled_time` > '".date("Y-m-d",mktime(0,0,0,date('m'),date('d')-7,date('Y')))."'";
		}
	}
}
else
{
	$sql .= " AND `scheduled_time` > '".date("Y-m-d",mktime(0,0,0,date('m'),date('d')-7,date('Y')))."'";
	$sql .= " ORDER BY scheduled_time DESC LIMIT 0,30";
}

$rs = $db->Execute($sql);

while($rw = $rs->FetchRow())
{
    $sql = "SELECT SUM(`count`) AS clicks FROM links WHERE msg_id = '".$rw['msg_id']."' AND dummy ='0' AND img = '0'";
    $rw2 = $db->GetRow($sql);
    $clicks = ($rw2['clicks'] < 1) ? '0' : $rw2['clicks'];
    $rw['clicks'] = $clicks;
    $rw['scheduled_date'] = $rw['s_date'] > 0 ? date("M d H:i:s",$rw['s_date']) : ".";
    $stats[] = $rw;
}
$data = $stats;

$tpl->pager = $pager;
$tpl->stats = $data;
$tpl->template = "cp/reporting/statistics-campaign.php";
$tpl->display('cp/layout.php');
?>
