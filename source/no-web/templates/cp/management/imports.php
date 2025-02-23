<h1>Imports</h1>
  
<br />
  
<div id="note">
  <p>
    If an import does not update for a long time (30mins) its likley the import has got stuck this can happen if the server gets rebooted, click <b>abort</b> 
    next to running import and then click <b>restart</b> to get the import going again.
    <br /><b>Global DNE and Wash</b> imports (email and domain) will show <b>0</b> for the added column click on the detailed stats to view.
  </p>
</div>
  
<br />
  
  <?php include($template->directory.'cp/pager.php'); ?>
  
  <div id="contenttable">
  <table width="100%" cellspacing="0" cellpadding="4" border="0">
    <tr>
      <th width="30" align="center">ID</th>
      <th>Title</th>
      <th align="center" width="100">State</th>
      <th align="center">File</th>
      <th align="center" width="50">List</th>
      <th align="center" width="50">Added</th>
      <th align="center" width="50">Total</th>
      <th align="center" width="50">Type</th>
      <th align="center" width="50">Action</th>
    </tr>
  <?php 
  foreach($template->imports AS $import)
  {
      if($import['list'] == '') $import['list'] = "&nbsp;";
  ?>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="center"><?php echo $import['import_id']?></td>
      <td><a href="/cp/management/import-view.php?id=<?php echo $import['import_id']?>"><?php echo $import['title']?></a></td>
      <td align="center"><?php echo getImportState($import['state']);?></td>
      <td align="center"><?php echo $import['file']?></td>
      <td align="center">
	  	<?php
			if ($import['type_id'] == '2')
				echo @$template->suppression_lists[$import['type']];
			else
				echo $import['list']
		?>
	  </td>
      <td align="center"><?php echo number_format($import['added'])?></td>
      <td align="center"><?php echo number_format($import['total'])?></td>
      
      <td align="center">
        <?php
        switch($import['type_id'])
        {
            case "1":
            echo "wizard";
            break;
            case "2":
            echo "suppression";
            break;
            case "3":
            echo "gdne";
            break;
            case "4":
            echo "wash";
            break;
            case "5":
            echo "suppression domains";
            break;
            case "6":
            echo "gdne domains";
            break;
            case "7":
            echo "wash domains";
            break;
        } 
        ?>
      </td>
      
      <td align="center">
        <?php if($import['state'] == 1 || $import['state'] == 0)  {?>
        <a href="/cp/management/imports.php?action=abort&import_id=<?php echo $import['import_id']?>">abort</a>
        <?php } elseif($import['state'] == 2 || $import['state'] == 3) { ?>
        <a href="/cp/management/imports.php?action=restart&import_id=<?php echo $import['import_id']?>">restart</a>
        <?php } ?>
      </td>
      
    </tr>
  <?php
  }
  ?>
</table></div><br />
