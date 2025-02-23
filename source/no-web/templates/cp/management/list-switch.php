<div id="contentbox">
  <h1>Switch List</h1>
  <p>Select your default list here</p>
</div>
<br />
<div id="contenttable">
<?php
if(isset($template->done))
{
?>
<table width="200" cellspacing="0" border="0" cellpadding="2" align="center">
    <tr>
      <th>List Changed</th>
    </tr>
</table>
<?php
}
?>
<form action="/cp/management/list-switch.php" method="post">
<table width="200" cellspacing="0" border="0" cellpadding="2" align="center">
  <tbody>
    <tr>
      <th>Select List</th>
    </tr>
    <tr>
      <td>
      <?php echo buildListSelect();?><input type="submit" name="submit" value="Select" />
      </td>
    </tr>
    
  </tbody>
</table>
</form>
</div>

 

  
  