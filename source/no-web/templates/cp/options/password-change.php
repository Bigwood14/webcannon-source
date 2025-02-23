  <h1>Password Change</h1>
<br />
  <form action="/cp/options/password-change.php" method="post">
  <p>
  <?php
  if(isset($template->msg))
  {
  ?>
  <span class="error"><?php echo $template->msg?></span><br />
  <?php
  }
  ?>
  <div id="contenttable">
  <table width="100%" cellspacing="2" border="0" cellpadding="2">
  <tbody>
  
    <tr>
      <th>Current Password</th>
    </tr>
    <tr>
      <td><input type="password" name="cur_password" size="20"  /></td>
    </tr>
    
    <tr>
      <th>New Password</th>
    </tr>
     <tr>
      <td>Type new password twice</td>
    </tr>
    <tr>
      <td><input type="password" name="password_1" size="20" value="" /></td>
    </tr>
    <tr>
      <td><input type="password" name="password_2" size="20" value="" /></td>
    </tr>
    

    <tr>
      <td>  <input type="submit" name="submit" value="Update Password" /> </td>
    </tr>
    
  </tbody>
</table>
</div>

  
  </p>

</form>
