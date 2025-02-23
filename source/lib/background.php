<?php
function _schedule_background_job($type,$body) {
    return insert(sprintf("
        INSERT INTO commands (command, date, state, type)
        VALUES ('%s',NOW(),0,'%s')",esc($body),esc($type)));
}

function schedule_background_query($query) {
    return _schedule_background_job('mysql',$query);
}

function schedule_background_command($cmd) {
    return _schedule_background_job('shell',$cmd);
}
?>
