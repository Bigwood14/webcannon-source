<?php
require_once('../../no-web/core/include.php');
require_once('../../no-web/core/functions-scheduling.php');
require_once('draft.cls.php');

checkCPAcces();

$draft 	= new draft();
$msg 	= $draft->load_draft($_GET['msg_id']);
$bodies = $draft->build();

$bodies['main'] = "To: {{01}}\n".$bodies['main'];

$emails 	= explode("\n",$_POST['test_emails']);

$file   = $config->values['site']['path'] . 'cp/scheduling/test/test';

$f_routes = fopen('/var/qmail/control/bindroutes', 'w+');
fwrite($f_routes, ':'.$_POST['use_ip']);
fclose($f_routes);

$i 			= 0;

$ip 			= esc($_POST['use_ip']);
$sql 			= "SELECT * FROM `server_to_ip` WHERE `ip` = '$ip' ORDER BY `server_id` ASC;";
$rows 			= all_rows(query($sql));
$domain_send 	= $rows[0]['domain'];

$from_line 		= $draft->get_froms(true);
$str 			= '';
foreach ($emails AS $email)
{
    $email 			= trim(str_replace("\n", '', $email));

    if(validEmail($email))
    {
		$fh = fopen($file, "w+");
        fwrite($fh, str_replace("{{01}}", $email, $bodies['main']));
        fclose($fh);

        $parts 	= explode('@', $email);

        $domain = escapeshellarg($parts[1]);
		$email 	= escapeshellarg($email);
		$from 	= escapeshellarg($from_line['from_local'].'@'.$domain_send);
    
		$cmd = 'cd '.$config->values['site']['path']."cp/scheduling/test/; /var/qmail/bin/qmail-remote {$domain} {$from} $email < test";
	    exec($cmd, $o, $r);
        //print_r($o);
        //print_r($r);

        if($o[$i]{0} !== "r")
        {
            $str .= "&e[$email]=".urlencode($o[$i]);
        }
        else
        {
            $str .= "&e[$email]=yes";
        }
        $i++;
    }
}

header("Location: /cp/scheduling/draft-view.php?msg_id=".$msg['id']."&sent=1$str");
?>
