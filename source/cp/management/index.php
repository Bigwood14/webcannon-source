<?PHP
require_once('../../no-web/core/include.php');
checkCPAcces();


$tpl->template = "cp/management/index.php";
$tpl->display('cp/layout.php');
?>