<?php
require '../../lib/control_panel.php';
require 'direct_routing.php';

function do_action() {
    global $tpl;
    $address = $tpl->address = trim(input($_POST['address']));
    $password = input($_POST['password']);
    $passconf = input($_POST['password_confirmation']);

    $errors = array();
    if (!strlen($address)) { $errors[] = 'Must supply an address'; }
    if ($password != $passconf) { $errors[] = 'Password must be the same both times'; }

    if ($errors) { show_page(null,$errors); }

    add_dr_host($address,$password);

    show_dr_list_page(array('Host added!'));
}

function show_page($msg=null,$err=null) {
    show_dr_host_form('/cp/server/dr_add_host.php',null,$msg,$err);
}

if (is_post()) {
    do_action();
}
else {
    show_page();
}
?>
