<?PHP
$auth_is_admin = true;
require_once('../../no-web/core/include.php');
require_once('HTML/Layout.php');
checkCPAcces();


$sql = "SHOW STATUS;";
$rs = $db->Execute($sql);

while($rw = $rs->FetchRow())
{
    $server_status[$rw[0]] = $rw[1];
}

$tmp_array = $server_status;

foreach ($tmp_array AS $name => $value)
{
    if (substr($name, 0, 4) == 'Com_')
    {
        $query_stats[str_replace('_', ' ', substr($name, 4))] = $value;
        unset($server_status[$name]);
    }
}
$tpl->server_status = $server_status;
$tpl->query_stats = $query_stats;
$tpl->up_time = shell_exec('uptime');
$tpl->template = "cp/server/server-status.php";
$tpl->display('cp/layout.php');
?>
