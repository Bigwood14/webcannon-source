<?PHP
require_once('../../no-web/core/include.php');

checkCPAcces();

if($_GET['type'] == 'html')
{
    $type = "HTML";
}
else 
{
    $type = "TEXT";
}

$head = getDBConfig($type."_HEADER",1);
$foot = getDBConfig($type."_FOOTER",1);
$domain = getDefaultDomain();

$head = str_replace("{{dn}}", $domain, $head);
$foot = str_replace("{{dn}}", $domain, $foot);

$tpl->type = $type;
$tpl->head = $head['value'];
$tpl->foot = $foot['value'];

$tpl->display('cp/options/preview-h-f.php');
?>