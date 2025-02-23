<?PHP
require '../../lib/control_panel.php';

checkCPAcces();


if($_POST['submit'])
{
    //print_r($auth);
    
    if($_POST['cur_password'] != $auth->user['text_password'])
    {
        $tpl->msg = 'Wrong current password';
    }
    elseif($_POST['password_1'] != $_POST['password_2'])
    {
        $tpl->msg = 'New passwords do not match';
    }
    elseif($_POST['password_1'] == '')
    {
        $tpl->msg = 'Cannot set a blank password';
    }
    else 
    {
        $sql = "UPDATE users_auth SET password = '".md5(mysql_real_escape_string($_POST['password_1']))."', text_password = '".mysql_real_escape_string($_POST['password_1'])."' WHERE user_id = '".$auth->user['user_id']."'";
        $db->Execute($sql);
        $tpl->msg = 'Password has been updated, you will have to log back in.';

		$sql = "TRUNCATE TABLE `users_session`";
		query($sql);
    }
    
}

$config2 = getDBConfig('',1);
//print_r($config2);


$tpl->config2 = $config2;
$tpl->template = "cp/options/password-change.php";
$tpl->display('cp/layout.php');
?>
