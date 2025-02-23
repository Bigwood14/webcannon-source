<?php
require '../../lib/control_panel.php';
require 'background.php';

$msgs = array();
$errors = array();

if (is_post()) {
    $msgs[] = 'Processes will be killed in approximately one minute.';
    schedule_background_command('killall celiberod');
}

show_cp_page('cp/extra/kill.php',$msgs,$errors);
?>
