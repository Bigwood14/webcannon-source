<?php
require_once('../lib/control_panel.php');
require_once('draft.cls.php');

$from = new draft_from();

$froms = array();
$froms[] = 'blah blah <local@ayhoo.info>';

$from->create(1, $froms, true);
?>
