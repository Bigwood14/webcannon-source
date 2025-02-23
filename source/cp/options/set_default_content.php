<?php
require '../../lib/control_panel.php';
require 'extra_content.php';

function show_set_default_form($id,$msgs=null,$errors=null) {
    global $tpl;

    $content = get_content_info($id);
    if (!$content) {
        show_extra_content(null,array("Can't find content with id=$id"));
    }

    $tpl->content_id = $id;
    $type = $content['content_type'] == 'header' ? 'Header' : 'Footer';
    $tpl->type = $content['content_type'];
    $tpl->title = "Set Default $type: " . htmlspecialchars($content['name']);

    show_extra_content_page('/cp/options/set_default_content.php',$msgs,$errors);
}

function do_set_default() {
    global $tpl;

    $id   = input($_POST['id']);

    $content = get_content_info($id);
    if (!$content) {
        show_extra_content(null,array("Can't find content with id=$id"));
    }

    set_content_default($id);
    show_extra_content(array('Default set!'));
}

if ($_POST['cancel']) {
    redirect_to_extra_content();
}
elseif (is_post()) {
    do_set_default();
}
else {
    show_set_default_form($_GET['id']);
}
?>
