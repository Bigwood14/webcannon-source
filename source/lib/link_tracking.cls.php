<?php
class link_tracking
{
	public $actions = array();

	public function __construct ()
	{
		$actions = array();
		$actions[1] = 'Simple Masking';
		$actions[2] = 'Subscribe';
		$actions[3] = 'Unsubscribe';

		$this->actions = $actions;
	}

	public function find ($body_text = '', $body_html = '', $body_aol = '', $images = false)
	{
		$image_links 	= array();

		if (!empty($body_html))
			$image_links 	= array_merge($image_links, $this->find_html_links($body_html, true));
		if (!empty($body_aol))
			$image_links 	= array_merge($image_links, $this->find_html_links($body_aol, true));

		$image_links 	= array_unique($image_links);

		if ($images === true)
			return $image_links;

		$normal_links 	= array();

		foreach (array($body_text, $body_html, $body_aol) as $body)
			$normal_links = array_merge($normal_links, $this->find_normal_links($body));

		foreach ($normal_links as $index => $normal_link)
		{
			if (in_array($normal_link, $image_links))
				unset($normal_links[$index]);
		}

		$normal_links 	= array_unique($normal_links);

		return array_values($normal_links);
	}

	protected function find_html_links ($body, $img = true)
	{
		$links 	= array();
		preg_match_all("/<([^>]*)>/i", $body, $matches);

		if (empty($matches[1]))
			return $links;

		foreach ($matches[1] as $match)
		{
			if ($img === true)
			{
				if (!eregi("src[\n\r ]*=", $match) && !eregi("img", $match))
					continue;
			}

			$reg_exp 	= "/(http(s)?:\/\/([^\\n\\r\"'\s>]*))[\"'\s>]?/i";
            preg_match($reg_exp, $match, $matches2);
			
			if (!empty($matches2[1]))
				$links[] = $matches2[1];
		}

		return array_unique($links);
	}

	protected function find_normal_links ($body)
	{
		$links = array();
		preg_match_all("/(http(s)?:\/\/([^\\n\\r\"'\s>]*))[\"'\s>]?/i", $body, $matches);

		if (empty($matches[1]))
			return $links;

		foreach($matches[1] as $match)
		{
			$match 		= str_replace("</a", "", $match);
			$links[] 	= $match;
		}

		return array_unique($links);
	}

	public function delete ($draft_id)
	{
		$draft_id 	= esc($draft_id);

		$sql 		= "DELETE FROM `tracked_link` WHERE `draft_id` = '$draft_id';";
		return query($sql);
	}

	public function insert ($draft_id, $url, $action, $target)
	{
		$draft_id 	= esc($draft_id);
		$url 		= esc($url);
		$action 	= esc($action);
		$target 	= esc($target);

		$sql 		= "INSERT INTO `tracked_link` (`draft_id`, `url`, `action`, `list_id`) VALUES ('$draft_id', '$url', '$action', '$target');";

		return insert($sql);
	}

	public function draft_get ($draft_id)
	{
		$draft_id 	= esc($draft_id);

		$sql 		= "SELECT * FROM `tracked_link` WHERE `draft_id` = '$draft_id';";
		$result 	= query($sql);

		$link 		= array();
		$action 	= array();
		$target 	= array();
		$id 		= array();

		while ($row = row($result))
		{
			$link[] 	= $row['url'];
			$action[]	= $row['action'];
			$target[]	= $row['list_id'];
			$id[]		= $row['tracked_link_id'];
		}

		return array('link' => $link, 'action' => $action, 'target' => $target);
	}

	public function get ($tracked_link_id)
	{
		$tracked_link_id 	= esc($tracked_link_id);
		$sql 				= "SELECT * FROM `tracked_link` WHERE `tracked_link_id` = '$tracked_link_id';";
		return row(query($sql));
	}

	public function format ($tracked_link_id)
	{
		//$tracked_link_id 	= esc($tracked_link_id);

		//if (!$link_info = $this->get($tracked_link_id))
		//	return false;

		$char 		= 97+rand(0,24);

		$format 	= 'http://{{dn}}/%d{{02}}%c{{03}}';
		return sprintf($format, $tracked_link_id, $char);
	}

	public function parse ($link)
	{
		preg_match('/([0-9]+)([a-z]{2})([0-9]+)([a-z]){1}([0-9]+)$/iU', $link, $matches);

		if (empty($matches))
		{
			// try to just grab first digits - might be link id 
			preg_match('/([0-9]+)[^0-9]/iU', $link, $matches);
			
			if (empty($matches[1]))
				return false;

			return array('link_id' => $matches[1]);
		}

		$values 			= array();
		$values['link_id'] 	= $matches[1];
		$values['table'] 	= $matches[2];
		$values['user_id'] 	= $matches[3];
		$values['list_id'] 	= $matches[5];

		return $values;
	}
}
?>
