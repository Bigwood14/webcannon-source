<form action="" method="post" enctype="multipart/form-data">
<div id="contentbox">
  <h1>Add Recipients</h1>
  <p>Using the <a href="/cp/management/import-wizard.php">import wizard</a> is the recommended way for adding recipients.</p>
  </div>
  <p>
  <?php
  if($template->counts['total'] > 0)
  {
  ?>
  <div id="contenttable">
  <table width="500" cellspacing="0" cellpadding="4">

    <tr>
      <th colspan="2">Adding Results</th>
    </tr>
    
    <tr bgcolor="#dce3ef">
      <td>Total:</td>
      <td width="30"><?php echo number_format($template->counts['total'])?></td>
    </tr>
    <tr>
      <td>Added:</td>
      <td width="30"><?php echo number_format($template->counts['add'])?></td>
    </tr>
    <tr  bgcolor="#eaeaee">
      <td>Duplicates:</td>
      <td><?php echo number_format($template->counts['dup'])?></td>
    </tr>
    <tr>
      <td>Invalid:</td>
      <td><?php echo number_format($template->counts['invalid'])?></td>
    </tr>
    <tr  bgcolor="#eaeaee">
      <td>Unsubscribed:</td>
      <td><?php echo number_format($template->counts['unsub'])?></td>
    </tr>
    <tr>
      <td>Globally Unsubscribed:</td>
      <td><?php echo number_format($template->counts['unsub_g'])?></td>
    </tr>
    <tr  bgcolor="#eaeaee">
      <td>Globally Unsubscribed (domain):</td>
      <td><?php echo number_format($template->counts['unsub_g_d'])?></td>
    </tr>
    <tr>
      <td>Globally Unsubscribed (word):</td>
      <td><?php echo number_format($template->counts['unsub_g_w'])?></td>
    </tr>

  </table>
  </div>

<br />
<br />

  <?php
  }
  ?>
  <div id="contenttable">
  
   <table width="500" cellspacing="2" border="0" cellpadding="2">
    <tr>
      <th>List Selection</th>
    </tr>
    <tr><td>Add to list: <?php print buildListSelect(); ?></td></tr>
   </table>
  <br /><br />
  <table width="500" cellspacing="2" border="0" cellpadding="2">
  <tbody>
    <tr>
      <th>Add Recipient (one email per line)</th>
    </tr>
    <tr>
      <td><textarea cols="60" rows="10" name="recip"></textarea></td></tr>
      
      <tr><td><input type="submit" name="submit" value="Add Recipients" /></td></tr>

    
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