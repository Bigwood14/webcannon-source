<?php
function registerfly($username, $password, $domains)
{
    global $config;

    $cookie_file = $config->values['site']['path'] . "cp/scheduling/test/cook_registerfly";
    $header[] = "Accept: text/vnd.wap.wml,*.*";


    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://registerfly.com/scripts/login.php");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER,    $header);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "create=1&url=&tab=default&username=$username&password=$password&checkbox=");
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
    curl_setopt($ch, CURLOPT_REFERER, "https://registerfly.com/scripts/login.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEJAR,     $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE,    $cookie_file);

    $buffer = curl_exec($ch);
    curl_close($ch);

    //print "<textarea>".htmlentities($buffer)."</textarea>";
    foreach($domains AS $domain)
    {
        $name   = $domain['domain'];
        $ip     = $domain['ip'];
        $spf    = $domain['spf'];
        $dk     = $domain['dk'];

        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://registerfly.com/scripts/manage.php?totalpage=0&sort=asc&sortby=domain&tab=domains&starts=&term=&menu=default&records=13&currpage=0&display=1300");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,    $header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_REFERER, "https://registerfly.com/scripts/login.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR,     $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE,    $cookie_file);

        $buffer = curl_exec($ch);
        curl_close($ch);
        preg_match('/account_view.php\?domain=('.$name.')&id=(.*)"/i', $buffer, $matches);
        //print $buffer;
        //print_r($matches);
        //print $buffer;exit;
        //$matches[2] = "21533842";
        $fields = "
return_url=control.php&
mode=1&
domain=".urlencode($matches[1])."&
id=".$matches[2]."&
maxnumber=10&

HostName1=".urlencode('@')."&
MXPref1=10&
RecordType1=TXT&
Address1=".urlencode($spf)."&

HostName2=".urlencode('@')."&
MXPref2=5&
RecordType2=MX&
Address2=".urlencode($name)."&

HostName3=".urlencode('www')."&
MXPref3=10&
RecordType3=A&
Address3=".urlencode($ip)."&

HostName4=*&
MXPref4=10&
RecordType4=A&
Address4=".urlencode($ip)."&

HostName5=".urlencode('@')."&
MXPref5=10&
RecordType5=A&
Address5=".urlencode($ip)."&
";
        if($dk != '')
        {
            $fields .= "HostName6=dk._domainkey&
MXPref6=10&
RecordType6=TXT&
Address6=".urlencode($dk)."&";
        }
        else
        {
            $fields .= "HostName6=&
MXPref6=&
RecordType6=&
Address6=&";
        }
        $fields .= "
HostName7=&
MXPref7=&
RecordType7= &
Address7=&
HostName8=&
MXPref8=&
RecordType8= &
Address8=&
HostName9=&
MXPref9=&
RecordType9= &
Address9=&
HostName10=&
MXPref10=&
RecordType10= &
Address10=&
x=39&
y=14
";

        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://registerfly.com/scripts/control.php");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,    $header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_REFERER, "https://registerfly.com/scripts/control.php?id=".$matches[2]."&domain=".$matches[1]."&maxnumber=10");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, str_replace("\n", '', $fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR,     $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE,    $cookie_file);

        $buffer = curl_exec($ch);
        curl_close($ch);

        //print $buffer;
        //print str_replace("\n", '', $fields);
    }
}
?>