<?php
/**
 * Main form class
 *
 */
class form
{
	public  $elements 	= array();
	public	$messages;
	public	$inject_attributes;
	public	$groups;
	public 	$counts 	= array();
	private $rules 		= array();
	
	public function add_element ($element)
	{
		$args 		 			= func_get_args();
		$element_obj 			= $this->load_element($element, $args);
		$id 					= $element_obj->get_id();
		$this->elements[$id] 	= $element_obj;
		
		if($id != $args[1])
		{
			$this->groups[$args[1]][] = $id;
		}
	}
	
	public function add_rule ($element, $message, $type)
	{
		$args = func_get_args();
		
		if (empty($args[3]))
			$args[3] = array();
		
		$this->rules[$element][] = array(
		'type'    	=> $type,
		'message' 	=> $message,
		'args'		=> @$args[3]
		);
	}
	
	public function inject_attributes ($attributes, $on_error = true, $specific = false)
	{
		if($on_error == true)
		{
			$inject = array($attributes, $specific);
		}
		$this->inject_attributes[] = $inject;
	}
	
	public function print_element ($name)
	{
		return $this->elements[$name]->print_element();
	}
	
	public function print_label ($name, $attributes = '')
	{
		$for 	= $this->elements[$name]->id;
		$label 	= $this->elements[$name]->label;
		
		$required = '';
		if (isset($this->rules[$name]))
		{
			foreach ($this->rules[$name] as $rule)
			{
				if ($rule['type'] == 'required' || $rule['type'] == 'email')
				{
					$required = ' <span class="required">*</span>';
					break;
				}
			}
		}	

		$html = '<label for="'.$for.'" '.$attributes.'>'.$label.' '.$required.'</label>';
		return $html;
	}
	
	public function set_default ($name, $default, $refill = true)
	{
		if(empty($this->elements[$name]))
		{
			if(!empty($this->groups[$name]))
			{
				foreach($this->groups[$name] As $id)
				{
					$this->elements[$id]->set_default($default, $refill);
				}
			}
		}
		else 
		{
			$this->elements[$name]->set_default($default, $refill);
		}
	}
	
	public function validate ($values)
	{
		foreach($this->elements AS $element)
		{
			if(!empty($values[$element->get_name()]))
				$value = $values[$element->get_name()];
			else 
				$value = false;
			
			$id = $element->get_id();
			
			if ($element->refill !== false)
			{
				if (strstr($element->name, '[]') !== false && @$element->setup == 2)
					$er;
				elseif (preg_match('/(.*)\[([0-9]+)\]/U', $element->name, $matches))
				{
					if (!empty($_POST[$matches[1]]))
						$value = @$_POST[$matches[1]][$matches[2]];
	
					$element->value = $value;
				}
				else
					$element->value = $value;
			}

			if(empty($this->rules[$id]))
				continue;
			
			if(is_array($this->rules[$id]))
			{
				foreach($this->rules[$id] AS $rule)
				{
					if (in_array('not_empty', $rule['args']))
					{
						if (empty($value))
							continue;
					}

					$obj_rule = $this->get_rule_instance($rule['type']);
					if(!$obj_rule->validate($value, $element, $values, $rule['args'], $this->elements))
					{
						if(is_array($this->inject_attributes))
						{
							foreach($this->inject_attributes AS $inject)
							{
								if(is_array($inject[1]))
								{
									if(in_array($element->get_name(), $inject[1]))
									{
										$new = array_merge($element->attributes, $inject[0]);
									}
								}
								else 
								{
									$new = array_merge($element->attributes, $inject[0]);
								}
								$element->attributes = $new;
							}
						}
						$this->messages[] = $rule['message'];
					}
				}
			}
		}
		
		if(is_array($this->messages))
		{
			return false;
		}
		else 
		{
			return true;
		}
	}
	
	private function get_rule_instance($rule)
	{
		static $instances;
		
		$class_name = 'form_rule_'.$rule;
		
		if(empty($instances[$class_name]))
		{
			$instances[$class_name] = new $class_name;
		}
		
		return $instances[$class_name];
	}
	
	private function load_element ($element, $args)
	{
		$class_name  = 'form_element_'.$element;
		$element_obj = new $class_name($args);
		return $element_obj;
	}
}

/**
 * Element class template
 *
 */
abstract class form_element
{ 
	public $attributes;
	public $name;
	public $id;
	public $label;
	public $value;
	public $refill = true;
	
	abstract public function print_element ();
	
	public function get_id ()
	{
		return $this->name;
	}
	
	public function get_name ()
	{
		return $this->name;
	}
	
	public function set_default ($default, $refill = true)
	{
		$this->value = $default;

		if ($refill === false)
			$this->refill = false;
	}
	
	public function find_id ()
	{
		if (is_array($this->attributes))
		{
			foreach ($this->attributes as $attribute => $value)
			{
				if($attribute == 'id')
					$this->id = $value;
			}
		}
		
		if($this->id == '')
		{
			$this->id = 'form-'.$this->name;
		}
	}
}
/**
 * Text element
 *
 */
class form_element_text extends form_element
{
	public function __construct ($args)
	{
		$this->name		  	= $args[1];
		$this->label		= $args[2];
		if(!empty($args[3]))
		{
			$this->attributes 	= $args[3];
		}
		$this->find_id();
	}
	
	public function print_element ()
	{
		$html = '<input type="text" name="'.$this->name.'" id="'.$this->id.'"';
		if($this->value !== false)
		{
			$html .= ' value="'.$this->value.'"';
		}
		
		if(is_array($this->attributes))
		{
			foreach($this->attributes AS $attribute => $value)
			{
				$html .= " $attribute=\"$value\"";
			}
		}
		$html .= " />";
		return $html;
	}
}
/**
 * Date element
 *
 */
class form_element_date extends form_element
{
	public function __construct ($args)
	{
		$this->name		  	= $args[1];
		$this->label		= $args[2];
		if(!empty($args[3]))
		{
			$this->attributes 	= $args[3];
		}
		if (!empty($args[4]))
		{
			$this->extra_args = $args[4];
		}

		if (!empty($args[4]['format']))
			$this->format = $args[4]['format'];
		else
			$this->format = '%d/%m/%Y';

		$this->find_id();
	}
	
	public function print_element ()
	{
		$html = '<input type="text" name="'.$this->name.'" id="'.$this->id.'"';
		if($this->value != '')
		{
			$html .= ' value="'.$this->value.'"';
		}
		
		if(is_array($this->attributes))
		{
			foreach($this->attributes AS $attribute => $value)
			{
				$html .= " $attribute=\"$value\"";
			}
		}
		$html .= ' />';
		$html .= '<img src="/images/icons/calendar.png" onclick="return showCalendar(\''.$this->id.'\', \''.$this->format.'\');">'; 
		return $html;
	}
}
/**
 * Textarea element
 *
 */
class form_element_textarea extends form_element
{
	public function __construct ($args)
	{
		$this->name		  	= $args[1];
		$this->label		= $args[2];
		if(!empty($args[3]))
		{
			$this->attributes 	= $args[3];
		}
		$this->find_id();
	}
	
	public function print_element ()
	{
		$html = '<textarea name="'.$this->name.'" id="'.$this->id.'"';
		
		if(is_array($this->attributes))
		{
			foreach($this->attributes AS $attribute => $value)
			{
				$html .= " $attribute=\"$value\"";
			}
		}
		$html .= '>';
		
		if($this->value != '')
		{
			$html .= $this->value;
		}
		
		$html .= "</textarea>";
		return $html;
	}
}
/**
 * Password element
 *
 */
class form_element_password extends form_element
{
	public function __construct ($args)
	{
		$this->name		  	= $args[1];
		$this->label		= $args[2];
		$this->attributes 	= @$args[3];
		$this->find_id();
	}
	
	public function print_element ()
	{
		$html = '<input type="password" name="'.$this->name.'" id="'.$this->id.'"';
		if($this->value != '')
		{
			$html .= ' value="'.$this->value.'"';
		}
		
		if(is_array($this->attributes))
		{
			foreach($this->attributes AS $attribute => $value)
			{
				$html .= " $attribute=\"$value\"";
			}
		}
		$html .= " />";
		return $html;
	}
}
/**
 * hidden element
 *
 */
class form_element_hidden extends form_element
{
	public function __construct ($args)
	{
		$this->name		  	= $args[1];
		$this->value		= $args[2];
		$this->attributes 	= @$args[3];
		$this->find_id();
	}
	
	public function print_element ()
	{
		$html = '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'"';
		if($this->value != '')
		{
			$html .= ' value="'.$this->value.'"';
		}
		
		if(is_array($this->attributes))
		{
			foreach($this->attributes AS $attribute => $value)
			{
				$html .= " $attribute=\"$value\"";
			}
		}
		$html .= " />";
		return $html;
	}
}
/**
 * Radio element
 *
 */
class form_element_radio extends form_element
{
	public $radio_value;
	public $check_me;
	
	public function __construct ($args)
	{
		$this->name		  	= $args[1];
		$this->id		  	= $args[2];
		$this->radio_value	= $args[4];
		$this->label		= $args[3];
		$this->attributes 	= $args[5];
	}
	
	public function set_default ($default)
	{
		if($default == $this->radio_value)
		{
			$this->check_me = 1;
		}
	}
	
	public function print_element ()
	{
		$checked = '';
		if($this->check_me == 1 && !$this->value)
		{
			$checked = ' checked';
		}
		if($this->value == $this->radio_value)
		{
			$checked = ' checked';
		}
		$html = '<input type="radio" name="'.$this->name.'" id="'.$this->id.'" value="'.$this->radio_value.'"'.$checked;
		
		if(is_array($this->attributes))
		{
			foreach($this->attributes AS $attribute => $value)
			{
				$html .= " $attribute=\"$value\"";
			}
		}
		
		$html .= ' />';
		return $html;
	}
	
	public function get_id ()
	{
		return $this->id;
	}
}
/**
 * Checkbox element
 *
 */
class form_element_checkbox extends form_element
{
	public $val;

	public function __construct ($args)
	{
		$this->name		  	= $args[1];
		$this->label		= $args[2];
		$this->val			= $args[3];
		if(!empty($args[4]))
		{
			$this->attributes 	= $args[4];
		}
		$this->find_id();
	}
	
	public function print_element ()
	{
		$html = '<input type="checkbox" name="'.$this->name.'" id="'.$this->id.'" value="'.$this->val.'"';
		if($this->value == $this->val)
		{
			$html .= ' checked="checked"';
		}
		
		if(is_array($this->attributes))
		{
			foreach($this->attributes AS $attribute => $value)
			{
				$html .= " $attribute=\"$value\"";
			}
		}
		$html .= " />";
		return $html;
	}
}
/**
 * Select element
 *
 */
class form_element_select extends form_element
{
	public $options = array();
	public $setup 	= 1;
	
	public function __construct ($args)
	{
		$this->name			= $args[1];
		$this->options		= $args[3];
		$this->setup		= $args[4];
		$this->label		= $args[2];
		if(!empty($args[5]))
		{
			$this->attributes	= $args[5];
		}
		$this->find_id();
	}
	
	public function print_element ()
	{
		$newline 	= "\n";
		$mul 		= '';
		$name 		= $this->name;

		if($this->setup == 4 || $this->setup == 3)
		{
			$mul  = ' multiple="multiple"';
			$name = $this->name . "[]";
		}
		
		$html  = '<select name="'.$name.'" id="'.$this->id.'"'.$mul;
		
		if(is_array($this->attributes))
		{
			foreach($this->attributes AS $attribute => $value)
			{
				$html .= " $attribute=\"$value\"";
			}
		}
		$html .= ">" . $newline;
		foreach($this->options AS $k => $v)
		{
			$selected = '';
			
			// Find pre selected
			switch ($this->setup)
			{
				case 1:
				if($this->value == $v) $selected = ' selected="selected"';
				break;
				case 2:
				if($this->value == $k) $selected = ' selected="selected"';
				break;
				case 3:
				if(is_array($this->value))
				{
					if(in_array($v, $this->value))
					{
						$selected = ' selected="selected"';
					}
				}
				break;
				case 4:
				if(is_array($this->value))
				{
					if(in_array($k, $this->value))
					{
						$selected = ' selected="selected"';
					}
				}
				break;
			}
			
			if($this->setup == 2 || $this->setup == 4)
			{
				$html .= ' <option value="'.$k.'"'.$selected.'>';
			}
			else 
			{
				
				$html .= '<option value="'.$v.'"'.$selected.'>';
			}
			
			$html .= $v.'</option>' . $newline;
		}
		$html .= '</select>';
		
		return $html;
	}
	
	public function get_id ()
	{
		return $this->id;
	}
}

/**
 * Rules class template
 *
 */
abstract class form_rule
{
}
/**
 * Required rule
 *
 */
class form_rule_required extends form_rule 
{
	public function validate ($value)
	{
		if($value == false || empty($value))
		{
			return false;
		}
		
		return true;
	}
}
/**
 * Alpha Numeric rule
 *
 */
class form_rule_alpha_numeric extends form_rule 
{
	public function validate ($value)
	{
		if(!eregi("^[a-zA-Z0-9]+$", $value))
		{
			return false;
		}
		
		return true;
	}
}
/**
 * Currency rule
 *
 */
class form_rule_currency extends form_rule 
{
	public function validate ($value)
	{
		if(!eregi("^[0-9]+\.?[0-9]?[0-9]?$", $value))
		{
			return false;
		}
		
		return true;
	}
}
/**
 * Numeric rule
 *
 */
class form_rule_numeric extends form_rule 
{
	public function validate ($value)
	{
		return ctype_digit($value);
	}
}
/**
 * Match rule
 *
 */
class form_rule_match extends form_rule 
{
	public function validate ($value, $element, $values, $args)
	{
		if(!empty($values[$args[0]]))
		{
			if($values[$args[0]] != $value)
			{
				return false;
			}
		}
		elseif(empty($values[$args[0]]) && !empty($value))
		{
			return false;
		}
		
		return true;
	}
}
/**
 * Valid Select rule
 *
 */
class form_rule_valid_select extends form_rule 
{
	public function validate ($value, form_element_select $element)
	{
		if($element->setup == 1)
		{
			if(!in_array($value, $element->options))
			{
				return false;
			}
		}
		else 
		{
			if($value === false)
			{
				return false;
			}
			if(!array_key_exists($value, $element->options))
			{
				return false;
			}
		}
		
		return true;
	}
}
/**
 * Range rule
 *
 */
class form_rule_range extends form_rule 
{
	public function validate ($value, $element, $values, $args)
	{
		foreach($args[0] AS $type => $mod)
		{
			switch($type)
			{
				case 'minlength':
				if(strlen($value) < $mod) return false;
				break;
				case 'maxlength':
				if(strlen($value) > $mod) return false;
				break;
			}
		}
		return true;
	}
}
/**
 * Email rule
 *
 */
class form_rule_email extends form_rule 
{
	var $regex = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';
	
	public function validate ($email)
	{
		if(!preg_match($this->regex, $email))
		{
			return false;
		}
		return true;
	}
}
/**
 * URL rule
 *
 */
class form_rule_url extends form_rule 
{
	var $regex = '/^(http(s?):\\/\\/|ftp:\\/\\/{1})((\w+\.)+)\w{2,}(\/?)$/i';
	
	public function validate ($url)
	{
		if(!preg_match($this->regex, $url))
			return false;

		return true;
	}
}
/**
 * Date rule
 *
 */
class form_rule_date extends form_rule 
{
	public function validate ($date, $element)
	{
		$date 	= explode(' ', $date);
		$parts 	= explode('/', $date[0]);

		if (strstr(@$element->format, '%d/%m/%Y') !== false)
		{
			$day 	= @$parts[0];
			$month 	= @$parts[1];
			$year 	= @$parts[2];
		}
		else
		{
			$day 	= @$parts[1];
			$month 	= @$parts[0];
			$year 	= @$parts[2];
		}

		return checkdate((int)$month, (int)$day, (int)$year);
	}
}

class form_rule_callback extends form_rule
{
	public function validate ($value, $element, $values, $args, $elements)
	{
		if (!empty($args['class']) && !empty($args['method']))
		{
			if (is_object($args['class']))
				return call_user_func(array($args['class'], $args['method']), $value, $element, $values, $args, $elements);
		}
	}
}
?>
