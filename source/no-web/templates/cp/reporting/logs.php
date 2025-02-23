
<form action="" method="get" enctype="multipart/form-data">
<div id="contentbox">
  <h1>Logs</h1>
  </div>
  <p>

  <div id="contenttable">
  <table width="700" cellspacing="2" border="0" cellpadding="2">
  <tbody>
  
    <tr>
      <th colspan="5">Filter</th>
    </tr>
    <tr>
      <td colspan="5"><input type="submit" name="search" value="Filter" /></td>
    </tr>
    
    
    <tr>
      <th colspan="5">Log Entrys <?php include $template->directory."cp/pager.php"; ?></th>
    </tr>
    
    <tr>
      <td align="center">ID</td><td align="center">Date</td><td align="center">Type</td><td>Message</td><td align="center">Code</td>
    </tr>
 <?php
 $i = 1;
 foreach($template->logs AS $log)
 {
      if($i == 1)
      {
          $bg = '#EAEAEE';
          $i = 0;
      }
      else
      {
          $bg = '#DCE3EF';
          $i = 1;
      }
 ?>   
    <tr bgcolor="<?php echo $bg?>">
      <td align="center"><?php echo $log['log_id']?></td><td align="center"><?php echo date("M d H:i:s",$log['date'])?></td><td align="center"><?php echo $log['type']?></td><td><?php echo $log['message']?></td><td align="center"><?php echo $log['code']?></td>
    </tr>
<?php
}
?>
    
  </tbody>
</table>
</div>
  
  
  </p>

</form>