<h1>Remote List Setting</h1>
<br />
<?php if (isset($this->updated)) { ?>
<strong>Settings Updated</strong><br />
<?php } ?>
<p>For list <em><?php echo $this->list['name'] ?></em></p>
<form method="post" class="content-form">
	<fieldset>
		<legend>Connection</legend>

		<div class="row clearfix">
			<?php echo $this->form->print_label('remote_hostname')?>
			<?php echo $this->form->print_element('remote_hostname')?>
		</div>

		<div class="row clearfix">
			<?php echo $this->form->print_label('remote_username')?>
			<?php echo $this->form->print_element('remote_username')?>
		</div>

		<div class="row clearfix">
			<?php echo $this->form->print_label('remote_password')?>
			<?php echo $this->form->print_element('remote_password')?>
		</div>
	</fieldset>

	<fieldset>
		<legend>Other</legend>

		<div class="row clearfix">
			<?php echo $this->form->print_label('send_unsubs')?>
			<?php echo $this->form->print_element('send_unsubs')?>
		</div>
	</fieldset>

	<div class="clearfix submit">
		<input type="submit" name="update" value="Update" />
	</div>
</form>
