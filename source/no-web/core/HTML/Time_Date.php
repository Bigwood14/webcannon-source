<?php
class HTML_Time_Date
{
    /**
    * @return string The built select menu.
    * @param string $name The name to give the select menu.
    * @param string $selected The name of the element pre selected
    * @desc Builds a select menu for the days of the month.
    */
    function buildDaysSelect($name = "HTML_Days",$selected = null)
    {
        for($i =1;$i < 32;$i++)
        {
            $num = $i;
            
            if($i < 10)
            {
                $num = "0".$i;
            }
            
            if($i == $selected)
            {
                $options .= '  <option value="'.$i.'" selected>'.$num.'</option>'."\r\n";   
            }
            else 
            {
                $options .= '  <option value="'.$i.'">'.$num.'</option>'."\r\n"; 
            }   
        }
        
        return "<select name=\"$name\">\r\n".$options."\r\n</select>";
    }
    /**
    * @return string The built select menu.
    * @param  string $name     The name to give the select menu.
    * @param  string $selected The name of the element pre selected.
    * @param  int    $display  The id of the display you want to use.
    * @desc Builds a select menu for the months of the year.
    */
    function buildMonthSelect($name = "HTML_Month",$selected = null,$display = 1)
    {
        $months = HTML_Time_Date::getMonths();
        
        for($i =1;$i < 13;$i++)
        {
            $title = $months[$i - 1];
            
            if($i == $selected)
            {
                $options .= '  <option value="'.$i.'" selected>'.$title.'</option>'."\r\n";   
            }
            else 
            {
                $options .= '  <option value="'.$i.'">'.$title.'</option>'."\r\n"; 
            }   
        }
        
        return "<select name=\"$name\">\r\n".$options."\r\n</select>";
    }
    /**
    * @return array  An array of the month names
    * @desc Build an array of the months and spits back.
    */
    function getMonths()
    {
        $months = array('January','Febuary','March','April','May','June','July','August','September','October','November','December');
        return $months;
    }
    /**
    * @return string The built select menu.
    * @param  string $name       The name to give the select menu.
    * @param  string $selected   The name of the element pre selected.
    * @param  int    $r_l        Number of years to start at minus'ed from now.
    * @param  int    $r_r        Number of years to end at plus'ed from now.
    * @param  int    $year_start The year to start at default is current year.
    * @desc Builds a select menu for the years.
    */
    function buildYearSelect($name = "HTML_Year",$selected = null,$r_l = 2,$r_r = 2,$year_start = '')
    {
        if($year_start == '')
        {
            $year_now   = date("Y");
        }
        
        $year_start = $year_now - ($r_l);
        $year_end   = $year_now + ($r_r);
        
        $j = (($year_end - $year_start));
        
        for($i = 1;$i <= $j;$i++)
        {
            $title = $year_start + $i;
            
            if($title == $selected)
            {
                $options .= '  <option value="'.$title.'" selected>'.$title.'</option>'."\r\n";   
            }
            else 
            {
                $options .= '  <option value="'.$title.'">'.$title.'</option>'."\r\n"; 
            }   
        }
        
        return "<select name=\"$name\">\r\n".$options."\r\n</select>";
    }
}
?>