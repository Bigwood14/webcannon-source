<div id="contentbox">

  <h1>Categories</h1>
  </div>
  <br />
  <form action="/cp/management/categories.php" method="post">
  <?php
  if(isset($_GET['id']))
  {
  ?>
    <input type="text" name="title" size="20" value="<?php echo urldecode($_GET['cat'])?>" />
    <input type="hidden" name="id" value="<?php echo $_GET['id']?>" />
    <input type="submit" name="edit" value="Edit Category" />
  <?php 
  }
  else
  {
  ?>
    <input type="text" name="title" size="20" />
    <input type="submit" name="add" value="Add Category" />
  <?php
  }
  ?>
  </form>
  <br /><br />
  <div id="contenttable">
   <form action="/cp/management/categories.php" method="post">
  <table width="500" cellspacing="0" cellpadding="4" border="0">
    <tr>
      <th width="30" align="center">ID</th>
      <th>Title</th>
      <th align="center" width="100">Emails</th>
      <th align="center" width="100">Edit</th>
      <th align="center" width="100">Del</th>
    </tr>
  <?php 
  foreach($template->cats AS $cat)
  {
  ?>
     <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="center"><?php echo $cat['category_id']?></td>
      <td><?php echo $cat['title']?></td>
      <td align="center"><?php echo number_format($cat['emails']);?></td>
      <td align="center"><a href="/cp/management/categories.php?id=<?php echo $cat['category_id']?>&cat=<?php echo urlencode($cat['title'])?>">edit</a></td>
      <td align="center"><input type="checkbox" name="selected[]" value="<?php echo $cat['category_id']?>" /></td>
    </tr>
  <?php
  }
  ?>
<tr>
  <td colspan="5" align="right"><input type="submit" name="delete" value="Delete Selected" /></td>
</tr>
</table></form></div><br />
