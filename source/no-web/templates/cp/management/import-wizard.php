<form action="" method="post" enctype="multipart/form-data" name="form" id="form">
  <h1>Import Wizard</h1>
 <br /> 
  <p>
  <?php
  if(@$template->uploaded == 1)
  {
  ?>
  <span class="error">File has been uploaded named: <?php echo $template->named?></span>
  <?php
  }
  ?>
  
  <?php
  if(@$template->count > 0)
  {
  ?>
  
  <div id="contenttable">
  
  <table width="100%" cellspacing="0" cellpadding="2">
    <tr>
      <th>Import Wizard Step 5 : Select Fields</th>
    </tr>
    <tr>
      <td>File has <?php echo $template->count?> parts.<br /><textarea rows="5" cols="60" readonly="true"><?php echo htmlentities($template->lines)?></textarea></td>
    </tr>
  </table>
  
  <br />
  
  <table width="650" cellspacing="0" cellpadding="2">
    <tr>
      <th>Sample</th>
      <th>&nbsp;</th>
      <th>Type</th>
    </tr>
    <tr>
      <td colspan="3">
        If the type is not in the drop list check the radio button next to the text field and type in how you would like that field to be identified and identifier can only contain letters, numbers and underscores.
      </td>
    </tr>
            
    <?php
    $i = 1;
    $j = 0;
    $k = 1;
            
    while($i <= $template->count)
    {
        if($k == 1)
        {
            $bg = '#EAEAEE';
            $k = 0;
        }
        else
        {
            $bg = '#DCE3EF';
            $k = 1;
        }
    ?>
    <tr bgcolor="<?php echo $bg?>">
      <td width="100" rowspan="2"><textarea rows="5" cols="30" readonly="true"><?php echo trim($template->example[$j])?></textarea></td>
        <td width="90" rowspan="2">
          <span class="error2">Field (<?php echo $i ?>) Type:<br />Ignore: <input name="ignore[<?php echo $i?>]" type="checkbox" value="1" /> </span>
        </td>
        <!-- Select from cols menu -->   
        <td>
          <input type="radio" name="from[<?php echo $i?>]" value="menu" id="h<?php echo $i?>" checked />
          <select name="type[<?php echo $i ?>]" onFocus="javascript:document.form.h<?php echo $i ?>.checked = 1">
            <?php
            foreach($template->c_options AS $option)
            {
                if($option == $template->format[$j])
                {
                    $sel = " selected";
                }
                else
                {
                    $sel = '';
                }
            ?>
              <option<?php echo $sel?>><?php echo $option ?></option>
           <?php
           }
           ?>
         </select>
       </td>
       <!-- /Select from cols menu -->
     </tr>
            
     <tr bgcolor="<?php echo $bg?>">
       <td>
         <input type="radio" name="from[<?php echo $i?>]" value="text" id="hi<?php echo $i?>" />
         <input type="text" name="text_type[<?php echo $i?>]" onFocus="javascript:document.form.hi<?php echo $i?>.checked = 1" />
         <br />
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         <select name="field_type[<?php echo $i?>]">
           <option>text</option>
           <option>number</option>
         </select>
         <input type="text" size="2" value="30" name="field_len[<?php echo $i?>]" />
       </td>
     </tr>
            
     <?php
         $i ++;
         $j ++;
     // End while loop
     }
     ?>
   </td>
 </tr>
       
   <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
     <td colspan="3">Please add a title to identify import by:<input type="text" name="title" value="<?php echo $_POST['selected']?>" /></td>
   </tr>
   
   <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
     <td colspan="3">Description:<br /><textarea name="description" rows="4" cols="50"></textarea></td>
   </tr>
       
   <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
     <td colspan="3">
       Import into list: <?php print buildListSelect('list_name2',$_POST['list_name'], "disabled"); ?> Option moved to first page.
       <input type="hidden" name="list_name" value="<?php echo $_POST['list_name'] ?>" />
     </td>
   </tr>
   
   <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
     <td colspan="3"><label for="overwrite">Overwite </label> <input type="checkbox" name="overwrite" id="overwrite" value="1" /> (Overwrites bounces).</td>
   </tr>
   
   <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
     <td colspan="3"><strong>Do not import if email is in list: </strong>(hold ctrl for multiple)</td>
   </tr>
   <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
     <td colspan="3"><?php print buildListSelect('check[]', '', 'size="3" multiple'); ?></td>
   </tr>
       
   <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
     <td colspan="3" align="center">
       <input type="hidden" name="file" value="<?php echo $_POST['selected']?>" />
       <input type="hidden" name="delim" value="<?php echo $_POST['delim']?>" />
       <strong>Clicking submit will start the import</strong>
     </td>
   </tr>
        
   <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
     <td colspan="3" align="center"><input type="submit" name="submit_2" value="Submit" /></td>
   </tr>
 
 </table>
 
 </div>
        

  
  <?php
  }
  elseif(@$template->finished == 1)
  {
  ?>
  
    <div id="contentbox">
    <h1>Import Wizard Finished</h1>
    <p>Import wizard has completed <a href="/cp/management/imports.php">monitor imports here</a></p>
    </div>
  
  <?php
  }
  else
  {
  ?>
  <div id="contenttable">
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th>Step 1 : File Upload</th>
    </tr>
    <tr>
      <td>
       <div id="help_me_content_3">
        The first step is to upload a file you can do this in one of two ways.
        <br />
        1: If it is a small file (under 2 megs) upload using the form below.
        <br />
        2: FTP it into the <strong>import</strong> folder.  
        <br />
        &nbsp;&nbsp;&nbsp;&nbsp;Hostname:<?php echo SYSTEM_DOMAIN ?>
        <br />
        &nbsp;&nbsp;&nbsp;&nbsp;Username: upload
        <br />
        &nbsp;&nbsp;&nbsp;&nbsp;you should have been provided a password.
        </div>
      </td>
    </tr>
    <tr>
      <td><input type="file" name="file" size="20" /><input type="submit" name="upload" value="Upload File" /></td>
    </tr>
  </table>
  <br />
  
    <table width="100%" cellspacing="0" border="0" cellpadding="2">
     <tr>
      <th>Step 2 (optional): Run uncompress operation</th>
    </tr>
    <tr>
      <td>
        <div id="help_me_content_2">
        If the file you uploaded is compressed as a zip, tar.gz, or gz you have to uncompress it, do this below.
        <br />
        If the uncompress operation fails you will have to manually uncompress the file.
        </div>
      </td>
    </tr>
    <?php
    if(isset($template->uncompress_output))
    {
    ?>
    <tr>
      <td>Output generated: <br /><?php echo str_replace("\n","<br />",$template->uncompress_output)?></td>
    </tr>
    <?php
    }
    ?>
    <tr>
      <td>
        <select size="1" name="uncompress_selected">
        <?php foreach($template->dir AS $file)
        {
            if(!checkCompress($file))
           {
               continue;
           }
          echo "<option>".$file."</option>";
        }
?></select><select name="uncompress_type"><option>zip</option><option>gz</option><option>tar.gz</option></select><input type="submit" name="uncompress" value="Uncompress" /></td>
    </tr>
    </table>
  <br />
  
    <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th>Step 3 : Select File - [ <a href="/cp/management/import-wizard.php">refresh</a> ]</th>
    </tr>
    <tr>
      <td>
        <div id="help_me_content_1">
        Select the file for import from the select menu below, you can select it by clicking on the name (it will become highlighted blue).
        <br />
        Also type in how the file is delimited in the delimiter field if the file is tab delimited put in <strong>\t</strong>. You do not need to enter a delimiter if you are not importing for the wizard.
        <br />
        An easy way to find how the file is delimited is to simply enter a comma and proceed to import the file, on next page you will see if a snippet of the top of the file if the delimiter is incorrect simply click back and enter the correct one.
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <select size="10" name="selected"><?php 
          foreach($template->dir AS $file)
          {
               if(checkCompress($file))
               {
                   continue;
               }
               
               echo "<option>".$file."</option>";
          }
          ?></select>
    </td>
  </tr>
  <tr>
    <td>Delimiter: <input type="text" name="delim" value="," size="2" /></td>
  </tr>
  </table>
  <br />
  
    <table width="100%" cellspacing="0" border="0" cellpadding="4">
     <tr>
      <th colspan="2">Step 4 : Import Type</th>
    </tr>
    <tr>
      <td colspan="2">
        Click the button for the appropriate import type below.
      </td>
    </tr>
   <tr>
    <td>List: <?php print buildListSelect('list_name',$_POST['list_name']); ?><input type="submit" name="select" value="Select File for Import Wizard" /></td>
  </tr>
  <tr><td><strong>OR</strong></td></tr>
  <tr>
    <td><input type="submit" name="select2" value="Select File for Suppression/DNE Import" /></td>
  </tr>
  
</table>
</div>
  
  
  </p>
<?php
}
?>
</form>
