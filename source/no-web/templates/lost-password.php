<br /><form action="/lost-password.php" method="post">
<div id="contenttable">
<table cellpadding="4" cellspacing="0" width="500" align="center">
  <tbody>
    <tr>
      <th colspan="2">Lost Password</th>
    </tr>
    
    <?php
if(isset($template->error))
{
?>
 <tr>
      <td colspan="2" class="error"><?php echo $template->error?></td>
    </tr>
<?php
}
?>

    <?php
if(isset($template->sent))
{
?>
 <tr>
      <td colspan="2" class="error">Login details have been sent to <?php echo $_POST['email']?></td>
    </tr>
<?php
}
else
{
?>
    
    <tr>
      <td>Email Address:</td>
      <td align="left"> <input id="email" name="email" type="text" /></td>
    </tr>
    
         <tr>
      <td colspan="2" align="left"><a href="/login.php">Login</a></td>
    </tr>
    
    <tr>
      <td colspan="2"><input type="submit" name="fetch" value="Fetch" /></td>
    </tr>
  </tbody>
</table></div>
<?php
}
?>
<form>
<br />
