<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Welcome to Celibero</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <style type="text/css" media="screen">@import "../../css/layout.css";</style>
  <style type="text/css" media="projection">@import "../../css/layout.css";</style>
  </head>
<body>
<div id="contenttable">
<form action"/cp/scheduling/personalization-how.php" method="post">
<table width="350" cellspacing="0" border="0" cellpadding="2" align="center">
    <tr>
      <th colspan="2">Personlization</th>
    </tr>
    <tr>
      <td align="left" colspan="2">To use personalization simply input the database field surrounded by curly brackets e.g {first}<br/>You can use these fields in either the header/footer,html,text or subject fields.<br /><br />Below is a list of the fields that are available to you.</td>
    </tr>
    <tr><th>Field</th><th>Default</th></tr>
    <?php
    $i = 1;
    foreach($template->fields AS $field)
    {
    if($i == 1)
    {
        $i = 0;
        $bg = '';
    }
    else
    {
        $bg = ' bgcolor="#EAEAEE"';
        $i = 1;
    }
    ?>
    <tr<?php echo $bg?>>
      <td>{<?php echo $field?>}</td>
      <td><input type="text" name="default[<?php echo $field?>]" value="<?php echo @$template->defaults[$field]?>" size="30" maxlength="30" /></td>
    </tr>
    <?php
    }
    ?>
    <tr><td colspan="2" align="center"><input type="submit" name="update" value="Update Defaults" /></td></tr>
    <tr><td colspan="2" align="center"><a href="javascript:window.close()">Close Window</a></td></tr>
</table>
</form>
</div>
</body>
</html>
