<h1>Top Domains</h1>

<table cellpadding="0" cellspacing="0" class="content">
<tr>
	<th>Domain</th>
	<th>Clicks</th>
</tr>
<?php $count = count($this->clicks); $i=0; foreach ($this->clicks as $domain => $num) { $i++; $last = ''; if ($i==$count) $last= 'class="last"';?>
<tr <?php echo $last?>>
	<td class="first"><?php echo $domain?></td>
	<td><?php echo number_format($num) ?></td>
</tr>
<?php } ?>
</table>
