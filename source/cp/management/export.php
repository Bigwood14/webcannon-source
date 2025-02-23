<?PHP
$auth_is_admin = true;
set_time_limit(0);
require_once('../../no-web/core/include.php');
require_once('HTML/Layout.php');
checkCPAcces();


if($_GET['action'] == 'abort')
{
    $sql = "UPDATE export SET state = '3' WHERE export_id = '".$_GET['export_id']."'";
    $db->Execute($sql);
    header("Location: /cp/management/export.php");
    die;
}

if($_GET['action'] == 'restart')
{
    $sql = "UPDATE export SET state = '0', progress = '0' WHERE export_id = '".$_GET['export_id']."'";
    $db->Execute($sql);
    header("Location: /cp/management/export.php");
    die;
}

$sql = "SELECT * FROM categories";
$rs = $db->Execute($sql);

while($rw = $rs->FetchRow())
{
    $options .= "<option value=\"".$rw['category_id']."\">".$rw['title']."</option>";
    $data[$rw['category_id']] = $rw['title'];
}
$where = "WHERE ";
if(isset($_POST['list']))
{
    // prepare the click / openers sql
    $openers = 'a';
    switch($_POST['openers']) {
        case "2":
        $where .= "(mask = '1' OR mask = '3')";
        $openers = 'y';
        $ext .= "_open";
        break;
        case "3":
        $where .= "(mask != '1' AND mask != '3')";
        $openers = 'n';
        $ext .= "_nopen";
        break;
    }
    $clickers = 'a';
    switch($_POST['clickers']) {
        case "2":
        if($where != 'WHERE ') $where .= " AND ";
        $where .= "(mask = '2' OR mask = '3')";
        $clickers = 'y';
        $ext .= "_clk";
        break;
        case "3":
        if($where != 'WHERE ') $where .= " AND ";
        $where .= "(mask != '2' OR mask != '3')";
        $clickers = 'n';
        $ext .= "_nclk";
        break;
    }

    if($_POST['tags'] != '0')
    {
        if($where != '') $where .= " AND ";
        $where .= "(import_id = '".$_POST['tags']."')";
        $import_id = $_POST['tags'];
    }
    
    $sub      = 'n';
    $unsub    = 'n';
    $bounce_s = 'n';
    $bounce_h = 'n';
    
    if(!is_array($_POST['types'])) die('You must select at least one list type.');
    foreach($_POST['types'] AS $type)
    {
        $slog_sql;
        // build slog query
        switch($type) {
            case "subscribed":
            if (trim($where) == 'WHERE')
		$where = '';

            $num += $Lists->countEmails($_POST['list_name'], $where);
            $where = str_replace('WHERE ', '', $where);
            $ext .= "_subs";
            $sub = 'y';
            break;
            case "unsubscribed":
            $slog_sql[] = "(event = '2' AND how < '5')";
            $ext .= "_unsubs";
            $unsub = 'y';
            break;
            case "bounced_s":
            $slog_sql[] = "(how = '5')";
            $ext .= "_bs";
            $bounce_s = 'y';
            break;
            case "bounced_h":
            $slog_sql[] = "(how = '6')";
            $ext .= "_bh";
            $bounce_h = 'y';
            break;
        }
    }


    if(is_array($slog_sql))
    {
        $slog_where = "WHERE ";
        $i = 0;
        foreach($slog_sql AS $sq)
        {
            if($i > 0) $slog_where .= " OR ";
            $slog_where .= $sq;
            $i ++;
        }
        
        //if($where != '')  $slog_where .= " AND $where";
        $sql  = "SELECT COUNT(*) AS count FROM slog $slog_where";
        //print $sql;
        $rs   = $Lists->query_list($_POST['list_name'], $sql);
        $rw   = row($rs);
        $num += $rw['count'];
    }
    
    

    $name 		= str_replace(" ","-",$_POST['list_name']. $ext . "-" . date("m-d_h_i_s",mktime()));
    $where 		= mysql_escape_string($where);
    $slog_where = mysql_escape_string($slog_where);

    $sql = "INSERT INTO export 
                (state, name, progress, type, `list-cat`, total, ts, openers, clickers, import_id, subscribed, unsubscribed, bounce_s, bounce_h, `where`, slog_where) 
            VALUES 
                (0, '$name', '0', '$type_db', '".$_POST['list_name']."', '$num', NOW(), '$openers', '$clickers', '$import_id', '$sub', '$unsub', '$bounce_s', '$bounce_h', '$where', '$slog_where');";
    
    $db->Execute($sql);
    print $db->ErrorMsg();
    header("Location: /cp/management/export.php"); exit;
}
elseif(isset($_POST['category']))
{
    $sql = "SELECT COUNT(*) AS count FROM email_to_category WHERE category_id = '".$_POST['category_id']."'";
    //print mysql_error();
    $rw = $db->GetRow($sql);
    $total = $rw['count'];
    $name = str_replace(" ","-",$data[$_POST['category_id']]. "-" . date("m-d_h_i_s",mktime()));
    $name = str_replace("/", "", $name);
    
    $sql = "INSERT INTO export 
             (state,name,progress,type,`list-cat`,total,ts) 
             VALUES 
             (0,'$name','0',3,'".$_POST['category_id']."','$total',NOW());";
    
    $db->Execute($sql);
    print $db->ErrorMsg();
    header("Location: /cp/management/export.php"); exit;
}

$sql = "SELECT import_id, title FROM imports";
$rs = $db->Execute($sql);

$tags[] = array('import_id' => '0', 'title' => 'All');
while($rw = $rs->FetchRow($rs))
{
    $tags[] = $rw;
}

$num_rows       = countDB($db, 'export', '', 'export_id');
$rows_per_page  = 20;

$page_num       = (empty($_GET['page_num'])) ? 1 : $_GET['page_num'];
$paging_from    = ($page_num - 1) *  $rows_per_page;
$pager = pager($paging_from, $rows_per_page, $num_rows);

$sql = "SELECT * FROM export ORDER BY ts DESC LIMIT $paging_from,$rows_per_page";
$rs = $db->Execute($sql);
$data = array();
while($rw = $rs->FetchRow($rs))
{
    if($rw['import_id'] > 0)
    {
        $import = $db->GetRow("SELECT * FROM imports WHERE import_id = '{$rw['import_id']}';");
        $rw['tag'] = $import['title'];
    }
    $data[] = $rw;
}

$tpl->cats      = $data;
$tpl->tags      = $tags;
$tpl->pager     = $pager;
$tpl->data      = $data;
$tpl->options   = $options;
$tpl->template  = "cp/management/export.php";
$tpl->display('cp/layout.php');
?>
