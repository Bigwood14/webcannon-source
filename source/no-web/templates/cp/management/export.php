<h1>Export</h1>
<br />
  
<form action="/cp/management/export.php" method="post">
<div id="contenttable">

<?php
if(isset($template->error))
{
?>
<table width="100%" cellpadding="2" cellspacing="0">
    <tr>
      <th>There were errors!!</th>
    </tr>
    <tr>
      <td>
      <?php
      foreach($template->error AS $error)
      {
      ?>
      <?php echo $error?><br />
      <?php      
      }
      ?>
      </td>
    </tr>
</table><br />
<?php
}
?>

<table width="100%" cellpadding="4" cellspacing="0">
 <tr>
  <th colspan="2">Export List</th>
 </tr>
 <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
  <td colspan="2">Export a list - warning this is not a good idea if you are importing to this now!.</td>
 </tr>
 <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
  <td width="80"><strong>List:</strong> </td>
  <td><?php print buildListSelect(); ?> </td>
 </tr>
 <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
  <td><strong>List Type:</strong> </td>
  <td>
   <input type="checkbox" name="types[]" value="subscribed" id="subscribed" /> <label for="subscribed">subscribed</label>
   <input type="checkbox" name="types[]" value="unsubscribed" id="unsubscribed" /> <label for="unsubscribed">unsubscribed</label>
   <input type="checkbox" name="types[]" value="bounced_s" id="bounced_s" /> <label for="bounced_s">bounced (soft)</label>
   <input type="checkbox" name="types[]" value="bounced_h" id="bounced_h" /> <label for="bounced_h">bounced (hard)</label>
   <!--
   <select name="list_type">
    <option value="1">subscribed</option>
    <option value="2">unsubscribed</option>
    <option value="3">bounced (soft)</option>
    <option value="4">bounced (hard)</option>
   </select>
   -->
  </td>
 </tr>
 <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
  <td><strong>Openers:</strong> </td>
  <td>                                         
   <select name="openers">
    <option value="1">all openers and non</option>
    <option value="2">openers</option>
    <option value="3">non openers</option>
   </select>
  </td>
 </tr>
 <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
  <td><strong>Clickers:</strong> </td>
  <td>
   <select name="clickers">
    <option value="1">all clickers and non</option>
    <option value="2">clickers</option>
    <option value="3">non clickers</option>
   </select>
  </td>
 </tr>
 <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
  <td><strong>Import Tag:</strong> </td>
  <td>
   <select name="tags">
    <?php
    foreach($template->tags AS $tag)
    {
    ?>
    <option value="<?php echo $tag['import_id'] ?>"><?php echo $tag['title'] ?></option>
    <?php
    }
    ?>
   </select>
  </td>
 </tr>
 <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
  <td colspan="2"><input type="submit" name="list" value="Export List" /></td>
 </tr>
</table>
<br />

<table width="100%" cellpadding="2" cellspacing="0">
  <thead>
    <tr>
      <th colspan="2">Export Categories</th>
    </tr>
  </thead>
  <tbody>
  <tr><td colspan="2">Export a category.</td></tr>
   <tr><td><select name="category_id"><?php echo $template->options?></select><input type="submit" name="category" value="Export Category" /></td></tr>
  </tbody>
</table>

<br />

<div id="note">
  <p>
    The progress total figure may not be completly accurate more or less records may be exported and the export declared complete. This happens because an email maybe removed via bounce etc, or an email maybe imported before the export starts.
  </p>
</div>

<br />

<?php @include($template->directory.'cp/pager.php'); ?>

<table width="770" cellpadding="4" cellspacing="0">
  <tr>
    <th align="center" width="40">State</th>
    <th align="left">Name</th>
    <th align="left">List/Cat</th>
    <th align="left">Subs</th>
    <th align="left">Usubs</th>
    <th align="left">BS</th>
    <th align="left">BH</th>
    <th align="left">Click</th>
    <th align="left">Open</th>
    <th align="left">Tag</th>
    <th align="center">Progress</th>
    <th align="center">Action</th>
  </tr>
  
  <?php
  foreach($template->data AS $export)
  {
  ?>
  <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
    <td align="center">
      <?php echo exportState($export['state'])?>
    </td>
    <td align="left"><a href="/cp/management/export-view.php?export_id=<?php echo $export['export_id']?>"><?php echo $export['name']?></a></td>
    <td>
      <?php 
      if($export['type'] == '3')
      {
          echo $template->cats[$export['list-cat']];
      }
      else
      {
          echo $export['list-cat'];
      }
      ?>
    </td>
    <td align="center"><?php echo $export['subscribed']?></td>
    <td align="center"><?php echo $export['unsubscribed']?></td>
    <td align="center"><?php echo $export['bounce_s']?></td>
    <td align="center"><?php echo $export['bounce_h']?></td>
    <td align="center"><?php echo $export['clickers']?></td>
    <td align="center"><?php echo $export['openers']?></td>
    <td align="center"><?php echo $export['tag']?></td>
    <td align="center">
      <?php echo number_format($export['progress'])?>/<?php echo number_format($export['total'])?>
    </td>
    
    <td align="center">
      <?php if($export['state'] == 1 || $export['state'] == 0)  {?>
      <a href="/cp/management/export.php?action=abort&export_id=<?php echo $export['export_id']?>">abort</a>
      <?php } elseif($export['state'] == 2 || $export['state'] == 3) { ?>
      <a href="/cp/management/export.php?action=restart&export_id=<?php echo $export['export_id']?>">restart</a>
      <?php } ?>
    </td>
  </tr>
  <?php
  }
  ?>
  
  
</table>
<br />

</div>
</form>
