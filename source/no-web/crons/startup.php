#!/usr/bin/php -q
<?php
set_time_limit(0);
DEFINE('CRON',1);
require_once(dirname(__FILE__) .'/../core/include.php');

// Update schedule preparing to not as its been restarted
$sql = "UPDATE schedule SET state = '0' WHERE id = '1';";
$db->Execute($sql);
?>