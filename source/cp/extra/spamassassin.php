<?php
set_time_limit(0);
require_once('../../no-web/core/include.php');
require_once('../../no-web/core/functions-scheduling.php');
require_once('draft.cls.php');

checkCPAcces();

if(isset($_GET['draft_id']))
{
    $draft 	= new draft();
	$msg 	= $draft->load_draft($_GET['draft_id']);
	$bodies = $draft->build();
	
	$bodies['main'] = "To: {{01}}\n".$bodies['main'];

	$domain = getDefaultDomain();
	$body 	= str_replace('{{01}}', 'satest@'.$domain, $bodies['main']);

	// inject the TO header

    $fname  = $config->values['site']['path'] . 'cp/scheduling/test/sa';
    $f      = fopen($fname, 'w+');

    fputs($f, $body);
    fclose($f);

	$cmd 	= '/usr/bin/spamassassin -t -x < '.$fname.' > '.$config->values['site']['path'].'cp/scheduling/test/dump';
    exec($cmd, $r1, $r2);

    $message = implode('', file($config->values['site']['path']."cp/scheduling/test/dump"));

    if(strpos($message, '[...]'))
    {
        $parts 	= explode('[...]', $message);
        $report = $parts[(count($parts) - 1)];
    }
    elseif(strpos($message, 'Start SpamAssassin results'))
    {
        $parts 	= explode('Start SpamAssassin results', $message);
        $report = $parts[(count($parts)-1)];
    }
    else 
        $report = $message;

    $tpl->report = $report;
}

$tpl->template  = 'cp/extra/spamassassin.php';
$tpl->display('cp/layout-pop.php');
?>
