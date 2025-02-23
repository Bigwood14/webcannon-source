<?php
class ip
{
	public function __construct ()
	{
	}

	public function get ($ip_id)
	{
		$ip_id 	= esc($ip_id);

		$sql 	= "SELECT * FROM `server_to_ip` WHERE `ip_id` = '$ip_id';";

		return row(query($sql));
	}

	public function draft_create ($draft_id, $ip_ids)
	{
		if (!is_array($ip_ids))
			$ip_ids = array($ip_ids);
		
		if (!$this->draft_delete($draft_id))
			return false;

		foreach ($ip_ids as $ip_id)
		{
			$this->draft_insert($draft_id, $ip_id);
		}

		return true;
	}

	public function draft_delete ($draft_id)
	{
		$draft_id 	= esc($draft_id);

		$sql 		= "DELETE FROM `msg_to_ip` WHERE `draft_id` = '$draft_id'";

		return query($sql);
	}

	public function draft_insert ($draft_id, $ip_id)
	{
		if (!$ip_data = $this->get($ip_id))
			return false;

		$draft_id 	= esc($draft_id);
		$ip_id 		= esc($ip_id);
		$domain 	= esc($ip_data['domain']);

		$sql = "INSERT INTO `msg_to_ip` (`draft_id`, `ip_id`, `domain`) VALUES ('$draft_id', '$ip_id', '{$domain}');";

		return query($sql);
	}

	public function draft_get ($draft_id)
	{
		$draft_id 	= esc($draft_id);
		$sql 		= "SELECT * FROM `msg_to_ip` WHERE `draft_id` = '$draft_id';";

		return all_rows(query($sql));
	}
}
?>
