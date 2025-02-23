<?php
/**
* Form Handling Class
*
* @author Tom Westcott <tom@integraclick.com>
* @version $Revision: 1.1.1.1 $
* @since 1.0
* @package TomMX
* @access public
* @copyright Tom Westcott
*/
class Form
{
    /**
    * Inputs array
    */
    var $inputs = array();
    /**
    * Format of the mark
    */
    var $mark = '<font color = "red">*</font>';
    /**
    * Marks array
    */
    var $marks = array();
    /**
    * Message
    */
    var $msg;
    /**
    * Color to highlight fields?
    */
    var $fieldHighlight = 'red';

    /**
    * @return void
    * @desc Constructor
    */
    function Form()
    {
    }

    /**
    * @return void
    * @desc Set the mark format
    */
    function setMark($format)
    {
        $this->mark = $format;
    }
    /**
    * @return void
    * @desc Echo a mark
    */
    function getMark($name)
    {
        echo $this->marks[$name];
    }
    /**
    * @return void
    * @desc Echo a message
    */
    function getMsg($name,$all = false)
    {
        if($all === true AND is_array($this->msg))
        {
            return $msg = implode('',$this->msg);
        }
        else
        {
            return $this->msg[$name];
        }
    }

    function addMsg($name,$msg)
    {
        $this->msg[$name] .= $msg;
        $this->marks[$name] = $this->mark;
    }

    /**
    * @return void
    * @param array $fields
    * @desc Add a field input to the array
    */
    function addInput($fields)
    {
        if(!isset($fields['id']))
        {
            $fields['id'] = $fields['name'];
            $this->inputs[$fields['id']] = $fields;
        }
        else
        {
            $this->inputs[$fields['id']] = $fields;
        }
    }

    /**
    * @return void
    * @param string $name
    * @desc Print the selected field
    */
    function printInput($name)
    {
        $values = $this->inputs[$name];

        if(isset($this->marks[$name]) AND isset($this->fieldHighlight))
        {
            $values['bg'] = '  style="background-color:'.$this->fieldHighlight.'"';
        }
        else
        {
            $values['bg'] = "";
        }

        if($values['type'] == "select")
        {
            $this->printSelect($values);
        }
        elseif($values['type'] == "textarea")
        {
            $this->printTextarea($values);
        }
        elseif($values['type'] == "checkbox")
        {
            $this->printCheckBox($values);
        }
        else
        {
            if(!$values['size'])
            {
                $values['size'] = 20;
            }

            if(isset($values['id'])) $id = " id=\"".$values['id']."\""; else $id = "";

            echo "<input name=\"".$values['name']."\"".$id." type=\"".$values['type']."\" size=\"".$values['size']."\" value=\"".$values['value']."\" ".$values['extra'].$values['bg']." />";
        }
    }

    function printCheckBox($values)
    {
        if(isset($values['id'])) $id = " id=\"".$values['id']."\""; else $id = "";
        if(isset($values['value']))
        {
            $attrib = ' checked';
        }
        echo "<input name=\"".$values['name']."\"".$id." type=\"".$values['type']."\" value=\"".$values['sValue']."\" ".$values['extra'].$values['bg'].$attrib." />";
    }

    function printSelect($info)
    {
        echo '<select name="'.$info['name'].'" size="'.$info['size'].'" id="'.$info['id'].'" '.$info['multiple'].' '.$info['extra'].''.$info['bg'].'>';
        foreach($info['options'] as $k => $v)
        {
            if(is_array($info['value']))
            {
                if(in_array($k,$info['value']))
                {
                    $s = ' selected';
                }
                else
                {
                    $s = null;
                }
            }
            else
            {
                if($k == $info['value']) $s = " selected"; else $s = "";
            }
            echo '<option value="'.$k.'"'.$s.'>'.$v.'</option>';
        }
        echo '</select>';
    }

    function printTextarea($info)
    {
        echo '<textarea name="'.$info['name'].'" cols="'.$info['cols'].'" rows="'.$info['rows'].'" id="'.$info['id'].'"'.$info['bg'].'>';
        echo stripslashes($info['value']);
        echo '</textarea>';
    }

    function arrayArray($one , $two)
    {
        //print '1:';var_dump($one);print'2';print_r($two);
        //print 'we went in here bitch';
        if(!is_array($one))
        {
            return false;
        }
        if(!is_array($two))
        {
            return false;
        }
        foreach($one AS $ar)
        {
            if(array_key_exists($ar, $two))
            {
                return true;
            }
        }

        return false;
    }

    /**
    * @return boolean
    * @param array $form
    * @desc Validate the form
    */
    function validate($form)
    {
        $values = $this->inputs;
        foreach ($values AS $value)
        {
            if($value['ignore'] != 'yes')
            {

                $data = $form[$value['id']];
                if(!is_array($data))
                {
                    $data = stripslashes($data);
                }
                // As set
                if((isset($value['validateAsSet'])) && ($data == "") && ($value['type'] != 'select') && ($value['type'] != 'file'))
                {
                    $this->msg[$value['id']] .= $value['validateAsSet']."<br />";
                    $this->marks[$value['id']] = $this->mark;
                }

                if(isset($value['validateAsSet']) && $value['type'] == 'file' && !$_FILES[$value['id']])
                {
                    $this->msg[$value['id']] .= $value['validateAsSet']."<br />";
                    $this->marks[$value['id']] = $this->mark;
                }

                if(isset($value['validateAsSet']) && $value['type'] == 'select' && $value['multiple'] != 'multiple' && !@array_key_exists($data,$value['options']))
                {
                    $this->msg[$value['id']] .= $value['validateAsSet']."<br />";
                    $this->marks[$value['id']] = $this->mark;
                }
                if(isset($value['validateAsSet']) && $value['type'] == 'select' && $value['multiple'] == 'multiple' && !$this->arrayArray($data,$value['options']))
                {
                    $this->msg[$value['id']] .= $value['validateAsSet']."<br />";
                    $this->marks[$value['id']] = $this->mark;
                }
                // Equal To
                if((isset($value['equalTo'])) AND ($data != $form[$value['equalTo']]))
                {
                    $this->msg[$value['id']] .= $value['equalToMsg']."<br />";
                    $this->marks[$value['id']] = $this->mark;
                    $this->marks[$value['equalTo']] = $this->mark;
                }

                if(isset($value['minChar']) AND $value['minChar'] > (strlen($data)))
                {
                    $this->msg[$value['id']] .= $value['validateMsg']."<br />";
                    $this->marks[$value['id']] = $this->mark;
                }

                if(isset($value['validateAsEmail']) AND (!eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,3}$", $data)))
                {
                    $this->msg[$value['id']] .= $value['validateAsEmail']."<br />";
                    $this->marks[$value['id']] = $this->mark;
                }

                if(isset($value['validateAsURL']) AND (!$this->validateURL($data)))
                {
                    $this->msg[$value['id']] .= $value['validateAsURL']."<br />";
                    $this->marks[$value['id']] = $this->mark;
                }

                if(isset($value['maxChar']) AND $value['maxChar'] < (strlen($data)))
                {
                    $this->msg[$value['id']] .= $value['validateMsg']."<br />";
                    $this->marks[$value['id']] = $this->mark;
                }

                if(isset($value['validateAsDate']) AND !$this->validateDate(($form[$value['name']])))
                {
                    $this->msg[$value['name']] .= $value['validateAsDate']."<br />";
                    $this->marks[$value['name']] = $this->mark;
                }

                if(isset($value['ereg']) AND ereg($value['ereg'],$data))
                {
                    $this->msg[$value['id']] .= $value['validateMsg']."<br />";
                    $this->marks[$value['id']] = $this->mark;
                }
            }
            if(isset($value['call_it']))
            {
                $this->inputs[$value['id']]['value'] = $form[$value['call_it']][$value['with_arr_index']];
            }
            else
            {
                $this->inputs[$value['id']]['value'] = $form[$value['id']];
            }
        }

        if($this->msg == "")
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function validateDate($date)
    {
        $parts = explode('-',$date);
        if(checkdate($parts[0],$parts[1],$parts[2]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function validateURL( $url )
    {

        if ( !( $parts = @parse_url( $url ) ) )
        {
            return false;
        }
        else {
            if ( $parts[scheme] != "http" && $parts[scheme] != "https" && $parts[scheme] != "ftp" && $parts[scheme] != "gopher" )
            return false;
            else if ( !eregi( "^[0-9a-z]([-.]?[0-9a-z])*\.[a-z]{2,4}$", $parts[host], $regs ) )
            return false;
            else if ( !eregi( "^([0-9a-z-]|[\_])*$", $parts[user], $regs ) )
            return false;
            else if ( !eregi( "^([0-9a-z-]|[\_])*$", $parts[pass], $regs ) )
            return false;
            else if ( !eregi( "^[0-9a-z/_\.@~\-]*$", $parts[path], $regs ))
            return false;
            else if ( !eregi( "^[0-9a-z?&=#\,]*$", $parts[query], $regs ) )
            return false;
        }
        return true;
    }

    function reformatDate($date)
    {
        $parts = explode('-',$date);
        return $parts[2].'-'.$parts[0].'-'.$parts[1];
    }
}
?>