<?PHP
$auth_is_admin = true;
require_once('../../no-web/core/include.php');
require_once('functions-scheduling.php');
require_once('Subscribe.php');
checkCPAcces();
// Prep Vars
$emails = array();
$count = 0;
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$local  	=  (!empty($_POST['local'])) 		? $_POST['local'] 		: @$_GET['local'];
$domain 	=  (!empty($_POST['domain'])) 		? $_POST['domain'] 		: @$_GET['domain'];
$id     	=  (!empty($_POST['id'])) 			? $_POST['id'] 			: @$_GET['id'];
$id_2   	=  (!empty($_POST['id_2'])) 		? $_POST['id_2'] 		: @$_GET['id_2'];
$multi_id 	=  (!empty($_POST['multi_id'])) 	? $_POST['multi_id'] 	: @$_GET['multi_id'];
$list 		=  (!empty($_POST['list_name'])) 	? $_POST['list_name'] 	: @$_GET['list_name'];

$list = mysql_real_escape_string($list);

if($list != '')
    $sql = "SELECT * FROM `list` where `name` = '$list' LIMIT 0,1;";
else
    $sql = "SELECT * FROM `list` LIMIT 0,1;";

$rw = $db->GetRow($sql);

if($rw == false)
    die("No lists to find in.");

$email_cols = $Lists->getCols($rw['name']);

$search_basic    = (!empty($_POST['search_basic'])) 	? $_POST['search_basic'] 		: @$_GET['search_basic'];
$search_id       = (!empty($_POST['search_id'])) 		? $_POST['search_id'] 			: @$_GET['search_id'];
$search_id_2 	 = (!empty($_POST['search_id_2'])) 		? $_POST['search_id_2'] 		: @$_GET['search_id_2'];
$search_multi_id = (!empty($_POST['search_multi_id'])) 	? $_POST['search_multi_id'] 	: @$_GET['search_multi_id'];
$search_sentence = (!empty($_POST['search_sentence'])	) ? $_POST['search_sentence'] 	: @$_GET['search_sentence'];
// Safe Vars
$local  = mysql_escape_string(str_replace('*', '%', $local));
$domain = mysql_escape_string(str_replace('*', '%', $domain));
// Construct Query
if($search_basic)
{
    // Local part
    if(strpos($local, '%') !== false)
    {
        // Just % no need to include!
        if($local != '%')
        {
            $sql = "`local` LIKE '$local'";
            $sql_where[] = array('type'=>'AND', 'sql'=>$sql);
        }
    }
    elseif(strlen($local) > 0)
    {
        // Needs to be equals
        $sql = "`local` = '$local'";
        $sql_where[] = array('type'=>'AND', 'sql'=>$sql);
    }

    // Domain Part
    if(strpos($domain, '%') !== false)
    {
        // Just % no need to include!
        if($domain != '%')
        {
            $sql = "`domain` LIKE '$domain'";
            $sql_where[] = array('type'=>'AND', 'sql'=>$sql);
        }
    }
    elseif(strlen($domain) > 0)
    {
        // Needs to be equals
        $sql = "`domain` = '$domain'";
        $sql_where[] = array('type'=>'AND', 'sql'=>$sql);
    }

    $search_up = 1;
}


function findID($id, $addid = false)
{
    global $list, $find, $db, $emails, $slog, $count;
    $id = trim($id);
    $find = new Subscribe_Find();

    $find->setList($list);
    $find->setID($id);
    $find->rtn_one = true;
    $rw = $find->find("WHERE `id` = '$find->sub_id'");

    if(!is_array($rw))
    {
        $rw2 = $find->findSLog("WHERE `id` = '$find->id'");
    }
    else
    {
        $rw['email'] = $rw['local'] . '@' . $rw['domain'];
        unset($rw['local']);
        unset($rw['domain']);
        if($addid == true)
        {
            $rw['com_id'] = $id;
        }
        $emails[] = $rw;
        $count ++;
    }

    if(is_array($rw2))
    {
        if(!$rw2['data'])
        {
            $data_row = $find->findSLogData("WHERE `id` = '$find->id'");
            $data = $data_row['data'];
        }
        else
        {
            $data = $rw2['data'];
        }

        $data = unserialize($data);

        $data['email'] = $data['local'] . '@' . $data['domain'];
        unset($data['local']);
        unset($data['domain']);
        foreach($data AS $k=>$v)
        {
            if(is_int($k))
            {
                continue;
            }
            else
            {
                $d[$k] = $v;
            }
        }
        if($addid == true)
        {
            $d['com_id'] = $id;
        }
        //print_r($d);
        $emails[] = $d;
        $count ++;
        $slog = $rw2['ts'];
        //print_r($emails);
    }
}

if($search_id || $search_multi_id || $search_id_2)
{
    if($search_multi_id)
    {
        $ids = explode("\n", $multi_id);
        foreach($ids AS $id)
        {
            if(trim($id) == '')
            {
                continue;
            }
            findID($id, true);
        }
    }
	else if ($search_id_2)
	{
		require_once('public.php');		
		require_once('link_tracking.cls.php');
		require_once('list_db.cls.php');
		$lists          = new list_db();
		$link_tracking 	= new link_tracking;
		$parts 			= $link_tracking->parse($id_2);
		$list_data 		= $lists->get($parts['list_id']);
		$list 			= $list_data['username'];
		findID($parts['table'].$parts['user_id']);
	}
    else
    {
        findID($id);
    }
}

if (isset($search_up))
{
    // Put the query together
    $sql = "SELECT [fields] FROM `{list}`.[table_name]";
    $i = 0;
    if(is_array($sql_where))
    {
        foreach($sql_where AS $where)
        {
            if($i > 0)
            {
                $sql_w .= " " . $where['type'];
            }

            $sql_w .= " " . $where['sql'];

            $i ++;
        }
    }

    // Query the db
    $count = $Lists->countEmails($list, 'WHERE' . $sql_w);
    $sql = str_replace('[fields]', '*', $sql);
    $sql = $sql . ' WHERE' .$sql_w;
    foreach($Lists->email_tables AS $table)
    {
        $tbl = $Lists->email_table_prefix . $table;
        $sql2 = str_replace('[table_name]', $tbl, $sql);

        $rs = $Lists->query_list($list, $sql2);

        while ($rw = row($rs))
        {
            $rw['email'] = $rw['local'] . '@' . $rw['domain'];
            unset($rw['local']);
            unset($rw['domain']);
            $emails[] = $rw;
        }
    }
}

$tpl->emails    = $emails;
$tpl->ignore    = array('mask','import_id');
$tpl->slog      = @$slog;
$tpl->count     = $count;
$tpl->id        = $id;
$tpl->id_2      = $id_2;
$tpl->template  = 'cp/management/recipient-find.php';
$tpl->display('cp/layout.php');
?>
