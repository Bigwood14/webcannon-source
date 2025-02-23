<form action="<?php echo $template->action; ?>" method="POST">
<h1><?php echo $template->title; ?></h1>
<?php if ($template->host_id) { ?>
<input type="hidden" name="id" value="<?php echo $template->host_id; ?>">
<?php } ?>

<div class="section">
    <h2>Address</h2>

    <div class="notes">
        <p>This is the remote address of the host you wish to use. It can
        be a host name or IP so long as it is reachable by that address
        over the internet using SSH.</p>
    </div>

    <input type="text" name="address" value="<?php echo $template->address; ?>">
</div>

<div class="section">
    <h2>Password</h2>

    <div class="notes">
        <p>This is the <strong>root</strong> password of the remote system.
        It is required to log into the machine with the proper privileges
        to set up DR networking.</p>

        <p>Enter it twice to make sure you type it right.</p>
    </div>

    <input type="password" name="password">
    <input type="password" name="password_confirmation">
</div>

<br>

<input type="submit" value="Submit">
</form>
