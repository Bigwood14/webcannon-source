#!/usr/bin/php -q
<?php
set_time_limit(0);
DEFINE('CRON',1);
require_once(dirname(__FILE__) .'/../core/include.php');
$sql = "UPDATE crons SET last_checkin = NOW() WHERE name = 'minute';";
$db->Execute($sql);

$sql = "INSERT INTO commands (`command`, `date`, `state`) VALUES ('chown upload:upload -R /home/upload/import', NOW(), '0');";
$db->Execute($sql);

$sql = "INSERT INTO commands (`command`, `date`, `state`) VALUES ('chmod 777 -R /home/upload/import', NOW(), '0');";
$db->Execute($sql);

print "Commands Section \n";
print "---------------- \n\n\n";
include(dirname(__FILE__) .'/minute/commands.php');

print "Parse Section \n";
print "---------------- \n\n\n";
include(dirname(__FILE__) .'/minute/parse_results.php');

print "Operations Section \n";
print "---------------- \n\n\n";
include(dirname(__FILE__) .'/minute/operations.php');

print "Celiberod Section \n";
print "---------------- \n\n\n";
$cmd = '/usr/bin/php '.dirname(__FILE__) .'/minute/engine_launch.php';
exec($cmd);

print "Prepare Section \n";
print "---------------- \n\n\n";
include(dirname(__FILE__) .'/minute/prepare.php');


print "Import Section \n";
print "---------------- \n\n\n";
include(dirname(__FILE__) .'/minute/import.php');

print "Export Section \n";
print "---------------- \n\n\n";
include(dirname(__FILE__) .'/minute/export.php');
?>
