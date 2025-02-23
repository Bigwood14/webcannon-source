<script type="text/javascript">
<!--
tracked_links.lists = {
<?php 
$js_lists = array();
foreach ($template->lists as $list) {
    $js_lists[] = $list['id'] . ': "' . $list['username'] . '"';
}
echo implode(",\n",$js_lists);
?>
}
//-->
</script>
<form action="/cp/scheduling/draft.php" method="post" name="form" id="form">
<div id="contentbox">
  <h1>New Draft</h1>
  <p><a href="/cp/scheduling/draft.php#" onClick="javascript:openWin('/cp/scheduling/personalization-how.php','Personlization',400,500,'yes')">Learn how to use personalisation</a> (opens in popup).</p>
  </div>
  <p>
  <?php
  if($template->error == 1)
  {
  ?>
  <span class="error">Error with form<br />
  <?php echo $template->form->getMsg('', true); ?>
  </span>
  <?php
  }
  ?>
  
  <div id="contenttable">
    <?php $template->form->printInput('edit');?>
    <?php $template->form->printInput('id');?>
    <!-- Title -->
    <table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th colspan="2">Title</th>
      </tr>
      <tr>
        <td>How will you id this draft?: <?php $template->form->printInput('title');?></td>
      </tr>
    </table>
    <!-- /Title -->
    <br />
    
    <!-- Server Selection -->
    <table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th>Server Selection</th>
		<th>Delivery Configuration</th>
      </tr>
      <tr>
        <td><?php $template->form->printInput('server_id');?></td>
		<td><?php $template->form->printInput('delivery_configuration_id');?></td>
      </tr>
    </table>
    <!-- /Server Selection -->
    <br />
    
    <!-- Domain Selection -->
    <script>
    var whatnow = 1;
    
    function domainsCheckAll()
    {
        for(i = 0;i <= <?php echo count($template->domains)-1?>;i ++)
        {
            bool = 'false';
            if(document.form.domainAll.checked == true)
            bool = 'true';

            eval('document.form.domain_'+i+'.checked = '+bool);
        }
    }
    </script>
    <table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th colspan="6">Domain Selection <input type="checkbox" name="domainAll" onchange="domainsCheckAll()" class="hideRow" /></th>
      </tr>
      <?php
      if($template->rotations['per_mailing'] > 0)
      {
      ?>
      <tr>
       <td colspan="3">Using Rotations (<a href="javascript:domainManual()">manual</a>)</td>
      </tr>
      <?php
      }
      ?>
      <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>" style="display:none">
        <?php
        $i = 0;
        foreach($template->domains AS $domain)
        {
            if(($i % 3) == 0)
            {
                print "</tr><tr bgcolor=\"".HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff'))."\" class=\"hideRow\">";
            }
            $ch = '';
            if(@in_array($domain['domain'], $template->domains_selected))
            {
                $ch = ' checked';
            }
        ?>
        <td width="166">
          <input type="checkbox" id="domain_<?php echo $i ?>" name="domains[]" value="<?php echo $domain['domain'] ?>"<?php echo $ch ?> />
          <label for="domain_<?php echo $i ?>" onmouseover="doTooltip(event,'<?php echo $domain['ip'] ?>')" onmouseout="hideTip()">
          <font color="#000"><?php echo $domain['domain'] ?></font></label>
        </td>
        <?php
        $i ++;
        }
        $left = 3 - ($i % 3);
        if($left != 3)
        {
            for($j =1;$j <= $left;$j ++)
            {
                print "<td>&nbsp;</td>";
            }
        }
        ?>
      </tr>
    </table>
    <!-- /Domain Selection -->
    <br />
    
    <!-- List Selection -->
    <table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th colspan="3">List Selection</th>
      </tr>
      <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
        <?php
        $i = 0;
        foreach($template->lists AS $list)
        {
            if(($i % 3) == 0)
            {
                print "</tr><tr bgcolor=\"".HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff'))."\">";
            }
            $ch = '';
            if(@in_array($list['id'], $template->lists_selected))
            {
                $ch = ' checked';
            }
        ?>
        <td width="166">
          <input type="checkbox" id="list_<?php echo $i ?>" name="list[]" value="<?php echo $list['id'] ?>"<?php echo $ch ?> />
          <label for="list_<?php echo $i ?>">
          <font color="#000"><?php echo $list['username'] ?></font></label>
        </td>
        <?php
        $i ++;
        }
        $left = 3 - ($i % 3);
        if($left != 3)
        {
            for($j =1;$j <= $left;$j ++)
            {
                print "<td>&nbsp;</td>";
            }
        }
        ?>
      </tr>
    </table>
    <!-- /List Selection -->
    <br />
    
    <!-- Recipient Selection -->
    <table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th colspan="2">Recipient Selection</th>
      </tr>
  
      <tr>
        <td colspan="2"><input type="radio" checked name="send_who" value="all" id="all" /><label for="all">Send to all recipients.</label></td>
      </tr>
      <tr bgcolor="#EAEAEE"> 
        <td colspan="2">
          <input type="radio" name="send_who" value="first" id="first" /><label for="first">Send to first </label>
          <input type="text" name="first_recipients" size="7" /><label for="first"> recipients.</label>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <input type="radio" name="send_who" value="skip" ID="skip" ><label for="skip">Skip first</label>
          <input type="text" name="start_recipient" size="7"/> <label for="skip">recipients, and send to maximum of </label><input type="text" name="max_recipients" size="7" /> 
          <label for="skip">recipients.</label>
        </td>
      </tr>
    </table>
    <!-- /Recipient Selection -->
    <br />
    
<table width="500" cellspacing="0" cellpadding="0" id="tracked_links">
    <tr>
        <th colspan="4">Link Tracking</th>
    </tr>
	<tr>
		<td colspan="4">
			<input type="checkbox" name="link_tracking" class="checkbox" id="auto-link-tracking" value="1" /> 
			<label for="auto-link-tracking">Enable automatic link tracking.</label>
		</td>
	</tr>
    <tr class="subheading">
        <th>Link</th>
        <th>Action</th>
        <th>Target</th>
        <th></th>
    </tr>
    <tr id="no_links_to_track">
        <td colspan="4">Click here to add a tracked link.</td>
    </tr>
    <tr id="more_links_to_track">
        <td colspan="4">Click here to add another tracked link.</td>
    <tr>
</table>
<br />
    
    <!-- From Names -->
    <table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th colspan="2">From Name:</th>
      </tr>
      <tr>
        <td> <label for="multi_from">Enable Multiple?</label> <?php $template->form->printInput('multi_from');?></td>
      </tr>
      <tr>
        <td>
          
        <div id="nooutlinetable">
        
          <div style="display:none" id="insideSubCategory2">
            <table cellpadding="2">
              <?php
              for($i =1;$i < 11;$i++)
              {
              ?>
              <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
                <td>From Name <?php echo $i ?>:</td>
                <td><?php $template->form->printInput('from_line_'.$i);?></td>
              </tr>
              <?php
              }
              ?>
            </table>
          </div>
          
          <div style="display:block" id="insideSubCategory1">
            <table>
              <tr>
                <td>From Name:</td>
                <td><?php $template->form->printInput('from_line_0');?></td>
              </tr>
            </table>
          </div>
          
        </div>
          
        </td>
      </tr>
    </table>
    <!-- /From Names -->
    <br />
    
    <!-- Subject Lines -->
    <table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th colspan="2">Subject Line:</th>
      </tr>
      <tr>
        <td> <label for="multi_subject">Enable Multiple?</label> <?php $template->form->printInput('multi_subject');?></td>
      </tr>
      <tr>
        <td>
          
        <div id="nooutlinetable">
        
          <div style="display:none" id="insideSubCategory4">
            <table cellpadding="2">
              <?php
              for($i =1;$i < 11;$i++)
              {
              ?>
              <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
                <td>Subject Line <?php echo $i ?>:</td>
                <td><?php $template->form->printInput('subject_line_'.$i);?></td>
              </tr>
              <?php
              }
              ?>
            </table>
          </div>
          
          <div style="display:block" id="insideSubCategory3">
            <table>
              <tr>
                <td>Subject Line:</td>
                <td><?php $template->form->printInput('subject_line_0');?></td>
              </tr>
            </table>
          </div>
          
        </div>
          
        </td>
      </tr>
    </table>
    <!-- /Subject Lines -->
    <br />
    
    <!-- Message Type -->
    <table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th colspan="2">Message Type</th>
      </tr>
      <tr>
        <td> 
          <?php $template->form->printInput('type');?>
        </td>
      </tr>
      <tr>
        <td>Personlization: <a name="person"></a></td>
      </tr>
      <tr>
        <td> <a href="javascript:openWin('/cp/scheduling/personalization-how.php','Personlization',400,500,'yes');">Get Personalization Identifiers</a>  | <a href="javascript:openWin('/cp/scheduling/personalization-how.php','Personlization',400,500,'yes');">Modify Default Field Replacements</a></td>
      </tr>
    </table>
    <!-- /Message Type -->
    <br />
    
    <!-- Text -->
    <div style="display:block" id="insideSubCategory6">
    <table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th colspan="2">Text Message</th>
      </tr>
      <tr>
        <td>Personalization Insert: <?php echo printEmailFieldsSelect("test001","onChange=\"javascript:insertMM('text','test001')\"");?></td>
      </tr>
      <tr>
        <td> 
          <?php $template->form->printInput('text');?>
        </td>
      </tr>
    </table>
    <br />
    </div>
    <!-- /Text -->
    
    <!-- HTML -->
    <div style="display:block" id="insideSubCategory5">
    <table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th colspan="2">HTML Message</th>
      </tr>
      <tr>
        <td>Personalization Insert: <?php echo printEmailFieldsSelect("test002","onChange=\"javascript:insertMM('html','test002')\"");?></td>
      </tr>
      <tr>
        <td> 
          <?php $template->form->printInput('html');?>
        </td>
      </tr>
    </table>
    <br />
    </div>
    <!-- /HTML -->
    
    <script>
    function setAOL()
    {
        // 1 = Wants some of that
        if(document.form.aol_check.checked == true)
        {
            elem = document.getElementById("insideSubCategory7");
            elem.style.display="";
            elem = document.getElementById("insideSubCategory8");
            elem.style.display="";
        }
        // Nah thanks
        else
        {
            elem = document.getElementById("insideSubCategory7");
            elem.style.display="none";
            elem = document.getElementById("insideSubCategory8");
            elem.style.display="none";
        }
    }
    </script>
    <!-- AOL -->
    <table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th colspan="2">AOL Message <?php $template->form->printInput('aol_check');?></th>
      </tr>

      <tr style="display:none" id="insideSubCategory7">
        <td>Personalization Insert: <?php echo printEmailFieldsSelect("test003","onChange=\"javascript:insertMM('aol','test003')\"");?></td>
      </tr>
      <tr style="display:none" id="insideSubCategory8">
        <td> 
          <?php $template->form->printInput('aol');?>
        </td>
      </tr>
    </table>
    <br />
    <!-- /AOL -->

	<!-- Yahoo -->
	<table width="500" cellspacing="0" border="0" cellpadding="2">
      <tr>
        <th colspan="2">AOL Message <?php $template->form->printInput('aol_check');?></th>
      </tr>

      <tr style="display:none" id="insideSubCategory7">
        <td>Personalization Insert: <?php echo printEmailFieldsSelect("test003","onChange=\"javascript:insertMM('aol','test003')\"");?></td>
      </tr>
      <tr style="display:none" id="insideSubCategory8">
        <td> 
          <?php $template->form->printInput('aol');?>
        </td>
      </tr>
    </table>
    <br />
	<!-- /Yahoo -->

<table width="500" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <th colspan="4">Headers and Footers</th>
    </tr>
    <tr>
        <td width="80">Header:</td>
        <td>
            <select name="header">
                <option value="">(None)</option>
                <?php foreach ($template->headers as $header) { ?>
                <option value="<?php echo $header['id']; ?>"<?php
                    if ($header['is_default']) { echo ' selected'; } ?>><?php
                    echo htmlspecialchars($header['name']);
                ?></option>
                <?php } ?>
            </select>
        </td>

        <td width="80">Footer:</td>
        <td>
            <select name="footer">
                <option value="">(None)</option>
                <?php foreach ($template->footers as $footer) { ?>
                <option value="<?php echo $footer['id']; ?>"<?php
                    if ($footer['is_default']) { echo ' selected'; } ?>><?php
                    echo htmlspecialchars($footer['name']);
                ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
</table>
<br />
    
<table width="500" cellspacing="0" border="0" cellpadding="2">
    <tr>
        <th colspan="2">Seeds -  5 Addresses Max One Per Line</th>
    </tr>
    <tr>
        <td>
        To have a seed be put in repeatitively put :x on the end of the email address x being the number to seed at. 
        </td>
    </tr>
    <tr>
        <td colspan="2"><?php $template->form->printInput('seeds');?></td>
    </tr>
</table>

<br />
    
    <table width="500" cellpadding="2" cellspacing="0">
      <tr> 
        <th>Categories: [<a href="/cp/management/categories.php" target="_new">edit</a>]</th>
        <th>Suppression List:</th>
      </tr>
      
      <tr> 
        <td rowspan="4" valign="top"> 
          <script>
          function clearSelection()
          {
              j = document.form.categories.length;
              for(i = 0;i < j;i ++)
              {
                  if(document.form.categories.options[i].selected == true)
                  {
                      document.form.categories.options[i].selected = false;
                  }

              }
          }
          </script>
          <a href="javascript:clearSelection()">Clear Selection</a><br />
          <?php $template->form->printInput('categories'); $template->form->getMsg('categories')?>
        </td>
        <td> </td>
      </tr>
      <tr> 
        <td align="left"><?php $template->form->printInput('sup_list'); $template->form->getMsg('categories')?></td>
      </tr>
      <tr>
        <th>Comments:</th>
      </tr>
      <tr> 
        <td valign="top"> 
          <?php $template->form->printInput('comments'); $template->form->getMsg('comments')?>
        </td>
      </tr>
      <tr> 
        <td colspan="2" align="center"><br /> <input type="submit" name="submit" value="Save Draft in Preparation for Scheduling" /> 
          <br /> <br /></td>
      </tr>
    </table>

  </tbody>
</table>
</div>
 
  
  </p>

</form>
