<?php
define("E_NORM",1);

class Error
{
    var $type;
    var $msg;
    
    function Error($type = E_NORM,$msg = '')
    {
        $this->type = $type;
        $this->msg  = $msg;
    }
}
?>