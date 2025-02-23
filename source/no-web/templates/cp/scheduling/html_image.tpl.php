<?php if (isset($template->file)) { ?>
Image Uploaded<br />
<?php foreach ($template->file as $file) { ?>
http://{{dn}}/img/<?php echo $file ?><br />
<?php } } ?>
<form method="post" enctype="multipart/form-data">
	<input type="file" name="image[]" /><br />
	<input type="file" name="image[]" /><br />
	<input type="file" name="image[]" />
	<input type="submit" name="submit" value="Upload" />
</form>
