<div id="contentbox">
  <h1>Delivery Details</h1>
</div>
<br />
<div id="contenttable">
<?php
foreach($template->details AS $detail)
{
?>
<table cellspacing="0" cellpadding="4" border="0" width="500">
  <tr>
    <th><?php echo $detail['time'] ?></th>
  </tr>
  
  <tr>
    <td><?php echo nl2br($detail['message']) ?></td>
  </tr>
</table>
<br />
<?php
}
?>
</div>
