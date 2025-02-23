<?php
require '../../lib/control_panel.php';
require_once('Net/DNS.php');

set_time_limit(0);

$msgs 	= array();
$errors = array();

function dk_gen_keypair() {
    shell_exec('openssl genrsa -out /tmp/celibero.key 768');
    shell_exec('openssl rsa -in /tmp/celibero.key -out /tmp/celibero.pub -pubout');

    $pub = file_get_contents('/tmp/celibero.pub');
    $priv = file_get_contents('/tmp/celibero.key');

    unlink('/tmp/celibero.pub');
    unlink('/tmp/celibero.key');

    query(sprintf("REPLACE INTO config (`KEY`,`value`) VALUES ('dk_pub','%s')",esc($pub)));
    query(sprintf("REPLACE INTO config (`KEY`,`value`) VALUES ('dk_priv','%s')",esc($priv)));
}

function dk_enable() {
    query("REPLACE INTO config (`KEY`,`value`) VALUES ('DK','on')");
}

function dk_disable() {
    query("REPLACE INTO config (`KEY`,`value`) VALUES ('DK','off')");
}

function dk_status() {
    $x = row(query("SELECT value FROM config WHERE `KEY` = 'DK'"));
    if (!$x) { return 'off'; }
    return $x['value'];
}

function dk_public_key() {
    $x = row(query("SELECT value FROM config WHERE `KEY` = 'dk_pub'"));
    if (!$x) { 
        dk_gen_keypair(); 
        $x = row(query("SELECT value FROM config WHERE `KEY` = 'dk_pub'"));
    }
    return $x['value'];
}

if (is_post()) {
    $state = dk_status();
    if ($state == 'on') { dk_disable(); }
    else { dk_enable(); }
}

$tpl->state = dk_status();

if ($tpl->state == 'on') {
    $pem = dk_public_key();
    $pub = str_replace('-----BEGIN PUBLIC KEY-----','',$pem);
    $pub = str_replace('-----END PUBLIC KEY-----','',$pub);
    $pub = str_replace("\n",'',$pub);
    $tpl->pub = $pub;
}

$sql = "SELECT * FROM server_to_ip";
$r = $db->Execute($sql);

while($rw = $r->FetchRow($sql))
{
    $resolver = new Net_DNS_Resolver();
    $resolver->usevc = 1; // Force the use of TCP instead of UDP
    $resolver->nameservers = array(              // Set the IP addresses
    '127.0.0.1',     // of the nameservers
    );
    // Domain Key
    if($tpl->state == 'on')
    {
        $rw['dk_status'] = 'bad';
        $response = $resolver->query('dk._domainkey.'.$rw['domain'], 'TXT');
        if ( $response) {
            foreach ($response->answer as $rr)
            {
                if(strstr($rr->text, $tpl->pub))
                {
                    $rw['dk_status'] = 'good';
                }
                $rw['dk_return'] = $rr->text;
            }
        }
    }
    // A record
    $ip = gethostbyname($rw['domain']);
    $rw['a_status'] = 'bad';
    if($ip == $rw['ip']) $rw['a_status'] = 'good';
    $rw['a_return'] = $ip;

    // MX Record
    $rw['mx_status'] = 'bad';
    $response = $resolver->query($rw['domain'], 'MX');
    if ( $response) {
        foreach ($response->answer as $rr)
        {
            if($rr->exchange == $rw['domain'] || $rr->exchange == 'mx.'.$rw['domain'] || $rr->exchange == 'mail.'.$rw['domain'])
            {
                $rw['mx_status'] = 'good';
            }
            $rw['mx_return'] = $rr->exchange;
        }
    }

    // SPF Record
    $rw['spf_status'] = 'bad';
    $response = $resolver->query($rw['domain'], 'TXT');
    if ( $response) {
        foreach ($response->answer as $rr)
        {
            if(strstr($rr->text, '~all'))
            {
                $rw['spf_status'] = 'good';
            }
            $rw['spf_return'] = $rr->text;
        }
    }

    $domains_check[] = $rw;
}
//print_r($domains_check);
function statusOut($status, $extra = '')
{
    if($status == 'good')
    {
        echo '<img src="/images/misc/check.gif" width="14" height="13" border="0" />';
    }
    else 
    {
        echo '<img src="/images/misc/x.gif" width="14" height="13" border="0" />';
    }
}
$tpl->styles[]	= 'table.css';
$tpl->domains = $domains_check;
show_cp_page('cp/isp-relations/dk.php',$msgs,$errors);
?>
