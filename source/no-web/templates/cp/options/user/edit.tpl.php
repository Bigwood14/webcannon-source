<h1>Edit User</h1>
<br />
<form action="/cp/options/user.php?action=edit&amp;user_id=<?php echo $template->user['user_id'] ?>" method="post" class="content-form">
	<?php if (!empty($template->msg)) { ?>
		<p><?php echo $template->msg ?></p>
	<?php } ?>
	<fieldset>
		<legend>Edit User</legend>
		<div class="row clearfix">
			<label>Username</label>
			<input type="text" name="user_username" autocomplete="off" readonly="readonly" value="<?php echo $template->user['username']?>" />
		</div>
		<div class="row clearfix">
			<label>Password</label>
			<input type="password" name="user_password" autocomplete="off" />
			Leave password blank to keep current.
		</div>
		<div class="row clearfix">
			<label>Access</label>
			<select name="access">
				<?php
				if ($template->user['mailer'] == 1)
					$msel = ' selected';
				else
					$asel = ' selected';
				?>
				<option value="mailer"<?php echo @$msel?>>Mailer</option>
				<option value="admin"<?php echo @$asel?>>Admin</option>
			</select>
		</div>
		<div class="row clearfix">
			<label>IPs</label>
			<textarea name="ips" rows="10" cols="30"><?php echo $template->user['ips']?></textarea>
		</div>
		<div class="submit-align clearfix">
			<input type="submit" value="Edit User" name="submit" />
		</div>
	</fieldset>
</form>
