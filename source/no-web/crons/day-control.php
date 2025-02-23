#!/usr/bin/php -q
<?php
set_time_limit(0);
DEFINE('CRON',1);
require_once(dirname(__FILE__) .'/../core/include.php');

$sql = "UPDATE crons SET last_checkin = NOW() WHERE name = 'day';";
$db->Execute($sql);

// Prune links - anything less then 7 days to go
$min_time = date('Y-m-d h:i:s', (mktime() - 3600*24*7));
$sql = "DELETE FROM links WHERE dummy = '1' AND `date` < '$min_time';";
$db->Execute($sql);

// Prune log - anything 2 days old should go
$min_time = date('Y-m-d h:i:s', (mktime() - 172800));
$sql = "DELETE FROM log WHERE date < '$min_time'";
$test_m = $db->GetRow($sql);

// Prune quick creatvies
$sql = "DELETE FROM quick_campaign_creative";
$db->Execute($sql);

// Truncate commands table
$sql = "TRUNCATE TABLE commands;;";
$db->Execute($sql);

// Remove old log files
$path = $config->values['site']['path'] . "no-web/celiberod/logs/";
$command = "cd $path; rm -rdf @*";
$sql = "INSERT INTO commands (`command`, `date`, `state`) VALUES ('$command', NOW(), '0');";
$db->Execute($sql);
?>