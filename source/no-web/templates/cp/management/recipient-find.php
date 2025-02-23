<form action="" method="get" enctype="multipart/form-data">

<h1>Find Recipients</h1>
<br />
  
<div id="contenttable">
<?php
$display = 'none'; $state = 0;
if (!empty($_GET['multi_id']))
	$display = ''; $state = 1;
?>
<script type="text/javascript">
var id_state = <?php echo $state?>;
function multiID()
{
    id_el = document.getElementById('multi_id');
    if(id_state == 0)
    {
        id_el.style.display = '';
        id_state = 1;
    }
    else
    {
        id_el.style.display = 'none';
        id_state = 0;
    }
}
</script>
  <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th>List Selection</th>
    </tr>
    <tr>
      <td>Find in list: <?php print buildListSelect('list_name', @$_GET['list_name']); ?> 
       <label for="email_only">Show only emails</label> <input type="checkbox" id="email_only" name="email_only" value="1" /></td>
    </tr>
  </table>
  
  <br />
  
  <table width="100%" cellspacing="0" border="0" cellpadding="2">  
    <tr>
      <th colspan="2">Basic Search</th>
    </tr>
    <tr>
      <td colspan="2">Entering a * or a % anywhere will act as a wildcard so it can match anything. <br />Its best to be as accurate as possible for best performance.</td>
    </tr>
    <tr>
      <td colspan="2">
        <input type="text" name="local" value="<?php echo (empty($_GET['local'])) ? '' : $_GET['local'];?>" size="20" />@
        <input type="text" name="domain" value="<?php echo (empty($_GET['domain'])) ? '' : $_GET['domain'];?>" size="20" />
        <input type="submit" name="search_basic" value="Basic Search" />
      </td>
    </tr>
  </table>
  <br />
  <table width="100%" cellspacing="0" border="0" cellpadding="2">  
    <tr>
      <th colspan="2">ID Search</th>
    </tr>
    <tr>
      <td colspan="2">Enter the ID found in the email.</td>
    </tr>
    <tr>
      <td colspan="2"><input type="text" name="id" size="11" value="<?php echo $template->id?>" /><input type="submit" name="search_id" value="ID Search" /> 
                      <a href="javascript:multiID();">Multiple</a>
      </td>
    </tr>
    <tr style="display:<?php echo $display?>" id="multi_id">
     <td colspan="2">
      <textarea rows="10" cols="12" name="multi_id"><?php echo (empty($_GET['multi_id'])) ? '' : $_GET['multi_id'];?></textarea>
      <br /><input type="submit" name="search_multi_id" value="Multiple ID Search" />
      <label for="prefix_id">Prefix email with id</label> <input type="checkbox" id="prefix_id" name="prefix_id" value="1" />
     </td>
    </tr>
	<tr>
      <th colspan="2">ID Search 2</th>
    </tr>
    <tr>
      <td colspan="2">Enter the ID found in the link.</td>
    </tr>
    <tr>
      <td colspan="2"><input type="text" name="id_2" size="11" value="<?php echo $template->id_2?>" /><input type="submit" name="search_id_2" value="Link ID Search" /> 
      </td>
    </tr>

  </table>
  <br />
  <table width="100%" cellspacing="0" border="0" cellpadding="2">  
    <tr>
      <th colspan="2">ID Sentence Search</th>
    </tr>
    <tr>
      <td colspan="2">Enter the full sentence below from the bottom the the email.</td>
    </tr>
    <tr>
      <td colspan="2"><input type="text" name="sentence" size="50" value="<?php echo @$template->sen_id?>" /><input type="submit" name="search" value="Sentence Search" /></td>
    </tr>
  </table>
  <br />
  
    <table width="100%" cellspacing="0" border="0" cellpadding="2">
    <tr>
      <th colspan="2">List <?php if(isset($template->pager)) { include $template->directory."cp/pager.php"; } ?></th>
    </tr>
    
    <?php
    if($template->slog != '')
    {
    ?>
    <tr bgcolor="#dce3ef">
      <td colspan="2">This user was found in the slog (they have already been unsubscribed). Date: <?php echo $template->slog?></td>
    </tr>
    <?php
    }
    ?>
    
    <tr>
      <td colspan="2">Found: <?php echo $template->count ?> record(s).
        <?php if($template->count > 0) { ?>
        <strong><a href="/cp/management/recipient-remove.php?<?php
        foreach($template->emails AS $emaill)
        {
            echo "email[]=". $emaill['email'] ."&";
        }
        ?>">Click here to remove.</a></strong>
        <?php } ?>
      </td>
    </tr>
    
    <tr>
      <td>
        <textarea cols="65" rows="35" readonly="true" wrap="off"><?php
        $i = 0;
        foreach($template->emails AS $email)
        {
            ksort($email);
            $sep = ",";
            if($i == 0)
            {
                if($_GET['email_only'] == '1')
                {
                    echo "email\n";
                }
                else
                {
                    $keys =  array_keys($email);
                    $c = count($keys);
                    $j = 1;
                    foreach($keys AS $key)
                    {
                        echo $key;
                        if($j == $c)
                        {
                            echo "\n";
                        }
                        else
                        {
                            echo ',';
                        }
                        $j ++;
                    }
                }
            }
            if($_GET['email_only'] == '1')
            {
                if($_GET['prefix_id'] == "1") echo $email['com_id'].",";
                echo $email['email']."\n";
            }
            else
            {
                echo implode($sep, $email)."\n";
            }
            $i ++;
?>
<?php
        }
?></textarea>
      </td>
      <td valign="top" align="center">
        <br />
        <!--
        <select name="field_select[]" size="5" multiple>
        <?php 
foreach($this->field_select_options AS $f)
{
        ?>
        
        <?php
}
        ?>
        </select>
        
        <br />
        <input type="submit" name="show" value="Show Fields" />
        -->
      </td>
    </tr>
  </table>
</div>
 
</form>
