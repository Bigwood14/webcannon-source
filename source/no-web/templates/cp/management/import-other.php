<div id="contentbox">
  <h1>Import Other</h1>
  <p>Selected file is: <strong><?php echo $template->file?></strong>. This file should be one email per line.</p>
</div>
<br />
  
<form action="/cp/management/import-other.php?file=<?php echo $_GET['file']?>" method="post">
<div id="contenttable">

<?php
if(isset($template->done))
{
?>
<table width="500">
    <tr>
      <th>Import Setup</th>
    </tr>
    <tr>
      <td>
      The import has been setup monitor on the <a href="/cp/management/imports.php">View Imports</a> page.
      </td>
    </tr>
</table><br />
<?php
}
else
{
?>

<table width="500">
  <tr>
    <th colspan="2">Title</th>
  </tr>
  <tr><td colspan="2">How shall you identify this import?</td></tr>
  <tr><td><input type="text" name="title" value="<?php echo $_GET['file']?>" /></td></tr>
</table>
<br />


<table width="500">
  <thead>
    <tr>
      <th colspan="2">Supression List:</th>
    </tr>
  </thead>
  <tbody>
  <tr><td colspan="2">Import this file to suppression list:</td></tr>
   <tr>
     <td>
       <select name="suppression_list">
         <?php echo $template->options ?>
       </select>
       <select name="suppression_type">
         <option value="1">Emails</option>
         <option value="2">Domains</option>
       </select>
	   <input type="checkbox" name="md5" value="true" id="md5" />
	   <label for="md5">MD5?</label>
       <input type="submit" name="suppression" value="Import" />
     </td>
   </tr>
  </tbody>
</table>
<br />

<table width="500">
  <thead>
    <tr>
      <th colspan="2">Global DNE</th>
    </tr>
  </thead>
  <tbody>
  <tr><td colspan="2">Import this file to the Global DNE (unsubcribes from all lists).</td></tr>
   <tr><td><select name="gdne_type">
         <option value="1">Emails</option>
         <option value="2">Domains</option>
         <option value="3">Words</option>
       </select><input type="submit" name="gdne" value="Import GDNE" /></td></tr>
  </tbody>
</table>
<br />

<table width="500">
  <thead>
    <tr>
      <th colspan="2">Wash List</th>
    </tr>
  </thead>
  <tbody>
  <tr><td colspan="2">This feature unsubscribes all the emails in the file against the selected list (GDNE does all lists).</td></tr>
   <tr><td><?php echo buildListSelect();?><select name="wash_type">
         <option value="1">Emails</option>
         <option value="2">Domains</option>
       </select><input type="submit" name="wash" value="Wash List" /></td></tr>
  </tbody>
</table>
<br />

<?php
}
?>
</div>
</form>
