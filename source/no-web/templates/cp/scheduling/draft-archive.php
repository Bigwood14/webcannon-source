<form action="/cp/scheduling/draft.php" method="post">

<h1>Draft Achive</h1>

<?php if (!empty($template->top)) { ?>
<br />
<h2>Top Campaigns</h2>
<br />
<table class="content" cellspacing="0" cellpadding="0">
	<tr>
		<th width="30" class="first" align="center">ID</th>
		<th>Title</th>
		<th colspan="6">Action</th>
	</tr>
	<?php foreach ($template->top as $draft) draft_row($draft, true);?>
</table>
<?php } ?>
 
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
  
  <table width="100%" cellspacing="0" cellpadding="4" border="0" class="content">
    <tr>
      <th width="30" class="first" align="center">ID</th>
      <th>Title</th>
      <th align="center" colspan="6">Action</th>
    </tr>
  <?php foreach($template->drafts AS $draft) draft_row($draft); ?>
</table></div><br />

</form>

<?php function draft_row($draft, $top = false) { ?>
<tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="center" class="first"><?php echo $draft['id']?></td>
      <td><?php echo $draft['title']?></td>
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
