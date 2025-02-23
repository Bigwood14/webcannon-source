<?PHP
set_time_limit(0);

require_once('../no-web/core/installer/installer.php');
require_once('../no-web/core/config.php');

$die 			= false;
$config             = new config();
$installer          = new installer();
$installer->file    = dirname(__FILE__) . "/../no-web/core/config.php";

// Pre Install Checks

if($config->values['site']['installed'] == '1')
{
    $die[] = 'Celibero is already installed? May need to reset value in /no-web/core/config.php.';
}

if(!$v = $installer->checkPHPVersion('4.1.0'))
{
    $die[] = 'Please update php to at least version 4.1.0, you are running '.$v;
}

if(!function_exists('mysql_connect'))
{
    $die[] = 'Please install the php mysql extension.';
}

$dir 		= dirname(__FILE__);
$files 		= array();
$files[] 	= $dir . '/../no-web/core/config.php';
$files[]	= $dir . '/../no-web/celiberod/etc/celibero.conf';
$files[]	= $dir . '/shell/run-install.sh';

foreach ($files as $file)
{
	if (!$installer->isFileWritable($file))
		$die[] = "File [$file] is not writable.";
}



if(is_array($die))
{
    echo 'There were errors install can not proceed<br />';
    foreach($die AS $value)
    {
        echo $value."<br />";
    }
    die;
}

// Requirements met

if(isset($_POST['submit']))
{
    if(!@mysql_connect($_POST['mysql_hostname'],$_POST['mysql_username'],$_POST['mysql_password']))
    {
        $error[] = "Could not connect to mysql with specified details (".mysql_error().")";
    }
    if(!is_dir($_POST['root_path']))
    {
        $error[] = "Root Path is not a directory";
    }
    if(!is_dir($_POST['core_path']))
    {
        $error[] = "Core Path is not a directory";
    }

    if(!is_array(@$error))
    {
        $server_db = 0;
        switch ($_POST['server_type'])
        {
            case 5:
            $server_db = 1;
            $master = setupMaster();
            $server_id = insertServer($_POST['server_type'], $_POST['server_name']);
            setupMailer();
            break;
            case 4:
            $server_db = 1;
            $master = setupMaster();
            $server_id = insertServer($_POST['server_type'], $_POST['server_name']);
            break;
            case 3:
            $server_id = insertServer($_POST['server_type'], $_POST['server_name']);
            setupMailer();
            break;
            case 2:
            $server_id = insertServer($_POST['server_type'], $_POST['server_name']);
            setupDB();
            break;
            case 1:
            $server_id = insertServer($_POST['server_type'], $_POST['server_name']);
            setupDB();
            setupMailer();
            break;
        }

        $reps = array(
        array('var' => '{domain}',        'val' => $_POST['url']),
        array('var' => '{just_domain}',   'val' => $_POST['domain']),
        array('var' => '{core_path}',     'val' => $_POST['core_path']),
        array('var' => '{root_path}',     'val' => $_POST['root_path']),
        array('var' => '{upload_path}',   'val' => $_POST['upload_path']),
        array('var' => '{installed}',     'val' => '1'),
        array('var' => '{hostname}',      'val' => $_POST['mysql_hostname']),
        array('var' => '{username}',      'val' => $_POST['mysql_c_username']),
        array('var' => '{password}',      'val' => $_POST['mysql_c_password']),
        array('var' => '{database}',      'val' => $_POST['mysql_c_database']),
        array('var' => '{server_id}',     'val' => $server_id),
        array('var' => '{server_db}',     'val' => $server_db),
        );

        if(!$installer->replaceFileVar($reps))
        {
            $error[] = "Could not replace config vars.";
        }

        if($master !== true)
        {
            $error[] = $master;
        }
        else
        {
            $write  = "mysql_user ".$_POST['mysql_c_username']."\n";
            $write .= "mysql_passwd ".$_POST['mysql_c_password']."\n";
            $write .= "mysql_server ".$_POST['mysql_hostname']."\n";
            $write .= "mysql_database ".$_POST['mysql_c_database']."\n";
            $write .= "connect_timeout 15\n";
            $write .= "max_threads 600\n";
            $write .= "dns 127.0.0.1\n";
            $write .= "server_id $server_id";

            $file   = $_POST['root_path']."no-web/celiberod/etc/celibero.conf";
            $fp     = fopen($file, 'w');
            fwrite($fp, $write);
            fclose($fp);

            $file   = $_POST['root_path']."install/shell/run-install.sh";
            $f      = fopen($file,'w');
            $url    = parse_url('http://'.$_POST['domain']);

            $script = $_POST['root_path']."install/shell/install.sh ".rtrim($_POST['root_path'],"/")." ".$_POST['domain'];
            fwrite($f,$script);
            fclose($f);
            //chmod($file, 0777);
            //umask($old);
            $script = $file;
            $install = 'complete';
        }
    }
}

function rollBack($sql,$stage)
{
    global $sqlA;
    $i = 1;
    while($i <= $stage)
    {
        mysql_query($sqlA[$i]['out']);
        $i ++;
    }
    $file    = $_POST['root_path']."no-web/core/config.php.original";
    $newfile = $_POST['root_path']."no-web/core/config.php";
    if (!copy($file, $newfile))
    {
        print "failed to copy $file...\n";
    }
    @chmod("777",$newfile);
}

function insertServer($type, $name)
{
    $domain     = $_POST['domain'];

    $sql = "INSERT INTO servers (`type`, `name`) VALUES ('$type', '$name');";
    if(!mysql_query($sql))
    {
        return "Could not insert new server";
    }
    else
    {
        $id = mysql_insert_id();
        $sql = "INSERT INTO server_to_ip (`server_id`, `ip`, `domain`, `default`) VALUES ('$id', '".$_POST['server_ip']."', '$domain', '1');";
        if(!mysql_query($sql))
        {
            return "Could not insert new server IP (".mysql_error().")";
        }
        else
        {
            return $id;
        }
    }
}

function setupMailer()
{
    return;
}

function setupDB()
{
    return;
}

function setupMaster()
{
    // Setup master celibero user
    mysql_query("DELETE FROM mysql.user WHERE User = '';");
    mysql_query("DELETE FROM mysql.user WHERE User = 'celibero';");
    if(!mysql_query("GRANT ALL PRIVILEGES ON
                           *.* 
                         TO 
                           ".$_POST['mysql_c_username']."@'%' 
                         IDENTIFIED BY 
                           '".$_POST['mysql_c_password']."' 
                         WITH GRANT OPTION"))
    {
        return "Could not create grant privalges to master user (".mysql_error().").";
    }
    // Flush make sure we can login!
    elseif(!mysql_query("FLUSH PRIVILEGES;"))
    {
        return"Could not flush privlages (".mysql_error().")";
    }
    // Create master control DB
    elseif(!mysql_query("CREATE DATABASE `".$_POST['mysql_c_database']."`;"))
    {
        return"Could not create database ".$_POST['mysql_c_database']." (".mysql_error().")";
    }
    // Select the new master DB
    elseif(!mysql_select_db($_POST['mysql_c_database']))
    {
        return "Could not select master database. (".mysql_error().")";
    }

    $db_username = $_POST['mysql_c_username'];
    $db_password = $_POST['mysql_c_password'];
    $hostname    = $_POST['mysql_hostname'];
    $database    = $_POST['mysql_c_database'];
    $physical_address    = $_POST['physical_address'];

    $username   = $_POST['username'];
    $password   = $_POST['password'];
    $domain     = $_POST['domain'];
    $path       = $_POST['root_path'];

    // Create the master DB's structure
    $sql_file   = $path."install/install-files/celibero.sql";
    $shell_cmd  = "mysql -u".$db_username." -p".$db_password." -h".$hostname." ".$database." < $sql_file";
    exec($shell_cmd);

    // Insert the data into master DB
    $sql_file   = $path."install/install-files/celibero-data.sql";
    $shell_cmd  = "mysql -u".$db_username." -p".$db_password." -h".$hostname." ".$database." < $sql_file";
    exec($shell_cmd);


    // Update data

    // Postal Address
    $sql = "UPDATE `config` SET `value` = '".mysql_escape_string(stripslashes($physical_address))."' WHERE `KEY` = 'ADDRESS';";
    if(!mysql_query($sql))
    {
        return "Could not update address (".mysql_error().").";
    }
    // Update username + password
    $sql = "UPDATE `users_auth` SET `username` = '".$username."', `password` = '".md5($password)."', text_password = '".$password."';";
    if(!mysql_query($sql))
    {
        return "Could not update user + pass";
    }

    // Insert Footers
    $html  = implode('', file(dirname(__FILE__)."/install-files/html_footer.txt"));
    $text  = implode('', file(dirname(__FILE__)."/install-files/text_footer.txt"));

    $html = str_replace('{address}', nl2br($physical_address), $html);
    $text = str_replace('{address}', $physical_address, $text);

    $sql = "UPDATE `config` SET `value` = '".mysql_escape_string($html)."' WHERE `KEY` = 'HTML_FOOTER';";
    if(!mysql_query($sql))
    {
        return "Could not update html footer";
    }

    $sql = "UPDATE `config` SET `value` = '".mysql_escape_string($text)."' WHERE `KEY` = 'TEXT_FOOTER';";
    if(!mysql_query($sql))
    {
        return "Could not update text footer.";
    }

    return true;
}

$path             = str_replace("install","",dirname(__FILE__));
$core_path        = $path."core/";
$random_password  = substr(md5(uniqid(rand(), true)),0,10);
$random_password2 = substr(md5(uniqid(rand(), true)),0,10);

require_once('../no-web/templates/install/index.php');
?>
