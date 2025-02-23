<h1>AOL Whitelist</h1>
<br />
<form method="post" action="">
	<table class="content" cellspacing="0" cellpadding="0" style="width: 750px">
		<tr>
			<th class="first center">W</th>
			<th>IP</th>
			<th>Domain</th>
			<th>Confirmation Code</th>
			<th>Date Apply</th>
			<th>Ratio</th>
			<th>Deny</th>
			<th>Link</th>
		</tr>
		<?php $count = count($template->domains); $i = 0; foreach ($template->domains as $domain) { $last = ''; if (++$i == $count) $last = 'class="last"';?>
		<tr <?php echo $last?>>
			<td class="first center">
				<?php if ($domain['aol'] == 1) $checked = 'checked="checked"'; else $checked = '';?>
				<input type="checkbox" name="whitelist[<?php echo $domain['domain'] ?>]" value="1" <?php echo $checked?>/>
			</td>
			<td><?php echo $domain['ip'] ?></td>
			<td><?php echo $domain['domain'] ?></td>
			<td><input type="text" name="code[<?php echo $domain['domain'] ?>]" size="20" value="<?php echo $domain['aol_confirmation_code'] ?>" /></td>
			<td><input type="text" name="date[<?php echo $domain['domain'] ?>]" size="8" value="<?php echo $domain['aol_date'] ?>"/></td>
			<td>
				<input type="text" name="ratio[<?php echo $domain['domain'] ?>]" size="4" value="<?php echo $domain['aol_ratio']?>" />
				(<?php if (isset($domain['ratio'])) echo '<a href="/cp/isp-relations/aol_log.php?domain='.$domain['domain'].'">'.$domain['ratio'].'</a>'; else echo 'N/A'; ?>)
			</td>
			<td><input type="text" name="deny[<?php echo $domain['domain'] ?>]" size="4" value="<?php echo $domain['aol_deny']?>" /></td>
			<td><?php if (!empty($domain['aol_link'])) { ?><a href="<?php echo $domain['aol_link'] ?>" target="_blank">Confirm</a><?php } else { ?>N/A<?php } ?></td>
		</tr>
		<?php } ?>
	</table>
	<br />
	<input type="submit" name="update" value="Update" />
</form>

<br /><br />

<p><strong>The "W" Column</strong></p>
<p>The W stands for whitelisting check this when the IP/Domain is whitelisted.</p>
<br />
<p><strong>The Deny Column</strong></p>
<p>
If the request is denied you can put the denial code in here - these are the current known ones:
<br />
<strong>UMH</strong> - Unacceptable Mail History<br />
<strong>HSC</strong> - High Spam Complaints<br />
<strong>BIP</strong> - Block in Place
</p>


<br />
<p><strong>The ratio in brackets?</strong></p>
<p>The ratio in the brackets is the one parsed (if any) from warning emails sent by AOL, click the link for more information on it.</p>
