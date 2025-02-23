<?PHP
set_time_limit(0);
require_once('../../no-web/core/include.php');
require_once('HTML/Layout.php');
checkCPAcces();

$sql = "SELECT * FROM user";
$rs = $db->Execute($sql);

if(isset($_POST['reset_bounce']))
{
	if($db->GetRow("SHOW TABLES FROM celibero LIKE 'operations';") == false)
	{
		$db->Execute("CREATE TABLE `operations` (
						`operation_id` INT( 9 ) NOT NULL AUTO_INCREMENT ,
					 	`type` VARCHAR( 50 ) NOT NULL ,
						`data` TEXT NOT NULL ,
						`state` ENUM( 'pending', 'processing', 'completed', 'cancelled' ) DEFAULT 'pending' NOT NULL ,
						`date_in` DATETIME NOT NULL ,
						`date_start` DATETIME NOT NULL ,
						`date_end` DATETIME NOT NULL ,
					  	PRIMARY KEY ( `operation_id` ) ,
					  	INDEX ( `type` )
					);");
	}
	$db->Execute("INSERT INTO `operations` (`type`, `data`, `date_in`) VALUES ('reset_bounce', '".mysql_escape_string(serialize($_POST['list']))."', NOW());");
	print mysql_error();
	header("Location: /cp/management/operations.php");die();
}

while($rw = $rs->FetchRow())
{
	$tpl->lists[] = $rw;
}

$num_rows       = countDB($db, 'operations', '', 'operation_id');
$rows_per_page  = 80;

$page_num       = (empty($_GET['page_num'])) ? 1 : $_GET['page_num'];
$paging_from    = ($page_num - 1) *  $rows_per_page;
$pager = pager($paging_from, $rows_per_page, $num_rows);

$sql = "SELECT * FROM operations ORDER BY date_in DESC LIMIT $paging_from,$rows_per_page";

$data = $db->GetAll($sql);
if(!is_array($data)) $data = array();

$tpl->data = $data;
$tpl->template = "cp/management/operations.php";
$tpl->display('cp/layout.php');
?>