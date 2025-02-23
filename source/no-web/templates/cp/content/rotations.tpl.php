
	<form action="" method="post" class="content-form">
		<fieldset>
			<legend>New Set</legend>
			<label for="name">Name:</label>
			<input type="text" name="name" id="name" />
			<input type="submit" name="new_set" value="Create New Set" />
		</fieldset>
	</form>

<div class="section">
	<h2>Rotation Sets</h2>
	<table>
		<tr>
			<th>Name</th>
			<th class="actions"></th>
		</tr>
<?php foreach ($this->sets as $set) { ?>
		<tr>
			<td><?php echo $set['name'] ?></td>
			<td class="actions">
				<a href="?view_content=<?php echo $set['id'] ?>">View/Add Content</a>
			</td>
		</tr>
<?php } ?>
	</table>
</div>

   	<?php if (isset($this->content)) { ?>
	<div class="section">
		<h2>Content</h2>
		<table>
			<tr>
				<th>Name</th>
				<th class="actions"></th>
			</tr>
<?php foreach ($this->content as $content) { ?>
			<tr>
				<td><?php echo $content['content_format'] ?></td>
				<td>
					<a href="#" class="view-content" rel="<?php echo $content['id'] ?>">View</a> |
					<a href="?view_content=<?php echo $_GET['view_content'] ?>&amp;content_id=<?php echo $content['id'] ?>&amp;delete=1">Delete</a>
				</td>
			</tr>
			<tr id="content_<?php echo $content['id'] ?>">
				<td colspan="2">
					<textarea name="content[<?php echo $content['id'] ?>]" rows="5" cols="60"><?php echo $content['data'] ?></textarea>
				</td>
			</tr>
<?php } ?>
		</table>
		<p class="add"><a href="#" class="add-content">Add more content...</a></p>
	</div>
	<?php } ?>
   	<form class="content-form" method="post" id="add-content" style="display: none;">
		<fieldset>
			<legend>Add Content</legend>

			<div class="row clearfix">
				<label for="name">Name</label>
				<input type="text" name="name" id="name" />
			</div>
			<div class="row clearfix">
				<label>Content</label>
				<textarea name="content" rows="5" cols="60"></textarea>
			</div>
			<div class="submit">
				<input type="hidden" name="content_id" value="<?php echo $_GET['view_content'] ?>" />
				<input type="submit" name="add_content" value="Add Content" />
			</div>
		</fieldset>
	</form> 

