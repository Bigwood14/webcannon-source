<?PHP
$auth_is_admin = true;
require_once('../../no-web/core/include.php');
require_once('HTML/Layout.php');
checkCPAcces();

$sql= "SELECT *, UNIX_TIMESTAMP(start) AS start_stamp, UNIX_TIMESTAMP(end) AS end_stamp FROM export WHERE export_id = '".$_GET['export_id']."';";
$export = $db->GetRow($sql);

if($export['import_id'] > 0)
{
    $import = $db->GetRow("SELECT * FROM imports WHERE import_id = '{$export['import_id']}';");
    $export['tag'] = $import['title'];
}
else 
{
    $export['tag'] = 'All';
}

$tpl->export = $export;
$tpl->template = "cp/management/export-view.php";
$tpl->display('cp/layout.php');
?>
