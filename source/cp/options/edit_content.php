<?php
require '../../lib/control_panel.php';
require 'extra_content.php';

function show_edit_content_form($id,$msgs=null,$errors=null) {
    global $tpl;

    $content = get_content_info($id);
    if (!$content) {
        show_extra_content(null,array("Can't find content with id=$id"));
    }

    $data = get_content_data($id);
    $tpl->content_id   = $id;
    $tpl->text_content = $data['text']['data'];
    $tpl->html_content = $data['html']['data'];

    $type = $content['content_type'] == 'header' ? 'Header' : 'Footer';
    $title = "Edit $type: " . htmlspecialchars($content['name']);

    show_extra_content_form('/cp/options/edit_content.php',
        $title,$id,$msgs,$errors);
}

function do_edit_content() {
    global $tpl;

    $id   = input($_POST['id']);

    $content = get_content_info($id);
    if (!$content) {
        show_extra_content(null,array("Can't find content with id=$id"));
    }

    $text = input($_POST['text_content']);
    $html = input($_POST['html_content']);

    set_content_data($id,array(
        'text' => $text,
        'html' => $html
    ));

    show_extra_content(array('Content updated!'));
}

if (is_post()) {
    do_edit_content();
}
else {
    show_edit_content_form($_GET['id']);
}
?>
