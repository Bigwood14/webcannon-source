<?php
/**
 * This page is to be run inside the minute-control.php cronjob.
 *
 * It open up the schedule table and looks for the next set of unparsed
 * results and starts parsing them removing failures from the db, and
 * finding 'real' soft bounces putting them into a log table which is also
 * processed at the end.
 *
 * POST : None
 * GET  : None
 *
 * @category   Crons-Minute
 * @package    Celibero
 * @author     Celibero Team
 * @copyright  2005 Celibero
 * @version    CVS: $Id: parse_results.php,v 1.9 2005/09/14 14:07:03 tom Exp $
 */

// Dont want to timeout!
set_time_limit(0);
// Main files prob already there
require_once(dirname(__FILE__) .'/../../core/include.php');
require_once("Subscribe.php");

parse_results();

function parse_results()
{
    global $db, $config;

    $config_db = getDBConfig('',1);

    // Check a process results not allready running dont want to flood sys
    if(!locked_filewrite('/tmp/cellocker', "data", 0.50, 86400))
    {
        print "Parse: locked out \n";
        return false;
    }

    $sql = "SELECT * FROM schedule WHERE process_results = '0' AND (state = '7' OR state = '9') LIMIT 0,1";
    $schedule = $db->GetRow($sql);

    if($schedule == false)
    {
        // Nothing to parse
        rmdir('/tmp/cellocker.lock');
        return false;
    }

    $sql = "UPDATE schedule SET process_results = '1' WHERE id = '{$schedule['id']}'";
    $db->Execute($sql);

	$aol_domains 	= array('aol.com', 'aim.com', 'netscape.net', 'wmconnect.com', 'wild4music.com', 'luckymail.com');

	$file 			= $config->values['site']['path'].'no-web/celiberod/results/'.$schedule['id'].'.success';
	$read 			= fopen($file, 'r');

	if (!$read)
		logMessage('parse_results', "Could not open file for parsing ($file)", 0);
	else
	{
		$stats = array();

		while (!feof($read))
        {
            $buffer = fgets($read, 4096);
            $parts 	= explode(':', $buffer, 9);

			if (empty($parts[1]))
				continue;

			$email 	= trim($parts[1]);
			$ip 	= trim($parts[2]);
			$stamp 	= trim($parts[7]);

			$parts 	= explode('@', $email);
			// its aol log it
			if (in_array(@$parts[1], $aol_domains))
			{
				$date 	= date('Y-m-d G:00:00', $stamp);
				@$stats[$ip][$date]++;	
			}
        }

		foreach ($stats as $ip => $dc)
		{
			foreach ($dc as $date => $count)
			{
				$sql = "SELECT * FROM `del_success_stats` WHERE `ip` = INET_ATON('$ip') AND `date` = '$date' AND `type` = 1";
				$row = row(query($sql));

				if (!empty($row))
				{
					$sql = "UPDATE `del_success_stats` SET `count` = `count` + $count WHERE `ip` = INET_ATON('$ip') AND `date` = '$date' AND `type` = 1";
					query($sql);
				}
				else
				{
					$sql = "INSERT INTO `del_success_stats` (`ip`, `date`, `count`, `type`) VALUES (INET_ATON('$ip'), '$date', $count, 1);";
					query($sql);
				}
			}
		}

		fclose($read);
	
	}

    // First up lets do the failure file - always fun
    $unsubscribe        = new Unsubscribe();
    $unsubscribe->gnde  = true;
    $unsubscribe->how   = 6;

    $file 			= $config->values['site']['path'] . "no-web/celiberod/results/".$schedule['id'].".failure";
	$failure_send 	= $config->values['site']['path'] . "no-web/celiberod/results/".$schedule['id'].".failure.send";

	
	$non_bounce 	= explode("\n", str_replace("\r", '', $config_db['BOUNCE_IGNORE']));
	$non_bounce[] 	= 'aol.com/errors';
    
	$read = fopen($file, "r");
	$send = fopen($failure_send, 'w+');

    if(!$read)
    {
        logMessage('parse_results', "Could not open file for parsing ($file)", 0);
    }
    else
    {
        $rems = 0;
        while (!feof($read))
        {
            $buffer = fgets($read, 4096);

            $parts = explode(":", $buffer, 9);
            $email = trim($parts[1]);
			$msg   = trim($parts[8]);

            // Here we need to decide if we have should class as a bounce
            // best way for this is to test against non-bounce rules.
            foreach($non_bounce AS $non)
            {
                $c = 0;
                $pos = strpos($msg, $non);

                if($pos !== false)
                {
                    $c = 1;
                    break;
                }
            }

            if($c == 1)
            {
                print "Parse failure(table): [$email] [$msg] ignored\n";
                continue;
            }

			fwrite($send, $buffer);
 
            print "Parse failure: [$email] [$msg]\n";

            $unsubscribe->setEmail($email);

            print " [".$unsubscribe->doUnsub()."]\n";
            $unsubscribe->reset();
            $rems ++;
        }

		fclose($read);
		fclose($send);
		// send it?
		if ($rems > 1)
		{
			exec('/usr/bin/gzip -f '.$failure_send);
			$failure_send .= '.gz';
			if (function_exists('ftp_connect'))
			{
				if ($ftp = ftp_connect('prime.webcannon.com'))
				{
					if (ftp_login($ftp, 'bouncedne', 'kxc92ps03'))
					{
						$server = getDefaultDomain();
						ftp_put($ftp, $server.'.'.basename($failure_send), $failure_send, FTP_BINARY);
						ftp_close($ftp);
					}
					else
						print "no login\n";
				}
				else
					print "no connect\n";
			}
			else
				print "nofunction\n";
		}
    }

    // Defferal file - never fun

    if($config_db['BOUNCE_PRUNE'] > 0)
    {
        $bcount = 1;

        if($schedule['retries'] > 0)
        {
            $file   = $config->values['site']['path'] . "no-web/celiberod/results/";
            $n      = $schedule['retries'] - 1;
            while($n > 0)
            {
                $fname .= $n.".";
                //unlink($file . $schedule['id'].".".$fname."deferral");
                $n --;
            }
            $file .= $schedule['id'].".".$fname."0.deferral";

            $bcount = $schedule['retries'];
        }
        else
        {
            $file = $config->values['site']['path'] . "no-web/celiberod/results/".$schedule['id'].".deferral";
        }
        logMessage('parse_results', "Parsing file [$file]", 0);
        $read = fopen($file, "r");
        if(!$read)
        {
            logMessage('parse_results', "Could not open file for parsing ($file)", 0);
        }
        else
        {
            $rems = 0;

            $non_bounce = explode("\n", str_replace("\r", '', $config_db['BOUNCE_IGNORE']));

            while (!feof($read))
            {
                $buffer = fgets($read, 4096);

                $parts = explode(":", $buffer, 9);

                $email = trim($parts[1]);
                $msg   = trim($parts[8]);

                // Here we need to decide if we have should class as a bounce
                // best way for this is to test against non-bounce rules.
                foreach($non_bounce AS $non)
                {
                    $c = 0;
                    $pos = strpos($msg, $non);

                    if($pos !== false)
                    {
                        $c = 1;
                        break;
                    }
                }

                if($c == 1)
                {
                    print "Parse defferal(table): [$email] [$msg] ignored\n";
                    continue;
                }
                print "Parse defferal: [$email] [$msg] done\n";
                // Still going then its wasnt in our words
                $sql = "SELECT COUNT(*) AS `count` FROM `bounce` WHERE email = '$email'";
                $c  = $db->GetRow($sql);
                if($c['count'] > 0)
                {
                    $sql = "UPDATE bounce SET `count` = `count` + $bcount WHERE email = '$email'";
                    $db->Execute($sql);
                }
                else
                {
                    if($email == '')
                    {
                        continue;
                    }
                    $sql = "INSERT INTO bounce (`email`, `count`) VALUES ('$email', '$bcount')";
                    $db->Execute($sql);
                }
            }
            fclose($read);
        }

        // Process the bounce table
        $sql = "SELECT * FROM bounce WHERE `count` >= '{$config_db['BOUNCE_PRUNE']}';";
        $rs = $db->Execute($sql);

        $unsubscribe        = new Unsubscribe();
        $unsubscribe->list  = '';
        $unsubscribe->how   = 5;

        while($rw = $rs->FetchRow())
        {
            $email = $rw['email'];

            print "Parse defferal: " . $email;

            $unsubscribe->setEmail($email);

            print " [".$unsubscribe->doUnsub()."]\n";
            $unsubscribe->reset();
        }

        $sql = "DELETE FROM bounce WHERE `count` >= '{$config_db['BOUNCE_PRUNE']}';";
        $db->Execute($sql);
    }

    // Results files processed remove them.
    $file = $config->values['site']['path'] . "no-web/celiberod/results/".$schedule['id'].".";
    //unlink($file . 'deferral');
    //unlink($file . 'failure');
    //unlink($file . 'success');

    $file   = $config->values['site']['path'] . "no-web/celiberod/results/";
    $n      = $schedule['retries'] - 1;
    $fname = '';
    while($n >= 0)
    {
        $fname .= $n.".";
        logMessage('parse_results', "Deleting results file [".$schedule['id'].".".$fname."deferral]", 0);
        logMessage('parse_results', "Deleting results file [".$schedule['id'].".".$fname."failure]", 0);
        logMessage('parse_results', "Deleting results file [".$schedule['id'].".".$fname."success]", 0);
        unlink($file . $schedule['id'].".".$fname."deferral");
        unlink($file . $schedule['id'].".".$fname."failure");
        unlink($file . $schedule['id'].".".$fname."success");
        $n --;
    }

    rmdir('/tmp/cellocker.lock');

    $sql = "UPDATE schedule SET process_results = '2' WHERE id = '{$schedule['id']}'";
    $db->Execute($sql);

    return true;
}

function microtime_float()
{
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
}

function locked_filewrite($filename, $data, $timeLimit = 0.50, $staleAge = 86400)
{
    ignore_user_abort(1);
    $lockDir = $filename . '.lock';
    time() - @filemtime($lockDir);
    if (is_dir($lockDir)) {
        if ((time() - @filemtime($lockDir)) > $staleAge) {
            rmdir($lockDir);
        }
    }

    $locked = @mkdir($lockDir);

    if ($locked === false) {
        $timeStart = microtime_float();
        do {
            if ((microtime_float() - $timeStart) > $timeLimit) break;
            $locked = @mkdir($lockDir);
        } while ($locked === false);
    }

    $success = false;

    if ($locked === true) {
        $fp = @fopen($filename, 'a');
        if (@fwrite($fp, $data)) $success = true;
        @fclose($fp);
        //rmdir($lockDir);
    }

    ignore_user_abort(0);
    return $success;
}
?>
