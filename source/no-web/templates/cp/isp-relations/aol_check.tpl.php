<h1>AOL Check</h1>
<br />

<div id="contenttable">
	<form method="post">
		<?php foreach ($this->ips as $group) { ?>
		<h2><?php echo @$this->groups[$group[0]['domain_group_id']]['name'] ?></h2>
		<table width="100%" cellpadding="4" cellspacing="0" border="0" class="ip-group">
		<tr>
			<th><input type="checkbox" class="check-all" /></th>
			<th>IP</th>
			<th>Result</th>
		</tr>

		<?php foreach ($group as $ip) { ?>
		<tr>
			<td width="20"><input type="checkbox" name="ips[]" value="<?php echo $ip['ip'] ?>" /></td>
			<td><?php echo $ip['ip'] ?></td>
			<td><?php echo @$this->results[$ip['ip']] ?></td>
		</tr>	
	<?php } ?>
	</table>
	<br />
	<input type="submit" value="Check Selected" name="check" />
	<br /><br /><br />
	<?php } ?>
	</form>
</div>
