<?PHP
set_time_limit(0);

require_once('../../no-web/core/include.php');

$upgrade_tree = array('2.0', '2.1', '2.2', '2.3', '3.0');
$us = getDBConfig('VERSION',1);
$me = $us['value'];

$i = 0;

foreach($upgrade_tree AS $sap)
{
    if($sap > $me)
    {
        $needed[] = $sap;
        $i ++;
    }
}

if($i < 1)
{
    header( 'refresh: 3; url=/cp/' );
    echo "You are fully up2date! Going back to Control Panel";
    exit;
}

include "upgrade-".$needed[0].".php";
?>