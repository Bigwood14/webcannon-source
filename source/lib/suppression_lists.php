<?php
require 'background.php';

function redirect_to_suppression_lists() {
    redirect('/cp/management/supression-lists.php');
}

function find_suppression_list($list_id) {
    if (!ctype_digit($list_id)) { return false; }
    return query("
        SELECT * FROM supression_lists
        WHERE sup_list_id = $list_id");
}

function create_suppression_list($title) {
    return insert(sprintf("
        INSERT INTO supression_lists (title)
        VALUES ('%s')",esc($title)));
}

function import_suppression_list($file,$list_id,$type='emails') {
    $import_type_id = null;
    if ($type == 'emails') {
        $import_type_id = 2;
    }
    elseif ($type == 'domains') {
        $import_type_id = 5;
    }
    else {
        return false;
    }

    return insert(sprintf("
        INSERT INTO imports
            (title,description,format,ts,file,state,list,type,type_id)
        VALUES 
            ('','','',NOW(),'%s','0','','%s',%d)",
        esc($file),esc($list_id),$import_type_id));
}

function show_suppression_lists($msgs=null,$errors=null) {
    global $tpl;
    $tpl->lists = find_suppression_lists();
	$tpl->scripts[] = 'LoadVars.js';
	$tpl->scripts[] = 'BytesUploaded.js';
    show_cp_page('cp/management/supression-lists.php',$msgs,$errors);
}

function count_suppression_list ($sup_list_id)
{
	$sql = "SELECT COUNT(`email`) AS `count` FROM `email_to_sup` WHERE `sup_list_id` = '$sup_list_id';";
	$row = row(query($sql));
	return $row['count'];
}

function delete_suppression_list($list_id) {
    /* background version
    $cmd = "celibero:" .
           "DELETE FROM email_to_sup WHERE sup_list_id = $list_id:" .
           "DELETE FROM supression_lists WHERE sup_list_id = $list_id";
    schedule_background_query($cmd);

    query("
        UPDATE supression_lists 
        SET state = 1 
        WHERE sup_list_id = $list_id");
     */

    /* realtime version */
    query("DELETE FROM email_to_sup WHERE sup_list_id = $list_id");
    query("DELETE FROM supression_lists WHERE sup_list_id = $list_id");
}

function find_suppression_lists() {
    return all_rows(query('
        SELECT * FROM supression_lists 
        WHERE state <> 1
        ORDER BY title'));
}
?>
