<?php
require_once('public.php');

function all_headers() {
    return all_rows(query("
        select * from extra_content
        where content_type = 'header'"));
}

function all_footers() {
    return all_rows(query("
        select * from extra_content
        where content_type = 'footer'"));
}

function rotation_get_random ($name)
{
	$content_info = get_content_info(false, $name);
	if (empty($content_info))
		return false;

	$content_data 	= get_content_data($content_info['id'], true);

	$content 		= $content_data[rand(0, (count($content_data)-1))]['data'];

	return $content;
}

function _add_content($name,$type) {
    return insert(sprintf("
        insert into extra_content (name, content_type)
        values ('%s', '%s')",esc($name),esc($type)));
}

function add_header($name) {
    return _add_content($name,'header');
}

function add_footer($name) {
    return _add_content($name,'footer');
}

function content_add_rotated ($name)
{
	return _add_content($name, 'rotated');
}

function remove_content($id) {
    query(sprintf("
        delete from extra_content
        where id = %d",$id));
    query(sprintf("
        delete from extra_content_data
        where content_id = %d",$id));
}

function get_content_info($content_id = false, $name = false)
{
	if (empty($content_id) && empty($name))
		return false;

	$sql = "SELECT * FROM `extra_content` WHERE ";

	if (!empty($content_id))
	{
		$content_id 	= esc($content_id);
		$sql 			.= "`id` = $content_id AND ";
	}

	if (!empty($name))
	{
		$name 			= esc($name);
		$sql 			.= "`name` = '$name' AND ";
	}

	$sql 	= rtrim($sql, 'AND ');

    return row(query($sql));
}

function add_content_data($content_id,$type,$data) {
    query(sprintf("
        insert into extra_content_data (
            content_id, content_format, data)
        values (%d, '%s', '%s')",
        $content_id,esc($type),esc($data)));
}

function update_content_data($content_id,$type,$data) {
    query(sprintf("
        update extra_content_data
        set data = '%s'
        where content_id = %d
        and content_format = '%s'",
        esc($data),$content_id,esc($type)));
}

function remove_content_data($content_id,$type) {
    query(sprintf("
        delete from extra_content_data
        where content_id = %d
        and content_format = '%s'",
        $content_id,esc($type)));
}

function get_content_data($content_id, $no_reindex = false) {
    $contents = array();
    $res = query(sprintf("
        select * from extra_content_data
        where content_id = %d",$content_id));
	
	if ($no_reindex == true)
		return all_rows($res);

    while ($row = row($res)) {
        $contents[$row['content_format']] = $row;
    }
    return $contents;
}

function set_content_data($content_id,$data) {
    $current_data = get_content_data($content_id);
    foreach ($data as $type => $content) {
        if (!strlen(trim($content))) {
            if ($current_data[$type]) {
                remove_content_data($content_id,$type);
            }
        }
        else {
            if ($current_data[$type]) {
                update_content_data($content_id,$type,$content);
            }
            else {
                add_content_data($content_id,$type,$content);
            }
        }
    }
}

function set_content_default($id) {
    $content = get_content_info($id);
    query(sprintf("
        update extra_content
        set is_default = 0
        where content_type = '%s'",
        esc($content['content_type'])));
    query(sprintf("
        update extra_content
        set is_default = 1
        where id = %d",$id));
}

function show_extra_content_page($page,$msgs=null,$errors=null) {
    global $tpl;
    $tpl->styles[] = 'extra_content.css';
    show_cp_page($page,$msgs,$errors);
}

function show_extra_content_form($action,$title,$id=null,$msgs=null,$errors=null) {
    global $tpl;
    $tpl->action = $action;
    $tpl->title = $title;
    $tpl->content_id = $id;
    show_extra_content_page('cp/options/extra_content_form.php',$msgs,$errors);
}

function show_extra_content($msgs=null,$errors=null) {
    global $tpl;
    $tpl->headers = all_headers();
    $tpl->footers = all_footers();
    show_extra_content_page('cp/options/header-footer.php',$msgs,$errors);
}

function redirect_to_extra_content() {
    redirect('/cp/options/header-footer.php');
}
?>
