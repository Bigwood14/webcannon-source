<style>
table.content td 
{
	font-size: 13px !important;
}
</style>

<h1>Draft Achive</h1>

<form action="/cp/scheduling/draft-archive.php" method="get">
<p>
	<br />
	<input type="text" size="30" name="q" value="<?php h(@$_GET['q'])?>" />
	<input type="submit" value="Search" name="search" />
	<br />
</p>
</form>

<form action="/cp/scheduling/draft.php" method="post">


<?php if (!empty($template->top)) { ?>
<br />
<h2>Top Campaigns</h2>
<br />
<table class="content" cellspacing="0" cellpadding="0">
	<tr>
		<th width="30" class="first" align="center">ID</th>
		<th>Title</th>
		<th colspan="2" class="center">Opens</th>
	    <th colspan="2" class="center">Clicks</th>
    	<th colspan="2" class="center">Our SC</th>
	    <th colspan="2" class="center">AOL SC</th>
    	<th class="center">Sent</th>
		<th class="center" colspan="6">Action</th>
	</tr>
	<?php foreach ($template->top as $draft) draft_row($draft, true);?>
</table>
<?php } ?>
 
<br />
<h2>Campaigns</h2>
 <br /> 
  <?php
  if($template->pager['numpages'] > 1)
  {
      include($template->directory.'cp/pager.php');
  ?>
  <br />
  <?php 
  }
  ?>
  
  <table width="800" cellspacing="0" cellpadding="4" border="0" class="content" style="width: 900px">
    <tr>
      <th width="30" class="first" align="center">ID</th>
      <th>Title</th>
      <th colspan="2" class="center">Opens</th>
      <th colspan="2" class="center">Clicks</th>
      <th colspan="2" class="center">Our SC</th>
      <th colspan="2" class="center">AOL SC</th>
      <th class="center">Sent</th>
      <th class="center" colspan="6">Action</th>
    </tr>
  <?php foreach($template->drafts AS $draft) draft_row($draft); ?>
</table></div><br />

</form>

<?php function draft_row($draft, $top = false) { ?>
<tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="center" class="first"><?php echo $draft['id']?></td>
      <td><?php echo $draft['title']?></td>

		<?php if ($draft['state'] == 1) { ?>

		<td class="center"><?php echo number_format($draft['sched']['opens'])?></td>
		<td class="center"><?php echo @round(($draft['sched']['opens']/$draft['sched']['success'])*100, 2)?>%</td>

		<td class="center"><?php echo number_format($draft['sched']['clicks'])?></td>
		<td class="center"><?php echo @round(($draft['sched']['clicks']/$draft['sched']['opens'])*100, 2)?>%</td>

		<td class="center"><?php echo number_format($draft['our_scomp'])?></td>
		<td class="center"><?php echo @round(($draft['our_scomp']/$draft['sched']['success'])*100, 2)?>%</td>

		<td class="center"><?php echo number_format($draft['scomp'])?></td>
		<td class="center"><?php echo @round(($draft['scomp']/$draft['sched']['success'])*100, 2)?>%</td>

		<td class="center"><?php echo number_format($draft['sched']['success']) ?></td>

		<?php } else { ?>
		<td colspan="9">&nbsp;</td>
		<?php } ?>

      <td class="center"><a href="/cp/scheduling/draft.php?draft_id=<?php echo $draft['id']?>">Copy</a></td>
      <td class="center"><a href="/cp/scheduling/draft-view.php?msg_id=<?php echo $draft['id']?>">View</a></td>
	  <td class="center">
	  	<?php if($draft['state'] == 0) {?><a href="/cp/scheduling/draft-view.php?msg_id=<?php echo $draft['id']?>">Schedule</a><?php } else echo '-'; ?></td>
      <td class="center">
        <?php if($draft['state'] == 0) {?><a href="/cp/scheduling/draft.php?draft_id=<?php echo $draft['id']?>&action=edit">Edit</a><?php
         } else echo '-'; ?>
      </td>
      <td class="center">
        <?php if($draft['state'] == 1) {?> - <?php } else { ?>
          <a href="/cp/scheduling/draft-archive.php?action=delete&id=<?php echo $draft['id'] ?>">Delete</a>
        <?php }?>
      </td>
	  <td class="center">
		<?php if ($top == false) { ?>
		  	<a href="/cp/scheduling/draft-archive.php?action=make_top&id=<?php echo $draft['id'] ?>">Top</a>
		<?php } else { ?>
			<a href="/cp/scheduling/draft-archive.php?action=unmake_top&id=<?php echo $draft['id'] ?>">Remove</a>
		<?php } ?>
	  </td>
    </tr>
<?php } ?>
