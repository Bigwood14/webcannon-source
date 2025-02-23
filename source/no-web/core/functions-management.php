<?PHP

function unsubscribe_domain($domain,$list = "",$global = 0,$how = 0,$sgdne = 0)
{
    global $db,$db2;

    $domain_db = $db->qstr($domain);
    //print $email."\n";
    if($list != "")
    {

        $db2 = connectToDB($list);

        $sql = "SELECT * FROM email WHERE address LIKE '%$domain'";
        $rs = $db2->Execute($sql);
        
        while($rw = $rs->FetchRow())
        {
            unsubscribe($rw['address'],$list,0,$how);
        }


        return 2;
    }
    if($global == 1)
    {
        $lists = getLists();

        //print_r($lists);

        /*foreach($lists AS $l)
        {
            unsubscribe_domain($domain,$l['username']);
        }*/
        //alterConf("block $domain\n","BLOCK_SECTION",1);

        $sql = "INSERT INTO global_unsub_domain (ts,domain,how,global_action) VALUES (NOW(),$domain_db,'$how','$sgdne')";
        $db->Execute($sql);


        return 1;
    }
}


function isSuppressed($email,$list_id)
{
    global $db;

    $sql = "SELECT COUNT(*) AS `count` FROM email_to_sup WHERE email = '$email' AND sup_list_id = '$list_id';";
    $rw = $db->GetRow($sql);

    if($rw['count'] > 0)
    {
        return true;
    }

    return false;
}

function addSuppressionEmail($email,$list_id)
{
    global $db;

    if(isSuppressed($email,$list_id))
    {
        return -1;
    }
    elseif(!validEmail($email))
    {
        return -2;
    }
    else
    {
        $sql = "INSERT INTO email_to_sup (email,sup_list_id) VALUES ('$email','$list_id');";
        $db->Execute($sql);
    }
}

function alterConf($text,$section = 'LIST_SECTION',$a = 1)
{
    global $config;
    $file = $config->values['site']['path'].'sys/blast/blast.conf';

    $contents = implode("",file($file));

    $pattern = "/(# ".$section."_START\n)([^#]*)(# ".$section."_END)/";
    if($a == 1)
    {
        $replacement = "\${1}\${2}$text\${3}";
    }
    else
    {
        $replacement = "\${1}$text\${3}";
    }
    $n_contents = preg_replace($pattern, $replacement, $contents);

    $fp = fopen($file,'w');
    fwrite($fp,$n_contents);
    fclose($fp);

    return ;
}
?>