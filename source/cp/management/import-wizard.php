<?PHP
set_time_limit(0);
require_once('../../no-web/core/include.php');

checkCPAcces();
require_once('HTML/Layout.php');

$import_dir  = $config->values['site']['upload_patch'].'import/';

if(isset($_POST['list_name']))
{
    $cols = $Lists->getCols($_POST['list_name']);
    
    foreach($cols AS $col)
    {
        
        $r_cols[] = $col['Field'];
    }
    
}
// Wants to upload a small file
if($_FILES['file']['name'] != '')
{
    $named = str_replace(" ","_",basename($_FILES['file']['name']));

    $uploadfile = $import_dir . $named;

	if (is_file($uploadfile))
	{
		$parts 	= explode('.', $named);
		$ext 	= $parts[count($parts)-1];
		unset($parts[count($parts)-1]);
		$name 	= implode('.', $parts);
	
		for ($i=0;$i<10;$i++)
		{
			$uploadfile = $import_dir.$name.'_'.$i.'.'.$ext;
			if (!is_file($upload_file))
				break;
		}
	}


    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile))
    {
        $tpl->named = $named;
        $tpl->uploaded = 1;
    }
    else
    {
        $tpl->uploaded = 0;
    }
}
// Wants to uncompress a file
if(isset($_POST['uncompress']))
{
    $file = stripslashes(str_replace(" ","*",$import_dir.$_POST['uncompress_selected']));
    if($_POST['uncompress_type'] == 'zip')
    {
        //print 'tried it';
        $tpl->uncompress_output = shell_exec('unzip '.$file.' -d '.$import_dir);
    }
    elseif($_POST['uncompress_type'] == 'gz')
    {
        $tpl->uncompress_output = shell_exec('gunzip '.$file.' -d '.$import_dir);
    }
    elseif($_POST['uncompress_type'] == 'tar.gz')
    {
        $tpl->uncompress_output = shell_exec('tar -zxpvf '.$file.' -C '.$import_dir);
    }
}
// Want to import for supression / gdne
if(isset($_POST['select2']))
{
    header("location: /cp/management/import-other.php?file=".$_POST['selected']);
}
// Select fields for import wizard
if(isset($_POST['select']))
{
    $read  = fopen($import_dir . $_POST['selected'], "r");

	if (!$read)
	{
		die('Could not open the file! please contact admin with this message.');
	}

    $i = 0;
    while (!feof ($read) && $i < 5)
    {
        $j = 0;
        $buffer  = fgets($read, 4096);
        
        if($buffer == '') continue;
        
        $lines .= $buffer;
        if(get_magic_quotes_gpc() > 0)
        {
            $delim = stripslashes($_POST['delim']);
        }
        else
        {
            $delim = $_POST['delim'];
        }
        if($delim == '\t')
        {
            $parts = explode("\t",$buffer);
        }
        else
        {
            $parts = explode(stripslashes($_POST['delim']),$buffer);
        }

        $count = count($parts);
        $format = array();
        foreach($parts AS $part)
        {
            $part = clean($part);
            $example[$j] .= $part."\n";
            if(eregi('@',$part))
            {
                $format[$j] = 'email';
            }
            elseif(preg_match("#(\d+\.\d+\.\d+\.\d+)#i", $part))
            {
                $format[$j] = 'ip';
            }
            elseif(eregi("male",$part) || eregi("female",$part) || eregi('^m$',$part) || eregi('^f$',$part))
            {
                $format[$j] = 'gender';
            }
            elseif(eregi("[0-9][0-9]/[0-9][0-9]/[0-9][0-9][0-9][0-9]",$part))
            {
                $format[$j] = 'dob';
            }
            elseif(eregi("www\.",$part) || eregi("\.com",$part))
            {
                $format[$j] = 'source';
            }
            elseif(eregi("pm",$part) || eregi("am",$part))
            {
                $format[$j] = 'timestamp';
            }
            elseif(eregi("^[a-z][a-z]$",$part) && !in_array('state',$format))
            {
                $format[$j] = 'state';
            }
            elseif(eregi("^[a-z][a-z]$",$part))
            {
                $format[$j] = 'country';
            }
            elseif(eregi("^[0-9][0-9][0-9][0-9][0-9]$",$part))
            {
                $format[$j] = 'zip';
            }
            elseif(!in_array('first_name',$format))
            {
                $format[$j] = 'first_name';
            }
            elseif(!in_array('last_name',$format))
            {
                $format[$j] = 'last_name';
            }
            elseif(!in_array('postal',$format))
            {
                $format[$j] = 'postal';
            }
            elseif(!in_array('city',$format))
            {
                $format[$j] = 'city';
            }
            $j ++;
        }
        $i ++;
    }

    // Get cols for this list.
    
    $hide = array('id', 'mask', 'local', 'domain', 'import_id');
    $c_options[] = 'email';
    
    foreach($cols AS $col)
    {
        if(in_array($col['Field'], $hide))
        {
            continue;
        }
        $c_options[] = $col['Field'];
    }
    
    $tpl->c_options = $c_options;
    $tpl->format    = $format;
    $tpl->example   = $example;
    $tpl->lines = $lines;
    $tpl->count = $count;
}
elseif(isset($_POST['submit_2']))
{
    $format = array();
    //print_r($_POST);
    $i = 0;
    foreach($_POST['from'] AS $k=>$v)
    {
        if($_POST['ignore'][$k] == '1')
        {
            $i ++;
            //print 'ignored';
            continue;
        }

        if($v == 'menu')
        {
            if(in_array($_POST['type'][$k],$format))
            {
                die("Field ".$_POST['type'][$k]." specified twice");
            }
            $format[$i] = $_POST['type'][$k];
        }
        else
        {
            if(!eregi('^[a-zA-Z0-9_]+$',$_POST['text_type'][$k]))
            {
                die("Bad field name entered go back");
            }
            elseif(in_array($_POST['text_type'][$k], $r_cols))
            {
                die("Field name entered already exists go back");
            }
            else
            {
                foreach($Lists->email_tables AS $table)
                {
                    $tbl = $Lists->email_table_prefix . $table;
                    
                    if($_POST['field_type'][$k] == "number")
                    {
                        $ft = "INT";
                    }
                    else 
                    {
                        $ft = "VARCHAR";
                    }
                    
                    $sql = "ALTER TABLE `{list}`.`$tbl` ADD `".$_POST['text_type'][$k]."` $ft( ".$_POST['field_len'][$k]." ) NOT NULL ;";
                    $r = $Lists->query_list($_POST['list_name'], $sql);
                    if($r === false)
                    {
                        print $sql;
                        die("Bad field name ".$_POST['text_type'][$k]);
                    }
                }
                $format[$i] = $_POST['text_type'][$k];
            }
        }

        $i ++;
    }

    //print_r($format);die;

    if(!in_array('email', $format))
    {
        die("Email field must be specified");
    }
    
    $format = serialize($format);
    $dedupe = serialize($_POST['check']);
    //print_r($_POST);
    if(get_magic_quotes_gpc() > 0)
    {
        $delim = stripslashes($_POST['delim']);
    }
    else
    {
        $delim = mysql_escape_string($_POST['delim']);
    }

    $overwite = 0;
    if($_POST['overwrite'] == 1)
    {
       $overwrite = 1; 
    }
    $description = mysql_escape_string($_POST['description']);
    $sql  = "INSERT INTO imports (`title`, `description`, `format`, `ts` ,`file` ,`state`, `list`, `delim`, `type_id`, `overwrite`, `dedupe`) VALUES ";
    $sql .= "('".$_POST['title']."', '$description', '".$format."', NOW(), '".$dir.$_POST['file']."', '0', '".$_POST['list_name']."', '".$delim."', '1', '$overwrite', '$dedupe');";

    $db->Execute($sql);
    print mysql_error();
    $tpl->finished = 1;
}

// Open a known directory, and proceed to read its contents
$files = array();

function doDir($dir)
{
    // Check someones not fooling with us
    $files = array();
    if (is_dir($dir))
    {
        // Its good open up
        if ($dh = opendir($dir))
        {
            // Loop files in system
            while (($file = readdir($dh)) !== false)
            {
                // We dont want .. or . paths
                if($file == '..' || $file == '.')
                {
                    continue;
                }
                // Its a dir read this bitch
                if(is_dir($dir . $file))
                {
                    $files[] = array('0'=>$file,'1'=>doDir($dir . $file .'/'));
                }
                // Plain ol file read into array
                else
                {
                    $files[] = $file;
                }
            }
            closedir($dh);
        }
    }

    return $files;
}

$t = doDir($import_dir);

$blah = array();
function makeSelect($array, $path = '')
{
    global $blah;

    foreach($array AS $file)
    {
        if(is_array($file))
        {
            if($path != '')
            {
                $sep = '/';
            }
            makeSelect($file[1], $path .$sep. $file[0]);
        }
        else
        {
            if($path != '')
            {
                $sep = '/';
            }
            $blah[] = $path .$sep. $file;
        }
    }
}

makeSelect($t);
//print_r($blah);

function checkCompress($file)
{
    if(ereg("(\.zip$)|(\.tar\.gz$)|(\.gz$)", $file))
    {
        return true;
    }

    return false;
}
//print_r($blah);
//print_r($files);
$tpl->dir   = $blah;
$tpl->files = $files;
$tpl->template = "cp/management/import-wizard.php";
$tpl->display('cp/layout.php');
?>
