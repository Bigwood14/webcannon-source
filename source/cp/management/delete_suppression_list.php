<?php
require '../../lib/control_panel.php';
require 'suppression_lists.php';

function validate_request()
{
    if (!is_post())
		return false;

    if (!ctype_digit($_POST['list']) && $_POST['list'] != 'all')
		return false;

    return true;
}

if (validate_request())
{
	if ($_POST['list'] == 'all')
	{
		query('TRUNCATE TABLE `email_to_sup`;');
		query('TRUNCATE TABLE `supression_lists`;');
	}
	else
		delete_suppression_list($_POST['list']);
}

redirect_to_suppression_lists();
?>
