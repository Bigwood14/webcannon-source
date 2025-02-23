<?php
set_time_limit(0);
require_once(dirname(__FILE__) .'/../../core/include.php');
require_once("Subscribe.php");
// Prep vars needed by more then 1 type
$import_dir = $config->values['site']['upload_patch'] . 'import/';
$regexp     = "(([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4}))";
$regexp2    = "(([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4}))";
// Check there is no import already running
$sql = "SELECT count(*) as count FROM imports WHERE state = 1";
$info = $db->GetRow($sql);

if($info['count'] > 0)
{
    // An Import is running exit out.
    return;
}

// None running? Lets grab one.
$sql = "SELECT * FROM imports WHERE state = '0' ORDER BY ts ASC LIMIT 0,1;";
$info = $db->GetRow($sql);
// Yup there is one
if(isset($info['import_id']))
{
    // Prep Vars
    $c['invalid']   = 0;
    $c['dups']      = 0;
    $c['dup']      	= 0;
    $c['add']       = 0;
    $c['unsub']     = 0;
    $c['unsub_g']   = 0;
    $c['unsub_g_d'] = 0;
    $c['unsub_g_w'] = 0;
    $c['filtered']  = 0;
    $c['total']     = 0;

    $file           = $import_dir . $info['file'];

    // We are going to run it update state to in progress
    $sql = "UPDATE imports SET state = '1', start= NOW() WHERE import_id = '".$info['import_id']."'";
    $r   = $db->Execute($sql);
    // Update failed exit out
    if($r === false)
    {
        logMessage('import',"Could not update table to progress Id: ".$info['import_id']." (".$db->ErrorMsg().").");
        exit;
    }
    // Open up the file
    if(!$read  = fopen($file, "r"))
    {
        logMessage('import',"Could not open file for import ($file)",1101);
        $sql  = "UPDATE imports SET";
        $sql .= " state = '3';";
        $sql .= "WHERE import_id = '".$info['import_id']."'";
        $db->Execute($sql);
        exit;
    }
    // Prep counter vars
    $i = 0;
    $j = 0;
    $k = 0;
    // Prep format
    $format = unserialize($info['format']);
    $subscribe        = new Subscribe();
    $subscribe->list  = $info['list'];
    $subscribe->how   = 0;
    $subscribe->overwrite = $info['overwrite'];
    
    if($info['dedupe'] != '')
    {
        $subscribe->dedupe = unserialize($info['dedupe']);
    }

    $unsubscribe        = new Unsubscribe();
    $unsubscribe->list  = $info['list'];
    $unsubscribe->how   = 1;
    
    $unsubscribe_domain        = new Unsubscribe_Domain();
    $unsubscribe_domain->list  = $info['list'];
    $unsubscribe_domain->how   = 1;
    
    $unsubscribe_word        = new Unsubscribe_Word();
    $unsubscribe_word->how   = 2;
    
    // Loop file line by line
    while (!feof ($read))
    {
        // Do a log update every 5k records.
        if($k == 500)
        {
            $c['total'] = $j;

            $sql  = "UPDATE imports
                     SET 
                       added = '".$c['add']."',
                       dups = '".$c['dup']."',
                       invalid = '".$c['invalid']."',
                       unsub = '".$c['unsub']."',
                       unsub_g = '".$c['unsub_g']."',
                       unsub_d = '".$c['unsub_g_d']."',
                       total = '".$c['total']."',
                       filtered = '".$c['unsub_g_w']."',
                       end = NOW()
                     WHERE 
                       import_id = '".$info['import_id']."'";

            $db->Execute($sql);
            print mysql_error();
            print "Imported: ".$j."\n";
            $sql = "SELECT * FROM imports WHERE import_id = '".$info['import_id']."'";
            $rw = $db->GetRow($sql);

            if($rw['state'] != '1')
            {
                print "Import has been aborted\n";
                exit();
            }

            $k = 0;
        }

        // Get line contents
        $buffer  = fgets($read, 4096);


        // Type is wizard so account for the format option.
        if($info['type_id'] == 1)
        {
            // Split parts + clean bad stuff from line.
            if($info['delim'] == '\t')
            {
                $parts   = explode("\t", clean($buffer,"\t"));
            }
            else
            {
                $parts   = explode($info['delim'], clean($buffer, $info['delim']));
            }

            // Find the email part
            $email = $parts[array_search('email', $format)];

            $subscribe->setEmail(trim($email));
            
            $subscribe->format      = $format;
            $subscribe->parts       = $parts;
            $subscribe->import_id   = $info['import_id'];

            // Try to add it and see what spits back
            $ret = $subscribe->doCustomSub();
            //print "Email was: [$email] Ret was: [$ret]\n";
            switch($ret)
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
        }
        // Type is suppresion email
        elseif($info['type_id'] == 2)
        {
			$buffer = strtolower($buffer);

			if ($info['md5'] > 0)
			{
				$email = trim($buffer);

				$email = mysql_escape_string($email);
				$sql = "INSERT INTO `email_to_sup` (email,sup_list_id,domain) VALUES ('$email','".$info['type']."','0');";

				$r = $db->Execute($sql);
		
				if($r === false)
					$c['dup'] ++;
					
				$c['add'] ++;
			}
			else
			{
            	preg_match($regexp, $buffer, $matches);
            
				if($matches[1] != "")
            	{
            	    $email = mysql_escape_string($matches[0]);
            	    $sql = "INSERT INTO `email_to_sup` (email,sup_list_id,domain) VALUES ('$email','".$info['type']."','0');";

					$r = $db->Execute($sql);
		
					if($r === false)
						$c['dup'] ++;
					
					$c['add'] ++;
				}
				else
					 $c['invalid'] ++;
			}
        }
        // Type is suppresion domain
        elseif($info['type_id'] == 5)
        {
			$buffer = strtolower($buffer);
            preg_match($regexp2, $buffer, $matches);
            if($matches[1] != "")
            {
                $domain = mysql_escape_string($matches[0]);
                $sql = "INSERT INTO `email_to_sup` (email,sup_list_id,domain) VALUES ('$domain','".$info['type']."','1');";
                $r = $db->Execute($sql);
                if($r === false)
                {
                    $c['dup'] ++;
                }
                
                $c['add'] ++;
            }
            else
            {
                $c['invalid'] ++;
            }
        }
        // Type is GDNE Emails
        elseif($info['type_id'] == 3)
        {
			$buffer = strtolower($buffer);
            $unsubscribe->gdne = true;

            preg_match($regexp, $buffer, $matches);
            if($matches[1] != "")
            {
                $email = mysql_escape_string($matches[0]);
                $unsubscribe->setEmail($email);

                switch($unsubscribe->doUnsub())
                {
                    case 1:
                    //$c['unsub_g'] ++;
                    break;
                    case 2:
                    $c['unsub_g'] ++;
                    break;
                    case 3:
                    //$c['removed_sgdne'] ++;
                    break;
                    case -1:
                    $c['invalid'] ++;
                    break;
                    case -2:
                    $c['dup'] ++;
                    break;
                }

                $unsubscribe->reset();
            }
        }
        // Type is GDNE Domains
        elseif($info['type_id'] == 6)
        {
			$buffer = strtolower($buffer);
            // We know its gdne
            $unsubscribe_domain->gdne = true;
            // Use regexp2 cuz its domain
            preg_match($regexp2, $buffer, $matches);
            if($matches[1] != "")
            {
                $domain = mysql_escape_string($matches[0]);
                $unsubscribe_domain->setDomain($domain);

                switch($unsubscribe_domain->doUnsub())
                {
                    case 1:
                    //$c['unsub_g'] ++;
                    break;
                    case 2:
                    $c['unsub_g_d'] ++;
                    break;
                    case 3:
                    //$c['removed_sgdne'] ++;
                    break;
                    case -1:
                    $c['invalid'] ++;
                    break;
                    case -2:
                    $c['dup'] ++;
                    break;
                }

                $unsubscribe_domain->reset();
            }
			else
				print 'no'.$buffer;
        }
        // Type is GDNE Words
        elseif($info['type_id'] == 8)
        {
            // We know its gdne
            $unsubscribe_word->gdne  = true;
            $unsubscribe_word->setWord(trim($buffer));

            switch($unsubscribe_word->doUnsub())
            {
                case 1:
                $c['unsub_g'] ++;
                break;
                case 2:
                //$c['unsub_g'] ++;
                break;
                case 3:
                //$c['removed_sgdne'] ++;
                break;
                case -1:
                $c['invalid'] ++;
                break;
            }

            $unsubscribe_word->reset();
        }
        // Type is Wash List
        elseif($info['type_id'] == 4)
        {
			$buffer = strtolower($buffer);
            preg_match($regexp, $buffer, $matches);
            if($matches[1] != "")
            {
                $email = $matches[0];
                $unsubscribe->setEmail($email);

                switch($unsubscribe->doUnsub())
                {
                    case 1:
                    $c['unsub'] ++;
                    break;
                    case 2:
                    //$c['unsub_g'] ++;
                    break;
                    case 3:
                    //$c['removed_sgdne'] ++;
                    break;
                    case -1:
                    $c['invalid'] ++;
                    break;
                }

                $unsubscribe->reset();
            }
        }
        // Type is Wash Domains
        elseif($info['type_id'] == 7)
        {
			$buffer = strtolower($buffer);
            // Use regexp2 cuz its domain
            preg_match($regexp2, $buffer, $matches);
            if($matches[1] != "")
            {
                $domain = mysql_escape_string($matches[0]);
                $unsubscribe_domain->setDomain($domain);

                switch($unsubscribe_domain->doUnsub())
                {
                    case 1:
                    $c['unsub'] ++;
                    break;
                    case 2:
                    $c['unsub_g'] ++;
                    break;
                    case 3:
                    //$c['removed_sgdne'] ++;
                    break;
                    case -1:
                    $c['invalid'] ++;
                    break;
                }

                $unsubscribe_domain->reset();
            }
        }

        $i++;
        $j++;
        $k++;
    }

    $c['total'] = $j;
    $sql  = "UPDATE imports
             SET 
               state = '2',
               added = '".$c['add']."',
               dups = '".$c['dup']."',
               invalid = '".$c['invalid']."',
               unsub = '".$c['unsub']."',
               unsub_g = '".$c['unsub_g']."',
               unsub_d = '".$c['unsub_g_d']."',
               total = '".$c['total']."',
               filtered = '".$c['unsub_g_w']."',
               end = NOW()
             WHERE 
               import_id = '".$info['import_id']."'";
    $r = $db->Execute($sql);
    if($r === false)
    {
        logMessage('import',"Could not update import (end): ".$info['import_id']." (".$db->ErrorMsg().").");
        exit;
    }

}
?>
