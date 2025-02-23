<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Webcannon</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <style type="text/css" media="screen">@import "/css/popup.css";</style>
<?php if (!empty($template->styles)) {
    foreach ($template->styles as $stylesheet) { ?>
<link rel="stylesheet" type="text/css" href="/css/<?php echo $stylesheet; ?>" />
<?php }
} ?>

</head>
<body>

<?php include $template->directory.$template->template; ?>
<br />
<center><a href="javascript:window.close();">Close Window</a></center>
</body>
</html>
