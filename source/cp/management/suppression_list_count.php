<?php
require '../../lib/control_panel.php';
require 'suppression_lists.php';

if (!empty($_GET['sup_list_id']))
	print count_suppression_list($_GET['sup_list_id']);
?>
