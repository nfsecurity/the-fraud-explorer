<?php 

/*
 * The Fraud Explorer 
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.67-beta
 *
 * Description: Code for login
 */

class Form
{
	var $values = array();
   	var $errors = array();  
   	var $num_errors;        

   	function Form()
	{
      		if(isset($_SESSION['value_array']) && isset($_SESSION['error_array']))
		{
         		$this->values = $_SESSION['value_array'];
         		$this->errors = $_SESSION['error_array'];
         		$this->num_errors = count($this->errors);

         		unset($_SESSION['value_array']);
         		unset($_SESSION['error_array']);
      		}
      		else
		{
        	 	$this->num_errors = 0;
     	 	}
   	}

   	function setError($error_type, $errmsg)
	{
      		$this->errors[$error_type] = $errmsg;
      		$this->num_errors = count($this->errors);
   	}

   	function value($field)
	{
      		if(array_key_exists($field,$this->values))
		{
         		return htmlspecialchars(stripslashes($this->values[$field]));
      		}
		else
		{
         		return "";
      		}
   	}

   	function error($error_type)
	{
      		if(array_key_exists($error_type,$this->errors))
		{
         		return "<font size=\"2\" color=\"#ff0000\">".$this->errors[$error_type]."</font>";
      		}
		else
		{
         		return "";
      		}
   	}

   	function getErrorArray()
	{
      		return $this->errors;
   	}
};
 
?>

