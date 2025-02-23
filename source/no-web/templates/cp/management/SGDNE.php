
<div id="contentbox">
  <h1>Super Global DNE</h1>
  </div>

  <div id="contenttable">
  
  <?php
  if(isset($template->msg))
  {
  ?>
  <br />
  <table width="500" cellspacing="0" border="0" cellpadding="2">
  <tbody>
    <tr>
      <th><?php echo $template->msg?></th>
    </tr>
  </tbody>
</table>
  <?php
  }
  ?>
  
  <br />
  <form action="/cp/management/SGDNE.php" method="post" enctype="multipart/form-data">
  <table width="500" cellspacing="0" border="0" cellpadding="2">
  <tbody>
      <tr>
      <th colspan="2">Active</th>
    </tr>
    <tr>
      <td colspan="2"><?php if($template->info['active'] == 0) {?>SuperGlobalDNE is not active, checked your user/pass?<?php }else{?>SuperGlobalDNE is active and working.<?php }?></td>
    </tr>
    <tr>
      <th colspan="2">Access Permissions</th>
    </tr>
    <tr>
      <td colspan="2">In order to access the Super Global DNE list you must have a username and password.</td>
    </tr>
    <tr>
      <td>Hostname:</td>
      <td><input type="text" name="hostname" size="20" value="<?php echo $template->info['hostname']?>" /></td>
    </tr>
    <tr>
      <td>Database:</td>
      <td><input type="text" name="database" size="20" value="<?php echo $template->info['database']?>" /></td>
    </tr>
    <tr>
      <td>Username:</td>
      <td><input type="text" name="username" size="20" value="<?php echo $template->info['username']?>" /></td>
    </tr>
    <tr>
      <td>Password:</td>
      <td><input type="text" name="password" size="20" value="<?php echo $template->info['password']?>" /></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="submit" name="submit_perms" value="Update Username/Password" /></td>
    </tr>
  </tbody>
</table>
<br />
<table width="500" cellspacing="0" border="0" cellpadding="2">
  <tr>
    <th colspan="2">Search GDNE</th>
  </tr>
  <tr>
    <td colspan="2"><input type="text" name="search_v" /><select name="type"><option>Emails</option><option>Domains</option></select><input type="submit" name="search" value="Search" /></td>
  </tr>
  <?php
  if(is_array($template->search_results))
  {
  ?>
  <tr bgcolor="#dce3ef">
    <td><b>Result</b></td>
    <td align="center"><b>Action</b></td>
  </tr>
  <?php
  foreach ($template->search_results AS $result)
  {
  ?>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td><?php echo $result['value'] ?></td>
    <td align="center">
    <a href="/cp/management/SGDNE.php?action=delete&id=<?php echo $result['id']?>&type=<?php echo $result['type']?>"><img src="/images/misc/trashcan.gif" width="14" height="15" border="0" /></a>
    </td>
  </tr>
  <?php
  }
  }
  ?>
</table>
<br />
<table width="500" cellspacing="0" border="0" cellpadding="2">
  <tr>
    <th colspan="3">Last SGDNE Updates</th>
  </tr>
  <tr bgcolor="#dce3ef">
    <td><strong>Type</strong></td>
    <td><strong>Last</strong></td>
    <td><strong>Date</strong></td>
  </tr>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td><strong>Email</strong></td>
    <td><?php echo $template->info['last_email']?></td>
    <td><?php echo $template->info['last_checkin']?></td>
  </tr>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td><strong>Domain</strong></td>
    <td><?php echo $template->info['last_domain']?></td>
    <td><?php echo $template->info['last_checkin_domain']?></td>
  </tr>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td><strong>Words</strong></td>
    <td><?php echo $template->info['last_word']?></td>
    <td><?php echo $template->info['last_checkin_word']?></td>
  </tr>
</table>
<br />
<table width="500" cellspacing="0" border="0" cellpadding="2">
  <tr>
    <th colspan="2">Global Words</th>
  </tr>
  <tr>
    <td colspan="2"><b>Add word:</b> <input type="text" name="word" /> <input type="submit" value="Add Word" /></td>
  </tr>
  
  <tr bgcolor="#dce3ef">
    <td><b>Word</b></td>
    <td align="center"><b>Action</b></td>
  </tr>
  
<?php
foreach($template->words AS $word)
{
?>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td><?php echo $word['word']?></td>
    <td align="center">
      <a href="/cp/management/SGDNE.php?action=delete&word_id=<?php echo $word['word_id']?>"><img src="/images/misc/trashcan.gif" width="14" height="15" border="0" /></a> 
    </td>
  </tr>
<?php
}
?>  
  
</table>

</form>
</div>

</form>