<h1>AOL Log</h1>
<br />

<div id="contenttable">

<table width="100%" cellpadding="4" cellspacing="0" border="0">
  <thead>
    <tr>
      <th width="150">Date</th>
      <th>Ratio</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
  <?php
  foreach($template->rows AS $row)
  {
  ?>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
		<td><?php echo $row['date'] ?></td>
		<td><?php echo $row['ratio'] ?>%</td>
		<td><a href="?view=<?php echo $row['aol_ratio_id'] ?>" target="_blank">View Message</a></td>
    </tr>
  <?php
  }
  ?>
  </tbody>
</table>
<br />
</div>
