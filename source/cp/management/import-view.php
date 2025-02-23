<?PHP
require_once('../../no-web/core/include.php');
require_once('HTML/Layout.php');
checkCPAcces();

$sql= "SELECT *, UNIX_TIMESTAMP(start) AS start_stamp, UNIX_TIMESTAMP(end) AS end_stamp FROM imports WHERE import_id = '".$_GET['id']."';";
$import = $db->GetRow($sql);

$tpl->import = $import;
$tpl->template = "cp/management/import-view.php";
$tpl->display('cp/layout.php');
?>