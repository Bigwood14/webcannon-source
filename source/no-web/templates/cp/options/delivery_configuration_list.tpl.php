<h1>Delivery Configurations</h1>
<br />
<div id="contenttable">
	<table width="100%" cellspacing="2" border="0" cellpadding="2">
		<tr>
			<th colspan="3">Delivery Configurations</th>
		</tr>
		<tr>
			<td colspan="3" class="create"><a href="?action=create">Create new delivery configuration</a></td>
		</td>
		<?php foreach ($template->rows as $row) { ?>
		<tr>
			<td><?php echo $row['name'] ?></td>
			<td class="edit"><a href="?action=update&amp;delivery_configuration_id=<?php echo $row['delivery_configuration_id'] ?>">edit</a></td>
			<td class="delete"><a href="?action=delete&amp;delivery_configuration_id=<?php echo $row['delivery_configuration_id'] ?>">delete</a></td>
		</tr>
		<?php } ?>
	</table>
</div>
