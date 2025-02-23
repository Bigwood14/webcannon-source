<?PHP
$auth_is_admin = true;
require_once('../../no-web/core/include.php');
require_once('Subscribe.php');

checkCPAcces();
// Setup result set
$c['add']       = 0;
$c['invalid']   = 0;
$c['dup']       = 0;
$c['unsub']     = 0;
$c['unsub_g']   = 0;
$c['unsub_g_d'] = 0;
$c['unsub_g_w'] = 0;
$c['total']     = 0;
// User pasted a bunch into the text field.
if($_POST['recip'] != '')
{
    // What list they choose?
    $list = $_POST['list_name'];

    // Make an array out of entered emails.
    $emails = explode("\n",$_POST['recip']);
    $subscribe = new Subscribe();
    $subscribe->list  = $list;
    $subscribe->how = 0;
    // Loop array
    foreach($emails AS $email)
    {
        // Its blank? That happens..
        if($email == '')
        {
            continue;
        }
        // Trim it up
        $email = trim($email);
        $subscribe->email = $email;
        
        // Try to add it and see what spits back
        switch($subscribe->doSub())
        {
            case 1:
            $c['add'] ++;
            break;
            case -1:
            $c['invalid'] ++;
            break;
            case -2:
            $c['dup'] ++;
            break;
            case -3:
            $c['unsub'] ++;
            break;
            case -4:
            $c['unsub_g'] ++;
            break;
            case -5:
            $c['unsub_g_d'] ++ ;
            break;
            case -6:
            $c['unsub_g_w'] ++ ;
            break;
        }

        $subscribe->reset();

        $i ++;
    }
}
// User uploaded a file!
if($_FILES['recipfile']['tmp_name'] != '')
{
    // Open up a file and read line by line until EOF (end of file)
    $fp = fopen($_FILES['recipfile']['tmp_name'], "r");
    
    $regexp = "(([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4}))";
    
    $subscribe = new Subscribe();
    $subscribe->how = 0;
    $list = $_POST['list_name'];
    $subscribe->setList($list);
    
    while (!feof ($fp))
    {
        $buffer  = fgets($fp, 4096);
        
        preg_match($regexp, $buffer, $matches);
        // We found nothing
        if($matches[1] == "")
        {
            continue;
        }
        
        // Trim it up
        $email            = trim($matches[0]);
        $subscribe->setEmail($email);
        
        // Try to add it and see what spits back
        switch($subscribe->doSub())
        {
            case 1:
            $c['add'] ++;
            break;
            case -1:
            $c['invalid'] ++;
            break;
            case -2:
            $c['dup'] ++;
            break;
            case -3:
            $c['unsub'] ++;
            break;
            case -4:
            $c['unsub_g'] ++;
            break;
            case -5:
            $c['unsub_g_d'] ++ ;
            break;
            case -6:
            $c['unsub_g_w'] ++ ;
            break;
        }

        $subscribe->reset();
        
        $i ++;
    }
}

$c['total']  = $i;
// Assign Tpl Stuff
$tpl->counts = $c;
$tpl->template = "cp/management/recipient-add.php";
$tpl->display('cp/layout.php');
?>
