<form action="<?php echo $template->action; ?>" method="POST">
<?php if ($template->content_id) { ?>
<input type="hidden" name="id" value="<?php echo $template->content_id; ?>">
<?php } ?>

<h1><?php echo $template->title; ?></h1>

<?php if (!$template->content_id) { ?>
<div class="section" id="content_name">
    <h2>Name</h2>

    <input type="text" name="name" value="<?php echo htmlspecialchars($template->content_name); ?>">
</div>
<?php } ?>

<div class="section" id="text_content">
    <h2>Text Content</h2>

    <textarea name="text_content" rows="10" cols="40"><?php 
        echo htmlspecialchars($template->text_content);
    ?></textarea>
</div>

<div class="section" id="html_content">
    <h2>HTML Content</h2>

    <textarea name="html_content" rows="10" cols="40"><?php 
        echo htmlspecialchars($template->html_content); 
    ?></textarea>
</div>

<input type="submit" value="Submit">
</form>

