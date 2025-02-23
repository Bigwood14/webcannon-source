<div id="contentbox">
  <h1>Import View</h1>
  <p><a href="/cp/management/export.php">View Exports</a></p>
</div>

<br />
<?php
function niceFormat($mod)
{
    switch($mod)
    {
        case 'y':
        return 'yes';
        break;
        case 'n':
        return 'no';
        break;
        case 'a':
        return 'all';
        break;
    }
}
?> 
<div id="contenttable">
  <table width="500" cellpadding="4" cellspacing="0" border="0">
    
    <tr>
      <th colspan="2">Export Overview</th>
    </tr>
  
    <tr bgcolor="#dce3ef">
      <td>You are viewing export: </td>
      <td><strong><?php echo $template->export['name']?></strong></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>File: </td>
      <td><?php echo $template->export['name']?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>List: </td>
      <td><?php echo $template->export['list-cat']?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Subscribes:</td>
      <td ><?php echo niceFormat($template->export['subscribed'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Unsubscribes:</td>
      <td ><?php echo niceFormat($template->export['unsubscribed'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Bounces (soft):</td>
      <td ><?php echo niceFormat($template->export['bounce_s'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Bounces (hard):</td>
      <td ><?php echo niceFormat($template->export['bounce_h'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Openers:</td>
      <td ><?php echo niceFormat($template->export['openers'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Clickers:</td>
      <td ><?php echo niceFormat($template->export['clickers'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Import Tag:</td>
      <td ><?php echo $template->export['tag']?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Time Inputted:</td>
      <td ><?php echo $template->export['ts']?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Start:</td>
      <td ><?php echo $template->export['start']?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>End/Check:</td>
      <td ><?php echo $template->export['end']?></td>
    </tr>
    
    <tr bgcolor="#dce3ef">
      <td>Per Second:</td>
      <td >
        <?php        
        if($template->export['end_stamp'] < $template->export['start_stamp'])
        {
            $template->export['end_stamp'] = mktime();
        }
        echo number_format(($template->export['progress'] / ($template->export['end_stamp'] - $template->export['start_stamp']+1)), 2);
        ?>
      </td>
    </tr>
    
</table>
</div>