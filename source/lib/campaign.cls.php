<?php
class campaign
{
	public function get ($campaign_id)
	{
		$campaign_id 	= esc($campaign_id);
		$sql 			= "SELECT * FROM `schedule` WHERE `id` = '$campaign_id';";
		$result 		= query($sql);

		return row($result);
	}
}
?>
