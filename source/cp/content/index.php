<?PHP
require_once('../../no-web/core/include.php');
checkCPAcces();


$tpl->template = "cp/content/index.php";
$tpl->display('cp/layout.php');
?>