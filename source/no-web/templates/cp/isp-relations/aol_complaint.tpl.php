<h1>AOL Complaint</h1>
<br />
<table class="content" cellpadding="0" cellspacing="0">
	<tr>
		<th class="first">IP</th>
		<?php foreach ($this->days as $day) { ?>
		<th><?php echo date('d M', $day) ?></th>
		<?php } ?>
	</tr>
	<?php foreach ($this->data as $ip => $data) { ?>
	<tr>
		<td class="first"><?php echo $ip ?></td>
		<?php foreach ($data as $stat) { ?>
			<td class="right">
				<?php echo $stat['com'].'/'.$stat['del']?>
			</td>
		<?php } ?>
	</tr>
	<?php } ?>
</table>
