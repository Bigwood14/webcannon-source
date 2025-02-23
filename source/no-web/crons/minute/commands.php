<?php

/**
 * This page is to be run inside the minute-control.php cronjob.
 *
 * It gathers a list of shell commands from the main (celibero) database , table commands
 * then executes them. It will only gather commands that have not yet been run (state=0)
 * and commands that are ready to be run (date<=NOW).
 * To run this on its own #!/usr/bin/php -q should be at the top this must not be present
 * in a release.
 *
 * POST : None
 * GET  : None
 *
 * @category   Crons-Minute
 * @package    Celibero
 * @author     Celibero Team
 * @copyright  2005 Celibero
 * @version    CVS: $Id: commands.php,v 1.9 2005/09/14 15:57:44 tom Exp $
 */

// Dont want to timeout!
set_time_limit(0);
// Main files prob already there
require_once(dirname(__FILE__) .'/../../core/include.php');


$sql = "SELECT * FROM commands WHERE (`state` = '0') AND (`date` <= NOW());";
$rs = $db->Execute($sql);

while($rw = $rs->FetchRow())
{
    if($rw['type'] == 'shell')
    {
        $sql = "UPDATE commands SET state = '1' where command_id = '".$rw['command_id']."';";
        $db->Execute($sql);
        // If output in db is set that means we dont want to record the output (might be big array).
        if($rw['output'] != '')
        {
            $output = '';
            $return = '';
            exec($rw['command']);
        }
        else
        {
            exec($rw['command'], $output, $return);
        }

        // Prep sql vars
        $output = mysql_escape_string(serialize($output));
        $return = mysql_escape_string($return);
        // Update to done.
        $sql = "UPDATE commands SET state = '2', `output` = '$output', `return` = '$return' WHERE command_id = '".$rw['command_id']."';";
        $db->Execute($sql);
    }
    elseif($rw['type'] == 'mysql') 
    {
        $command = explode(':', $rw['command']);
        $database = $command[0];
        $count = count($command);
        $sql = "UPDATE commands SET state = '1' where command_id = '".$rw['command_id']."';";
        $db->Execute($sql);
        for($i=1;$i < $count;$i ++)
        {
           print $command[$i];
           $db->Execute($command[$i]); 
        }
        $sql = "UPDATE commands SET state = '2' where command_id = '".$rw['command_id']."';";
        $db->Execute($sql);
    }
}
?>