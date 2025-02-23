<?PHP
set_time_limit(0);
require_once(dirname(__FILE__) .'/../../core/include.php');

do_export();

function do_export()
{
    global $config, $db, $Lists;
    // Prep vars
    $export_dir = $config->values['site']['upload_patch']."export/";
    // Do we have any runners?
    $sql = "SELECT count(*) as count FROM export WHERE state = 1";
    $info = $db->GetRow($sql);

    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    // We have runners exit
    if($info['count'] > 0)
    {
        return false;
    }

    // No runners do we have any waiting?
    $sql = "SELECT * FROM export WHERE state = '0' ORDER BY ts ASC LIMIT 0,1;";
    $info = $db->GetRow($sql);

    function checkAbort($export_id)
    {
        global $db;

        $sql = "SELECT state FROM export WHERE export_id = '".$export_id."'";
        $rw = $db->GetRow($sql);

        if($rw['state'] != '1')
        {
            print "Export has been aborted\n";
            return false;
        }

    }

    if(isset($info['export_id']))
    {
        // The file we shall write to.
        $file = $export_dir . $info['name'] . '.csv';
        // Let others know we are exporting.
        $sql = "UPDATE export SET state = '1', start=NOW() WHERE export_id = '".$info['export_id']."'";
        $r = $db->Execute($sql);
        // Update failed exit out
        if($r === false)
        {
            logMessage('export',"Could not update table to progress Id: ".$info['import_id']." (".$db->ErrorMsg().").");
            return false;
        }

        if(!$write  = fopen($file, "w+"))
        {
            logMessage('export',"Could not open file for export ($file)");
            $sql  = "UPDATE export SET";
            $sql .= " state = '3' ";
            $sql .= "WHERE export_id = '".$info['export_id']."'";
            $db->Execute($sql);
            return false;
        }
        // Prep counters
        $i = 0;
        $j = 1;
        $k = 1;

        // Not a category
        if($info['type'] != 3)
        {
            // we want subscribes
            if($info['subscribed'] == 'y')
            {
                $cols = $Lists->getCols($info['list-cat']);
                $hide = array('id', 'mask', 'import_id');

                foreach($cols AS $col)
                {
                    if(in_array($col['Field'], $hide))
                    {
                        continue;
                    }
                    $c_options[] = $col['Field'];
                }
                $sql_fields = implode(",", $c_options);

                if($info['where'] != '')
                {
                    $sql_where = " WHERE " . $info['where'];
                }

                $sql = "SELECT $sql_fields FROM `{list}`.[table_name]$sql_where";

                foreach($Lists->email_tables AS $table)
                {
                    $tbl = $Lists->email_table_prefix . $table;

                    print $sql2 = str_replace("[table_name]", $tbl, $sql);
                    print "\n";
                    $rs = $Lists->query_list($info['list-cat'], $sql2);

                    while($rw = row($rs))
                    {
                        if($i == 5000)
                        {
                            print "Subs export: ".$i*$j."\n";
                            $sql_u = "UPDATE export SET progress = '".$i*$j."', end = NOW() WHERE export_id = '".$info['export_id']."';";
                            $db->Execute($sql_u);
                            $i = 0;
                            $j ++;
                            // Check for abort
                            checkAbort($info['export_id']);
                        }

                        $rw['aaa_email'] = $rw['local'].'@'.$rw['domain'];
                        unset($rw['local']);
                        unset($rw['domain']);
                        ksort($rw);
                        $line = implode(",",$rw);
                        fwrite($write,$line."\r\n");
                        $i ++;
                        $k ++;
                        $the_order = $rw;
                    }
                }
            }

            // we want something from unsubs?
            if($info['slog_where'] != '')
            {
                print $where = $info['slog_where']."\n";
                //if($info['where'] != '') $where .= " " . $info['where'];
                print $sql = "SELECT * FROM `{list}`.slog $where";
                $rs = $Lists->query_list($info['list-cat'], $sql);

                if(is_array($the_order))
                {
                    $the_order = array_keys($the_order);
                    $order_count = count($the_order) - 1;
                }

                while($rw = $rs->FetchRow())
                {

                    if($i == 5000)
                    {
                        print "Subs export: ".$i*$j."\n";
                        $sql_u = "UPDATE export SET progress = '".$i*$j."', end = NOW() WHERE export_id = '".$info['export_id']."';";
                        $db->Execute($sql_u);
                        $i = 0;
                        $j ++;
                        // Check for abort
                        checkAbort($info['export_id']);
                    }

                    $rw_slog = $db->GetRow("SELECT * FROM slog_data WHERE id = '{$rw['id']}';");
                    $data = unserialize($rw_slog['data']);

                    $rw_slog['aaa_email'] = $rw['local'].'@'.$rw['domain'];
                    unset($rw_slog['local']);
                    unset($rw_slog['domain']);
                    $line = '';

                    if(is_array($the_order))
                    {
                        $l = 0;
                        foreach($the_order AS  $v)
                        {
                            $line .= $rw_slog[$v];
                            if($l < $order_count) $line .= ",";
                            $l ++;
                        }
                    }
                    else 
                    {
                        ksort($rw_slog);
                        $line = implode(",",$rw_slog);
                    }

                    fwrite($write,$line."\r\n");
                    $i ++;
                    $k ++;
                }
            }
        }
        // Category
        if($info['type'] == 3)
        {
            $sql = "SELECT email FROM email_to_category WHERE category_id = '".$info['list-cat']."'";
            $rs = $db->Execute($sql);
            while($rw = $rs->FetchRow())
            {
                if($i == 5000)
                {
                    print "Category Export: ". $i*$j."\n";
                    $sql = "UPDATE export SET progress = '".$i*$j."', end = NOW() WHERE export_id = '".$info['export_id']."';";
                    $db->Execute($sql);
                    $i = 0;
                    $j ++;
                    // Check for abort
                    checkAbort($info['export_id']);
                }
                $line = $rw['email'];
                ksort($rw);
                fwrite($write,$line."\r\n");
                $i ++;
                $k ++;
            }
        }

        fclose($write);
        $k = $k -1;
        $sql = "UPDATE export SET state = '2', progress = '".$k."', end = NOW() WHERE export_id = '".$info['export_id']."'";
        $r = $db->Execute($sql);
        if($r === false)
        {
            logMessage('export',"Could not update table to complete Id: ".$info['import_id']." (".$db->ErrorMsg().").");
            return false;
        }


        exec("cd $export_dir; zip ".$info['name']." ".$info['name'].".csv>/dev/null 2>&1");
        print mysql_error();

    }
}
?>
