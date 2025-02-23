<?PHP
set_time_limit(0);
require_once('../../no-web/core/include.php');
require_once('functions-management.php');
checkCPAcces();
$regexp = "(([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4}))";
//print_r($_FILES);
if($_FILES['filename']['tmp_name'] != '')
{//print 'ye';
    $lines = file($_FILES['filename']['tmp_name']);
    //$emails = explode("\n",$emails);

    foreach($lines AS $line)
    {
        preg_match($regexp, $line, $matches);
        if($matches[1] != "")
        {
            $rtn = addSuppressionEmail($matches[0],$_POST['list']);
            
            if($rtn == -1)
            {
                $c['duplicate'] ++;
            }
            elseif ($rtn == -2)
            {
                $c['invalid'] ++;
            }
            else 
            {
                $c['added'] ++;
            }
        }
    }
    
    $tpl->c = $c;
}


$sql = "SELECT * FROM supression_lists";

$rs = $db->Execute($sql);

while($rw = $rs->FetchRow())
{
    if($rw['sup_list_id'] == $_GET['list-id'])
    {
        $sel = ' selected';
    }
    else 
    {
        $sql = '';
    }
    $options .= "<option value=\"".$rw['sup_list_id']."\"$sel>".$rw['title']."</option>";    
}
$tpl->options = $options;
$tpl->template = "cp/management/supression-lists-add.php";
$tpl->display('cp/layout.php');
?>