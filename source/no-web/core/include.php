<?PHP
DEFINE('CELIBERO_VERSION', '4.2.0');
/* Set up config */
require_once(dirname(__FILE__) .'/config.php');
$config = new config;
ini_set('include_path',
	ini_get('include_path') . ':'.
    $config->values['site']['core_path'] . ':' .
    dirname(__FILE__) . '/../../lib');

require_once('functions.php');
require_once('Error.php');
require_once('db/adodb/adodb.inc.php');


/* Set up Master DB */
$db = &ADONewConnection($config->values['db']['type']);
$db->Connect($config->values['db']['hostname'],
             $config->values['db']['username'],
             $config->values['db']['password'],
             $config->values['db']['database']
            );

$_db = $db->_connectionID;

require_once('public.php');
require_once('list.cls.php');

$Lists = lists::singleton();

/* Set up Auth */
if(!defined('CRON') && !defined('OUTSIDE'))
{
    require_once('user/auth.php');
    require_once('user/permissions.php');
    require_once('user/profile.php');

    $container_options = array(
    'type' => 'mysql',
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'user',
    'table_prefix' => 'users_',
    );

    $options = array(
    'sess_name' => 'celibero',
    'cookie_domain' => '.'.getDefaultDomain(),
    'cookie_path' => '/',
    'sess_duration' => '259200',
    'pass_cypher' => 'md5',
    );
    
    $auth           = new auth('adodb',$container_options,$options,$db);
    $permissions    = new permissions($auth);
    $profile        = new profile($auth);

    /* Set up template */
    require_once('template/template.php');
    
    DEFINE("SYSTEM_DOMAIN", getDefaultDomain());
    $us = getDBConfig('VERSION',1);
   
   	$pt = getDBConfig('PAGE_TITLE',1); 
    
    $tpl            	= new template;
    $tpl->real_version 	= $us['value'];
	$tpl->page_title 	= $pt['value'];
    $tpl->directory = $config->values['template']['directory'];
    $tpl->username  = $profile->getText("FIRST_NAME");
    $tpl->username  = $tpl->username[0];

    $tpl->layout_username = $auth->user['username'];
}
?>
