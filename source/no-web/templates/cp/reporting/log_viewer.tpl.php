<html>

	<head>
		<link href="/css/old.css" rel="stylesheet" type="text/css" media="all" />
		<link href="/css/main.css" rel="stylesheet" type="text/css" media="all" />
		<link href="/css/clearfix.css" rel="stylesheet" type="text/css" media="all" />
	</head>

<body>

<h1>Log Viewer</h1>
<br />
<div id="contenttable">
	<table cellspacing="0" cellpadding="4" border="0" width="100%">
		<tr>
			<th>Email</th>
			<th>Local IP</th>
			<th>HELO Domain</th>
			<th>Remote IP</th>
			<th>MX Server</th>
			<th>Domain</th>
			<th>Timestamp</th>
			<th>Message</th>
		</tr>
    <?php $i = 0; foreach ($template->r as $row) { 
	$parts = explode(':', $row, 9);
	if ($i == 0)
	{
		$class = '';
		$i  = 1;
	}
	else
	{
		$class = 'class="grey"';
		$i  = 0;
	}
	?>
	<tr <?php echo $class ?>>
		<td><?php if (!empty($template->mailer)) echo '-';  else echo $parts[1] ?></td>
		<td><?php echo $parts[2] ?></td>
		<td><?php echo $parts[3] ?></td>
		<td><?php echo $parts[4] ?></td>
		<td><?php echo $parts[5] ?></td>
		<td><?php echo $parts[6] ?></td>
		<td><?php echo date('Y-m-d h:i:s', $parts[7]) ?></td>
		<td><?php echo $parts[8] ?></td>
	</tr>
	<?php } ?>  
	</table>
</div>

</body>
</html>
