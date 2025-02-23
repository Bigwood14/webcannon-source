<form action="" method="post" enctype="multipart/form-data">
 <h1>Remove Recipients</h1>
	<br />
  <p>
  <?php
  if($template->counts['total'] > 0)
  {
  ?>
  <div id="contenttable">
   <table width="100%" cellspacing="0" cellpadding="4">

    <tr>
      <th colspan="2">Removing Results</th>
    </tr>
    
    <tr bgcolor="#dce3ef">
      <td>Total:</td>
      <td width="30"><?php echo number_format($template->counts['total'])?></td>
    </tr>
    <tr>
      <td>Removed:</td>
      <td width="30"><?php echo number_format($template->counts['removed'])?></td>
    </tr>
    <tr  bgcolor="#eaeaee">
      <td>Removed GDNE:</td>
      <td><?php echo number_format($template->counts['removed_gdne'])?></td>
    </tr>
    <tr>
      <td>Removed SGDNE:</td>
      <td><?php echo number_format($template->counts['removed_sgdne'])?></td>
    </tr>
    <tr  bgcolor="#eaeaee">
      <td>Invalid:</td>
      <td><?php echo number_format($template->counts['invalid'])?></td>
    </tr>

  </table>
  </div>
  
  <br /><br />
  
  <?php
  }
  ?><div id="contenttable">
  
  <table width="100%" cellspacing="0" border="0" cellpadding="4">
    <tr>
      <th>List Selection</th>
    </tr>
    <tr><td>Remove from list (removed from all if you click global): <?php print buildListSelect(); ?></td></tr>
   </table>
  <br />
  
    <table width="100%" cellspacing="2" border="0" cellpadding="2">
    <tr>
      <th>Remove Options</th>
    </tr>
    <tr>
        <td>
          <input type="checkbox" checked="true" name="gdne" value="1" id="gdne" /> <label for="gdne">Add names to the Global DNE.</label>
        </td>
      </tr>
      <tr>
        <td>
          <input type="checkbox" name="sgdne" value="1" id="sgdne" /> <label for="sgdne">Add names to the SuperGlobal DNE.</label>
        </td>
      </tr>
   </table>
  <br />
  
  
  <table width="100%" cellspacing="2" border="0" cellpadding="2">
  <tbody>
    <tr>
      <th>Remove Recipient (one email per line)</th>
    </tr>
    <tr>
      <td>
        <textarea cols="60" rows="10" name="recip"><?php
if(is_array($_GET['email']))
{
  foreach($_GET['email'] AS $email)
  {
    echo $email."\n";
  }
}
?></textarea></td>
      </tr>   
      <tr>
      <td><input type="submit" name="submit" value="Remove Recipients" /></td>
    </tr>
    <tr>
      <th>Upload Text File (2mb and under - one email per line )</th>
    </tr>
    <tr>
      <td><input name="recipfile" type="file"> <input type="submit" name="submit" value="Submit File" /></td>
    </tr>
  </tbody>
</table>
</div>
  
  
  </p>

</form>
