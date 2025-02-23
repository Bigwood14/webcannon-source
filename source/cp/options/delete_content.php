<?php
require '../../lib/control_panel.php';
require 'extra_content.php';

function show_delete_content_form($id,$msgs=null,$errors=null) {
    global $tpl;

    $content = get_content_info($id);
    if (!$content) {
        show_extra_content(null,array("Can't find content with id=$id"));
    }

    $tpl->content_id = $id;
    $type = $content['content_type'] == 'header' ? 'Header' : 'Footer';
    $tpl->type = $content['content_type'];
    $tpl->title = "Delete $type: " . htmlspecialchars($content['name']);

    show_extra_content_page('/cp/options/delete_content.php',$msgs,$errors);
}

function do_delete_content() {
    global $tpl;

    $id   = input($_POST['id']);

    $content = get_content_info($id);
    if (!$content) {
        show_extra_content(null,array("Can't find content with id=$id"));
    }

    remove_content($id);
    show_extra_content(array('Content removed!'));
}

if ($_POST['cancel']) {
    redirect_to_extra_content();
}
elseif (is_post()) {
    do_delete_content();
}
else {
    show_delete_content_form($_GET['id']);
}
?>
