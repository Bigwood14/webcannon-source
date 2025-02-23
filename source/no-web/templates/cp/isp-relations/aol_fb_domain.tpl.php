<h1>AOL Feedback Loop</h1>
<br />
<form method="post" action="">
	<table class="content" cellspacing="0" cellpadding="0">
		<tr>
			<th class="first center">FL</th>
			<th>IP</th>
			<th>Domain</th>
			<th>Confirmation Code</th>
			<th>Date Apply</th>
			<th>Link</th>
		</tr>
		<?php $count = count($template->domains); $i = 0; foreach ($template->domains as $domain) { $last = ''; if (++$i == $count) $last = 'class="last"';?>
		<tr <?php echo $last?>>
			<td class="first center">
				<?php if ($domain['aol_fl'] == 1) $checked = 'checked="checked"'; else $checked = '';?>
				<input type="checkbox" name="fl[<?php echo $domain['domain'] ?>]" value="1" <?php echo $checked?>/>
			</td>
			<td><?php echo $domain['ip'] ?></td>
			<td><?php echo $domain['domain'] ?></td>
			<td><input type="text" name="code[<?php echo $domain['domain'] ?>]" size="20" value="<?php echo $domain['aol_fl_code'] ?>" /></td>
			<td><input type="text" name="date[<?php echo $domain['domain'] ?>]" size="8" value="<?php echo $domain['aol_fl_date'] ?>"/></td>
			<td><?php if (!empty($domain['aol_fl_link'])) { ?><a href="<?php echo $domain['aol_fl_link'] ?>" target="_blank">Confirm</a><?php } else { ?>N/A<?php } ?></td>
		</tr>
		<?php } ?>
	</table>
	<br />
	<input type="submit" name="update" value="Update" />
</form>

<br /><br />

<p><strong>The "FL" Column</strong></p>
<p>FL = Feedback Loop</p>
<br />
