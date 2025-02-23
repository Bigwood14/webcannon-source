<?php
class HTML_Layout
{
    /**
    * @return string The next bg colour string.
    * @param array $colors An arry of colours to alternate between.
    * @desc Alternates through the supplied colors proving as background.
    */
    function alternateBgColor($colors)
    {
        static $i = 0;
        
        $total = count($colors) - 1;
        
        if($i == $total)
        {
            $i = 0;
        }
        else 
        {
            $i ++;
        }
        
        return $colors[$i];
    }
}
?>