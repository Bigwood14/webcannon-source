<h1>Create New Draft</h1>

<?php if(!empty($this->error)) { ?>
<div class="error">
	<p>There was an error with your form.</p>
	<?php foreach ($this->errors as $error) { ?>
		<?php print $error ?><br />
	<?php } ?>
</div>
<?php } ?>

<form method="post" class="content-form" id="draft-form">
	<fieldset>
		<legend>Draft Title</legend>

		<div class="row clearfix">
			<?php echo $this->form->print_label('title')?>
			<?php echo $this->form->print_element('title')?>
			<?php echo $this->form->print_element('server_id')?>
		</div>
	</fieldset>

	<fieldset>
		<legend>Delivery Configurations</legend>

<?php $fields = array('form-delivery_configuration_id'); ?>
		<?php foreach ($fields as $field) { ?>
			<div class="row clearfix">
				<?php echo $this->form->print_label($field)?>
				<?php echo $this->form->print_element($field)?>
			</div>
		<?php } ?>
		<div class="row clearfix">
			<label>Personlization:</label>
			<span class="input"><a href="/cp/scheduling/personalization-how.php" id="personalization-customize">Customize</a></label>
		</div>
	</fieldset>

	<fieldset id="domains">
		 <legend>Domain/IP Selection</legend>
<?php if (!empty($this->form->group)) { ?>
<?php foreach ($this->form->group as $group) { ?>
		<div class="ip-group">
			<p class="ip-group-title">
				<?php if ($group['count'] <= @$group['checked']) { $class= 'ip-group-ips';?>
					<a href="#"><img src="/images/misc/plus.gif" /></a>
					<input type="checkbox" id="ip-title-<?php echo $group['domain_group_id'] ?>" class="ip-group-checkbox" checked="checked" />
					<label for="ip-title-<?php echo $group['domain_group_id'] ?>"><?php echo $group['name'] ?></label>
				<?php } else  { $class = 'ip-group-ips'; ?>
					<?php if (@$group['checked'] > 0) { $class .= ' ip-group-ips-show'; ?>
						<a href="#"><img src="/images/misc/minus.gif" /></a>
					<?php } else { ?>
					<a href="#"><img src="/images/misc/plus.gif" /></a>
					<?php } ?>
					<input type="checkbox" id="ip-title-<?php echo $group['domain_group_id'] ?>" class="ip-group-checkbox" />
					<label for="ip-title-<?php echo $group['domain_group_id'] ?>"><?php echo $group['name'] ?></label>
				<?php } ?>
			</p>
			<div class="<?php echo $class?>">
			<?php foreach ($group['ips'] as $ip) { ?>
				<span>
					<?php echo $this->form->print_element($ip)?>
					<?php echo $this->form->print_label($ip)?>
				</span>
				<br />
			<?php } ?>
			</div>
		</div>
<?php } } else { ?>
		 <?php for($i=0;$i<$this->form->counts['domains'];$i++) {?>
			<span>
			 	<?php echo $this->form->print_element('domain['.$i.']')?>
			 	<?php echo $this->form->print_label('domain['.$i.']')?>
			</span>
		 <?php } } ?>
		 <br />
			</fieldset>

	<fieldset>
		<legend>AOL</legend>
		<span>
			<?php echo $this->form->print_label('aol_rotate')?>
			<?php echo $this->form->print_element('aol_rotate')?> send a max of <?php echo $this->form->print_element('max_per_ip')?> per IP.
		</span>
		<br />
		<span>
			<?php echo $this->form->print_label('aol_check_total')?>
			<?php echo $this->form->print_element('aol_check_total')?>

			<?php echo $this->form->print_label('aol_check_hits')?>
			<?php echo $this->form->print_element('aol_check_hits')?>
		</span>

	</fieldset>

	<fieldset>
		 <legend>List Selection</legend>
		<table cellpadding="0" cellspacing="0" class="content" style="width: auto">
			<tr>
				<th class="first">&nbsp;</th>
				<th>List</th>
				<th>Start</th>
				<th>Max</th>
			</tr>
			<?php for($i=0;$i<$this->form->counts['lists'];$i++) {?>
			<tr>
				<td class="first center"><?php echo $this->form->print_element('list['.$i.']')?></td>
			 	<td><?php echo $this->form->print_label('list['.$i.']')?></td>
			 	<td><?php echo $this->form->print_element('list_skip['.$this->form->list_ids[$i].']')?></td>
				<td><?php echo $this->form->print_element('list_max['.$this->form->list_ids[$i].']')?></td>
			</tr>
			 <?php } ?>
		 </table>
	</fieldset>

	<fieldset>
		<legend>From Domain</legend>

		<div class="row clearfix">
			<?php echo $this->form->print_label('from_domain')?>
			<?php echo $this->form->print_element('from_domain')?>
			Leave blank if you dont know what this is.
		</div>
	</fieldset>


	<fieldset>
		<legend>From Lines</legend>
	
		<?php for ($i=0;$i<$this->form->counts['froms'];$i++) { ?>	
			<div class="row clearfix" id="from-main">
				<?php echo $this->form->print_label('from['.$i.']')?>
				<?php echo $this->form->print_element('from['.$i.']')?>
				<?php if ($i>0) {?><a href="#" class="from-remove">remove</a><?php } ?>
			</div>
		<?php }?>
		<div class="row clearfix" id="from-clone">
			<?php echo $this->form->print_label('from[]')?>
			<?php echo $this->form->print_element('from[]')?>
			<a href="#" class="from-remove">remove</a>
		</div>

		<div class="row clearfix" id="from-link">
			<p><a href="#" class="from-new">add new</a></p>
		</div>
	</fieldset>


	<fieldset>
		<legend>Subject Lines</legend>
	
		<?php for ($i=0;$i<$this->form->counts['subjects'];$i++) { ?>	
			<div class="row clearfix" id="subject-main">
				<?php echo $this->form->print_label('subject['.$i.']')?>
				<?php echo $this->form->print_element('subject['.$i.']')?>
				<?php if ($i>0) {?><a href="#" class="subject-remove">remove</a><?php } ?>
			</div>
		<?php }?>
		<div class="row clearfix" id="subject-clone">
			<?php echo $this->form->print_label('subject[]')?>
			<?php echo $this->form->print_element('subject[]')?>
			<a href="#" class="subject-remove">remove</a>
		</div>

		<div class="row clearfix" id="subject-link">
			<p><a href="#" class="subject-new">add new</a></p>
		</div>
	</fieldset>

	<fieldset>
		<legend>Bodies</legend>

		 <div id="bodies">
			<ul>
				<li><a href="#fragment-1"><span>Text</span></a></li>
				<li><a href="#fragment-2"><span>HTML</span></a></li>
				<li><a href="#fragment-3"><span>AOL</span></a></li>
				<li><a href="#fragment-4"><span>Yahoo</span></a></li>
			</ul>
			<div id="fragment-1">
				<div class="row clearfix checkbox">
					<?php echo $this->form->print_element('body_text_check')?>
					<?php echo $this->form->print_label('body_text_check')?>
				</div>
				<?php echo $this->form->print_element('body_text')?>
			</div>
			<div id="fragment-2">
				<div class="row clearfix checkbox">
					<?php echo $this->form->print_element('body_html_check')?>
					<?php echo $this->form->print_label('body_html_check')?>
				</div>
				<?php echo $this->form->print_element('body_html')?>
				<a href="/cp/scheduling/html_image.php" class="html-image">Add Image</a>
				<div class="row clearfix checkbox">
					<?php echo $this->form->print_element('embed_images')?>
					<?php echo $this->form->print_label('embed_images')?>
				</div>
			</div>
			<div id="fragment-3">
				<div class="row clearfix checkbox">
					<?php echo $this->form->print_element('body_aol_check')?>
					<?php echo $this->form->print_label('body_aol_check')?>
				</div>
				<?php echo $this->form->print_element('body_aol')?>
			</div>
			<div id="fragment-4">
				<div class="row clearfix checkbox">
					<?php echo $this->form->print_label('yahoo_date')?>
					<?php echo $this->form->print_element('yahoo_date')?>(mm/dd/yyyy hh:mm:ss) - leave blank for default
				</div>

				<div class="row clearfix checkbox">
					<?php echo $this->form->print_element('body_yahoo_check')?>
					<?php echo $this->form->print_label('body_yahoo_check')?>
				</div>
				<?php echo $this->form->print_element('body_yahoo')?>
			</div>
		</div>	
	</fieldset>

	<fieldset>
		<legend>Link Tracking</legend>

		<?php for ($i=0;$i<$this->form->counts['tracked_links'];$i++) { ?>
			<div class="row clearfix">
				<?php echo $this->form->print_label('tracked_link['.$i.']')?>
				<?php echo $this->form->print_element('tracked_link['.$i.']')?>
				<?php echo $this->form->print_element('form-tracked_link_action['.$i.']')?>
				<?php echo $this->form->print_element('form-tracked_link_target['.$i.']')?>
				<a href="#" class="link-remove">remove</a>
			</div>
		<?php } ?>

		<div class="row clearfix" id="link-clone">
			<?php echo $this->form->print_label('tracked_link[]')?>
			<?php echo $this->form->print_element('tracked_link[]')?>
			<?php echo $this->form->print_element('form-tracked_link_action[]')?>
			<?php echo $this->form->print_element('form-tracked_link_target[]')?>
			<a href="#" class="link-remove">remove</a>
		</div>

		<div class="row clearfix" id="link-links">
			<p>
				<a href="#" class="link-new">add new</a> -
				<a href="#" class="link-auto">auto fill links</a> -
				<a href="#" class="link-image-auto">auto fill images</a>
			</p>	
		</div>
	</fieldset>

	<fieldset>
		<legend>Open Tracking</legend>
			<div class="row clearfix">
				<?php echo $this->form->print_label('form-open_action')?>
				<?php echo $this->form->print_element('form-open_action')?>
				<?php echo $this->form->print_element('form-open_list_id')?>
			</div>
	</fieldset>



	<fieldset>
		<legend>Header/Footer</legend>

		<div class="row clearfix">
			<?php echo $this->form->print_label('form-header_id')?>
			<?php echo $this->form->print_element('form-header_id')?>
		</div>

		<div class="row clearfix">
			<?php echo $this->form->print_label('form-footer_id')?>
			<?php echo $this->form->print_element('form-footer_id')?>
		</div>
	</fieldset>

	<fieldset>
		<legend>Domain Selection</legend>

		<div class="row clearfix">
			<?php echo $this->form->print_label('domain_only')?>
			<?php echo $this->form->print_element('domain_only')?>
		</div>
		<div class="row clearfix">
			<?php echo $this->form->print_label('domain_not')?>
			<?php echo $this->form->print_element('domain_not')?>
		</div>

	</fieldset>

	<fieldset>
		<legend>Other</legend>

		<div class="row clearfix">
			<?php echo $this->form->print_label('seed_rotate')?>
			<?php echo $this->form->print_element('seed_rotate')?>
		</div>

		<div class="row clearfix">
			<?php echo $this->form->print_label('seeds')?>
			<?php echo $this->form->print_element('seeds')?>
<br />To seed every X names enter address like this "me@mydomain.com:5000" replacing 5000 with your desired amount.
		</div>
	
		<div class="row clearfix">
			<?php echo $this->form->print_label('threads'); ?>
			<?php echo $this->form->print_element('threads'); ?> 0 for default (fastest).
		</div>	
		<?php for ($i=0;$i<$this->form->counts['suppressions'];$i++) { ?>	
			<div class="row clearfix" id="suppression-main">
				<?php echo $this->form->print_label('form-suppression_list['.$i.']')?>
				<?php echo $this->form->print_element('form-suppression_list['.$i.']')?>
				<?php if ($i>0) {?><a href="#" class="suppression-remove">remove</a><?php } ?>
			</div>
		<?php }?>
		<div class="row clearfix" id="suppression-clone">
			<?php echo $this->form->print_label('form-suppression_list[]')?>
			<?php echo $this->form->print_element('form-suppression_list[]')?>
			<a href="#" class="suppression-remove">remove</a>
		</div>

		<div class="row clearfix" id="suppression-link">
			<p><a href="#" class="suppression-new">add another</a></p>
		</div>


	</fieldset>

	<div class="submit clearfix">
		<input type="submit" value="<?php echo $this->submit ?>" name="submit" id="draft-submit" />
	</div>
</form>
