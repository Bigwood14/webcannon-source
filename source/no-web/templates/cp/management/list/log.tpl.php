<h1>List Log</h1>
<br />
<table cellpadding="0" cellspacing="0" class="content">
	<tr>
		<th class="first">Date</th>
		<th>Message</th>
	</tr>
	<?php foreach ($this->log as $log) { ?>
		<tr>
			<td class="first"><?php echo $log['date'] ?></td>
			<td><?php echo nl2br($log['message']) ?></td>
		</tr>
	<?php } ?>
</table>
