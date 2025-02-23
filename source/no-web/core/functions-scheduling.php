<?PHP
function dbFieldMerge($content,$fields_used = array())
{
    global $db2;

    $sql = "SHOW COLUMNS FROM email";

    $rs = $db2->Execute($sql);
    $fields = array();
    while($rw = $rs->FetchRow())
    {
        /*if(in_array($rw['Field'],$config->values['mm_field_hide']))
        continue;*/
        $fields[] = $rw['Field'];
    }

    preg_match_all("/{(.*?)}/i",$content,$r);

    $i = count($fields_used);

    if($i < 1)
    {
        $i = 3;
    }
    else
    {
        $i = $i + 3;
        //$i ++;
    }

    foreach($r[1] AS $field)
    {
        if(!in_array($field,$fields))
        {
            continue;
        }

        if(in_array($field,$fields_used))
        {
            continue;
        }

        if($field == "address")
        {
            $fields_used[0] = $field;
        }
        else
        {
            $fields_used[$i] = $field;
        }

        $i ++;
    }

    foreach($fields_used AS $k=>$v)
    {
        $content = str_replace('{'.$v.'}',"##$k",$content);
    }

    $r['fields'] = $fields_used;
    $r['content'] = $content;

    return $r;
}

/**
 * Enter description here...
 *
 * @param unknown_type $body
 * @param unknown_type $html_body
 * @param unknown_type $msg_id
 * @param unknown_type $dummy
 * @param unknown_type $domain
 * @return unknown
 */
function linkTracker($body, $html_body, $aol_body, $msg_id, $dummy = '0', $domain = '{{dn}}')
{
    global $db;

    // Lets do the html parts!
    $a_links    = array();
    $links      = array();
    $html_parts = array(
    array('body' => $html_body , 'type' => 1),
    array('body' => $aol_body ,  'type' => 2),
    );
    // Loop HTML Parts
    foreach($html_parts AS $part)
    {
        // Grab everything in a tag <*>
        preg_match_all("/<([^>]*)>/i", $part['body'], $matches);
        // Great Now let see which are real links
        foreach($matches[1] AS $match)
        {
            $match = $match;
            // A href
            if(eregi("href[\n\r ]*=", $match))
            {
                // Get the link out
                $exp = "/(http(s)?:\/\/([^\\n\\r\"'\s>]*))[\"'\s>]?/i";
                $link = preg_match($exp, $match, $matches2);
                if(!in_array($matches2[1], $a_links))
                {
                    $a_links[] = $matches2[1];
                }
            }
            // Image
            elseif(eregi("src[\n\r ]*=", $match) || eregi("img", $match))
            {
                // Get the link out
                $exp = "/(http(s)?:\/\/([^\\n\\r\"'\s>]*))[\"'\s>]?/i";
                $link = preg_match($exp, $match, $matches2);
                if(!in_array($matches2[1], $links))
                {
                    $links[] = $matches2[1];
                }
            }
        }
        // Now we got out the a href links extract and replace any other urls and mark as img if type = 1
        $matches = array();
        preg_match_all("/(http(s)?:\/\/([^<\\n\\r\"'\s>]*))[\"'\s>]?/i", $part['body'], $matches);

        // Loop them
        foreach($matches[1] AS $match)
        {
            // HTML image so mark with image linkage
            if($part['type'] == 1)
            {
                if(!in_array($match, $a_links) && !in_array($match, $links))
                {
                    $links[] = $match;
                }
            }
            // AOL draft so prob not an image
            else
            {
                if(!in_array($match, $a_links))
                {
                    $a_links[] = $match;
                }
            }
        }
    }

    // Text part
    $matches = array();
    preg_match_all("/(http(s)?:\/\/([^\\n\\r\"'\s>]*))[\"'\s>]?/i", $body, $matches);
    // Loop them
    foreach($matches[1] AS $match)
    {
        $match = str_replace("</a", "", $match);
        if(!in_array($match, $a_links))
        {
            $a_links[] = $match;
        }
    }

    // Sort them
    $urls2 = array();
    foreach($a_links AS $k => $v)
    {
        $len        = strlen($v);
        $urls2[$len][] = $v;
    }

    krsort($urls2);
    $a_links = $urls2;

    $urls2 = array();
    foreach($links AS $k => $v)
    {
        $len        = strlen($v);
        $urls2[$len][] = $v;
    }

    krsort($urls2);
    $links = $urls2;

    //print "These are a links:\n";
    //print_r($a_links);
    //print "These are other links:\n";
    //print_r($links);

    $i = 1;
    // A links
    foreach($a_links AS $link1)
    {
        foreach($link1 AS $link)
        {
        	if($link == 'http://') continue;
            $sql = "INSERT links (msg_id,URL,count,dummy,img,`date`) VALUES ('".$msg_id."','".$link."','0','$dummy','0',NOW());";
            //print "\n".$sql."\n";
            if ($db->Execute($sql) === false)
            {
                print 'error inserting: '.$db->ErrorMsg().'<BR>'.$sql;
            }

            $link_id = $db->Insert_ID();

            $html_body  = str_replace($link, "http://{{dn}}/t/c/$link_id/{{03}}/{{02}}.html", $html_body);
            $body       = str_replace($link, "http://{{dn}}/t/c/$link_id/{{03}}/{{02}}.html", $body);
            $aol_body   = str_replace($link, "http://{{dn}}/t/c/$link_id/{{03}}/{{02}}.html", $aol_body);
            $i++;
        }
    }
    // Normal Links
    foreach($links AS $link1)
    {
        foreach($link1 AS $link)
        {
        	if($link == 'http://') continue;
            $sql = "INSERT links (msg_id,URL,count,dummy,img,`date`) VALUES ('".$msg_id."','".$link."','0','$dummy','1',NOW());";

            if ($db->Execute($sql) === false)
            {
                print 'error inserting: '.$db->ErrorMsg().'<BR>'.$sql;
            }

            $link_id = $db->Insert_ID();

            $html_body  = str_replace($link, "http://{{dn}}/t/c/$link_id/i", $html_body);
            $body       = str_replace($link, "http://{{dn}}/t/c/$link_id/i", $body);
            $aol_body   = str_replace($link, "http://{{dn}}/t/c/$link_id/i", $aol_body);
            $i++;
        }
    }

    //print "Here is the text body:\n";
    //print $body;
    //print "Here is the HTML body:\n";
    //print $html_body;

    return array('html_body'=>$html_body, 'body'=>$body, 'aol_body' => $aol_body);
}

/**
* @return array
* @param array  $msg       Information from the db about the message
* @param string $list_name The list being mailed from.
* @param array  $settings
*
* Make the full email message from draft. Headers + footers
* and all that she bang.
*/
function makeDraft($draft_id)
{
    srand(time());

    $msg    = getDraft($_GET['draft_id']);
    // Get vars outta config that we need
    $config_2  = getDBConfig('', 1);
    $tag       = $config_2['DOMAIN_TAG'];
    //$open_html = "<img src=\"http://{{dn}}/t/o.php?l={{03}}&id={{02}}&mid=".$msg['id']."\">";
    $open_html = "<img src=\"http://{{dn}}/t/o/{{03}}/{{02}}/".$msg['id'].".gif\">";
    // Link Tracking is on
    if($msg['link_tracking'] == 1)
    {
        $ret = linkTracker($msg['body'], $msg['html_body'], $msg['id'], 1);

        $msg['body']        = $config_2['TEXT_HEADER'] . $ret['body'] . "\n" . $config_2['TEXT_FOOTER'];
        $msg['html_body']   = $config_2['HTML_HEADER'] . $ret['html_body'] . $open_html . $config_2['HTML_FOOTER'];
    }
    else
    {
        $msg['body']        = $config_2['TEXT_HEADER'] . $msg['body'] . "\n" . $config_2['TEXT_FOOTER'];
        $msg['html_body']   = $config_2['HTML_HEADER'] . $msg['html_body'] . $open_html . $config_2['HTML_FOOTER'];
    }

    $mi         = substr(md5(rand() % 1000000), 0, 20) . date("YmdHis");
    $date_now   = date("r");
    // Do the email headers
    $body .= $config_2['HEADERS'];

    $body  = str_replace('{{header_mi}}', $mi, $body);
    $body  = str_replace('{{header_date}}', $date_now, $body);
    $body  = rtrim($body, '\n');
    $body .= "\n";

    // The Bodys
    if ($msg['content'] == 0)
    {
        //Plain text only
        // The text specific headers
        $body .= "MIME-Version: 1.0\n";
        $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
        $body .= "\n";

        $body .= $msg['body'];

        $body .= "\n";

        //AOL body just the same
        $aol_body = $body;

    }
    elseif ($msg['content'] == 1)
    {
        //HTML Only
        $body .="MIME-Version: 1.0\n";
        $body .="Content-type: text/html; charset=\"iso-8859-1\"\n\n";

        $body .= $msg['html_body'];

        $body .= "\n";

        //want to send HTML to aol?  okay...
        //should probably strip tags
        $aol_body = strip_tags($body);

    }
    elseif ($msg['content'] == 2)
    {
        //HTML and plain text alternative
        $boundary=("mg_boundary-".(rand() % 1000000));
        $boundary.="-";
        $boundary.=("".rand() % 1000000);

        $body.="MIME-Version: 1.0\n";
        $body.="Content-Type: multipart/alternative; boundary=\"$boundary\"\n\n";

        //Plain Text Alternative
        $body.="--$boundary\n";
        $body.="Content-Type: text/plain; charset=\"iso-8859-1\"\n\n";

        $body .= $msg['body'];

        $body.="\n";

        //HTML Content
        $body.="--$boundary\n";
        $body.="Content-Type: text/html; charset=\"iso-8859-1\"\n\n";

        $body .= $msg['html_body'];

        $body.="\n";

        $body.="--$boundary--\n";
    }

    return array('body'=>$body,'aol_body'=>$aol_body);
}

?>