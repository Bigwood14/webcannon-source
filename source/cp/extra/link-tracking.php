<?PHP
require_once('../../no-web/core/include.php');
require_once('HTML/Layout.php');
checkCPAcces();

$tpl->we_are = "Add";

if(isset($_GET['msg_id']))
{
    $sql_where = " AND msg_id = '".mysql_real_escape_string($_GET['msg_id'])."'";
}

if(isset($_GET['edit']))
{
    $sql = "SELECT * FROM links WHERE link_id = '".mysql_real_escape_string($_GET['link_id'])."';";
    $info = $db->GetRow($sql);
    $tpl->info = $info;
    $tpl->we_are = "Edit";
}

if(isset($_GET['delete']))
{
    $sql = "DELETE FROM links WHERE link_id = '".mysql_real_escape_string($_GET['link_id'])."';";
    $db->Execute($sql);
}

if(isset($_POST['add']))
{
    $url    = mysql_real_escape_string($_POST['url']);
    $msg_id = mysql_real_escape_string($_POST['msg_id']);
    $img    = mysql_real_escape_string($_POST['img']);

    // We editing?
    if($_POST['link_id'] > 0)
    {
        $sql_top = "link_id,";
        $sql_bot = "'".mysql_real_escape_string($_POST['link_id'])."',";
    }
    
    $sql = "REPLACE INTO links ($sql_top`URL`,`count`,`msg_id`,`img`) VALUES ($sql_bot'$url','0','$msg_id','$img');";
    $db->Execute($sql);
    $id = $db->Insert_ID();
    $tpl->draft_link = "http://{{dn}}/t/c.php?link_id=$id&l={{02}}&id{{01}}";
    $domain = getDefaultDomain();
    $tpl->ext_link   = "http://$domain/t/c.php?link_id=$id";
}

// Pagination - 5 lines argh
$num_rows       = countDB($db, 'links', "WHERE dummy = '0'", 'link_id');
$rows_per_page  = 20;

$page_num       = (empty($_GET['page_num'])) ? 1 : $_GET['page_num'];
$paging_from    = ($page_num - 1) *  $rows_per_page;
$pager          = pager($paging_from, $rows_per_page, $num_rows);

$sql = "SELECT * FROM links WHERE dummy = '0'$sql_where ORDER BY link_id DESC LIMIT $paging_from,$rows_per_page";
$rs = $db->Execute($sql);
$links = array();
while($rw = $rs->FetchRow())
{
    if($rw['msg_id'] > 0)
    {
        $sql = "SELECT * FROM msg WHERE id = '".mysql_real_escape_string($rw['msg_id'])."';";
        $msg = $db->GetRow($sql);
        $rw['msg_title'] = $msg['title'];
        
    }
    
    $links[] = $rw;
}
$tpl->pager     = $pager;
$tpl->drafts    = $db->GetAll("SELECT * FROM msg");
$tpl->links     = $links;
$tpl->template  = "cp/extra/link-tracking.php";
$tpl->display('cp/layout.php');
?>
