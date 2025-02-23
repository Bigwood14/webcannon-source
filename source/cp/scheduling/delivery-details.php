<?PHP
require_once('../../no-web/core/include.php');

checkCPAcces();

$sql = "SELECT * FROM schedule_log WHERE schedule_id = '".$_GET['id']."'";

$tpl->details = $db->GetAll($sql);
$tpl->template = "cp/scheduling/delivery-details.php";
$tpl->display('cp/layout.php');
?>