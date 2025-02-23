<?php
require '../../lib/control_panel.php';
require 'suppression_lists.php';

function do_suppression_list_creation() {
    $msgs = array();
    $errors = array();

    $list_name = $_POST['list_name'];

    $list_id = create_suppression_list($list_name);
    if (!$list_id) {
        $errors[] = 'Unable to create new suppression list';
    }
    else {
        $msgs[] = 'Suppression list created';
    }

    show_suppression_lists($msgs,$errors);
}

if (is_post()) { do_suppression_list_creation(); }
else { redirect_to_suppression_lists(); }
?>
