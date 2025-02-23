<?PHP
require_once('../../no-web/core/include.php');

checkCPAcces();
$list = mysql_real_escape_string(@$_GET['list']);

if($list != '')
    $sql = "SELECT * FROM `list` where `name` = '$list' LIMIT 0,1;";
else 
    $sql = "SELECT * FROM `list` LIMIT 0,1;";

$rw = $db->GetRow($sql);
if($rw == false)
{
    die("No lists to find in.");
}

$lists 		= lists::singleton();
$email_cols = $lists->getCols($rw['name']);


$fields 	= array();
$fields[] 	= 'email';

foreach($email_cols AS $col)
{
    if(in_array($col['Field'],$config->values['mm_field_hide']) || ($col['Field'] == 'local') || ($col['Field'] == 'domain'))
        continue;
    $fields[] = $col['Field'];
}

if(isset($_POST['update']))
{
    $sql = "SELECT * FROM mm_defaults";
    $rw = $db->GetRow($sql);
    if($rw == false)
    {
        $sql = "INSERT INTO mm_defaults () VALUES ();";
        $db->Execute($sql);
    }
    $sql = "UPDATE mm_defaults SET ";
    foreach($fields AS $field)
    {
        if(!empty($rw[$field]))
        {
            $db->Execute("ALTER TABLE `mm_defaults` ADD `$field` VARCHAR( 40 ) NOT NULL");
        }
        $sql .= "`".$field."` = '".mysql_real_escape_string($_POST['default'][$field])."',";
    }
    $sql = rtrim($sql,',');
    if(!$db->Execute($sql))
    {
        
    }
}

$sql = "SELECT * FROM mm_defaults LIMIT 0,1";
$rw = $db->GetRow($sql);
//print_r($rw);
$tpl->defaults = $rw;
$tpl->fields = $fields;
$tpl->display('cp/scheduling/personalization-how.php');
?>
