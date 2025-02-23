  <h1>General Configuration</h1>
<br />  

  <form action="/cp/options/general_config.php" method="post">

  <?php
  if(@$template->complete == 1)
  {
  ?>
  <span class="error">Configuration Updated</span><br />
  <?php
  }
  ?>
  <div id="contenttable">
  <table width="100%" cellspacing="2" border="0" cellpadding="2">
  <tbody>
  
   <tr>
      <th>Page Title</th>
    </tr>
    <tr>
      <td><input type="text" name="cfg[PAGE_TITLE]" size="30" value="<?php echo @$template->config2['PAGE_TITLE']?>" /></td>
    </tr>
  
    <tr>
      <th>Address</th>
    </tr>
    <tr>
      <td><textarea cols="30" rows="7" name="cfg[ADDRESS]"><?php echo @$template->config2['ADDRESS']?></textarea></td>
    </tr>
    
     <tr>
      <th>Unsub Redirect</th>
    </tr>
     <tr>
      <td>Specify a custom page to redirect people to when they have unsubscribed. Leave blank for none.</td>
    </tr>
    <tr>
      <td><input type="text" name="cfg[UNSUB_REDIRECT]" size="30" value="<?php echo @$template->config2['UNSUB_REDIRECT']?>" /></td>
    </tr>
    
    
    <tr>
      <th>Default Test</th>
    </tr>
     <tr>
      <td>These address will be prepopulated in the send test field, one per line.</td>
    </tr>
    <tr>
      <td><textarea cols="50" rows="5" name="cfg[DEFAULT_TESTS]"><?php echo $template->config2['DEFAULT_TESTS']?></textarea></td>
    </tr>
    
   
    <tr>
      <th>Bounce Prune</th>
    </tr>
     <tr>
      <td>How many times should an email bounce before it is pruned, must be a number. If you enter 0 no pruning will be applied.</td>
    </tr>
    <tr>
      <td><input type="text" name="cfg[BOUNCE_PRUNE]" size="2" value="<?php echo $template->config2['BOUNCE_PRUNE']?>" /></td>
    </tr>
    
    <tr>
      <th>Bounce Ignore</th>
    </tr>
     <tr>
      <td>Bounce sayings retured that contain these sayings will be ignored and not classed as a bounce. <br />
      One saying per line.</td>
    </tr>
    <tr>
      <td><textarea cols="60" rows="7" name="cfg[BOUNCE_IGNORE]"><?php echo $template->config2['BOUNCE_IGNORE']?></textarea></td>
    </tr>
    
    <tr>
      <th>Complaint Email</th>
    </tr>
     <tr>
      <td>When a complaint is sent to complaint@youdomain.com a report will be sent to this email address.</td>
    </tr>
    <tr>
      <td><input type="text" value="<?php echo $template->config2['COMPLAINT_EMAIL']?>" name="cfg[COMPLAINT_EMAIL]" /></td>
    </tr>
	<?php foreach ($this->emails as $email) { ?>  
	<tr>
		<th><?php echo ucfirst($email) ?> Email</th>
	</tr>
	<tr>
		<td>Forward all emails sent to <?php echo strtolower($email) ?>@yourdomain.com to a certain address.</td>
	</tr>
	<tr>
		<td>
			<input type="text" value="<?php echo @$template->config2[$email.'_EMAIL']?>" name="cfg[<?php echo $email ?>_EMAIL]" />
			Process for complaints?
			<?php
			$yes = $no = '';
			if (@$template->config2[$email.'_COMPLAINT'] == 'yes')
				$yes = ' selected';
			else
				$no = ' selected';
			?>
			<select name="cfg[<?php echo $email ?>_COMPLAINT]">
				<option value="no"<?php echo $no?>>no</option>
				<option value="yes"<?php echo $yes?>>yes</option>
			</select>
		</td>
	</tr>
   <?php } ?>

	<tr><th>AOL IP Test Email</th></tr>
	<tr><td>Send all AOL IP tester email to these address's one per line (<strong>has to be an AOL address</strong>). They will be picked from randomly.</td></tr>
	<tr><td><textarea rows="10" cols="30" name="cfg[AOL_IP_TEST_EMAIL]"><?php echo @$template->config2['AOL_IP_TEST_EMAIL']?></textarea></td></tr>

    <tr>
      <th>Default Retry</th>
    </tr>
    <tr>
      <td>
      <?php
      @$sel[$template->config2['DEFAULT_RETRY']] = ' selected';
      ?>
      <select name="cfg[DEFAULT_RETRY]">
      <option<?php echo @$sel[0]?>>0</option>
      <option<?php echo @$sel[1]?>>1</option>
      <option<?php echo @$sel[2]?>>2</option>
      <option<?php echo @$sel[3]?>>3</option>
      <option<?php echo @$sel[4]?>>4</option>
      <option<?php echo @$sel[5]?>>5</option>
      </select></td>
    </tr>
   
   	<tr>
		<th>Draft Archive Display</th>
	</tr>
	<tr>
		<td>
			<select name="cfg[ARCHIVE_DISPLAY]">
				<option value="normal">Normal</option>
				<option value="aol"<?php if (@$template->config2['ARCHIVE_DISPLAY'] == 'aol') echo 'selected="selected" '?>>AOL</option>
			</select>
		</td>
	</tr>
    
   	<tr>
		<th>Display Index Page</th>
	</tr>
	<tr>
		<td>
			<select name="cfg[INDEX_PAGE]">
				<option value="yes">Yes</option>
				<option value="no"<?php if (@$template->config2['INDEX_PAGE'] == 'no') echo 'selected="selected" '?>>No</option>
			</select>
		</td>
	</tr>

    <tr>
      <th>Engine Settings</th>
    </tr>
    <tr>
      <td>Connect Timeout: <input type="text" name="cfg[ENGINE_CT]" size="2" value="<?php echo $template->config2['ENGINE_CT']?>" /></td>
    </tr>
    <tr>
      <td>Read Timeout: <input type="text" name="cfg[ENGINE_RT]" size="2" value="<?php echo $template->config2['ENGINE_RT']?>" /></td>
    </tr>
    <tr>
      <td>DNS Server: <input type="text" name="cfg[ENGINE_DNS]" size="12" value="<?php echo $template->config2['ENGINE_DNS']?>" /></td>
    </tr>
    <tr>
      <td>Max Sends per Connection: <input type="text" name="cfg[ENGINE_MSC]" size="2" value="<?php echo $template->config2['ENGINE_MSC']?>" /></td>
    </tr>
    <tr>
      <td>Max Threads: <input type="text" name="cfg[ENGINE_THREADS]" size="4" value="<?php echo @$template->config2['ENGINE_THREADS']?>" /></td>
    </tr>
    <tr>
      <td>Max Connections Per IP: <input type="text" name="cfg[ENGINE_CPI]" size="5" value="<?php echo @$template->config2['ENGINE_CPI']?>" /></td>
    </tr>

    <tr>
      <td>  <input type="submit" name="submit" value="Update Configuration" /> </td>
    </tr>
    
  </tbody>
</table>
</div>


</form>
