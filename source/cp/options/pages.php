<?php
$auth_is_admin = true;
require_once('../../lib/control_panel.php');

class page
{
	public function __construct ()
	{
		global $tpl;

		$this->tpl 	= $tpl;
	}

	private function create ($url, $type, $content)
	{
		$url 		= esc($url);
		$type 		= esc($type);
		$content 	= esc($content);

		$sql 		= "INSERT INTO `pages` (`url`, `type`, `content`) VALUES ('$url', '$type', '$content');";

		if (!$page_id = insert($sql))
			return false;

		return $page_id;
	}

	private function select ($page)
	{
		global $config;

		if ($page == 'index.php')
		{
			$content = file_get_contents($config->values['site']['path'].'no-web/templates/index.php');
			$row 			= array();
			$row['content'] = $content;
			$row['page_id'] = 'index.php';
			$row['url']		= 'index.php';

			$this->tpl->select = $row;
		}
		else
		{
			$page_id 	= esc($page);
			$sql 		= "SELECT * FROM `pages` WHERE `page_id` = '$page';";
			$row 		= row(query($sql));

			$this->tpl->select = $row;
		}
	}

	private function update ($page_id, $content)
	{
		global $config;

		$content 	= str_replace('http://{{dn}}', '', $content);

		if ($page_id == 'index.php')
		{
			$fp = fopen($config->values['site']['path'].'no-web/templates/index.php', 'w');
			fwrite($fp, stripslashes($content));
			fclose($fp);
		}
		else
		{
			$page 		= esc($page_id);
			$content 	= esc($content);

			$sql 		= "UPDATE `pages` SET `content` = '$content' WHERE `page_id` = '$page_id';";
			query($sql);
		}
	}

	private function delete ($page_id)
	{
		$page_id 	= esc($page_id);
		$sql 		= "DELETE FROM `pages` WHERE `page_id` = '$page_id';";
		query($sql);
	}

	public function index ()
	{
		if (isset($_POST['create']))
			$this->create($_POST['url'], 2, '');

		if (isset($_POST['delete']))
			$this->delete($_POST['page']);

		if (isset($_POST['update']))
			$this->update($_POST['page_id'], $_POST['content']);

		if (isset($_POST['select']))
			$this->select($_POST['page']);

		
		$sql 	= "SELECT * FROM `pages`;";
		$result = query($sql);
		$pages 	= array();

		while ($row = row($result))
			$pages[] = $row;	

		$this->tpl->pages 		= $pages;
		$this->tpl->template 	= "cp/options/pages.tpl.php";
		$this->tpl->display('cp/layout.php');
	}
}

$page = new page;
$page->index();
?>
