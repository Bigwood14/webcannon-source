<?PHP
require_once('../../no-web/core/include.php');
require_once('HTML/Layout.php');
checkCPAcces();

if(@$_GET['action'] == 'delete')
{
	$id = mysql_real_escape_string($_GET['id']);

    $sql = "DELETE FROM msg WHERE id = '$id' AND state = '0';";
    $db->Execute($sql);
    
    $sql = "DELETE FROM msg_to_subject WHERE msg_id = '$id';";
    $db->Execute($sql);
    
    $sql = "DELETE FROM msg_to_domain WHERE msg_id = '$id';";
    $db->Execute($sql);
    
    $sql = "DELETE FROM msg_to_list WHERE msg_id = '$id';";
    $db->Execute($sql);
    
    $sql = "DELETE FROM msg_to_from WHERE msg_id = '$id';";
    $db->Execute($sql);
    
    $sql = "DELETE FROM msg_to_category WHERE msg_id = '$id';";
    $db->Execute($sql);
}

if (@$_GET['action'] == 'make_top')
{
	$msg_id = esc($_GET['id']);
	$sql 	= "UPDATE `msg` SET `top` = 1 WHERE `id` = '$msg_id';";
	query($sql);
}

if (@$_GET['action'] == 'unmake_top')
{
	$msg_id = esc($_GET['id']);
	$sql 	= "UPDATE `msg` SET `top` = 0 WHERE `id` = '$msg_id';";
	query($sql);
}


$drafts = array();

$user_sql = '';

//if ($permissions->auth->user['mailer'] == 1)
//	$user_sql .= " WHERE `msg`.`user_id` = '{$permissions->auth->user['user_id']}' ";
$where = '';
$top_where = '';

if (!empty($_GET['q']))
{
	$q 		= esc($_GET['q']);
	$where .= " WHERE `title` LIKE '%{$q}%' ";
	$top_where .= " AND `title` LIKE '%{$q}%' ";
}

$sql = "SELECT * FROM `msg` WHERE `top` > 0 {$top_where} ORDER BY `top` ASC;";
$res = query($sql);
$top = array();

while ($row = row($res))
	$top[] = do_row($row);

$sql 	= "SELECT count(*) FROM msg {$user_sql} {$where}";
$rw 	= $db->GetRow($sql);

$rows_per_page 	= 20;
$num_rows 		= $rw[0];

$page_num 			= (int) (empty($_GET['page_num'])) ? 1 : $_GET['page_num'];
$paging_from 		= ($page_num - 1) *  $rows_per_page;
$pager 				= pager($paging_from, $rows_per_page, $num_rows);
$ADODB_FETCH_MODE 	= ADODB_FETCH_ASSOC;
$sql 				= "SELECT * FROM msg {$user_sql} {$where} ORDER BY id DESC LIMIT $paging_from,$rows_per_page";

$rs = $db->Execute($sql);

while ($rw = $rs->FetchRow())
	$drafts[] = do_row($rw);

function do_row ($rw)
{
	if ($rw['state'] == 1)
	{
		$sql 			= "SELECT * FROM `schedule` WHERE `msg_id` = '{$rw['id']}';";
		$rw['sched'] 	= row(query($sql));

		$sql 			= "SELECT * FROM `tracked_link` WHERE `draft_id` = '{$rw['id']}';";
		$res 			= query($sql);
		$rw['links'] 	= array();

		$rw['sched']['clicks'] 	= 0;
		$rw['our_scomp'] 		= 0;

		while ($row = row($res))
		{
			$rw['links'][] = $row;
			$rw['sched']['clicks'] += $row['count'];

			if (strpos($row['url'], 'report.html'))
				$rw['our_scomp'] += $row['count'];
		}

		$sql  			= "SELECT SUM(`count`) AS `count` FROM `msg_complaint` WHERE `msg_id` = '".$rw['id']."';";
		$tmp 			= row(query($sql));
		$rw['scomp'] 	= $tmp['count'];

	}

	return $rw;
}

$tpl->top 		= $top;
$tpl->pager 	= $pager;
$tpl->drafts 	= $drafts;

$config_db = getDBConfig('',1);

if (@$config_db['ARCHIVE_DISPLAY'] == 'aol')
	$tpl->template 	= "cp/scheduling/draft_archive_aol.php";
else
	$tpl->template  = "cp/scheduling/draft-archive.php";

$tpl->display('cp/layout.php');
?>
