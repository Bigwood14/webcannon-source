<?php
function format_tracked_link($link) {
    return 'http://' . getDefaultDomain() . '/link.php?id=' . $link['id'] . '&email={email}';
}

function get_tracked_links_for_message($msg_id) {
    if (!ctype_digit($msg_id)) { return false; }
    return all_rows(query("
        select *
        from tracked_links
        where msg_id = $msg_id"));
}

function get_tracked_link($id) {
    if (!ctype_digit($id)) { return false; }
    return row(query("
        select * 
        from tracked_links
        where id = $id"));
}

function add_tracked_link($msg,$link,$action,$target) {
    return insert(sprintf("
        insert into tracked_links (msg_id, url, action, target)
        values (%d,'%s','%s','%s')",
        $msg,esc($link),esc($action),esc($target)));
}
?>
