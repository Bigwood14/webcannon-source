<form action="/cp/options/set_default_content.php" method="POST">
<input type="hidden" name="id" value="<?php echo $template->content_id; ?>">

<h1><?php echo $template->title; ?></h1>

<p>Are you sure you wish to make this the default <?php echo $template->type; ?>?</p>

<input type="submit" value="Yes">
<input type="submit" name="cancel" value="No">
</form>
