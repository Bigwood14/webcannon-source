<?PHP
require_once('../../no-web/core/include.php');

require_once('HTML/Layout.php');

checkCPAcces();

if(isset($_POST['submit_perms']))
{
    $username = $db->qstr($_POST['username'],get_magic_quotes_gpc());
    $password = $db->qstr($_POST['password'],get_magic_quotes_gpc());
    $hostname = $db->qstr($_POST['hostname'],get_magic_quotes_gpc());
    $database = $db->qstr($_POST['database'],get_magic_quotes_gpc());
    // Check there is a row to update else create it.
    $count = countDB($db, 'sgdne');
    if($count < 1)
    {
        $sql = "INSERT INTO sgdne () VALUES ();";
        $db->Execute($sql);
    }
    $sql = "UPDATE sgdne SET `username` = $username, `password` = $password, `active` = '1', `hostname` = $hostname,`database` = $database;";
    //$db->debug = 1;
    $db->Execute($sql);
    print mysql_error();

    $tpl->msg = 'Permissions updated - You are now active.';
}

$sql = "SELECT * FROM sgdne";
$tpl->info = $db->GetRow($sql);

if($_GET['action'] == 'delete' && isset($_GET['word_id']))
{
    $id = mysql_escape_string($_GET['word_id']);
    $sql = "DELETE FROM global_words WHERE word_id = '$id';";
    $db->Execute($sql);
}
// Delete domain or email
elseif($_GET['action'] == 'delete')
{
    switch ($_GET['type'])
    {
        case 'email':
        $table = 'global_unsub';
        $field = 'address';
        break;
        case 'domain':
        $table = 'global_unsub_domain';
        $field = 'domain';
        break;
    }

    $sql = "DELETE FROM `$table` WHERE `$field` = '".$_GET['id']."';";
    $db->Execute($sql);
}

// Search GDNE
if(isset($_POST['search']))
{
    switch ($_POST['type'])
    {
        case 'Emails':
        $table = 'global_unsub';
        $field = 'address';
        $type  = 'email';
        break;
        case 'Domains':
        $table = 'global_unsub_domain';
        $field = 'domain';
        $type  = 'domain';
        break;
    }
    $sql = "SELECT * FROM `$table` WHERE `$field` = '".$_POST['search_v']."';";
    $rs = $db->Execute($sql);
    $results = array();
    while($res = $rs->FetchRow())
    {
        $res['value'] = $res[$field];
        $res['id']    = $res['value'];
        $res['type']  = $type;
        $results[] = $res;
    }
    $tpl->search_results = $results;
}

if(isset($_POST['word']))
{
    require_once "Subscribe.php";

    $unsubscribe_word        = new Unsubscribe_Word();
    $unsubscribe_word->how   = 3;
    $unsubscribe_word->gdne  = true;

    $unsubscribe_word->setWord($_POST['word']);
    $unsubscribe_word->doUnsub();
}

$sql = "SELECT * FROM global_words";
$tpl->words = $db->GetAll($sql);

$tpl->template = "cp/management/SGDNE.php";
$tpl->display('cp/layout.php');
?>