<?php
function all_dr_hosts() {
    return all_rows(query("select * from dr_hosts"));
}

function add_dr_host($address,$password) {
    return insert(sprintf("
        insert into dr_hosts (address,password)
        values ('%s','%s')",esc($address),esc($password)));
}

function get_dr_host($id) {
    return row(query(sprintf("
        select * from dr_hosts
        where id = %d",$id)));
}

function remove_dr_host($id) {
    query(sprintf("
        delete from dr_hosts
        where id = %d",$id));
}

function update_dr_host($id,$address,$password=null) {
    if (isset($password)) {
        query(sprintf("
            update dr_hosts
            set address = '%s',
                password = '%s'
            where id = %d",
            esc($address),esc($password),$id));
    }
    else {
        query(sprintf("
            update dr_hosts
            set address = '%s'
            where id = %d",
            esc($address),$id));
    }
}

function _dr_prepare_template() {
    global $tpl;
    $tpl->styles[] = 'dr.css';
}

function show_dr_host_form($action,$id=null,$msgs=null,$errors=null) {
    global $tpl;
    _dr_prepare_template();

    if (isset($id)) {
        $host = get_dr_host($id);
        if (!$host) {
            show_dr_list_page(null,array("Unknown host"));
        }

        $tpl->host_id = $id;
        $tpl->title = 'Edit DR Host: ' . htmlspecialchars($host['address']);
        $tpl->address = $host['address'];
    }
    else {
        $tpl->title = 'Add DR Host';
    }

    show_cp_page('cp/server/dr_host_form.php',$msgs,$errors);
}

function show_dr_list_page($msgs=null,$errors=null) {
    global $tpl;
    _dr_prepare_template();
    $tpl->hosts = all_dr_hosts();
    show_cp_page('cp/server/dr.php',$msgs,$errors);
}

function dr_host_status_class($host) {
    return 'dr_' . $host['status'];
}
?>
