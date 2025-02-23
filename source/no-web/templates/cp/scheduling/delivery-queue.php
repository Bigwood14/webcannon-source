<h1>Delivery Queue</h1>

<br />
<h2>Currently Sending</h2>
<br />
  <div id="contenttable">
  <table cellspacing="0" cellpadding="3" border="0" width="700">
    <tr>
      <th width="30" align="center">ID</th>
      <th width="200">Title</th>
      <th align="center" width="200">State</th>
      <th align="center" width="150">Scheduled Time</th>
      <th align="center" width="150">Start Time</th>
      <th align="center" width="150">End/Check Time</th>
    </tr>
    
    <?php
    if(count($template->data) < 1)
    {
    ?>
    <tr>
      <td colspan="6" align="center"><strong>None Scheduled Yet.</strong></td>
    <tr>
    <?php
    }
    ?>
    
<?php 
print_rows($template, $template->data);
?>
</table>

</div>



<br />
<h2>Archive</h2>
<br />

  <?php include($template->directory.'cp/pager.php'); ?>
  <br />
  <div id="contenttable">
  <table cellspacing="0" cellpadding="3" border="0" width="700">
    <tr>
      <th width="30" align="center">ID</th>
      <th width="200">Title</th>
      <th align="center" width="200">State</th>
      <th align="center" width="150">Scheduled Time</th>
      <th align="center" width="150">Start Time</th>
      <th align="center" width="150">End/Check Time</th>
    </tr>
    
    <?php
    if(count($template->q) < 1)
    {
    ?>
    <tr>
      <td colspan="6" align="center"><strong>None Scheduled Yet.</strong></td>
    <tr>
    <?php
    }
    ?>
    
  <?php 
print_rows($template, $template->q);
function print_rows ($template, $data)
{
  $i = 1;

  foreach($data AS $q)
  {
      if($q['st'] == 4)
      {
          $i = 2;
      }
      
      if($i == 1)
      {
          $bg = '#EAEAEE';
          $i = 0;
      }
      elseif($i == 2)
      {
          $bg = '';
          $i = 1;
      }
      else
      {
          $bg = '#DCE3EF';
          $i = 1;
      }
  ?>
    <tr bgcolor="<?php echo $bg?>">
      <td align="center"><?php echo $q['sid']?></td>
      <td><a href="/cp/scheduling/draft-view.php?msg_id=<?php echo $q['msg_id']?>"><?php echo $q['title']?></a></td>
      <td align="center"><?php echo getState($q['st'])?></td>
      <td align="center"><?php echo $q['scheduled_date']?></td>
      <td align="center"><?php echo $q['start_date']?></td>
      <td align="center"><?php echo $q['end_date']?></td>
    </tr>
    <tr bgcolor="<?php echo $bg?>">
      <td align="center" colspan="2">
      
           
            <?php
            if($q['st'] < 5  || $q['st'] == 11)
            {
            ?>
                <div id="nooutlinetable">
        <table width="100" height="12" bgcolor="#ffffff" cellpadding="0" cellspacing="0" class="blackbordertable"><tr>
            <?php
                $blocks = @$q['blocks'];
                while(@$q['blocks'] > 0)
                {
                  ?>
                 <td width="10" style="padding: 0;"><img src="/images/misc/progress_blue.jpg" alt="<?php echo $q['percent']?>%" title="<?php echo $q['percent']?>%" width="10" height="12" border="0" /></td>
                 <?php
                 $q['blocks'] --;
                }
                if($blocks < 10)
                {
                    echo "<td width=\"".(100 - ($blocks*10))."\" style=\"padding: 0;\"></td>";
                }
                ?>
                 </tr>
        </table>
        </div>
                <?php
                
            }
            elseif($q['st'] == 5)
            {
            ?>
            <div id="nooutlinetable">
            <table width="100" height="12" bgcolor="#ffffff" cellpadding="0" cellspacing="0" class="blackbordertable">
              <tr>
              <?php
              $i = 10;
              while($i > 0)
              {
              ?>
                <td width="10"><img src="/images/misc/progress_red.jpg" alt="Processing Retries" width="10" height="12" border="0" /></td>
              <?php
              $i --;
              }
              ?>
              </tr>
            </table>
            <?php
            }
            else
            {
                echo getState($q['st']);
            }
            ?>
            
         
      </td>
      <td align="center">Progress: <?php echo number_format($q['total_tried'])?>/<?php echo number_format($q['total_emails']) ?></td>
      <td align="center"><?php echo number_format($q['bounced']) ?> undeliverable</td>
      <td align="center"><a href="/cp/scheduling/delivery-details.php?id=<?php echo $q['sid']?>">Details</a></td>
      <td align="center">
        <?php if($q['st'] < 7) {?>
        <a href="/cp/scheduling/delivery-cancel.php?id=<?php echo $q['id']?>">Cancel</a>
        <?php }  else { echo $q['end_date']; } ?>
        <?php if($q['st'] == 4){?>
         - <a href="/cp/scheduling/delivery-queue.php?id=<?php echo $q['id']?>&action=pause">Pause</a>
         <?php } elseif($q['st'] == 11) { ?> 
         - <a href="/cp/scheduling/delivery-queue.php?id=<?php echo $q['id']?>&action=unpause">Unpause</a>
         <?php } ?>
      </td>
    </tr>
    <tr bgcolor="<?php echo $bg?>">
      <td colspan="6">
        [ <strong>List(s)</strong>:
        <?php
        foreach($q['lists'] AS $list)
        {
        ?>
        <?php echo $list['name']?> :
        <?php
        }
        ?>
        ] 
        - [ <strong>Server</strong>: <?php echo $q['server']['name']?> ] 
        - [ <strong>Opens</strong>: <?php echo number_format($q['opens']) ?> ] 
        - [ <a href="/cp/reporting/campaign-breakdown.php?id=<?php echo $q['sid'] ?>">Campaign Stats</a> ]
		- [ <strong>AOL</strong> <?php echo round($q['aol_ratio'], 2) ?> % (<?php echo number_format($q['aol_count']) ?>) ]
      </td>
    </tr>
  <?php
  }
}
  ?>
</table>

</div>

<br />
