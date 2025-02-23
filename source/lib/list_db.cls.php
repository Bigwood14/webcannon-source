<?php
class list_db
{
	public function get ($list_id)
	{
		$list_id 	= esc($list_id);

		$sql 	= "SELECT * FROM `list` WHERE `list_id` = '$list_id';";
		return row(query($sql));
	}

	public function draft_create ($draft_id, $list_ids, $skips = false, $maxs = false)
	{
		if (!is_array($list_ids))
			$list_ids = array($list_ids);

		if (!$this->draft_delete($draft_id))
			return false;

		foreach ($list_ids as $list_id)
		{
			if (!empty($skips[$list_id]))
				$skip = $skips[$list_id];
			else
				$skip = 0;

			if (!empty($maxs[$list_id]))
				$max = $maxs[$list_id];
			else
				$max = 0;


			$this->draft_insert($draft_id, $list_id, $skip, $max);
		}

		return true;
	}

	public function draft_delete ($draft_id)
	{
		$draft_id 	= esc($draft_id);

		$sql 		= "DELETE FROM `msg_to_list` WHERE `msg_id` = '$draft_id'";

		return query($sql);
	}

	public function draft_insert ($draft_id, $list_id, $skip = 0, $max = 0)
	{
		if (!$list_data = $this->get($list_id))
			return false;

		$draft_id 	= esc($draft_id);
		$list_id 	= esc($list_id);
		$skip 		= esc($skip);
		$max 		= esc($max);

		$sql = "INSERT INTO `msg_to_list` (`msg_id`, `list_id`, `skip`, `max`) VALUES ('$draft_id', '$list_id', '$skip', '$max');";

		return insert($sql);
	}

	public function draft_get ($draft_id)
	{
		$draft_id 	= esc($draft_id);
		$sql 		= "SELECT * FROM `msg_to_list` WHERE `msg_id` = '$draft_id';";

		return all_rows(query($sql));
	}
}
?>
