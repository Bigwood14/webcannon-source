<div id="contentbox">
  <h1>SpamAssassin</h1>
  <p>Most hosts have the limit score of spamassassin set from 4.7 to 5.0 if your below this your good!</p>
</div>
<br />

<?php
if(isset($template->report))
{
?>
<div id="contenttable">
<table width="500" cellspacing="0" border="0" cellpadding="2" align="center">
  <tbody>
  
    <tr>
      <th>Report</th>
    </tr>
    <tr>
      <td>
      <textarea cols="80" rows="20"><?php echo $template->report?></textarea>
      </td>
    </tr>
    
  </tbody>
</table>
</div>
<?php
}
?>
 

  
  