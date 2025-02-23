<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Welcome to Celibero</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <style type="text/css" media="screen">@import "../css/main.css";</style>
  <style type="text/css" media="projection">@import "../css/main.css";</style>
</head>
<body>
<!-- 
Info

php version: <?php echo $v?>
-->
<form action="index.php" method="post">
<div id="wrapper">
<?php

if(@$install == 'complete')
{
?>
<div id="contenttable">
<table width="750" cellspacing="0" border="0" cellpadding="2">
  <tbody>
    <tr>
      <th>Stage 1 Complete</th>
    </tr>
    <tr>
      <td>Install stage 1 has completed for the second part of the install please login as root user via SSH and run the script:<br /><?php echo $script?></td>
    </tr>
  </tbody>
</table>
</div>
<?php
}
else
{
?>
<?php
if(is_array(@$error))
{
?>
<div id="contenttable">
<table width="750" cellspacing="2" border="0" cellpadding="2">
  <tbody>
   <!-- Error -->
    <tr>
      <th colspan="2">Error in setup</th>
    </tr>
    
    <tr>
      <td colspan="2" class="error"><?php
       foreach($error AS $msg)
       {
           ?>
           <?php echo $msg?><br />
           <?php
       }
       ?></td>
    </tr>
    </tbody>
    </table>
    </div>
    <br />
<?php
}
?>
<div id="contenttable">
<table width="750" cellspacing="0" border="0" cellpadding="2">
  <tbody>
   <!-- Main Details -->
    <tr>
      <th colspan="2">Celibero setup Stage 1 - Details</th>
    </tr>
    
    <tr>
      <td width="200">This server is:</td>
      <td align="left">
      <input type="text" name="server_name" value="Mailer + DB" />
      <?php
        if(isset($_POST['server_type']))
        {
          $sel[$_POST['server_type']] = " selected";
        }
      ?>
        <select name="server_type">
          <option value="5"<?php echo @$sel[5]?>>Master DB + Mailer</option>
          <option value="4"<?php echo @$sel[4]?>>Master DB</option>
          <option value="3"<?php echo @$sel[3]?>>Mailer</option>
          <option value="2"<?php echo @$sel[2]?>>DB</option>
          <option value="1"<?php echo @$sel[1]?>>Mailer + DB</option>
        </select>
        <input type="text" name="server_ip" value="<?php echo $_SERVER['SERVER_ADDR'] ?>" />
        </td>
    </tr>
    
    <tr>
      <td width="200">URL to files:</td>
      <td align="left"><input type="text" name="url" size="20" value="<?php echo (empty($_POST['url'])) ? $_SERVER['HTTP_HOST'] : $_POST['url'];?>" /></td>
    </tr>
    
       <tr>
      <td width="200">Domain (domain.com):</td>
      <td align="left"><input type="text" name="domain" size="20" value="<?php echo (empty($_POST['domain'])) ? str_replace('www.','',$_SERVER['HTTP_HOST']) : $_POST['domain'];?>" /></td>
    </tr>
    
    
    <tr>
      <td colspan="2">All path settings should have a trailing slash.</td>
    </tr>
    
    <tr>
      <td width="200">Root Path:</td>
      <td align="left"><input type="text" name="root_path" size="30" value="<?php echo (empty($_POST['path'])) ? $path : $_POST['path'];?>" /></td>
    </tr>
    
    <tr>
      <td width="200">Core Path:</td>
      <td align="left"><input type="text" name="core_path" size="30" value="<?php echo (empty($_POST['core_path'])) ? $path."no-web/core/" : $_POST['core_path'];?>" /></td>
    </tr>
    
    <tr>
      <td width="200">Upload Path:</td>
      <td align="left"><input type="text" name="upload_path" size="20" value="<?php echo (empty($_POST['upload_path'])) ? "/home/upload/" : $_POST['upload_path'];?>" /></td>
    </tr>
    <!-- Mysql Root Details -->
    <tr>
      <th colspan="2">Mysql Root Details</th>
    </tr>
    
    <tr>
      <td colspan="2" align="left">
      Mysql root details are not stored just needed to setup databases and users. You can speciify any user with global grant privlages.<br />
      If this server is not the Master then enter the master db details here.
      </td>
    </tr>
    
    <tr>
      <td width="200">Hostname:</td>
      <td align="left"><input type="text" name="mysql_hostname" size="20" value="<?php echo (empty($_POST['mysql_hostname'])) ? 'localhost' : $_POST['mysql_hostname'];?>" /></td>
    </tr>
    
    <tr>
      <td width="200">Username:</td>
      <td align="left"><input type="text" name="mysql_username" size="20" value="<?php echo (empty($_POST['mysql_username'])) ? 'root' : $_POST['mysql_username'];?>" /></td>
    </tr>
    
    <tr>
      <td width="200">Password:</td>
      <td align="left"><input type="text" name="mysql_password" size="20" value="<?php echo (empty($_POST['mysql_password'])) ? '' : $_POST['mysql_password'];?>" /></td>
    </tr>
    <!-- Others -->
    <tr>
      <th colspan="2">Master Database Details</th>
    </tr>
    
      <tr>
      <td colspan="2">This user will be setup with root privlages.</td>
    </tr>
    
    <tr>
      <td width="200">Celibero Database:</td>
      <td align="left"><input type="text" name="mysql_c_database" size="20" value="<?php echo (empty($_POST['mysql_c_database'])) ? 'celibero' : $_POST['mysql_c_database'];?>" /></td>
    </tr>
    
    <tr>
      <td width="200">Celibero Username:</td>
      <td align="left"><input type="text" name="mysql_c_username" size="20" value="<?php echo (empty($_POST['mysql_c_username'])) ? 'celibero' : $_POST['mysql_c_username'];?>" /></td>
    </tr>
    
    <tr>
      <td width="200">Celibero Mysql Password:</td>
      <td align="left"><input type="text" name="mysql_c_password" size="20" value="<?php echo (empty($_POST['mysql_c_password'])) ? $random_password : $_POST['mysql_c_password'];?>" /></td>
    </tr>
    
     <tr>
      <th colspan="2">User Details</th>
    </tr>
    
      <tr>
      <td colspan="2">User will be setup as full admin.</td>
    </tr>
    
    <tr>
      <td width="200">Username:</td>
      <td align="left"><input type="text" name="username" size="20" value="<?php echo (empty($_POST['username'])) ? '' : $_POST['username'];?>" /></td>
    </tr>
    
    <tr>
      <td width="200">Password:</td>
      <td align="left"><input type="text" name="password" size="20" value="<?php echo (empty($_POST['password'])) ? '' : $_POST['password'];?>" /></td>
    </tr>
    
    <tr>
      <td width="200">Email Address:</td>
      <td align="left"><input type="text" name="email" size="20" value="<?php echo (empty($_POST['email'])) ? '' : $_POST['email'];?>" /></td>
    </tr>
    
     <tr>
      <th colspan="2">Other Details</th>
    </tr>
    
    <tr>
      <td colspan="2">Physical address will be shown on the can spam and privacy policy.</td>
    </tr>
    
    <tr>
      <td width="200">Physical Address:</td>
      <td align="left"><textarea name="physical_address" cols="20" rows="5"><?php echo (empty($_POST['physical_address'])) ? '' : $_POST['physical_address'];?></textarea></td>
    </tr>
    
    <tr>
      <th colspan="2">Submit</th>
    </tr>
    
     <tr>
      <td colspan="2">Press submit to start the setup. you may have to wait a few minutes.<br/><input type="submit" name="submit" value="Setup Celibero" /></td>
    </tr>
    
  </tbody>
</table>
  </div>
  <?php
  }
  ?>
</div>
</form>
</body>
</html>
