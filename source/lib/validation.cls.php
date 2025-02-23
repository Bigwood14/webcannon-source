<?php
class validation
{
	static public function is_domain ($string)
	{
		if (empty($string))
			return false;

		if (preg_match('/^([a-z][a-z0-9\-]+(\.|\-*\.))+[a-z]{2,6}$/i', $string))
			return true;

		return false;
	}

	static public function is_email ($string)
	{
		if (empty($string))
			return false;

		if (preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$|', $string))
			return true;

		return false;
	}
}
?>
