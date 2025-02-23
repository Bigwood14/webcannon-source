<?PHP
require '../../lib/control_panel.php';
require_once('HTML/Layout.php');
require 'suppression_lists.php';

checkCPAcces();

if($_GET['action'] == 'abort')
{
    $sql = "UPDATE imports SET state = '3' WHERE import_id = '".$_GET['import_id']."'";
    $db->Execute($sql);
    header("Location: /cp/management/imports.php");
    die;
}
if($_GET['action'] == 'restart')
{
    $sql = "UPDATE imports SET state = '0' WHERE import_id = '".$_GET['import_id']."'";
    $db->Execute($sql);
    header("Location: /cp/management/imports.php");
    die;
}

$num_rows       = countDB($db, 'imports', '', 'import_id');
$rows_per_page  = 100;

$page_num       = (empty($_GET['page_num'])) ? 1 : $_GET['page_num'];
$paging_from    = ($page_num - 1) *  $rows_per_page;
$pager = pager($paging_from, $rows_per_page, $num_rows);

$sql = "SELECT * FROM imports ORDER BY ts DESC LIMIT $paging_from,$rows_per_page";

$data = $db->GetAll($sql);

$lists 				= find_suppression_lists();
$suppression_lists 	= array();

foreach ($lists as $list)
	$suppression_lists[$list['sup_list_id']] = $list['title'];

$tpl->suppression_lists = $suppression_lists;
$tpl->pager 			= $pager;
$tpl->imports 			= $data;
$tpl->template 			= "cp/management/imports.php";
$tpl->display('cp/layout.php');
?>
