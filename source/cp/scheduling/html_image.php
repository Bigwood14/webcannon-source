<?php
require_once('../../lib/control_panel.php');
require_once('public.php');

if (isset($_POST['submit']))
{
	foreach ($_FILES['image']['name'] as $k => $image)
	{
		if (empty($image))
			continue;

		$files 	= $_FILES['image'];
		$parts 	= explode('.', $files['name'][$k]);
		$ext 	= $parts[count($parts)-1];
		$dir 	= $config->values['site']['path'].'img/';
		$token 	= substr(md5(uniqid(rand(), true)), 0, 10);
		$file 	= $token.'.'.$ext;
		move_uploaded_file($files['tmp_name'][$k], $dir.$file);
		$tpl->file[] = $file;
	}
}

$tpl->template 	= 'cp/scheduling/html_image.tpl.php';
$tpl->display('cp/layout-pop.php');
?>
