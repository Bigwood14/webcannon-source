<?php
class Email
{
    function splitEmail($email)
    {
        $parts = explode('@',$email);
        return array('local'=>$parts[0],'domain'=>$parts[1]);
    }
}
?>