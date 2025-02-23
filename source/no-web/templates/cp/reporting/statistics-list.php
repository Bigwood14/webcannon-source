
<div id="contentbox">
  <h1>List Stats</h1>
</div>
<br />

<div id="contenttable">
<table width="500" cellspacing="0" border="0" cellpadding="4">
   <tr>
      <th>Choose List</th>
    </tr>
    <tr>
      <form action="/cp/reporting/statistics-list.php" method="POST">
      <td><?php print buildListSelect('list_name', $_POST['list_name']); ?><input type="submit" name="submit" value="Generate Stats" /></td>
      </form>
    </tr>
</table>
<br />

<?php
if(is_array($template->list_stats))
{
?>

<table width="500" cellspacing="0" border="0" cellpadding="4">
  <tr>
    <th colspan="2">Counts</th>
  </tr> 
  <tr bgcolor="#dce3ef">
    <td colspan="2"><b>Main Counts</b></td>
  </tr> 
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td width="120">List Size: </td>
    <td><?php echo number_format($template->list_stats['count']) ?></td>
  </tr>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td>No. UnSubscribed: </td>
    <td><?php echo number_format($template->list_stats['unsubs']) ?></td>
  </tr>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td>Bounce Soft: </td>
    <td><?php echo number_format($template->list_stats['bounce_s']) ?></td>
  </tr>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td>Bounce Hard: </td>
    <td><?php echo number_format($template->list_stats['bounce_h']) ?></td>
  </tr>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td>Clickers: </td>
    <td><?php echo number_format($template->list_stats['clickers']) ?></td>
  </tr>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td>Openers: </td>
    <td><?php echo number_format($template->list_stats['openers']) ?></td>
  </tr>
  
  <tr bgcolor="#dce3ef">
    <td colspan="2"><b>Popular Name Counts</b></td>
  </tr> 
  
  <?php
  foreach($template->list_stats['big_names'] AS $name=>$count)
  {
  ?>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td><?php echo $name?>: </td>
    <td><?php echo number_format($count) ?> (<?php
    if($template->list_stats['count'] < 1)
    {
        echo '0';
    }
    else 
    {
        echo number_format(($count/$template->list_stats['count'])*100,2) ;
    }
     ?>%)</td>
  </tr>
  <?php
  }
  ?>
  
</table> 

<br />


<table width="500" cellspacing="0" border="0" cellpadding="4">
  <tr>
    <th colspan="3">Latest Subscription Log Activity</th>
  </tr> 
  <tr bgcolor="#dce3ef">
    <td><b>Email</b></td>
    <td><b>Event</b></td>
    <td><b>Timestamp</b></td>
  </tr> 
  <?php
  foreach($template->data AS $rw)
  {
  ?>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td><?php echo $rw['local'] ?>@<?php echo $rw['domain'] ?></td>
    <td>
      <?php
      switch($rw['event'])
      {
        case '1':
        echo "sub";
        break;
        case '2':
        echo "unsub";
        break;
      }
      ?>
    </td>
    <td><?php echo $rw['ts'] ?></td>
  </tr>
  <?php
  }
  ?>
<?php
}
?>
</table>
</div>

 

  
  