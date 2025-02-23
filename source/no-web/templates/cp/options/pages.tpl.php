<div id="contenttable">
<form action="/cp/options/pages.php" method="post">

<table width="550" cellspacing="0" border="0" cellpadding="2">
  <tr>
    <th>Create Page</th>
  </tr>
  <tr>
    <td>Page:
		<input type="text" name="url" value="" />
		<!--<select name="type">
			<option value="1">Plain</option>
			<option value="2">Unsub</option>
		</select>-->
		<input type="submit" name="create" value="Create Page" /></td>
  </tr>
</table>
<br />
<table width="550" cellspacing="0" border="0" cellpadding="2">
  <tr>
    <th>Edit Page</th>
  </tr>
  <tr>
    <td>Page: 
      <select name="page">
   		<?php foreach ($this->pages as $page) { ?>
			<option value="<?php echo $page['page_id'] ?>"><?php echo $page['url'] ?></option>
		<?php } ?>
		<option value="index.php">Index/Unsubscribe</option>
      </select>
      <input type="submit" name="select" value="Select Page" /></td>
  </tr>
</table>
</form>
<form action="/cp/options/pages.php" method="post">
<br /> 
<?php
if(isset($template->select))
{
?>

<table width="550" cellspacing="0" border="0" cellpadding="2">
	<tr>
		<th>Edit Page <em><?php echo $template->select['url']?></em></th>
	</tr>
	<tr>
		<td>
			<a href="/cp/scheduling/html_image.php" class="html-image">Upload Image</a>
		</td>
	</tr>
  <tr>
    <td><textarea name="content" cols="70" rows="60"><?php echo $template->select['content'] ?></textarea></td>
  </tr>
  <tr>
    <td>
		<input type="hidden" name="page_id" value="<?php echo $template->select['page_id'] ?>" />
		<input type="hidden" name="page" value="<?php echo $template->select['page_id'] ?>" />
		<input type="hidden" name="select" value="true" />
		<input type="submit" name="update" value="Update" />
	</td>
  </tr>
</table>
<?php
}
?>

<table width="550" cellspacing="0" border="0" cellpadding="2">
  <tr>
    <th>Delete Page</th>
  </tr>
  <tr>
    <td>Page: 
      <select name="page">
		<?php foreach ($this->pages as $page) { ?>
			<option value="<?php echo $page['page_id'] ?>"><?php echo $page['url'] ?></option>
		<?php } ?>
      </select>
      <input type="submit" name="delete" value="Delete Page" /></td>
  </tr>
</table>


</form>
</div>
