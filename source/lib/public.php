<?php
define('CELIBERO_CORE',dirname(__FILE__) . '/../no-web/core');
require_once CELIBERO_CORE . '/include.php';

// allows us to add modular scripts/styles to the layout template
$tpl->styles  = array();
$tpl->scripts = array();

function get_domains() {
    return all_rows(query("select * from server_to_ip"));
}

function db_error() {
	//print '<!--';
	//debug_print_backtrace();
	//print '-->';

    die('Database error: ' . mysql_error());
}

function query($query, $ignore_error = false) {
    global $_db;

    $res = null;
    if (!$res = mysql_query($query,$_db))
	{
		if ($ignore_error == false && mysql_errno() != 1062)
			db_error();
	}
    return $res;
}

function insert($query) {
    global $_db;
    query($query);
    return mysql_insert_id($_db);
}

function row($res) {
    return mysql_fetch_assoc($res);
}

function all_rows($res) {
    $rows = array();
    while ($row = row($res)) { $rows[] = $row; }
    mysql_free_result($res);
    return $rows;
}

function input($var) {
    if (get_magic_quotes_gpc()) { $var = stripslashes($var); }
    return $var;
}

function esc($input) {
    global $_db;
    return mysql_real_escape_string($input,$_db);
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function url_for($path,$abs=false) {
    if ($abs) {
        return 'http://' . $_SERVER['HTTP_HOST'] . $path;
    }
    else {
        return $path;
    }
}

function redirect($url,$abs=true) {
    if (!$abs) { $url = url_for($url,true); }
    header("Location: $url");
    exit(0);
}

function show_cp_page($page,$msgs=null,$errors=null) {
    global $tpl;
    $tpl->msgs = $msgs;
    $tpl->errors = $errors;
    $tpl->template = $page;
    $tpl->display('cp/layout.php');
    exit(0);
}

function h($arg) {
    echo htmlspecialchars($arg);
}
?>
