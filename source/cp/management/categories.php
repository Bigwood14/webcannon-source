<?PHP
require_once('../../no-web/core/include.php');
require_once('HTML/Layout.php');
checkCPAcces();

if(isset($_POST['add']))
{
    $title = $db->qstr($_POST['title'],get_magic_quotes_gpc());
    $sql = "INSERT INTO categories (title) VALUES ($title)";
    $db->Execute($sql);
    echo mysql_error();
    header("Location: /cp/management/categories.php");
}
elseif (isset($_POST['edit']))
{
    $title = $db->qstr($_POST['title'],get_magic_quotes_gpc());
    $sql = "UPDATE categories SET title = ".$title." WHERE category_id = '".$_POST['id']."'";
    $db->Execute($sql);
}
elseif(isset($_POST['delete']) && is_array($_POST['selected']))
{
    foreach($_POST['selected'] AS $category_id)
    {
        $db->Execute("DELETE FROM email_to_category WHERE category_id = '$category_id';");
        $db->Execute("DELETE FROM categories WHERE category_id = '$category_id';");
    }
}

$sql = "SELECT COUNT(*) FROM categories";
$rw = $db->GetRow($sql);
$rows_per_page = 30;
$num_rows = $rw[0];
$page_num = (empty($_GET['page_num'])) ? 1 : $_GET['page_num'];
$paging_from = ($page_num - 1) *  $rows_per_page;
$pager = pager($paging_from, $rows_per_page, $num_rows);

$sql = "SELECT * FROM categories ORDER BY title ASC LIMIT $paging_from,$rows_per_page";
$rs = $db->Execute($sql);
while($rw = $rs->FetchRow())
{
    $sql = "SELECT COUNT(*) AS emails FROM email_to_category WHERE category_id = '".$rw['category_id']."'";
    $rw2 = $db->GetRow($sql);
    $rw['emails'] = $rw2['emails'];
    $data[] = $rw;
}


$tpl->pager = $pager;
$tpl->cats = $data;
$tpl->template = "cp/management/categories.php";
$tpl->display('cp/layout.php');
?>