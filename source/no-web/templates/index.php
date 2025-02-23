<?php
$email = '';
if (!empty($_GET['e']))
$email = $_GET['e'];
else if (!empty($_GET['a']))
$email = $_GET['a'];
else if (!empty($_GET['ea']))
$email = $_GET['ea'];

if (!empty ($template->error))
{
	if ($template->error == 'Error: Invalid email address.')
	{
		$template->error = 'Bad Email Address Format';
	}
	else if (strpos($template->error, 'removed.'))
	{
		$template->error = 'Your email address has been removed.';
	}
}
?>
<html>
  <head>
    <title><?php echo $_SERVER['SERVER_NAME'] ?></title>
    <style type="text/css">
body
{
background: #ececec;
font-family: arial,verdana;
color: #333;
}
h1
{
font-size: 18px;
border-bottom: 1px solid #666;
}
label
{
	font-weight: normal;
}
    </style>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  </head>
  <body>
	<h1><?php echo ucfirst($_SERVER['SERVER_NAME']) ?> Removal.</h1>

	<p>To stop receiving notices from <?php echo $_SERVER['SERVER_NAME'] ?>, please enter your<br/> email address below and then click the "Remove" button.</p>

	<?php if (!empty($template->error)) { ?>
	<p><strong><?php h($template->error); ?></strong></p>
	<?php } ?>

    <form method="POST">
    <label>Email Address:</label>
    <input name="email" type="text" size="35" value="<?php h($email); ?>"/>
    <input type="submit" name="submit" value="Remove">
   </form>
  </body>
</html>
