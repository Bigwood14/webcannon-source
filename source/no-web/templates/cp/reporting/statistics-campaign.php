<div id="contentbox">
  <h1>Statistics-Campaign</h1>
</div>

<br /><br />

<div id="contenttable">

<!-- Message Displayer -->

<table width="100%">
  <tr>
    <th>Message Display Options</th>
  </tr>
</table>

<form method="GET" enctype="application/x-www-form-urlencoded" action="/cp/reporting/statistics-campaign.php">

<table cellpadding="4" cellspacing="1" width="100%">
  <tr>
    <td colspan="3"><strong>Define Date Range</strong></td>
  </tr>
  <tr bgcolor="#dddddd">
    <td width="20"><input type="radio" name="option" value="range" checked /></td>
    <td align="left" width="70"><strong>Date Range:</strong></td>
    <td align="left">
      <select name="range">
        <option value="7" selected>Last 7 Days</option>
        <option value="1M">Last Month</option>
        <option value="6M">Last 6 Months</option>
      </select>
    </td>
  </tr>
  <tr>
    <td colspan="3" align="left"><strong>OR</strong></td>
  </tr>
  <tr bgcolor="#dddddd">
    <td rowspan="2"><input type="radio" name="option" value="between" /></td>
    <td align="left"><strong>From:</strong></td>
    <td align="left">
      
      <?php echo HTML_Time_Date::buildMonthSelect("month_1",(isset($_POST['month_1']) ? $_POST['month_1'] : date("n"))); ?>
      
      <?php echo HTML_Time_Date::buildDaysSelect("day_1",(isset($_POST['day_1']) ? $_POST['day_1'] : date("j"))); ?>
      
      <?php echo HTML_Time_Date::buildYearSelect("year_1",(isset($_POST['year_1']) ? $_POST['year_1'] : date("Y"))); ?>
      
    </td>
  </tr>
  <tr bgcolor="#dddddd">
    <td align="left"><strong>To:</strong></td>
    <td align="left">
    
      <?php echo HTML_Time_Date::buildMonthSelect("month_2",(isset($_POST['month_2']) ? $_POST['month_2'] : date("n") - 1)); ?>
      
      <?php echo HTML_Time_Date::buildDaysSelect("day_2",(isset($_POST['day_2']) ? $_POST['day_2'] : date("j"))); ?>
      
      <?php echo HTML_Time_Date::buildYearSelect("year_2",(isset($_POST['year_2']) ? $_POST['year_2'] : date("Y"))); ?>
      
    </td>
  </tr>
  <tr>
    <td colspan="3"><input type="submit" name="display" value="Display" /></td>
  </tr>
</table>

</form>

<!-- / Message Displayer -->
  
  <table width="820" cellspacing="0" cellpadding="2" border="0" align="center">
    <tr>
      <th width="120" align="center">Delivery Date</th>
      <th width="70" align="center">Status</th>
      <th align="center" width="150">Draft</th>
      <th align="center" width="50">Size</th>
      <th align="center" width="100">Type</th>
      <th align="center" width="130">Delivered</th>
      <th align="center" width="50">Clicks</th>
      <th align="center" width="50">Opens</th>
      <th align="center" width="100">Comments</th>
    </tr>
  <?php 
  $i = 1;
  foreach($template->stats AS $cam)
  {
      if($cam['st'] == 1)
      {
          //print_r($cam);
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
      
      $total_sent = $cam['raw_emails_sent'] - $cam['emails_bounced'];
      
      $campaign['total_tried'] = $cam['success'] + $cam['failure'] + $cam['deferral'];
      
      //print_r($cam);
      //print $cam['content'];
  ?>
     <tr bgcolor="<?php echo $bg?>">
      <td align="center"><?php echo $cam['scheduled_date']?></td>
      <td align="center"><?php echo getState($cam['st'])?></td>
      <td align="left"><a href="/cp/scheduling/draft-view.php?msg_id=<?php echo $cam['msg_id'] ?>"><?php echo $cam['title'];?></a></td>
      <td align="center">
        <?php echo number_format($cam['size']/1024,2);?>KB
      </td>
      <td><?php echo type($cam['content'])?></td>
      <td align="right">
        <?php echo number_format($campaign['total_tried'])?>/<strong><?php echo number_format($cam['total_emails'])?></strong>
        <br />
        (<?php
        if($cam['total_emails'] < 1)
        {
            echo '0.00';
        }
        else
        {
            echo number_format(($cam['success']/$cam['total_emails'])*100,2);
        }
        ?>%)
      </td>
      <td align="right">
        <a href="/cp/reporting/campaign-breakdown.php?id=<?php echo $cam['s_id']?>">
        <?php echo number_format($cam['clicks'])?>
        <br />
        (<?php echo calculatePercentage($cam['clicks'],$cam['success'],2);?>%)
        </a>
      </td>
      <td align="right"><?php echo $cam['opens']?><br />
        (<?php 
        if($cam['success'] < 1)
        {
            echo '0.00';
        }
        else
        {
            echo number_format(($cam['opens']/$cam['success'])*100,2);
        }
        ?>%)
      </td>
      <td align="center"><?php echo $cam['comments']?></td>
    </tr>
  <?php
  }
  ?>
</table></div><br />
