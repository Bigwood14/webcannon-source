<?php
require '../../lib/control_panel.php';
require 'direct_routing.php';

function do_action() {
    global $tpl;
    $id = $tpl->host_id = input($_POST['id']);
    $address = $tpl->address = trim(input($_POST['address']));
    $password = input($_POST['password']);
    $passconf = input($_POST['password_confirmation']);

    $host = get_dr_host($id);
    if (!$host) {
        show_dr_list_page(null,array('Unknown host'));
    }

    $errors = array();
    if (!strlen($address)) { $errors[] = 'Must supply an address'; }
    if (strlen($password) &&
        $password != $passconf) { $errors[] = 'Password must be the same both times'; }

    if ($errors) { show_page($id,null,$errors); }

    if (strlen($password)) {
        update_dr_host($id,$address,$password);
    }
    else {
        update_dr_host($id,$address);
    }

    show_dr_list_page(array('Host updated!'));
}

function show_page($id,$msg=null,$err=null) {
    global $tpl;

    $host = get_dr_host($id);
    if (!$host) {
        show_dr_list_page(null,array('Unknown host'));
    }

    $tpl->host_id = $id;
    $tpl->address = $host['address'];

    show_dr_host_form('/cp/server/dr_edit_host.php',$id,$msg,$err);
}

if (is_post()) {
    do_action();
}
else {
    show_page($_GET['id']);
}
?>
