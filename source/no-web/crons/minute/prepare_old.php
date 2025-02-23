<?PHP
// This IS A MESS!
set_time_limit(0);
require_once(dirname(__FILE__) .'/../../core/include.php');
require_once('draft.cls.php');
prepare();

function prepare()
{
	global $db,$config,$Lists;

	$draft_suppression 	= new draft_suppression();

	$sql = "SELECT COUNT(*) AS count FROM schedule WHERE state > '0' AND state < '3'";
	$info = $db->GetRow($sql);

	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	if($info['count'] > 0)
	{
		//Prepare already running let it finish
		return;
	}

	$sql = "SELECT COUNT(*) AS count FROM schedule WHERE state > '2' AND state < '7'";
	$info = $db->GetRow($sql);

	if($info['count'] > 0)
	{
		// Already one in the pipe no prepare until its done otherwise
		// miss unsubscribes.
		echo "Prepare exit waiting mailing finish\n";
		return;
	}


	$sql = "SELECT * FROM schedule WHERE state = '0' AND scheduled_time < NOW() ORDER BY scheduled_time ASC LIMIT 0,1;";
	$info = $db->GetRow($sql);

	// Mail merge deafults
	$sql            = "SELECT * FROM mm_defaults;";
	$defaults       = $db->GetRow($sql);
	$mm_add     	= explode(',', @$info['sql_extra']);
	$seed_line_add 	= '';
	foreach($mm_add AS $add)
		$seed_line_add .= '|' . @$defaults[$add];

	if(isset($info['id']))
	{
		$draft 	= getDraft($info['msg_id']);

		// get suppression lists
		$data 						= $draft_suppression->get($info['msg_id']);
		$draft['suppression_sql'] 	= '';

		foreach ($data as $row)
			$draft['suppression_sql'] .= " sup_list_id = '{$row['suppression_list_id']}' OR";

		$draft['suppression_sql']  = rtrim($draft['suppression_sql'], ' OR');
var_dump($draft['suppression_sql']);
		if($draft['seeds'] != '')
		{
			$seed_number = $info['total_emails'] / 2;
			$seeds = explode("\n",$draft['seeds']);
			foreach($seeds AS $seed)
			{
				if(strpos($seed, ':') !== false)
				{
					$parts = explode(':', $seed);
					$num_seeds[] = array('num' => $parts[1], 'email' => $parts[0]);
				}
				else
				{
					$real_seeds[] = $seed;
				}
			}
			$seeds = $real_seeds;
		}
		$file  = $config->values['site']['path'] . 'no-web/celiberod/list/'.$info['id'];

		if(!$fh  = fopen($file, "w+"))
		{
			logMessage('prepare',"Could not open list file ($list_file)");
			$sql  = "UPDATE schedule SET";
			$sql .= " state = '9';";
			$sql .= "WHERE schedule_id = '".$info['schedule_id']."'";
			$db->Execute($sql);
			return;
		}

		// We are going to run it update state to in progress
		$sql = "UPDATE schedule SET state = '1' WHERE id = '".$info['id']."'";
		$r   = $db->Execute($sql);
		// Update failed exit out
		if($r === false)
		{
			logMessage('schedule',"Could not update table to progress Id: ".$info['id']." (".$db->ErrorMsg().").");
			return;
		}

		$db->Execute("INSERT INTO schedule_log (schedule_id,time,message) VALUES ('".$info['id']."',NOW(),'Started Prepare');");

		$i = 1;

		// Grab Domains
		$sql = "SELECT * FROM msg_to_domain WHERE msg_id = '".$info['msg_id']."';";
		$domains = $db->GetAll($sql);
		$num_domains = count($domains) - 1;
		// Grab Subject Lines
		$sql = "SELECT * FROM msg_to_subject WHERE msg_id = '".$info['msg_id']."';";
		$subjects = $db->GetAll($sql);
		foreach($subjects AS $k => $subject)
		{
			if(strpos($subject['subject'], '{') !== false)
			{
				$subjects[$k]['_mm'] = true;
			}
		}
		
		// Lists
		$sql = "SELECT * FROM msg_to_list WHERE msg_id = '".$info['msg_id']."'";
		$rs = $db->Execute($sql);
		$i = 0;


		$sups = 0;
		// Loop over selected mailing lists
		while ($lt = $rs->FetchRow())
		{
			// Get the list from db
			$sql = "SELECT * FROM user WHERE id = '".$lt['list_id']."'";
			$lst = $db->GetRow($sql);
			// It is a valid list
			if(isset($lst['username']))
			{
				// Here we need check that this list has all the merge fields if not remove bad field(s)
				$cols = $Lists->getCols($lst['username']);
				// Pisant why return cols like that? gota re org now
				foreach($cols AS $col)
				{
					$cols_2[] = $col['Field'];
				}
				$cols = $cols_2;
				$sql_extra = '';
				$mm_extras = array();
				print_r($mm_add);
				foreach($mm_add AS $mm)
				{
					if(!in_array($mm, $cols) || $mm == 'local' || $mm == 'domain' || $mm == 'id')
					{
						continue;
					}
					$mm_extras[] = $mm;
					$sql_extra  .= $mm . ',';
				}

				if($sql_extra != '')
				{
					$sql_extra = rtrim(',' . $sql_extra, ',');
				}


				// Loop over the tables in list
				print $sql = "SELECT local, domain, id{$sql_extra} FROM [table_name]";
				foreach($Lists->email_tables AS $table)
				{

					$tbl = $Lists->email_table_prefix.$table;
					$sql2 = str_replace('[table_name]', $tbl, $sql);
var_dump($lst['username'], $sql2);
					$rs2 = $Lists->queryList($lst['username'], $sql2);
					if(!$rs2)
					{
						$msg  = "Possible table broke Table: ".mysql_escape_string($tbl);
						$msg .= "Database: ".mysql_escape_string($lst['username']);
						makeError($msg);
					}
					// Loop over the email in the table checking for suppression
					while($rw = $rs2->FetchRow())
					{
						$k ++;
						if($k == 500)
						{
							print "Prepped: ".$i."\n";
							$sql_s = "SELECT * FROM schedule WHERE id = '".$info['id']."'";
							$rw = $db->GetRow($sql_s);
							if($rw['state'] != '1')
							{
								print "Prepare has been aborted\n";
								exit();
							}

							$k = 0;
						}

						$rw['local'] = $rw['local'].'@'.$rw['domain'];
						// Suppression List - email
						if(!empty($draft['suppression_sql']))
						{
							$sql_sup = "SELECT COUNT(email) AS `count` FROM email_to_sup WHERE email = '".$rw['local']."' AND {$draft['suppression_sql']};";
							$c = $db->GetRow($sql_sup);
							if($c['count'] > 0)
							{
								$sups ++;
								echo "Supped: ".$rw['local']."\n";
								continue;
							}
						}
						// send to first xx
						if($info['send_to_first'] > 0 && ($i >= $info['send_to_first']))
						{
							break;
						}
						// skip first xx
						if($info['skip_first'] > 0 && ($i <= $info['skip_first']))
						{
							continue;
						}
						// max of
						if($info['max_of'] > 0 && ($i >= $info['max_of']))
						{
							break;
						}

						// Make ID
						$id = $Lists->whatID($rw['local']);
						$id = $id . $rw['id'];

						// Make Subject - Do mail merge if required
						$subject_line = $subjects[rand(0,$info['subject_lines']-1)]['subject'];
						if($subject_line['_mm'] == true)
						{
							foreach($mm_add AS $extra)
							{
								if($rw[$extra] == '')
								{
									$rw[$extra] = $defaults[$extra];
								}
								$subject_line = str_replace("{".$extra."}", $rw[$extra], $subject_line);
							}
						}

						unset($rw['domain']);

						if(is_array($num_seeds))
						{
							foreach($num_seeds AS $num_seed)
							{
								if($i % $num_seed['num'] == 0)
								{
									$line = trim($num_seed['email']);

									$line .= "|".$id."|".$lst['id']."||" .$subject_line;
									$line .= $seed_line_add;
									fwrite($fh, $line."\n");
								}
							}
						}

						if($i == $seed_number)
						{
							if(is_array($seeds))
							{
								foreach($seeds AS $seed)
								{
									$line = trim($seed) ;

									$line .= "|seed|seedlist||" .$subject_line;
									$line .= $seed_line_add;
									fwrite($fh, $line."\n");
								}
							}
							$seed_done = 1;
						}

						// Write out to file
						$line  = $rw['local'] . "|" . $id . "|" . $lst['id'] . "||" .$subject_line;

						foreach($mm_add AS $extra)
						{
							if($rw[$extra] == '')
							{
								$rw[$extra] = $defaults[$extra];
							}

							$line .= "|".$rw[$extra];
						}

						fwrite($fh, $line."\n");
						$i ++;
					}
				}
			}
		}

		if(is_array($seeds) && $seed_done != 1)
		{
			foreach($seeds AS $seed)
			{
				$line = trim($seed) ;

				$line .= "|seed|seedlist||" .$subject_line;
				$line .= $seed_line_add;
				fwrite($fh, $line."\n");
			}
		}

		fclose($fh);

		// Update state to pending send (skipping transfer step for now)
		$sql = "UPDATE schedule SET state = '3', total_emails = '$i' WHERE id = '".$info['id']."' AND `state` = '1'";
		$r   = $db->Execute($sql);
		// Update failed exit out
		if($r === false)
		{
			logMessage('schedule',"Could not update table to trasnfering Id: ".$info['id']." (".$db->ErrorMsg().") ($sql).");
			return;
		}

		$db->Execute("INSERT INTO schedule_log (schedule_id,time,message) VALUES ('".$info['id']."',NOW(),'Ended Prepare (Suppressed $sups emails)');");


	}
}
?>
