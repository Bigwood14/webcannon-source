<?PHP
require_once('../../no-web/core/include.php');

checkCPAcces();

if(@$_GET['confirm'] == 'y')
{
	$id 	= mysql_real_escape_string($_GET['id']);
    $sql 	= "SELECT state FROM schedule WHERE msg_id = '$id';";
    $rw 	= $db->GetRow($sql);

    $set = '8';

    if($rw['state'] < '4')
    {
        $set = '9';
    }
    
    $sql = "UPDATE schedule SET state = '$set', end_time = NOW() WHERE msg_id = '$id'";
    $db->Execute($sql);
    $tpl->confirmed = 1;
}

$tpl->template = "cp/scheduling/delivery-cancel.php";
$tpl->display('cp/layout.php');
?>
