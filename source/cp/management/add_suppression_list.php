<?php
require '../../lib/control_panel.php';
require 'suppression_lists.php';

function import_dir($path=null) {
    global $config;
    $base = $config->values['site']['upload_patch'];
    $imports_dir = rtrim($base,'/') . '/import';
    if ($path === null) {
        return $imports_dir;
    }
    else {
        return $imports_dir . '/' . ltrim($path,'/');
    }
}

function new_import_file($name) {
    $dot = strrpos($name,'.');
    $base = substr($name,0,$dot);
    $ext = substr($name,$dot+1);
    $date = strftime('%d-%b-%y');

    $file = null;
    for ($i = 0; ; $i++) {
        switch ($i) {
            case 0:  $file = $name; break;
            case 1:  $file = "$base.$date.$ext"; break;
            default: $file = "$base.$date.$i.$ext"; break;
        }
        if (!file_exists(import_dir($file))) { return $file; }
    }
}

function extract_import_file($file)
{

    $file 	= import_dir($file);
    $dot 	= strrpos($file,'.');
    $base 	= substr($file,0,$dot);

    if (!file_exists($file))
		return false;

    if (!mkdir($base))
		return false;

    $output = array();
    $cmd 	= sprintf('unzip %s -d %s', escapeshellarg($file), escapeshellarg($base));

	exec($cmd, $output);

    $files = array();
    foreach ($output as $line)
	{
        $matches = array();

        if (preg_match('/^\s*(?:inflating|extracting):\s+(\S+.*)/',$line,$matches))
            $files[] = $matches[1];
    }

    if (!$files)
		return false;

    $biggest 		= null;
    $biggest_size 	= 0;

	foreach ($files as $file)
	{
        $fs = filesize($file);
		var_dump($fs, $file);
        if ($fs > $biggest_size)
		{
            $biggest 		= $file;
            $biggest_size 	= $fs;
        }
    }

	$return = substr($biggest, strlen(import_dir())+1);
    return $return;
}

function do_suppression_list_addition() {
    $file = $_FILES['file'];
    $append_list_id = $_POST['appendee_list_id'];
    $new_list_name = $_POST['new_list_name'];
    $file_contents = $_POST['file_contents'];
    $list_id = null;
    $msgs = array();
    $errors = array();

    if (strlen($append_list_id) && !find_suppression_list($append_list_id)) {
        $errors[] = "Couldn't find the specified suppression list";
    }
    elseif (strlen($append_list_id) && strlen($new_list_name)) {
        $errors[] = "Can't select a list to append to and also create a new list";
    }
    elseif (!strlen($append_list_id) && !strlen($new_list_name)) {
        $errors[] = "Must either select a list to append to or provide a new list name";
    }

    if (!$file || $file['error'] == 4) {
        $errors[] = 'Must specify a file to upload';
    }
    elseif ($file['error'] != 0) {
        $errors[] = 'File upload failed';
    }

    if ($file_contents != 'emails' && $file_contents != 'domains') {
        $errors[] = "Specify either emails or domains for the file's contents";
    }

    if ($errors) {
        show_suppression_lists($msgs,$errors);
        return;
    }


    $import_name = new_import_file(basename($file['name']),$file['tmp_name']);
    if (!move_uploaded_file($file['tmp_name'],import_dir($import_name))) {
        $errors[] = "File upload failed";
    }

    if (preg_match('/\.zip$/i',$import_name)) {
        if (!$import_name = extract_import_file($import_name)) {
            $errors[] = 'Zip file extraction failed';
        }
    }

    if ($errors) {
        show_suppression_lists($msgs,$errors);
        return;
    }

    $list_id = $append_list_id;
    if ($new_list_name) {
        $list_id = create_suppression_list($new_list_name);
        if (!$list_id) {
            $errors[] = "Unable to create new suppression list";
        }
    }

    if ($errors) {
        show_suppression_lists($msgs,$errors);
        return;
    }

    import_suppression_list($import_name,$list_id,$file_contents);
    $msgs[] = "Scheduled import of suppression list file $import_name";
    show_suppression_lists($msgs,$errors);
}

if (is_post()) { do_suppression_list_addition(); }
else { redirect_to_suppression_lists(); }
?>
