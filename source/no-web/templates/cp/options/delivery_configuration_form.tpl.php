	<h1>Create Delivery Configuration</h1>

<?php if(!empty($this->error)) { ?>
<div class="error">
	<p>There was an error with your form.</p>
	<?php foreach ($this->errors as $error) { ?>
		<?php print $error ?><br />
	<?php } ?>
</div>
<?php } ?>

	<form method="post" class="content-form">
		<fieldset>
			<legend>Configuration Name</legend>

			<div class="row clearfix">
				<?php echo $this->form->print_label('name')?>
				<?php echo $this->form->print_element('name')?>
			</div>
		</fieldset>

		<fieldset>
			<legend>Headers</legend>

			<div class="row clearfix">
				<?php echo $this->form->print_label('header')?>
				<?php echo $this->form->print_element('header')?>
			</div>
		</fieldset>

		<fieldset>
			<legend>Encoding</legend>

<?php $fields = array('encoding_text', 'encoding_html', 'encoding_aol'); ?>		
<?php foreach ($fields as $field) { ?>
			<div class="row clearfix">
				<?php echo $this->form->print_label($field)?>
				<?php echo $this->form->print_element($field)?>
			</div>
<?php } ?>
		</fieldset>

		<fieldset>
			<legend>Charset</legend>

<?php $fields = array('charset_head', 'charset_text', 'charset_html', 'charset_aol'); ?>		
<?php foreach ($fields as $field) { ?>
			<div class="row clearfix">
				<?php echo $this->form->print_label($field)?>
				<?php echo $this->form->print_element($field)?>
			</div>
<?php } ?>
		</fieldset>

		<fieldset>
			<legend>Boundry</legend>

		<?php $fields = array('boundry_prefix', 'boundry_postfix'); ?>		
<?php foreach ($fields as $field) { ?>
			<div class="row clearfix">
				<?php echo $this->form->print_label($field)?>
				<?php echo $this->form->print_element($field)?>
			</div>
<?php } ?>
		</fieldset>

		<div class="submit clearfix">
			<input type="submit" value="Create" name="submit" />
		</div>
	</form>
