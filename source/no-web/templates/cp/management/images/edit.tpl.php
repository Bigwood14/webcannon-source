<h1>Edit Image</h1>
<?php if (isset($template->file)) { ?>
<p><strong>Done</strong></p>
<?php } ?>
<form method="post" enctype="multipart/form-data">
	<input type="file" name="image[]" />
	<input type="submit" name="submit" value="Upload" />
</form>
