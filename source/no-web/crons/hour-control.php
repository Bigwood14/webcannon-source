#!/usr/bin/php -q
<?php
set_time_limit(0);

DEFINE('CRON',1);

// make sure all domains are in the qmail files!
foreach (array('locals', 'rcpthosts') as $file)
{
	$cmd = "/bin/ls /var/named/chroot/etc/zones/master/ | /bin/egrep '(.net|.com)' | /bin/sed 's/\.db//g' >> /var/qmail/control/{$file};";
	exec($cmd);

	$cmd = "/bin/cat /var/qmail/control/{$file} | /bin/sort | /usr/bin/uniq > /var/qmail/control/{$file}.tmp; /bin/mv -f /var/qmail/control/{$file}.tmp /var/qmail/control/{$file}";
	exec($cmd);
}

require_once(dirname(__FILE__) .'/../core/include.php');
$sql = "UPDATE crons SET last_checkin = NOW() WHERE name = 'hour';";
$db->Execute($sql);

$path 		= $config->values['site']['path'];
$command 	= $path . "no-web/celiberod/bin/keepup ";
$sql 		= "INSERT INTO commands (`command`, `date`, `state`) VALUES ('$command', NOW(), '0');";

$db->Execute($sql);

$command 	= '/bin/chmod 777 '.$path.'img';
$sql 		= "INSERT INTO commands (`command`, `date`, `state`) VALUES ('$command', NOW(), '0');";
$db->Execute($sql);


$sql = "INSERT INTO commands (`command`, `date`, `state`) VALUES ('/usr/local/bin/svc -h /service/qmail-send;/usr/local/bin/svc -h /service/qmail-smtpd', NOW(), '0');";
$db->Execute($sql);

//exec('/sbin/iptables -A OUTPUT -p tcp -s 0/0 -d 205.158.62.0/24 -j REJECT');

print "SGDNE Get \n";
print "---------------- \n\n\n";
$cmd = '/usr/bin/php '.dirname(__FILE__) .'/hour/SGDNE-get.php';
exec($cmd);

print "SGDNE Send \n";
print "---------------- \n\n\n";
include(dirname(__FILE__) .'/hour/SGDNE-send.php');
?>
