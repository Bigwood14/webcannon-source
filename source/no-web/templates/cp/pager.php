<?php
$SCRIPT_REQUEST = eregi_replace('page_num=[0-9]?&','',makeScriptRequestURL());
?>
<div id="nooutlinetable">
<table width="500" align="center">
  <tr>
    <td><a href="<?php echo $SCRIPT_REQUEST ?>page_num=1"><img src="/images/misc/p_start.gif" width="16" height="16" border="0" /></a></td>
    <!-- Prev page -->
    <td>
      <?php 
      if($template->pager['current'] > 1)
      {
      ?>
      <a href="<?php echo $SCRIPT_REQUEST ?>page_num=<?php echo $template->pager['current']-1?>"><img src="/images/misc/p_prev.gif" width="16" height="16" border="0" /></a>
      <?php
      }
      else
      {
      ?>
      <img src="/images/misc/p_prev.gif" width="16" height="16" border="0" />
      <?php
      }
      ?>
    </td>
    <!-- /Prev page -->
    
    <td align="center">
      <font color="#000000"><strong>
      Displaying <?php echo $template->pager['from'] ?> - <?php echo $template->pager['to'] ?> of <?php echo $template->pager['numrows'] ?> ::
      Pages(<?php echo $template->pager['numpages'] ?>):
      <!-- sliding -->
      <?php
      foreach ($template->pager['sliding'] AS $page)
      {
        if ($template->pager['current'] == $page)
        {
      ?>
      [<?php echo $page?>]
      <?php
        }
        else
        {
      ?>
      <a href="<?php echo $SCRIPT_REQUEST?>page_num=<?php echo $page?>"><?php echo $page?></a>
      <?php
        }
      } 
      ?>
      <!-- /sliding -->
      </strong></font>
    </td>
    <!-- Next page -->
    <td>
      <?php 
      if($template->pager['current'] < $template->pager['numpages'])
      {
      ?>
      <a href="<?php echo $SCRIPT_REQUEST ?>page_num=<?php echo $template->pager['current']+1?>"><img src="/images/misc/p_next.gif" width="16" height="16" border="0" /></a>
      <?php
      }
      else
      {
      ?>
      <img src="/images/misc/p_next.gif" width="16" height="16" border="0" />
      <?php
      }
      ?>
    </td>
    <!-- /Next page -->
    
    <td>
      <a href="<?php echo $SCRIPT_REQUEST ?>page_num=<?php echo $template->pager['numpages'] ?>"><img src="/images/misc/p_end.gif" width="16" height="16" border="0" /></a>
    </td>
  
  </tr>
</table>
</div>