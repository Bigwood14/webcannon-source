<div id="contenttable">

<form action="<?php echo url_for('/cp/management/create_suppression_list.php'); ?>" method="post">
<table width="100%">
    <tr><th colspan="2">Create List</th></tr>
    <tr>
        <td><input type="text" name="list_name" value=""></td>
        <td><input type="submit" value="Create"></td>
    </tr>
</table>
</form>
<br>

<form action="<?php echo url_for('/cp/management/delete_suppression_list.php'); ?>" method="post">
<table width="100%" border="0" cellspacing="0" cellpadding="4">
    <tr>
        <th colspan="2">Delete List</th>
    </tr>

    <tr>
        <td>
            <select name="list">
                <option value="">Select a list</option>
            <?php foreach ($template->lists as $list) { ?>
                <option value="<?php echo $list['sup_list_id']; ?>"><?php echo htmlspecialchars($list['title']); ?></option>
            <?php } // foreach(list) ?>
				<option value="all">-- delete all --</option>
            </select>
        </td>
        <td>
            <input type="submit" value="Delete">
        </td>
    </tr>
</table>
</form>

<br>

<form enctype="multipart/form-data" action="<?php echo url_for('/cp/management/add_suppression_list.php'); ?>" method="POST">
<table width="100%" border="0" cellspacing="0" cellpadding="4">
    <tr>
        <th colspan="2">Add File</th>
    </tr>

    <tr>
        <td>Append to existing list</td>
        <td>
            <select name="appendee_list_id">
                <option value="">Select a list</option>
            <?php foreach($template->lists as $list) { ?>
                <option value="<?php echo $list['sup_list_id']; ?>"><?php echo htmlspecialchars($list['title']); ?></option>
            <?php } //foreach(list) ?>
            </select>
        </td>
    </tr>

    <tr>
        <td><strong><em>-or-</em></strong> Create a new list</td>
        <td><input type="text" name="new_list_name"></td>
    </tr>

    <tr>
        <td>Choose a file to upload</td>
        <td><input type="file" name="file"></td>
    </tr>

    <tr>
        <td>File contents</td>
        <td>
            <input type="radio" name="file_contents" value="emails">Emails
            <input type="radio" name="file_contents" value="domains">Domains
        </td>
    </tr>

    <tr>
        <td colspan="2"><input type="submit" value="Add"></td>
    </tr>
</table>
</form>
<br /><br />

<table width="100%" border="0" cellspacing="0" cellpadding="4">
    <tr>
        <th>Suppression Lists</th>
    </tr>

        <?php foreach($template->lists as $list) { ?>
               <tr><td><a onclick="window.open(this.href, 'count', 'width=100,height=100'); return false;" href="suppression_list_count.php?sup_list_id=<?php echo $list['sup_list_id']; ?>"><?php echo htmlspecialchars($list['title']); ?></a></td></tr>
        <?php } //foreach(list) ?>
</table>

</div>
