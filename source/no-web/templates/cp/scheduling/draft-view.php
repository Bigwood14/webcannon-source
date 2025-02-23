<h1>View Draft</h1>

<?php
  if(@$template->sent == 1)
  {
  ?>
  <br /><br /><span class="error">Test emails sent</span><br />
  <div id="contenttable">
  <table cellpadding="2" cellspacing="0" border="0" width="100%">
  <tr>
    <th align="center">&nbsp;</th>
    <th>Address</th>
    <th>Why</th>
  </tr>
  <tbody>
  
  <?php
  foreach($_GET['e'] AS $k=>$v)
  {
  ?>
      <tr>
      <td align="center"><?php if($v == "yes") {?><img src="/images/misc/check.gif" width="14" height="13" border="0" /><?php } else {?><img src="/images/misc/x.gif" width="14" height="13" border="0" /><?php }?></td>
      <td><?php echo $k?></td>
      <td><?php echo stripslashes(urldecode($v))?></td>
    </tr>
  <?php
  }
  ?>
    </tbody>
</table>
</div>
  <?php
  }
  ?>
  <br />

<div id="contenttable">

<?php
if($template->draft['state'] == '0')
{
?>
  <!-- Edit -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">Edit</th>
    </tr>
    <tr>
      <td>Something wrong? <strong><a href="/cp/scheduling/draft.php?draft_id=<?php echo $template->draft['id']?>&action=edit">Edit this draft.</a></strong></td>
    </tr>
  </table>
  <!-- /Edit -->
  <br />
<?php
}
elseif($template->is_paused)
{
?>
  <!-- Paused? -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">Paused Draft</th>
    </tr>
    <tr>
      <td><strong><a href="/cp/scheduling/draft.php?draft_id=<?php echo $template->draft['id']?>&edit=1">Edit.</a></strong></td>
    </tr>
  </table>
  <!-- /Paused? -->
  <br />
<?php	
}
else
{
?>
  <!-- Copy -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">Copy to Draft</th>
    </tr>
    <tr>
      <td>Want to send another like this? <strong><a href="/cp/scheduling/draft.php?draft_id=<?php echo $template->draft['id']?>">Copy to draft.</a></strong></td>
    </tr>
  </table>
  <!-- /Copy -->
  <br />
<?php
}
?>

 
  <!-- Domains/Lists -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">
        <strong>Domains + Lists</strong>
      </th>
    </tr>
  </table>
  
  <div style="display:block" id="insideSubCategory2">
  
  <table width="100%" cellspacing="0" border="0" cellpadding="2">  
    <tr>
      <td width="250">Domains</td>
      <td>Lists</td>
    </tr>
    
    <tr>
    
      <td valign="top">
        <?php 
        if(!is_array($template->draft['domains'])) { ?>
        <strong><font color="blue">Using Rotations</font></strong><br />
        <?php } else { 
        foreach($template->draft['domains'] AS $domain) { ?>
         <strong><font color="green"><?php echo $domain['domain'] ?></font></strong><br />
        <?php } } ?>
      </td>
      
      <td valign="top">
        <?php
        if(!empty($template->no_list))
        {
        ?>
        <strong><font color="red">No Lists available for this draft.</font></strong><br /> Scheduling has been disabled.
        <?php
        }
        else
        {
        ?>
        <?php foreach($template->draft['lists'] AS $list) { ?>
         <strong><font color="green"><?php echo $list['name'] ?></font> (skip: <?php echo number_format($list['skip']) ?>, max: <?php echo number_format($list['max']) ?>)</strong><br />
        <?php } } ?>
      </td>
      
    </tr>
  </table>
  
  </div>
  <!-- /Domains/Lists -->
  <br />
  
  
  <!-- From/Subject Lines -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">
        <strong>From + Subject Lines</strong></th>
    </tr>
  </table>
  
  <div style="display:block" id="insideSubCategory3">
  
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <td width="250">From Lines</td>
      <td>Subject Lines</td>
    </tr>
    <tr>
    
      <td valign="top">
        <?php foreach($template->draft['froms'] AS $from) { ?>
         <strong><font color="green"><?php echo htmlentities($from) ?></font></strong><br />
        <?php } ?>
      </td>
      
      <td valign="top">
        <?php foreach($template->draft['subjects'] AS $subject) { ?>
         <strong><font color="green"><?php echo $subject ?></font></strong><br />
        <?php } ?>
      </td>
      
    </tr>
  </table>
  </div>
  <!-- /From/Subject Lines -->
  <br />
  
  
  <!-- Link Tracking -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">
        <strong>Link Tracking</strong>
      </th>
    </tr>
  </table>
  
  <div style="display:block" id="insideSubCategory4">
  
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <td>
		You have <?php print count($this->draft['tracked_links']) ?> links tracked/masked.
		<?php foreach ($this->draft['tracked_links'] as $link) { ?>
			<br />
			[ <a href="/cp/management/tracked_link.php?action=edit&amp;tracked_link_id=<?php echo $link['tracked_link_id'] ?>" class="tracked-link-edit">edit</a> ] -
			<a href="<?php echo $link['url'] ?>"><?php echo $link['url'] ?></a> - <?php print $this->draft['link_tracking_actions'][$link['action']] ?>
			<?php if (!empty($link['list_id'])) { ?> - <?php echo $this->draft['list_data'][$link['list_id']] ?><?php } ?>	
		<?php } ?>
      </td>
    </tr>
  </table>
  </div>
  <!-- /Link Tracking -->
  <br />
  
<?php
// Do we have a text part?
if(!empty($template->draft['body']))
{
?>  
  <!-- Text -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">
        <strong>Text Creative</strong>
      </th>
    </tr>
  </table>
  
  <div style="display:block" id="insideSubCategory6">
  
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <td>
        <textarea readonly="1" cols="60" rows="20"><?php echo $template->draft['body']?></textarea>
      </td>
    </tr>
  </table>
  </div>
  <!-- /Text -->
  <br />
<?php
}
?>

<?php
if (!empty($template->draft['yahoo_body']))
{
?>
  <!-- Yahoo -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">
        <strong>Yahoo Creative</strong>
      </th>
    </tr>
  </table>
  
  <div style="display:block" id="insideSubCategory66">
  
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <td>
        <textarea readonly="1" cols="60" rows="20"><?php echo $template->draft['yahoo_body']?></textarea>
      </td>
    </tr>
  </table>
  </div>
  <!-- /Yahoo -->
  <br />
<?php
}
?>

<?php
// Do we have an html part?
if(!empty($template->draft['html_body']))
{
?> 
  <!-- HTML -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">
        <strong>HTML Creative</strong>
      </th>
    </tr>
  </table>
  
  <div style="display:block" id="insideSubCategory7">
  
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <td align="center">
        [<a href="/cp/scheduling/draft-html-frame.php?msg_id=<?php echo $template->draft['id']?>" target="_blank">view in new window</a>]<br />
      <iframe src="/cp/scheduling/draft-html-frame.php?msg_id=<?php echo $template->draft['id']?>" scrolling="yes" marginwidth=0 marginheight=0 frameborder=1 vspace=0 hspace=0 width=520 height=400></iframe>
      </td>
    </tr>
  </table>
  </div>
  <!-- /HTML -->
  <br />
<?php
}
?>

<?php
// Do we have an html part?
if(!empty($template->draft['aol_body']))
{
?>  
  <!-- AOL -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">
        <strong>AOL</strong>
      </th>
    </tr>
  </table>
  
  <div style="display:block" id="insideSubCategory20">
  
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <td align="center">
        [<a href="/cp/scheduling/draft-html-frame.php?msg_id=<?php echo $template->draft['id']?>&type=aol" target="_blank">view in new window</a>]<br />
      <iframe src="/cp/scheduling/draft-html-frame.php?msg_id=<?php echo $template->draft['id']?>&type=aol" scrolling="yes" marginwidth=0 marginheight=0 frameborder=1 vspace=0 hspace=0 width=520 height=400></iframe>
      </td>
    </tr>
  </table>
  </div>
  <!-- /AOL -->
  <br />
<?php
}
?>


  <!-- Seeds -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">
        <strong>Seeds</strong>
      </th>
    </tr>
  </table>
  
  <div style="display:block" id="insideSubCategory8">
  
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <td>
        <strong><?php if($template->draft['seeds'] == '') {?>No Seeds<?php } else echo nl2br($template->draft['seeds'])?></strong>
      </td>
    </tr>
  </table>
  </div>
  <!-- /Seeds -->
  <br />
 
 <!-- Domain NOT + ONLY -->
 <table width="100%" cellspacing="0" border="0" cellpadding="2">
 	<tr>
		<th><strong>Domain NOT</strong></th>
		<th><strong>Domain ONLY</strong></th>
	</tr>
	<tr>
		<td><?php foreach ($template->draft['domain_not'] as $not) { echo $not."<br />"; } ?></td>
		<td><?php foreach ($template->draft['domain_only'] as $not) { echo $not."<br />"; } ?></td>
	</tr>
</table>
  
  <!-- Others -->
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">
        <strong>Others</strong>
      </th>
    </tr>
  </table>
  
  <div style="display:block" id="insideSubCategory9">
  
  <table width="100%" cellpadding="2" cellspacing="0">
<?php if (!empty($template->draft['yahoo_date'])) { ?>
 	<tr>
		<td>Yahoo Date: <strong><?php echo $template->draft['yahoo_date'] ?></strong></td>
	</tr> 
<?php } ?>
    <tr> 
      <!--<td width="100">Categories:</td>-->
      <td colspan="2">Suppression List:</td>
    </tr>
    
    <tr> 
      <!--<td rowspan="4" valign="top"> 
        <?php foreach($template->draft['cats'] AS $cat) { ?>
         <strong><font color="green"><?php echo $cat ?></font></strong><br />
        <?php } ?>
      </td>-->
     
    </tr>
   
	<tr> 
    	<td align="left" colspan="2"><strong><font color="green">
	  		<?php foreach ($template->draft['suppression_list'] as $suppression_list) { ?>
				<?php echo $suppression_list; ?><br />
			<?php } ?></font></strong>
		</td>
    </tr>
    
	<tr>
		<td>Threads</td>
		<td>Wait</td>
    <tr>
	<tr>
		<td><?php echo $template->draft['threads'] ?></td>
		<td><?php echo $template->draft['thread_wait']?></td>
	</tr>
       
  </table>
    
  </div>
  <!-- /Others -->
  <br />
  <table width="100%" cellpadding="2" cellspacing="0">
  <tr>
      <th>Send Test</th>
    </tr>
   
    <form action="/cp/scheduling/send-test.php?msg_id=<?php echo $template->draft['id']?>" method="post">
    <tr>
      <td>
        One email per line no more then 5! - May be a slight pause while it sends
        <br />
        Use IP: <select name="use_ip"><?php foreach($template->draft['domains'] AS $domain) {?><option value="<?php echo $domain['ip']?>"><?php echo $domain['ip']." - ".$domain['domain']?></option><?php }?></select>
        <br />
        <textarea rows="5" cols="30" name="test_emails"><?php echo $template->default_test ?></textarea>
        <br />
        <input type="submit" name="test" value="Send Test" />
      </td>
    </tr>
    </form>
  </table>
<br />
 <!-- Advanced Settings -->
<form action="/cp/scheduling/schedule.php?msg_id=<?php echo $template->draft['id']?>" method="post">
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">
       <strong>Advanced Settings</strong>
      </th>
    </tr>
  </table>
  
  <div style="display:block" id="insideSubCategory10">
  
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
     <td>
     <?php
     $def = getDBConfig('DEFAULT_RETRY');
	 
	 if (empty($def['value']) && @$def['value'] !== '0')
		 $def['value'] = 1;
	 
	 if($def === false)
     {
     	$sel[0] = ' selected';
     }
     else 
     {
     	$sel[$def['value']] = ' selected';
     }
     ?>	   
	   Retries:
	   <select name="retries">
	   	<?php for ($i=0;$i<=5;$i++) { ?>
	    <option value="<?php echo $i ?>"<?php echo @$sel[$i]?>><?php echo $i ?></option>
		<?php } ?>
	   </select>
      </td>
    </tr>
  </table>
  </div>
  <!-- /Advanced Settings -->
  <br />
  
  <table width="100%" border="0" cellpadding="2">
  <tbody>
   <tr>
      <th>Tools</th>
    </tr>
    <tr>
      <td><a href="javascript:openWin('/cp/extra/spamassassin.php?draft_id=<?php echo $template->draft['id'] ?>','SpamAssassin',600,600,'yes')">Check SpamAssassin Rating</a></td>
    </tr>
    <tr>
  

 
    <?php if($template->draft['state'] == 0) { ?>
    <tr>
      <th>Schedule:</th>
    </tr>
    
    <tr>
      <td><input type="radio" name="when" checked="true" value="1" id="r1" /><label for="r1">Immediately (will be queued behind other deliveries if running) </label>
      <br />
      <br />
      <label for="r2"><input type="radio" name="when" value="2" id="r2" />At this time: 
      <select name="hour">
<option value="1"  >01</option>
<option value="2"  >02</option>
<option value="3" selected >03</option>
<option value="4"  >04</option>
<option value="5"  >05</option>
<option value="6"  >06</option>

<option value="7"  >07</option>
<option value="8"  >08</option>
<option value="9"  >09</option>
<option value="10"  >10</option>
<option value="11"  >11</option>
<option value="12"  >12</option>
</select>
:
<select name="minute">
<option value="0" selected>00</option>

<option value="15" >15</option>
<option value="30" >30</option>
<option value="45" >45</option>
</select>
<select name="ampm">
<option value="am"  >AM</option>
<option value="pm" selected >PM</option>
</select>

<select name="day">
<?php $selected="selected"; 
for ($i=time();$i<time()+86400*10;$i+=86400)
{ 
?>
<option value="<?php echo date("Y-m-d",$i)?>" <?php echo $selected?>><?php echo date("M d, Y",$i)?></option>
<?php
$selected="";
}
?>
</select>

<select name="timezone">
	<?php foreach ($template->timezones as $zone) { $sel = ''; if ($zone == 'US/Eastern') $sel = ' selected="selected"';?>
	<option<?php echo $sel?>><?php echo $zone ?></option>
	<?php } ?>
</select>
</label>
 <br /><br />
<!--
<div id = "nooutlinetable">

   <table width="400" cellspacing="1" border="0" cellpadding="1">
		<tr>
			<td width="320">
				<input type="radio" name="when" value="3" id="when" />
				<label for="when">At this time(calendar):  <input type="text" name="cal-field-1" id="cal-field-1" readonly="1" /></label>     
			</td>
			<td align="left">
				<img src="/images/misc/calendar.gif" id="f_trigger_c" border="0" title="Date selector" onmouseover="this.style.background='red';" onmouseout="this.style.background=''" class="imgfloat" />
			</td>
		</tr>
	</table>

</div>
-->
     
      <br />
      <input type="submit" name="submit" value="Schedule" <?php echo @$template->schedule_disabled?>/></td>
    </tr>
    <?php
    } elseif(@$template->is_paused == 1) {
    ?>
    <tr>
      <th>Paused Draft:</th>
    </tr>
    <tr>
     <td><input type="submit" name="submit" value="Rebuild Paused Draft" <?php echo $template->schedule_disabled?>/></td>
    </tr>
    <?php
    }
    ?>
    
    </form>
    
  </tbody>
</table>
</label>
