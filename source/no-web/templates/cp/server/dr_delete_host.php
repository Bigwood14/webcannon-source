<form method="POST">
<input type="hidden" name="id" value="<?php echo $template->host_id; ?>">

<h1><?php echo $template->title; ?></h1>

<p>Are you sure you wish to delete this host? This action cannot be undone.</p>

<input type="submit" value="Yes">
<input type="submit" name="cancel" value="No">
</form>
