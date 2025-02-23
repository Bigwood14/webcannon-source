<?PHP
require_once 'lib/public.php';
require_once 'lib/filter.cls.php';
require_once('functions-management.php');

$email = (empty($_POST['email'])) ? @$_GET['e'] : $_POST['email'];

$email = filter::remove_xss($email);

if($email != '')
{
	$i = 0;
    require_once('Subscribe.php');

    $unsubscribe = new Unsubscribe();

    $unsubscribe->how   = 8;
    $unsubscribe->gdne = true;

    $email = trim($email);

    $unsubscribe->setEmail(mysql_escape_string($email));
    $good = 0;
    switch($unsubscribe->doUnsub())
    {
        case 1:
        $tpl->error = "Email address $email removed.";
        $good = 1;
        break;
        case 2:
        $tpl->error = "Email address $email removed.";
        $good = 1;
        break;
        case 3:
        $tpl->error = "Email address $email removed.";
        $good = 1;
        break;
        case -1:
        $tpl->error = "Error: Invalid email address.";
        break;
    }

    $i++;
    
    if($good == 1)
    {
        $sql = "SELECT `value` AS url FROM config WHERE `KEY` = 'UNSUB_REDIRECT';";
        $rw  = $db->GetRow($sql);
        
        if(@$rw['url'] != '')
        {
            header("Location: ".$rw['url']);
        }
    }
}

//$tpl->template = "index.php";
$tpl->display('index.php');
?>
