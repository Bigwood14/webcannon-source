<h1>Seeds</h1>
<br />

<form method="post" action="" class="content">
	<label>User/Email</label>
	<input type="text" name="seed_username" />

	<label>Password</label>
	<input type="text" name="seed_password" />

	<input type="submit" name="add" value="Add" />
</form>
<br /><br />
<table class="content" cellspacing="0" cellpadding="0">
	<tr>
		<th class="first">Username</th>
		<th>Password</th>
		<th colspan="2">Action</th>
	</tr>
	<?php $count = count($template->seeds); $i = 0; foreach ($template->seeds as $seed) { $last = ''; if (++$i == $count) $last = 'class="last"';?>
	<tr <?php echo $last?>>
		<td class="first"><?php echo $seed['username']?></td>
		<td><?php echo $seed['password'] ?></td>
		<td>Log</td>
		<td><a href="?delete=<?php echo $seed['seed_account_id']?>">Delete</a></td>
	</tr>
	<?php } ?>
</table>
