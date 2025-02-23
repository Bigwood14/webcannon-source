
<div id="contentbox">
  <h1>Server Status</h1>
</div>
<br />
<div id="contenttable">
<table width="500" cellspacing="0" border="0" cellpadding="2">
   <tr>
      <th colspan="2">Server Readouts</th>
    </tr>
    <tr>
      <td width="60">Up Time: </td>
      <td><?php echo $template->up_time; ?></td>
    </tr>
</table>
<br />

<table width="500" cellspacing="0" border="0" cellpadding="2">
<tr>
      <th colspan="2">Mysql Readouts</th>
    </tr>  
    <tr>
      <td width="60">Up Time: </td>
      <td><?php echo timespanFormat($template->server_status['Uptime']); ?> (<?php echo date("m-d-y",mktime() - $template->server_status['Uptime']);?>)</td>
    </tr>
</table> 
   <br />
<table width="500" cellspacing="0" border="0" cellpadding="2"> 
 <tr>
   <th colspan="4">Mysql Query Stats</th>
 </tr>
 
 <tr bgcolor="#dce3ef">
   <td><strong>Total</strong></td>
   <td><strong>Per Hour</strong></td>
   <td><strong>Per Min</strong></td>
   <td><strong>Per Sec</strong></td>
 </tr>
 
 <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
   <td><?php echo number_format($template->server_status['Questions']); ?></td>
   <td><?php echo number_format(($template->server_status['Questions'] * 3600 / $template->server_status['Uptime']), 2); ?></td>
   <td><?php echo number_format(($template->server_status['Questions'] * 60 / $template->server_status['Uptime']), 2); ?></td>
   <td><?php echo number_format(($template->server_status['Questions'] / $template->server_status['Uptime']), 2); ?></td>
 </tr>
 
 
 <tr bgcolor="#dce3ef">
   <td><strong>Query</strong></td>
   <td><strong>Count</strong></td>
   <td><strong>Per Hour</strong></td>
   <td><strong>%</strong></td>
 </tr>
   <?php
   foreach($template->query_stats AS $k => $v)
   {
   ?>
 <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
   <td><?php echo $k ?>: </td>
   <td><?php echo $v ?></td>
   <td><?php echo number_format(($v * 3600 / $template->server_status['Uptime']),2); ?></td>
   <td><?php echo number_format(($v * 100 / ($template->server_status['Questions'] - $template->server_status['Connections'])),2); ?></td>
 </tr>
    <?php
    }
    ?>
    
</table>

</div>

 

  
  