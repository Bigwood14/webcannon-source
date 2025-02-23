<?php
require '../../lib/control_panel.php';
require 'background.php';

$msgs = array();
$errors = array();

if (is_post()) {
    $time = $_POST['time'];
    $m = array();
    $pattern = "!
        ^\s*(\d\d?)/       # month
         (\d\d?)/          # date
         (\d\d(?:\d\d)?)\s # year
         (\d\d?):          # hour
         (\d\d)\s*$        # minute
    !x";

    if (!preg_match($pattern,$time,$m)) {
        $errors[] = "Invalid time format given";
    }
    else {
        $unixtime = sprintf("%02d%02d%02d%02d%d",$m[1],$m[2],$m[4],$m[5],$m[3]);
        schedule_background_command("date $unixtime");
        $msgs[] = "Time change has been scheduled. " .
                  "It should take a minute or so to take effect.";
    }
}

$tpl->current_time = strftime("%m/%d/%Y %H:%M");

show_cp_page('cp/extra/time.php',$msgs,$errors);
?>
