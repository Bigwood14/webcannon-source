<?php
require_once(dirname(__FILE__) .'/../auth.php');
require_once(dirname(__FILE__) .'/../permissions.php');
require_once(dirname(__FILE__) .'/../profile.php');

$container_options = array(
'type' => 'mysql',
'host' => 'localhost',
'username' => 'root',
'password' => '',
'database' => 'user',
'table_prefix' => 'users_',
);

$options = array(
'sess_name' => 'test',
'cookie_domain' => '.cyberdummy.loc',
'cookie_path' => '/',
'sess_duration' => '900',
'pass_cypher' => 'md5',
);

$auth = new auth('adodb',$container_options,$options);
$permissions = new permissions(&$auth);
$profile = new profile(&$auth);
?>