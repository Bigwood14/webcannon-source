<div id="contentbox">
  <h1>Supression Lists</h1>
</div>
<br />
<?php
if(isset($template->c))
{
?>
<div id="contenttable">
<table>
  <thead>
    <tr>
      <th>Added</th>
      <th>Duplicate</th>
      <th>Invalid</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?php echo $template->c['added']?></td>
      <td><?php echo $template->c['duplicate']?></td>
      <td><?php echo $template->c['invalid']?></td>
    </tr>
  </tbody>
</table>
</div>
<br />
<?php
}
?>  
<form action="/cp/management/supression-lists-add.php" method="post" enctype="multipart/form-data" >
<div id="contenttable">

<table width="500">
  <thead>
    <tr>
      <th colspan="2">Add Emails to Supression List</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>File:</td>
      <td><input type="file" name="filename" /></td>
    </tr>
    <tr>
      <td>List:</td>
      <td><select name="list"><?php echo $template->options?></select></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="submit" name="submit" value="Submit" /></td>
    </tr>
  </tbody>
</table>
</div>
</form>