<h1>Lists</h1>
<br />
<h2>Local Lists</h2> 

<form action="/cp/management/lists.php" method="post" class="content-form">

<fieldset>
	<legend>Add List</legend>

	<div class="row clearfix">
		<label for="name">Name:</label>
      	<input type="text" name="name" value="" id="name" />
    </div>

	<div class="submit clearfix">
		<input type="submit" name="add_local"  value="Add List" />
	</div>

</fieldset>

<br />

<table class="content" cellpadding="0" cellspacing="0">
	<tr>
		<th width="10" class="first">&nbsp;</th>
		<th width="10">ID</th>
		<th>List Name</th>
		<th>No. Emails</th>
	</tr>
	<?php foreach($template->local_lists AS $list) { ?>
	<tr>
		<td width="10" class="first"><input type="checkbox" name="selected[]" value="<?php echo $list['list_id'] ?>"></td>
		<td><?php echo $list['list_id']?></td>
		<td><?php echo $list['name']?></td>
		<td><?php echo number_format($list['count'])?></td>
	</tr>
	<?php
	}
	?>
</table>
<br />
<?php if (empty($template->mailer)) { ?>
<input type="submit" name="delete" value="Delete List(s)" onClick="return confirmSubmit('Are you sure you wish to delete this list(s)?')" />
<?php } ?>
</form>
<br />

<h2>Remote Lists</h2>
<br />

<?php if (empty($template->remote_lists)) { ?>
<strong>No Remote Lists</strong>
<?php } else { ?>
<form action="" method="post" class="content-form">
	<table class="content" cellpadding="0" cellspacing="0">
		<tr>
			<th width="10" class="first">&nbsp;</th>
			<th width="10">ID</th>
			<th>List Name</th>
			<th>No. Emails</th>
			<th>Hostname</th>
			<th>Username</th>
			<th>Action</th>
		</tr>
		<?php foreach($template->remote_lists AS $list) { ?>
		<tr>
			<td width="10" class="first"><input type="checkbox" name="selected[]" value="<?php echo $list['list_id'] ?>"></td>
			<td><?php echo $list['list_id']?></td>
			<td><?php echo $list['name']?></td>
			<td><?php echo number_format($list['count'])?></td>
			<td><?php echo $list['remote_hostname'] ?></td>
			<td><?php echo $list['remote_username'] ?></td>
			<td>
				<a href="/cp/management/lists.php?action=log&amp;list_id=<?php echo $list['list_id'] ?>">Log</a> |
				<a href="/cp/management/lists.php?action=setting&amp;list_id=<?php echo $list['list_id'] ?>">Settings</a> 
			</td>
		</tr>
		<?php
		}
		?>
	</table>
	<br />
	<?php if (empty($template->mailer)) { ?>
	<input type="submit" name="unsubscribe" value="Unsubscribe List(s)" onClick="return confirmSubmit('Are you sure you wish to unsubscribe this list(s)?')" />
	<?php } ?>
</form>
<br />
<?php } ?>
<br />
<?php if (empty($template->mailer)) { ?>
<form action="" method="post" class="content-form">
	<fieldset>
		<legend>List Remote Lists</legend>

		<div class="row clearfix">
			<label for="remote-host">Hostname:</label>
			<input type="text" name="remote_host" value="<?php print (empty($template->remote_host)) ? 'api.prime.webcannon.com' : $template->remote_host ?>" id="remote-host" />
		</div>

		<div class="row clearfix">
			<label for="remote-remote-user">Username:</label>
			<input type="text" value="<?php echo @$template->remote_user ?>" name="remote_user" id="remote-remote-user" />
		</div>

		<div class="row clearfix">
			<label for="remote-remote-pass">Password:</label>
			<input type="text" value="<?php echo @$template->remote_pass ?>" name="remote_pass" id="remote-remote-pass" />
		</div>

		<div class="submit clearfix">
			<input type="submit" name="remote_get"  value="Show Lists" />
		</div>

	</fieldset>

	<?php if (isset($template->remote_listing)) { ?>
	<?php if (empty($template->remote_listing)) { ?>
	<p>No Lists</p>
	<?php } else { ?>

	<table class="content" cellspacing="0" cellpadding="0">
		<tr>
			<th class="first" width="20">&nbsp;</th>
			<th>Name</th>
			<th>#</th>
		</tr>

		<?php foreach ($template->remote_listing as $list) { ?>
		<tr>
			<td class="first"><input type="checkbox" name="selected[]" value="<?php echo $list['list_id'] ?>" /></th>
			<td><?php echo $list['name'] ?></td>
			<td><?php echo $list['count'] ?></td>
		</tr>
		<?php } ?>
	</table>
	<br />
	<input type="submit" name="subscribe" value="Subscribe" />
	<?php } } } ?>
</form>
