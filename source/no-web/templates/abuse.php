<html>
<head><title></title></head>
<body>
<br /><form action="/abuse.php" method="post">
<table cellpadding="4" cellspacing="2" width="500" align="center">
  <tbody>
    <tr>
      <th colspan="2">Report Abuse</th>
    </tr>
    
    <?php
if($template->error == 1)
{
?>
 <tr>
      <td colspan="2" class="error"><?php echo $template->form->getMsg('',true)?></td>
    </tr>
<?php
}
?>

    <?php
if($_GET['com'] == 1)
{
?>
 <tr>
      <td colspan="2"><font color="red">Abuse form has been sent you have also been unsubscribed from all list</font></td>
    </tr>
<?php
}
else
{
?>
    
    <tr>
      <td colspan="2" align="left">
      ABUSE - CAUTION PLEASE ONLY USE THIS PAGE IF YOU ARE 100% SURE THE CORRESPONDING AFFILIATE HAS SENT YOU UNSOLICITED MAIL. WE WILL ANALYZE ALL COMPLAINTS WE RECEIVE THROUGH THIS FORM THOROUGHLY AND TAKE THE APPROPRIATE ACTION FOR EACH AND EVERY ONE.<br /><br />
      Notice: ALL FIELDS ARE REQUIRED IN ORDER TO CONFIRM COMPLAINT AUTHENTICITY
      </td>
    </tr>

    <tr>
      <td><span class="error">*</span>Email:</td>
      <td align="left"> <?php $template->form->printInput('email');?></td>
    </tr>
    
      <tr>
      <td colspan="2" align="left" class="error">
      *Email must be the email the alleged unsolicited mail is being sent to - please check the To: field so we can match up the email accordingly.
      </td>
    </tr>
    
    
    <tr>
      <td>First Name:</td>
      <td align="left"><?php $template->form->printInput('first_name');?></td>
    </tr>
    
<tr>
      <td>Last Name:</td>
      <td align="left"><?php $template->form->printInput('last_name');?></td>
    </tr>
    
    <tr>
      <td>State:</td>
      <td align="left"><?php $template->form->printInput('state');?></td>
    </tr>
    
    <tr>
      <td>Country:</td>
      <td align="left"><?php $template->form->printInput('country');?></td>
    </tr>
    
    <tr>
      <td>Cut and Paste Entire Offending Message:</td>
      <td align="left"><?php $template->form->printInput('message');?></td>
    </tr>
    
    <tr>
      <td>Comments:</td>
      <td align="left"><?php $template->form->printInput('comments');?></td>
    </tr>
    
   
    <tr>
      <td colspan="2"><input type="submit" name="report" value="Report Abuse" /></td>
    </tr>
  </tbody>
</table>
<form>
<?php
}
?>
<br />
