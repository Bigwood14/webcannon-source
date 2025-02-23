<form action="/cp/options/delete_content.php" method="POST">
<input type="hidden" name="id" value="<?php echo $template->content_id; ?>">

<h1><?php echo $template->title; ?></h1>

<p>Are you sure you wish to delete this <?php echo $template->type; ?>?
This action cannot be undone.</p>

<input type="submit" value="Yes">
<input type="submit" name="cancel" value="No">
</form>
