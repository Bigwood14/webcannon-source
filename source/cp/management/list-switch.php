<?PHP
set_time_limit(0);
require_once('../../no-web/core/include.php');
require_once('../../core/functions-scheduling.php');

checkCPAcces();

if(isset($_POST['list_name']))
{
    //$profile->addText('LIST',$_POST['list_name']);
    //print_r($auth);
    $sql = "UPDATE users_profile_text SET `text` = '".$_POST['list_name']."' WHERE `profile_key` = 'LIST' AND user_id = '".$auth->user['user_id']."';";
    $db->Execute($sql);
    $tpl->done = 1;
}

$tpl->template  = "cp/management/list-switch.php";
$tpl->display('cp/layout-pop.php');
?>