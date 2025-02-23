<h1>AOL Removal</h1>
<br />
<form method="post" action="">
	<input type="submit" name="nslookup" value="NS Lookup" />
	<?php if (isset($template->lookup)) { ?>
	<br />
	<br />
	<textarea rows="20" cols="70"><?php echo $template->lookup ?></textarea>
	<?php } ?>
</form>
<br />
<form method="post" action="">
	<select name="ip">
		<?php foreach ($this->options as $value => $option) { ?>
			<option value="<?php echo $value ?>"><?php echo $option?></option>
		<?php } ?>
	</select>
	<input type="submit" name="telnet" value="Telnet Test" />
	<?php if (isset($template->telnet)) { ?>
	<br />
	<br />
	<textarea rows="20" cols="70"><?php echo $template->telnet ?></textarea>
	<?php } ?>
</form>
