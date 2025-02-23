<?php if($template->import['list'] == '') $import['list'] = "&nbsp;"; ?>
<div id="contentbox">
  <h1>Import View</h1>
  <p><a href="/cp/management/imports.php">View Imports</a></p>
</div>

<br />
  
<div id="contenttable">
  <table width="500" cellpadding="4" cellspacing="0" border="0">
    
    <tr>
      <th colspan="2">Import Overview</th>
    </tr>
  
    <tr bgcolor="#dce3ef">
      <td>You are viewing import: </td>
      <td><strong><?php echo $template->import['title']?></strong></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>File: </td>
      <td><?php echo $template->import['file']?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>List: </td>
      <td><?php echo $template->import['list']?></td>
    </tr>
    
    <tr bgcolor="#dce3ef">
      <td width="200">Total:</td>
      <td><?php echo number_format($template->import['total'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Added:</td>
      <td><?php echo number_format($template->import['added'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Invalid:</td>
      <td><?php echo number_format($template->import['invalid'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Duplicates:</td>
      <td><?php echo number_format($template->import['dups'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Unsubscribed:</td>
      <td><?php echo number_format($template->import['unsub'])?></td>
    </tr>
    
     <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Global Unsubscribed:</td>
      <td ><?php echo number_format($template->import['unsub_g'])?></td>
    </tr>
    
     <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Global Domain Unsubscribed:</td>
      <td ><?php echo number_format($template->import['unsub_d'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Global Word Filter:</td>
      <td ><?php echo number_format($template->import['filtered'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Overwrites:</td>
      <td ><?php
      switch($template->import['overwrite'])
      {
          case "1":
          echo "Yes";
          break;
          default:
          echo "No";
          break;
      }
      ?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>List Deduping:</td>
      <td ><?php
      if($template->import['dedupe'] != '')
      {
          $lists = unserialize($template->import['dedupe']);
          if(is_array($lists)) {
            foreach($lists AS $list) { $str .= $list . ", "; }
            echo rtrim($str, ", ");
          }
      }
      else 
      {
          echo "N/A";
      }
      ?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Time Inputted:</td>
      <td ><?php echo $template->import['ts']?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Start:</td>
      <td ><?php echo $template->import['start']?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>End/Check:</td>
      <td ><?php echo $template->import['end']?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td>Duration:</td>
      <td ><?php
      if($template->import['end_stamp'] < $template->import['start_stamp'])
      {
          $template->import['end_stamp'] = mktime();
      }
      echo timespanFormat($template->import['end_stamp']-$template->import['start_stamp'])?></td>
    </tr>
    
    <tr bgcolor="#dce3ef">
      <td>Per Second:</td>
      <td >
        <?php
        $number = ($template->import['end_stamp'] - $template->import['start_stamp']);
        if($number < 1)
        {
            echo "N/A";
        }
        else 
        {
            echo number_format(($template->import['total'] / ($template->import['end_stamp'] - $template->import['start_stamp'])), 2);
        }
        ?>
      </td>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td colspan="2"><?php echo nl2br($template->import['description']) ?></td>
    </tr>
    </tr>
    
</table>
</div>