<?php
/**
 * This page controls the other types of import (any not wizard).
 *
 * It does: suppression (domain/email), (s)gdne (domain/email), list wash (domain/email)
 * The type ids are as follows:
 * 1 : wizard
 * 2 : suppression emails
 * 3 : gdne emails
 * 4 : wash emails
 * 5 : suppression domains
 * 6 : gdne domains
 * 7 : wash domains
 * 8 : gdne words
 *
 * POST : Lots!
 * GET  : file (string, the name of the file in the import folder).
 *
 * @category   Crons-Minute-Import
 * @package    Celibero
 * @author     Celibero Team
 * @copyright  2005 Celibero
 * @version    CVS: $Id: import-other.php,v 1.2 2005/09/28 15:49:35 tom Exp $
 */


set_time_limit(0);
require_once('../../no-web/core/include.php');

checkCPAcces();

$file 	= $config->values['site']['upload_patch'].'import/'.$_GET['file'];
$md5 	= 0;

if(isset($_POST['suppression']))
{
    $type_id = 2;
    
    if($_POST['suppression_type'] == '2')
    {
        $type_id = 5;
    }
    
    $type    = $_POST['suppression_list'];

	if (!empty($_POST['md5']))
	{
		$md5 	= 1;
		$type 	= esc($type);
		$sql 	= "UPDATE `supression_lists` SET `has_md5` = 1 WHERE `sup_list_id` = '$type';";
		query($sql);
	}
}
elseif(isset($_POST['gdne']))
{
    $type_id = 3;
    $type    = '';
    
    if($_POST['gdne_type'] == '2')
    {
        $type_id = 6;
    }
    if($_POST['gdne_type'] == '3')
    {
        $type_id = 8;
    }
}
elseif(isset($_POST['wash']))
{
    $type_id = 4;
    $type    = $_POST['list_name'];
    $list    = $type;
    
    if($_POST['wash_type'] == '2')
    {
        $type_id = 7;
    }
}

if(isset($type_id))
{
    $sql  = "INSERT INTO imports (title,description,format,ts,file,state,list,delim,type,type_id,md5) VALUES ";
    $sql .= "('".$_POST['title']."','','',NOW(),'".$_GET['file']."','0','$list','".$delim."','$type','$type_id', '$md5');";

    $db->Execute($sql);
    $tpl->done = 1;
}

if(!is_file($file) && !$tpl->done)
{
    header("Location: /cp/management/import-wizard.php");
    exit;
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

$tpl->file = $_GET['file'];
$tpl->template  = "cp/management/import-other.php";
$tpl->display('cp/layout.php');
?>
