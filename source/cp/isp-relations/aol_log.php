<?php

//require_once('../../no-web/core/include.php');
require '../../lib/control_panel.php';
require_once('HTML/Layout.php');
checkCPAcces();

if (isset($_GET['view']))
{
	$aol_ratio_id 	= esc($_GET['view']);
	$sql 			= "SELECT * FROM `aol_ratio` WHERE `aol_ratio_id` = '$aol_ratio_id';";
	$row 			= row(query($sql));

	die('<pre>'.$row['message'].'</pre>');
}


$domain 	= @$_GET['domain'];
$domain 	= esc($domain);

$sql 		= "SELECT * FROM `server_to_ip` WHERE `domain` = '$domain';";

if (!row(query($sql)))
	die('No Domain');

$sql 		= "SELECT * FROM `aol_ratio` WHERE `ip` = '$domain' ORDER BY `date` DESC;";
$rows 		= all_rows(query($sql));

$tpl->rows 		= $rows;
$tpl->template 	= "cp/isp-relations/aol_log.php";
$tpl->display('cp/layout.php');
?>
