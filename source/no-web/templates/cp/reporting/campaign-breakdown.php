<h1>Campaign (<?php echo $template->stats['msg']['title']?>) Breakdown</h1>

  <br />
  <div id="contenttable">
  <table cellspacing="0" cellpadding="4" border="0" width="100%">
    
    <tr>
      <th align="left" colspan="2">Title</th>
    </tr>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left" colspan="2"><a href="/cp/scheduling/draft-view.php?msg_id=<?php echo $template->stats['msg']['id'] ?>" target="_blank"><?php echo $template->stats['msg']['title']?></a></td>
    </tr>
    
     <tr>
      <th align="left" colspan="2">Information</th>
    </tr>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">Status</td>
      <td align="left"><?php echo getState($template->stats['campaign']['state'])?></td>
    </tr>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">Type</td>
      <td align="left"><?php echo $template->stats['msg']['content'] ?></td>
    </tr>
    
    <tr>
      <th align="left" colspan="2">Dates/Times</th>
    </tr>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td width="100" align="left">Start Date:</td>
      <td align="left"><?php if($template->stats['campaign']['start_stamp'] > 0) { echo date("m-d-y H:i:s",$template->stats['campaign']['start_stamp']); } else {?>Not yet started<?php } ?></td>
    </tr>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">End/Check Date:</td>
      <td align="left"><?php if($template->stats['campaign']['end_stamp'] > 0) { echo date("m-d-y H:i:s",$template->stats['campaign']['end_stamp']); } else {?>Not yet finished<?php } ?></td>
    </tr>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">Duration:</td>
      <td align="left"><?php echo timespanFormat($template->stats['campaign']['end_stamp']-$template->stats['campaign']['start_stamp']) ?></td>
    </tr>
    
    <tr>
      <td align="left" bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">Per Hour:</td>
      <td align="left">
        <?php
        if($template->stats['campaign']['start_stamp'] > 0)
        {
          if($template->stats['campaign']['end_stamp'] < 1)
          {
            $template->stats['campaign']['end_stamp'] = mktime();
          }
          $the_number = $template->stats['campaign']['end_stamp'] - $template->stats['campaign']['start_stamp'];
          if($the_number < 1)
          {
              $the_number = 1;
          }
          echo number_format($template->stats['campaign']['total_tried'] * 3600 / ($the_number), 2);
        }
        else
        {
          echo "N/A";
        }
        ?>
      </td>
    </tr>
    
    <tr>
      <th align="left" colspan="2">Delivery</th>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">Retries:</td>
      <td align="left"><?php echo number_format($template->stats['campaign']['retries'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">Current Retry Level:</td>
      <td align="left"><?php echo ($template->stats['campaign']['retries'] - $template->stats['campaign']['retry_level'])?></td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">Total Emails:</td>
      <td align="left"><?php echo number_format($template->stats['campaign']['total_emails'])?></td>
    </tr>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">Emails Tried:</td>
      <td align="left"><?php echo number_format($template->stats['campaign']['total_tried'])?></td>
    </tr>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">Successful Emails:</td>
      <td align="left">
        <?php echo number_format($sent = $template->stats['campaign']['success'])?>
        (<?php echo calculatePercentage($sent,$template->stats['campaign']['total_tried'],2)?>%)
		<?php if (empty($template->mailer)) { ?>
	        <a href="/cp/reporting/log-viewer.php?log=success&amp;id=<?php echo $template->stats['campaign']['id'] ?>" target="_blank">View Tail Log</a> -
			<a href="/cp/reporting/log-viewer.php?log=success&amp;id=<?php echo $template->stats['campaign']['id'] ?>&amp;download=1">Download</a>
		<?php } ?>
    </td>
    </tr>
    
    <tr>
      <td align="left" colspan="2">
      <div id="help_me_content_1">
        <font color="#000000">
			A deferral is an email that returned a temporary failure code (a 400) also known as a soft bounce. These emails are moved after two (or a value you set in the <a href="/cp/options/config.php">config</a>) repeat soft bounces.<br />
		</font>
        </div>
      </td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">Deferred Emails:</td>
      <td align="left"><?php echo number_format($template->stats['campaign']['deferral'])?>
        (<?php echo calculatePercentage($template->stats['campaign']['deferral'],$template->stats['campaign']['total_tried'],2)?>%) 
        <a href="/cp/reporting/log-viewer.php?log=deferral&amp;id=<?php echo $template->stats['campaign']['id'] ?>" target="_blank">View Tail Log</a> -
		<?php if (empty($template->mailer)) { ?>
		<a href="/cp/reporting/log-viewer.php?log=deferral&amp;id=<?php echo $template->stats['campaign']['id'] ?>&amp;download=1">Download</a>
		<?php } ?>
      </td>
    </tr>
    
    <tr>
      <td align="left" colspan="2">
      <div id="help_me_content_2">
        <font color="#000000">A failure is a hard bounce these email returned a permenant failure error (a 500) these will never go through and so are removed automatically.</font>
        </div>
      </td>
    </tr>
    
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">Failed Emails:</td>
      <td align="left"><?php echo number_format($template->stats['campaign']['failure'])?>
        (<?php echo calculatePercentage($template->stats['campaign']['failure'],$template->stats['campaign']['total_tried'],2)?>%)
        <a href="/cp/reporting/log-viewer.php?log=failure&amp;id=<?php echo $template->stats['campaign']['id'] ?>" target="_blank">View Tail Log</a> -
		<?php if (empty($template->mailer)) { ?>
		<a href="/cp/reporting/log-viewer.php?log=failure&amp;id=<?php echo $template->stats['campaign']['id'] ?>&amp;download=1">Download</a>
		<?php } ?>
      </td>
    </tr>
    
    <tr>
      <th align="left" colspan="2">Clicks</th>
    </tr>
    <tr>
      <td align="left" colspan="2">
        <div id="nooutlinetable">
          <table cellspacing="0" cellpadding="3" width="100%">
            <tr bgcolor="#dce3ef">
              <td width="200"><strong>URL</strong></td>
              <td width="10" colspan="2"><strong>Count</strong></td></tr>
            <?php 
            if(count($template->stats['clicks']) > 0)
            {
				$total = 0;
              foreach($template->stats['clicks'] AS $link)
              {
                  $total += $link['count'];
              ?>
              <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
                <td width="300"><a href="<?php echo $link['url']?>" target="_blank"><?php echo $link['url']?></a></td>
                <td width="10"><?php echo number_format($link['count'])?></td>
                <td width="100">
					<a href="/cp/reporting/campaign.php?action=top_click&amp;campaign_id=<?php echo $template->stats['campaign']['id'] ?>&amp;tracked_link_id=<?php echo $link['tracked_link_id']?>" class="top-domains">top domains</a> -
					<?php if (empty($template->mailer)) { ?>
					<a href="/cp/reporting/campaign.php?action=click_download&amp;campaign_id=<?php echo $template->stats['campaign']['id'] ?>&amp;tracked_link_id=<?php echo $link['tracked_link_id']?>">download</a> -
					<?php } ?> 
					<a href="/cp/management/tracked_link.php?action=edit&amp;tracked_link_id=<?php echo $link['tracked_link_id'] ?>" class="tracked-link-edit">edit</a>
				</td>
              </tr>
              <?php
              }
            ?>
            <tr bgcolor="#dce3ef"><td width="200"><strong>Total:</strong></td><td width="10" colspan="2"><strong><?php echo number_format($total)?></strong></td></tr>
            <?php
            }
            else
            {
            ?>
            <tr><td colspan="3">No URLS to be tracked</td></tr>
            <?php
            }
            ?>
          </table>
        </div>
      </td>
    </tr>

	<tr>
      <th align="left" colspan="2">Images</th>
    </tr>
    <tr>
      <td align="left" colspan="2">
        <div id="nooutlinetable">
          <table cellspacing="0" cellpadding="3" width="100%">
            <tr bgcolor="#dce3ef">
              <td width="200"><strong>URL</strong></td>
              <td width="10" colspan="2"><strong>Action</strong></td></tr>
            <?php 
            if(count($template->stats['images']) > 0) {
				foreach($template->stats['images'] AS $image) {
              ?>
              <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
                <td width="300"><a href="<?php echo $image?>" target="_blank"><?php echo $image ?></a></td>
                <td width="100">
					<a href="/cp/management/images.php?action=view&amp;image=<?php echo $image ?>" target="_blank">view</a> -
					<a href="/cp/management/images.php?action=edit&amp;image=<?php echo $image ?>" class="image-edit">edit</a>
				</td>
              </tr>
              <?php
              }
            ?>
            <?php
            }
            else
            {
            ?>
            <tr><td colspan="3">No Images</td></tr>
            <?php
            }
            ?>
          </table>
        </div>
      </td>
    </tr>


    <tr>
    
    <tr>
      <th align="left" colspan="2">Opens</th>
    </tr>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td align="left">Total Opens:</td>
      <td align="left"><?php echo number_format($template->stats['campaign']['opens'])?> -
		<?php if (empty($template->mailer)) { ?>
	  	<a href="/cp/reporting/campaign.php?action=open_download&amp;campaign_id=<?php echo $template->stats['campaign']['id'] ?>">download</a></td>
		<?php } ?>
    </tr>
    
    <tr>
      <th align="left" colspan="2">Comments</th>
    </tr>
    <tr>
      <td align="left" colspan="2"><?php echo $template->stats['msg']['comments']?></td>
    </tr>
    
  </table>
  </div>
	<br />
	<table class="content" cellspacing="0" cellpadding="0">
	  	<tr>
			<th class="first">IP</th>
			<th>Spam Count</th>
		</tr>
		<?php foreach ($template->stats['ips'] as $ip) { ?>
		<tr>
			<td class="first"><?php echo $ip['ip'] ?></td>
			<td><?php echo $ip['spam_seed_count'] ?></td>
		</tr>
		<?php } ?>
	</table>
