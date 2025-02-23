<div id="contentbox">
  <h1>Link Tracking</h1>
</div>
<br />
<div id="contenttable">
<form action="/cp/extra/link-tracking.php" method="post">
<table width="800" cellspacing="0" border="0" cellpadding="2">
  
    <tr>
      <th colspan="2">Track a Link</th>
    </tr>
    
    <?php
    if($template->ext_link)
    { 
    ?>
    <tr bgcolor="#dce3ef">
      <td colspan="2">Link Done</td>
    </tr>
    <tr>
      <td>Link for Draft:</td>
      <td><?php echo $template->draft_link ?></td>
    </tr>
    <tr>
      <td>Link for Other:</td>
      <td><?php echo $template->ext_link ?></td>
    </tr>
    <?php
    }
    ?>
    
    <tr>
      <td>URL:</td>
      <td><input type="text" name="url" size="50" value="<?php echo $template->info['URL']?>" /></td>
    </tr>
    <tr>
      <td>Tie to Draft:</td>
      <td><select name="msg_id">
            <option value="0">None</option>
            <?php 
            foreach($template->drafts AS $draft)
            {
              if($draft['id'] == $_GET['msg_id'] || $draft['id'] == $template->info['msg_id'])
              {
                $sel = " selected";
              }
              else
              {
                $sel = "";
              }
            ?>
            <option value="<?php echo $draft['id']?>"<?php echo $sel ?>><?php echo $draft['title']?></option>
            <?php
            }
            ?>
          </select></td>
    </tr>
    
    <tr>
      <td>Count as:</td>
      <td>
        <?php
        if($template->info['img'] == '1')
        {
          $img = ' selected';
        }
        ?>
        <select name="img">
          <option value="0">A click</option>
          <option value="1"<?php echo $img?>>Nothing (when using as masking for an image)</option>
        </select>
      </td>
    </tr>
    
    <tr>
      <td colspan="2" align="center">
        <input type="hidden" name="link_id" value="<?php echo $template->info['link_id']?>" />
        <input type="submit" name="add" value="<?php echo $template->we_are ?> Link" />
      </td>
    </tr>
</table>
</form>
<br />
<?php include($template->directory.'cp/pager.php'); ?>

<table width="800" cellspacing="0" border="0" cellpadding="2">
  
    <tr>
      <th colspan="4">Tracked Links</th>
    </tr>
    <tr bgcolor="#dce3ef">
      <td width="300"><strong>URL</strong></td>
      <td width="40"><strong>Hits</strong></td>
      <td><strong>Action</strong></td>
      <td><strong>Msg</strong></td>
    </tr>
    <?php
    foreach($template->links AS $link)
    {
    ?>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td><a href="<?php echo $link['URL']?>" target="_blank"><?php echo $link['URL']?></a></td>
      <td align="right"><?php echo number_format($link['count']) ?></td>
      <td align="center">
        <a href="/cp/extra/link-tracking.php?link_id=<?php echo $link['link_id'] ?>&edit=1">edit</a>
        -
        <a href="/cp/extra/link-tracking.php?link_id=<?php echo $link['link_id'] ?>&delete=1">del</a>
      </td>
      <td><?php if($link['msg_id'] > 0) { ?><a href="/cp/scheduling/draft-view.php?msg_id=<?php echo $link['msg_id'] ?>"><?php echo $link['msg_title'] ?></a><?php }  else {?>None<?php } ?></td>
    </tr>
    <?php
    }
    ?>
</table>

</div>

 

  
  