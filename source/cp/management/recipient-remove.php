<?PHP
// Includes
require_once('../../no-web/core/include.php');
require_once('functions-management.php');
require_once('Subscribe.php');
// Access
checkCPAcces();
// Crap ?
$sql = "SELECT * FROM sgdne";
$info = $db->GetRow($sql);
$tpl->sgdne = $info['active'];

// Intialise the result set
$c['total']         = 0;
$c['removed']       = 0;
$c['removed_gdne']  = 0;
$c['removed_sgdne'] = 0;
$c['invalid']       = 0;

// User used the textarea
if(@$_POST['recip'] != '')
{
    // Blast emails into array
    $emails = explode("\n",$_POST['recip']);
    // Intialise the object
    $unsubscribe = new Unsubscribe();
    $list = $_POST['list_name'];
    $unsubscribe->list  = $list;

    $unsubscribe->how   = 0;
    // Remove options
    if(@$_POST['sgdne'] == 1)
        $unsubscribe->sgdne = true;

    if(@$_POST['gdne'] == 1)
        $unsubscribe->gdne = true;

    $find = new Subscribe_Find();
    $find->setList($list);
	$i = 0;
    // Loop emails removing one at a time.
    foreach($emails AS $email)
    {
		if (trim($email) == '')
			continue;

        // no @ so its probably an ID
        if(!strpos($email, '@') && ($email != ''))
        {
            $find->setID(trim($email));
            $find->rtn_one = true;
            $rw = $find->find("WHERE `id` = '$find->sub_id'");
            
            if(is_array($rw))
            {
                $email = $rw['local'] . '@' . $rw['domain'];
            }
        }

        $email = trim($email);
        $unsubscribe->setEmail($email);
        
		if($email == '')
        {
            continue;
        }

        switch($unsubscribe->doUnsub())
        {
            case 1:
            $c['removed'] ++;
            break;
            case 2:
            $c['removed_gdne'] ++;
            break;
            case 3:
            $c['removed_sgdne'] ++;
            break;
            case -1:
            $c['invalid'] ++;
            break;
        }

        $unsubscribe->reset();

        $i ++;
    }
}
// User uploaded a file!
if(@$_FILES['recipfile']['tmp_name'] != '')
{
    // Open up a file and read line by line until EOF (end of file)
    $fp = fopen($_FILES['recipfile']['tmp_name'], "r");

    $regexp = "(([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4}))";

    $unsubscribe = new Unsubscribe();
    $list = $_POST['list_name'];
    $unsubscribe->list  = $list;
    $unsubscribe->how   = 0;
    // Remove options
    if($_POST['sgdne'] == 1)
    {
        $unsubscribe->sgdne = true;
    }

    if($_POST['gdne'] == 1)
    {
        $unsubscribe->gdne = true;
    }

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
	
		if (empty($email))
			continue;

        $unsubscribe->setEmail($email);

        // Try to remove it and see what spits back
        switch($unsubscribe->doUnsub())
        {
            case 1:
            $c['removed'] ++;
            break;
            case 2:
            $c['removed_gdne'] ++;
            break;
            case 3:
            $c['removed_sgdne'] ++;
            break;
            case -1:
            $c['invalid'] ++;
            break;
        }

        $unsubscribe->reset();
        $i ++;
    }
}
@$c['total']  = $i;
// Assign Tpl Vars
$tpl->counts = $c;
$tpl->template = "cp/management/recipient-remove.php";
$tpl->display('cp/layout.php');
?>
