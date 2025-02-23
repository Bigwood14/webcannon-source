<?php
require_once('public.php');

class delivery_configuration
{
	public function get ($delivery_configuration_id)
	{
		$delivery_configuration_id 	= esc($delivery_configuration_id);
		$sql 						= "SELECT * FROM `delivery_configuration` WHERE `delivery_configuration_id` = '$delivery_configuration_id';";
		$row 						= row(query($sql));
		return $row;
	}

	public function insert ($name, $values)
	{
		if (empty($name))
			return false;

		$name 				= esc($name);
		$header				= esc($values['header']);
		$encoding_text		= esc($values['encoding_text']);
		$encoding_html		= esc($values['encoding_html']);
		$encoding_aol		= esc($values['encoding_aol']);
		$charset_head		= esc($values['charset_head']);
		$charset_text		= esc($values['charset_text']);
		$charset_html		= esc($values['charset_html']);
		$charset_aolt		= esc($values['charset_aol']);
		$boundry_prefix		= esc($values['boundry_prefix']);
		$boundry_postfix	= esc($values['boundry_postfix']);

		$sql 				= 'INSERT INTO `delivery_configuration` ';
		$sql 				.= '(`name`, `header`, `encoding_text`, `encoding_html`, `encoding_aol`, `boundry_prefix`, `boundry_postfix`, `charset_head`, `charset_text`, `charset_html`, `charset_aol`)';
		$sql 				.= ' VALUES ';
		$sql 				.= "('$name', '$header', '$encoding_text', '$encoding_html', '$encoding_aol', '$boundry_prefix', '$boundry_postfix', '$charset_head', '$charset_text', '$charset_html', '$charset_aol');";

		if (!$delivery_configuration_id = insert($sql))
			return false;

		return $delivery_configuration_id;
	}

	public function update ($delivery_configuration_id, $values)
	{
		if (!$this->get($delivery_configuration_id))
			return false;
	
		$fields 	= array();
		$fields[] 	= 'header';
		$fields[] 	= 'encoding_text';
		$fields[] 	= 'encoding_html';
		$fields[] 	= 'encoding_aol';
		$fields[] 	= 'boundry_prefix';
		$fields[] 	= 'boundry_postfix';
		$fields[] 	= 'charset_head';
		$fields[] 	= 'charset_text';
		$fields[] 	= 'charset_html';
		$fields[] 	= 'charset_aol';

		$i 			= 0;
		$sql_update = '';
		foreach ($fields as $field)
		{
			if (!empty($values[$field]))
			{
				$i++;
				$value 		= esc($values[$field]);
				$sql_update .= "`$field` = '$value',";
			}
		}

		if ($i == 0)
			return false;

		$sql_update 	= rtrim($sql_update, ',');
		$sql 			= "UPDATE `delivery_configuration` SET $sql_update WHERE `delivery_configuration_id` = '$delivery_configuration_id';";

		if (!query($sql))
			return false;

		return true;
	}
}
?>
