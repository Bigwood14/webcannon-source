<h1>Domain Management</h1>
<br />

<form action="/cp/isp-relations/domains.php" method="post" name="form">
<div id="contenttable">

<p><a href="/cp/isp-relations/dk.php">Click here to check domains</a><br /><br /></p>
<p><a href="/cp/isp-relations/aol_check.php">Click here to AOL check domains</a><br /><br /></p>

<table width="100%" cellpadding="4" cellspacing="0" border="0">
  <thead>
    <tr>
      <th width="10">&nbsp;</th>
      <th>Domain</th>
      <th>IP</th>
      <th>Group</th>
      <th>Default</th>
    </tr>
  </thead>
  <tbody>
  <?php
  //print_r($template);
  foreach($template->domains AS $domain)
  {
  ?>
    <tr bgcolor="<?php echo HTML_Layout::alternateBgColor(array('#eaeaee','#ffffff')) ?>">
      <td><input type="checkbox" name="selected[]" value="<?php echo $domain['ip'] ?>"></td>
      <td><a href="http://<?php echo $domain['domain']?>" target="_blank"><?php echo $domain['domain']?></a></td>
      <td><?php echo $domain['ip']?></td>
      <td><?php echo @$template->groups[$domain['domain_group_id']]['name']?></td>
      <td><?php if($domain['default'] != '1') { ?><a href="/cp/isp-relations/domains.php?make_default=1&id=<?php echo $domain['domain'] ?>">Make Default</a><?php } else {?>Default<?php } ?></td>
    </tr>
  <?php
  }
  ?>
	<tr>
		<th colspan="2" align="center" style="text-transform: none;">
			<select name="action">
				<option value="delete">Delete</option>
				<?php foreach ($template->groups as $group) { ?>
				<option value="group_<?php echo $group['domain_group_id']?>">Add group <em><?php echo $group['name'] ?></em></option>
				<?php } ?>
			</select>
	  		<input type="submit" name="action_submit" value="Submit" />
		</th>
      <td colspan="3">Delete selected domain.</td>
    </tr>
    </tr>
  </tbody>
</table>
<br />



<script type="text/javascript">
<?php
$r_list = $template->rotations;
foreach($template->servers AS $id => $server) {
    if(!is_array($r_list[$id])) $r_list[$id]['per_mailing'] = 0;
    ?>
    ips_<?php echo $id ?> = <?php echo $r_list[$id]['per_mailing'] ?>;
    <?php } ?>

    function doPer() {
        var b = document.getElementById('rot_server_id');
        c = b.options[b.selectedIndex].value;
        if (eval("ips_" + c) != undefined)
        {
            document.getElementById('ip_num').value = eval("ips_" + c);
        }
    }
</script>
<table width="500" cellspacing="0" cellpadding="4" border="0">
   <tr>
     <th>Rotations</th>
   </tr>
   <tr>
    <td>
     <select name="server_id" id="rot_server_id">
     <?php
     $i = 0;
     foreach($template->servers AS $id => $server) {
         if($i < 1) $sel = 'selected';
     ?>
     <option value="<?php echo $id ?>"<?php echo $sel ?>><?php echo $server ?></option>
     <?php
     $i ++;
     $sel = '';
     }
     ?>
     </select> <input type="submit" name="rotations" value="Update" />
    </td>
   </tr>
   <tr>
    <td>Rotate <input type="text" size="3" id="ip_num" name="ip_num" /> domains / ips every mailing (entering 0 disables rotation).</td>
   </tr>
</table>

<br />

<?php
if(isset($template->error))
{
?>
<table width="500" cellspacing="0" cellpadding="2" border="0">
    <tr>
      <th>There were errors!!</th>
    </tr>
    <tr>
      <td>
      <?php
      foreach($template->error AS $error)
      {
      ?>
      <?php echo $error?><br />
      <?php      
      }
      ?>
      </td>
    </tr>
</table><br />
<?php
}
?>
<script>
function silentErrorHandler() {return true}
window.onerror=silentErrorHandler()

function resetForm()
{
    document.form.reset();
}

function generateIPS()
{
    max = (document.form.num_rows2.value);

    ob = document.getElementById("ip_0");
    IP = ob.value;


    parts = IP.split(".");
    //alert('End number is...:'+parts[3]);
    part_0 = Number(parts[0]);
    part_1 = Number(parts[1]);
    part_2 = Number(parts[2]);
    part_3 = Number(parts[3]);

    if(part_0 < 1)
    {
        return;
    }



    for(i = 1; i < max;i ++)
    {
        ob = document.getElementById("ip_"+i);

        if(part_3 == 254)
        {
            part_2 = (part_2 + 1);
            part_3 = 0;
        }
        else
        {
            part_3 = (part_3 + 1);
        }

        ob.value = parts[0] +'.'+ parts[1] +'.'+ part_2 +'.'+ part_3;
    }
}

function buildForm()
{
    j = document.form.num_rows2.value;
    if(j > 200)
    {
        alert('Keep it below 200, joker!');
        return;
    }
    html = '';
    bg = '#fff';
    var k = 0;
    for(i = 0;i < j;i ++)
    {
        value_d = '';
        d = document.getElementById("domain_"+i);

        if(d != null)
        {
            value_d = d.value;
        }

        value_ip = '';
        ip = document.getElementById("ip_"+i);

        if(ip != null)
        {
            value_ip = ip.value;
        }
        if(k == 0)
        {
            bg = '#eaeaee';
            k = 1;
        }
        else
        {
            bg = '#fff';
            k = 0;
        }

        html += '<tr bgcolor="'+bg+'"><td>'+(i+1)+'</td><td><input id="domain_'+i+'" type="text" name="domain['+i+']" value="'+value_d+'" /></td>';
        html += '<td><input id="ip_'+i+'" type="text" name="ip['+i+']" value="'+value_ip+'" /></td></tr>';
    }
    document.getElementById("rep").innerHTML = html;
}

function typo()
{
    if(document.getElementById("textarea_c").checked == true)
    {
        document.getElementById("rows_selector").style.display = 'none';
        document.getElementById("textarea").style.display = '';
    }
    else
    {
        document.getElementById("rows_selector").style.display = '';
        document.getElementById("textarea").style.display = 'none';
    }
}

window.onload = function() {
    typo();
    doPer();
}
</script>
<table>
 <tr><th colspan="2">Mode</th></tr>
 <tr>
  <td><label for="textarea_c">textarea mode<label> <input type="radio" name="selector" id="textarea_c" onclick="typo()"></td>
 </tr>
 <tr>
  <td><label for="rows_c">rows mode</label> <input type="radio" name="selector" id="rows_c" onclick="typo()" checked></td>
 </tr>
</table>
<br />
<div id="textarea">
<table width="500" cellspacing="0" cellpadding="4" border="0">
   <tr>
     <th>Add Domain / IP</th>
   </tr>
   <tr>
    <td><textarea rows="20" cols="45" name="domain_textarea"></textarea></td>
   </tr>
   <tr>
    <td>Delim <input type="text" size="4" value="->" name="delim" /> Order: 
    <select name="order">
     <option value="1">ip (delim) domain</option>
     <option value="2">domain (delim) ip</option>
    </select> <input type="submit" name="add_domain_textarea" value="Add Domain(s) / IP(s)" /></td>
   </tr>
</table>
</div>
<div id="rows_selector">
<table width="100%" cellspacing="0" cellpadding="4" border="0">
  <thead>
    <tr>
      <th colspan="3">Add Domain / IP</th>
    </tr>
  </thead>
  
  
   <tr>
    <td colspan="2">
     Rows: <input type="text" size="3" name="num_rows2" onKeyUp="buildForm()">
    </td>
    <td>
    <input type="button" name="button" value="IP Generation" onclick="generateIPS()" />
    <input type="button" onclick="resetForm()" value="Reset Form" />
    </td>
   </tr>
    <tr bgcolor="#dce3ef">
      <td>&nbsp;</td>
      <td><strong>Domain Name</strong></td>
      <td><strong>IP Address</strong></td>
    </tr>
    
    <tbody id="rep">
    
    <tr>
      <td>1: </td>
      <td><input type="text" id="domain_0" name="domain[0]" value="" /></td>
      <td><input type="text" id="ip_0" name="ip[0]" value="" /></td>
    </tr>
    
    </tbody>
    
    <tr>
      <td colspan="3" align="center"><input type="submit" name="add_domain" value="Add Domain(s) / IP(s)" /></td>
    </tr>
</table>
</div>
<br />
</div>
</form>

<div id="contenttable">

<form class="content-form" method="post">
	<fieldset>
		<legend>Add Group</legend>

		<div class="row clearfix">
			<label>Name</label>
			<input type="text" name="name" />
		</div>
		<div class="submit clearfix">
			<input type="submit" value="Add Group" name="group_add"/>
		</div>
	</fieldset>
	<br />
	<?php if (!empty($template->groups)) { ?>
	<table width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<th>Name</th>
			<th>Action</th>
		</tr>
		<?php foreach ($template->groups as $group) { ?>
		<tr>
			<td><?php echo $group['name'] ?></td>
			<td><a href="?group_delete=<?php echo $group['domain_group_id'] ?>">delete</a></td>
		</tr>
		<?php } ?>
	</table>
	<?php } else { ?>
	<p>No groups</p>
	<?php } ?>
</form>

</div>
