<?php
require '../../lib/control_panel.php';

class images
{
	public function __construct ()
	{
		global $tpl;

		$this->tpl = $tpl;
	}

	public function edit ()
	{
		global $config;

		$image_url	= @$_GET['image'];

		if (isset($_FILES['image']))
		{
			foreach ($_FILES['image']['name'] as $k => $image)
			{
				if (empty($image))
					continue;
		
				$files 	= $_FILES['image'];
				$parts 	= explode('.', $files['name'][$k]);
				$ext 	= $parts[count($parts)-1];
				$dir 	= $config->values['site']['path'].'img/';
				$parts 	= explode('/', $image_url);
				$file 	= $parts[count($parts)-1];
				move_uploaded_file($files['tmp_name'][$k], $dir.$file);
				$this->tpl->file[] = $file;
			}
		}

		$this->tpl->template 	= 'cp/management/images/edit.tpl.php';
		$this->tpl->display('cp/layout-pop.php');
	}

	public function view ()
	{
		$image 	= @$_GET['image'];

		$url 	= str_replace('{{dn}}', getDefaultDomain(), $image);
		header("Location: $url");
		die;
	}
}

$controller = new images;

switch (@$_GET['action'])
{
	case 'edit':
		$controller->edit();
		break;
	default:
		$controller->view();
		break;
}
?>
