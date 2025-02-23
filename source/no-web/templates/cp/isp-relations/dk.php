<h1>Domain Check</h1>
    <!--<div class="section">
        <h2>Enable/Disable</h2>

        <p class="notes">Domain keys are
            <strong><?php h($template->state); ?></strong>.</p>

        <form method="post">
            <input type="submit" value="Toggle">
        </form>
    </div>-->

    <?php if ($template->state == 'on') { ?>
    <div class="section">
        <h2>Key Data</h2>

        <p class="notes">To use domain keys, this is the key data you
            need to configure on your DNS servers.</p>

        <textarea rows="5" style="width: 100%">t=y; k=rsa; p=<?php h($template->pub); ?></textarea>
    <?php } ?>

<br /> <br />
<table width="100%" cellpadding="4" cellspacing="0" class="content">
 <tr>
  <th class="first">IP</th>
  <th>Domain</th>
  <th align="center" width="50">A</th>
  <th align="center" width="50">MX</th>
  <th align="center" width="50">SPF</th>
  <?php if($template->state == 'on') {?><th align="center">DK</th><?php } ?>
 </tr>
<?php $count = count($template->domains); $i = 0; foreach ($template->domains as $domain) { $last = ''; if (++$i == $count) $last = 'class="last"';?>
 <tr <?php echo $last?>>
  <td class="first"><?php echo $domain['ip'] ?></td>
  <td><?php echo $domain['domain'] ?></td>
  <td class="center"><?php statusOut($domain['a_status']); ?><!-- <?php echo $domain['a_return'] ?> --></td>
  <td align="center"><?php statusOut($domain['mx_status']); ?><!-- <?php echo $domain['mx_return'] ?> --></td>
  <td align="center"><?php statusOut($domain['spf_status']); ?><!-- <?php echo $domain['spf_return'] ?> --></td>
  <?php if($template->state == 'on') {?><td align="center"><?php statusOut($domain['dk_status']); ?><!-- <?php echo $domain['dk_return'] ?> --></td><?php } ?>
 </tr>
<?php } ?>
 </table>
