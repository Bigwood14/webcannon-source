<?PHP
require_once('../../no-web/core/include.php');

checkCPAcces();

$sql = "SELECT COUNT(*) FROM log";


$rw = $db->GetRow($sql);
$rows_per_page = 100;
$num_rows = $rw[0];
$page_num = (empty($_GET['page_num'])) ? 1 : $_GET['page_num'];
$paging_from = ($page_num - 1) *  $rows_per_page;
//$obj_games->limit($paging_from,$rows_per_page);
$pager = pager($paging_from, $rows_per_page, $num_rows);
$sql = "SELECT *,unix_timestamp(date) AS date FROM log $where ORDER BY date DESC LIMIT $paging_from,$rows_per_page";
$data = array();
$data = $db->GetAll($sql);
//print mysql_error();
$tpl->pager = $pager;
$tpl->logs = $data;
$tpl->template = "cp/reporting/logs.php";
$tpl->display('cp/layout.php');
?>