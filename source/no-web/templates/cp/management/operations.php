<script>
var sections = new Array();
sections[0] = "reset_bounce";
sections[1] = "test";

function show_hide(id)
{
	// First close all
	for(i = 0; i < sections.length; i ++)
	{
		document.getElementById(sections[i]).style.display = 'none';
	}
	// Show selected one
	document.getElementById(id).style.display = '';
}

function show_and_hide(id)
{
	var elem = document.getElementById(id); 
	if(elem.style.display == 'none')
	{
		elem.style.display = '';
	}
	else
	{
		elem.style.display = 'none';
	}
}
</script>

<form action="/cp/management/operations.php" method="post">
<div id="contenttable">

<table cellpadding="4" width="500">
 <tr>
  <th width="50%" align="center" style="border-right: 1px solid #000" onmouseover="this.style.background = '#efefef'" onmouseout="this.style.background = '#dce3ef'" onclick="show_hide('reset_bounce')">
   <a onclick="show_hide('reset_bounce')">Reset Soft Bounce</a>
  </th>
  <th  width="50%" align="center" onmouseover="this.style.background = '#efefef'" onmouseout="this.style.background = '#dce3ef'" onclick="show_hide('test')">
   <a onclick="show_hide('test')">Test</a>
  </th>
 </tr>
</table>

<table cellpadding="4" id="reset_bounce" style="display: none" width="500">
 <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
  <?php
  $i = 0;
  foreach($template->lists AS $list)
  {
  	if(($i % 3) == 0)
    {
    	print "</tr><tr bgcolor=\"".HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff'))."\">";
    }
    $ch = '';
    if(@in_array($list['id'], $template->lists_selected))
    {
    	$ch = ' checked';
    }
    ?>
    <td width="166">
          <input type="checkbox" id="list_<?php echo $i ?>" name="list[]" value="<?php echo $list['id'] ?>"<?php echo $ch ?> />
          <label for="list_<?php echo $i ?>">
          <font color="#000"><?php echo $list['username'] ?></font></label>
        </td>
        <?php
        $i ++;
        }
        $left = 3 - ($i % 3);
        if($left != 3)
        {
        	for($j =1;$j <= $left;$j ++)
        	{
        		print "<td>&nbsp;</td>";
        	}
        }
        ?>
      </tr>
    <tr>
     <td colspan="3"><input type="submit" name="reset_bounce" value="Reset Soft Bounces on Selected Lists" /></td>
    </tr>
</table>

<table cellpadding="4" id="test" style="display: none" width="500">
 <tr>
  <td>Test!</td>
 </tr>
</table>
<br />

<table width="500" cellpadding="4">
 <tr>
  <th>ID</th>
  <th>State</th>
  <th>Type</th>
  <th></th>
 </tr>
 <?php foreach($template->data AS $operation) { ?>
 <tr>
  <td><?php echo $operation['operation_id']?></td>
  <td><?php echo $operation['state']?></td>
  <td><?php echo $operation['type']?></td>
  <td><a onclick="show_and_hide('details_<?php echo $operation['operation_id']?>')">Details</a></td>
 </tr>
 <tr style="display:none" id="details_<?php echo $operation['operation_id']?>">
  <td colspan="4">
   <?php $lists = unserialize($operation['data']); ?>
   Resetting soft bounces on lists: <?php echo implode(', ', $lists);?>
  </td>
 </tr>
 <?php } ?>
</table>

</div>
</form>
