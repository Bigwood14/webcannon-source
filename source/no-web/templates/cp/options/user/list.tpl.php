<h1>Users</h1>
<br />
<form action="" method="post" class="content-form">
	<?php if (!empty($template->error)) { ?>
		<p><?php echo $template->error ?></p>
	<?php } ?>
	<fieldset>
		<legend>Add User</legend>
		<div class="row clearfix">
			<label>Username</label>
			<input type="text" name="user_username" autocomplete="off" />
		</div>
		<div class="row clearfix">
			<label>Password</label>
			<input type="password" name="user_password" autocomplete="off" />
		</div>
		<div class="row clearfix">
			<label>Access</label>
			<select name="access">
				<option value="mailer">Mailer</option>
				<option value="admin">Admin</option>
			</select>
		</div>
		<div class="submit-align clearfix">
			<input type="submit" value="Add User" name="submit" />
		</div>
	</fieldset>
</form>
<br />
<form method="post" action="">
	<table class="content" cellspacing="0" cellpadding="0">
		<tr>
			<th class="first center" width="20">&nbsp;</th>
			<th class="center" width="20">ID</th>
			<th>Username</th>
			<th>Type</th>
			<th>Action</th>
		</tr>
		<?php $count = count($template->users); $i = 0; foreach ($template->users as $user) { $last = ''; if (++$i == $count) $last = 'class="last"';?>
		<tr <?php echo $last?>>
			<td class="first center">
				<?php if ($user['username'] != 'admin') { ?>
				<input type="checkbox" name="users[<?php echo $user['user_id'] ?>]" value="<?php echo $user['user_id']?>" />
				<?php } ?>
			</td>
			<td class="center"><?php echo $user['user_id']?></td>
			<td><?php echo $user['username'] ?></td>
			<td><?php echo $user['type'] ?></td>
			<td>
				 <?php if ($user['username'] != 'admin') { ?>
				<a href="/cp/options/user.php?user_id=<?php echo $user['user_id']?>&amp;action=edit">Edit</a>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
	</table>
	<br />
	<input type="submit" name="delete" value="Delete Selected" />
</form>
