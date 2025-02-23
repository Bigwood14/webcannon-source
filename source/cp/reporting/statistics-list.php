<?PHP
require_once('../../no-web/core/include.php');
require_once('HTML/Layout.php');
checkCPAcces();

$big_names 	= array('hotmail.com','aol.com','msn.com','yahoo.com');
$stats 		= '';

// List Selected build some stats
if(isset($_POST['list_name']))
{
    $list 			= $_POST['list_name'];
    $stats['count'] = $Lists->countEmails($list);

    // Big name counts
    foreach($big_names as $big_name)
        $stats['big_names'][$big_name] = $Lists->countEmails($list, "WHERE `domain` = '$big_name'");

    // Sub / Unsubs
    $sql 				= "SELECT COUNT(event) AS `count` FROM `{list}`.slog;";
    $rs 				= $Lists->query_list($list, $sql);
    $rw 				= row($rs);
    $stats['slog'] 		= $rw['count'];

    $sql 				= "SELECT COUNT(event) AS `count` FROM `{list}`.slog WHERE event = '2' AND how < '5';";
    $rs 				= $Lists->query_list($list, $sql);
    $rw 				= row($rs);
    $stats['unsubs'] 	= $rw['count'];
    
    $sql 				= "SELECT COUNT(event) AS `count` FROM `{list}`.slog WHERE how = '5';";
    $rs 				= $Lists->query_list($list, $sql);
    $rw 				= row($rs);
    $stats['bounce_s'] 	= $rw['count'];

    $sql 				= "SELECT COUNT(event) AS `count` FROM `{list}`.slog WHERE how = '6';";
    $rs 				= $Lists->query_list($list, $sql);
    $rw 				= row($rs);
    $stats['bounce_h'] 	= $rw['count'];
    
    $stats['clickers'] += $Lists->countEmails($list, "WHERE mask = '2' || mask = '3'");
    $stats['openers']  += $Lists->countEmails($list, "WHERE mask = '1' || mask = '3'");
    
    $tpl->list_stats = $stats;


    $sql 	= "SELECT * FROM `{list}`.slog ORDER BY ts DESC LIMIT 0,30";
    $rs 	= $Lists->query_list($list, $sql);
    $data 	= array();

    while($rw = row($rs))
        $data[] = $rw;

    $tpl->data = $data;
}

$tpl->template = 'cp/reporting/statistics-list.php';
$tpl->display('cp/layout.php');
?>
