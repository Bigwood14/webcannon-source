#!/usr/bin/php -q
<?PHP
set_time_limit(0);
require_once('../../core/include.php');
require_once("Subscribe.php");

$unsubscribe        = new Unsubscribe();
$unsubscribe->list  = '';
$unsubscribe->how   = 0;

while(1)
{

    $sql = "SELECT * FROM bouncer";

    $r = $db->Execute($sql);
    print mysql_error();
    while ($rw = $r->FetchRow())
    {


        $unsubscribe->setEmail($rw['email']);
        $db->Execute("DELETE FROM celibero.bouncer WHERE email = '{$rw['email']}'");
        print mysql_error();
        print $rw['email']." [".$unsubscribe->doUnsub()."]\n";
        $unsubscribe->reset();
    }
    sleep(1);
}
?>
