<?PHP
require_once('no-web/core/include.php');

$auth->logout();

if(isset($_POST['login']))
{
    if($auth->login($_POST['username'],$_POST['password']))
    {
        header("Location: /cp/");
    }
    else 
    {
        $tpl->error = 'Error: you supplied bad login details';
    }
}
$tpl->template = "login.php";
$tpl->display('layout.php');
?>
