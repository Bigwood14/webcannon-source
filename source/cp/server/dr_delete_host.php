<?php
require '../../lib/control_panel.php';
require 'direct_routing.php';

function do_action() {
    $id = input($_POST['id']);
    $host = get_dr_host($id);

    if (!$host) {
        show_dr_list_page(null,array('Unknown host'));
    }

    remove_dr_host($id);
    show_dr_list_page(array('Host removed!'));
}

function show_page($id,$msg=null,$err=null) {
    global $tpl;

    $host = get_dr_host($id);

    if (!$host) {
        show_dr_list_page(null,array('Unknown host'));
    }

    _dr_prepare_template();
    $tpl->host_id = $id;
    $tpl->title = "Delete DR Host: " . htmlspecialchars($host['address']);
    show_cp_page('/cp/server/dr_delete_host.php',$msg,$err);
}

if ($_POST['cancel']) {
    show_dr_list_page();
}
elseif (is_post()) {
    do_action();
}
else {
    show_page($_GET['id']);
}
?>
