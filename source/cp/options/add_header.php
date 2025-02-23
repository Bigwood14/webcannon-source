<?php
require '../../lib/control_panel.php';
require 'extra_content.php';

function show_add_header_form($msgs=null,$errors=null) {
    show_extra_content_form('/cp/options/add_header.php',
        'Add Header',null,$msgs,$errors); 
}

function do_add_header() {
    global $tpl;
    $name = $tpl->content_name = input($_POST['name']);
    $text = $tpl->text_content = input($_POST['text_content']);
    $html = $tpl->html_content = input($_POST['html_content']);

    if (!$name) { show_add_header_form(null,array('Name must be given')); }

    $id = add_header($name);
    set_content_data($id,array(
        'text' => $text,
        'html' => $html
    ));

    show_extra_content(array('Header added!'));
}

if (is_post()) {
    do_add_header();
}
else {
    show_add_header_form();
}
?>
