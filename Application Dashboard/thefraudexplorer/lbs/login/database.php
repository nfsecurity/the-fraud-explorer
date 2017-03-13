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

include "constants.php";
include "lbs/open-db-connection.php";     
 
class MySQLDB
{
	var $connection;

   	function MySQLDB()
	{
      		$this->connection = mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die(mysql_error());
      		mysql_select_db(DB_NAME, $this->connection) or die(mysql_error());
   	}

   	function confirmIPAddress($value) 
	{
		$q = "SELECT attempts, (CASE when lastlogin is not NULL and DATE_ADD(LastLogin, INTERVAL ".TIME_PERIOD." MINUTE)>NOW() then 1 else 0 end) as Denied FROM ".TBL_ATTEMPTS." WHERE ip = '$value'";
 		$result = mysql_query($q, $this->connection);
   		$data = mysql_fetch_array($result);   
 
   		if (!$data) 
		{
     			return 0;
   		} 

   		if ($data["attempts"] >= ATTEMPTS_NUMBER)
   		{
      			if($data["Denied"] == 1)
      			{
         			return 1;
      			}
     			else
     			{
        			$this->clearLoginAttempts($value);
        			return 0;
     			}
   		}
   		
		return 0;  
  	}
   
   	function addLoginAttempt($value) 
	{
	  	$q = "SELECT * FROM ".TBL_ATTEMPTS." WHERE ip = '$value'"; 
	  	$result = mysql_query($q, $this->connection);
	  	$data = mysql_fetch_array($result);
	  
	  	if($data)
      		{
        		$attempts = $data["attempts"]+1;

       			if($attempts==3) 
			{
		 		$q = "UPDATE ".TBL_ATTEMPTS." SET attempts=".$attempts.", lastlogin=NOW() WHERE ip = '$value'";
		 		$result = mysql_query($q, $this->connection);
			}
        		else 
			{
		 		$q = "UPDATE ".TBL_ATTEMPTS." SET attempts=".$attempts." WHERE ip = '$value'";
		 		$result = mysql_query($q, $this->connection);
			}
       		}
      		else 
		{
	   		$q = "INSERT INTO ".TBL_ATTEMPTS." (attempts,IP,lastlogin) values (1, '$value', NOW())";
	   		$result = mysql_query($q, $this->connection);
	  	}
    	}
   
   	function confirmUserPass($username, $password, $captcha)
	{
      		if(!get_magic_quotes_gpc()) 
		{
	      		$username = addslashes($username);
      		}

      		$q = "SELECT password FROM ".TBL_USERS." WHERE user = '$username'";
      		$result = mysql_query($q, $this->connection);
  
    		if(!$result || (mysql_numrows($result) < 1))
		{
         		return 1; 
      		}

      		$dbarray = mysql_fetch_array($result);
      		$dbarray['password'] = stripslashes($dbarray['password']);
      		$password = stripslashes($password);

		/* Captcha validation */

		$sql2 = "SELECT count(*) FROM t_captcha WHERE captcha='".(stripslashes($captcha))."'";
		$result_b = mysql_query($sql2);

		if (@$row = mysql_fetch_array($result_b))
		{
        		if(!$row[0]>0) 
			{
				return 1;
			}
                }

		/* Password validation */

      		if(sha1($password) == $dbarray['password'])
		{
         		return 0;
      		}
      		else
		{
         		return 1; 
      		}
   	}	
   
   	function confirmUserName($username)
	{
      		if(!get_magic_quotes_gpc()) 
		{
	      		$username = addslashes($username);
      		}

      		$q = "SELECT * FROM ".TBL_USERS." WHERE user = '$username'";
      		$result = mysql_query($q, $this->connection);
      		
		if(!$result || (mysql_numrows($result) < 1))
		{
         		return 1;
      		} 
	  
	  	return 0;
   	}
   
   	function clearLoginAttempts($value) 
	{
    		$q = "UPDATE ".TBL_ATTEMPTS." SET attempts = 0 WHERE ip = '$value'"; 
		return mysql_query($q, $this->connection);
   	}
   
   	function getUserInfo($username)
	{
      		$q = "SELECT * FROM ".TBL_USERS." WHERE user = '$username'";
      		$result = mysql_query($q, $this->connection);
 
     		if(!$result || (mysql_numrows($result) < 1))
		{
         		return NULL;
      		}
      
		$dbarray = mysql_fetch_array($result);
      		return $dbarray;
   	}
     
	function getUserDomain($username)
        {
                $q = "SELECT domain FROM ".TBL_USERS." WHERE user = '$username'";
                $result = mysql_query($q, $this->connection);

                if(!$result || (mysql_numrows($result) < 1))
                {
                        return NULL;
                }

                $dbarray = mysql_fetch_array($result);
                return $dbarray;
        }
 
   	function displayAttempts($value)
	{
   		$q = "SELECT ip, attempts,lastlogin FROM ".TBL_ATTEMPTS." WHERE ip = '$value' ORDER BY lastlogin";
   		$result = mysql_query($q, $this->connection);
   		$num_rows = mysql_numrows($result);

   		if($num_rows == 0)
		{
			echo "You have 3 available login attempts before locking your account";
      			return;
   		}

   		for($i=0; $i<$num_rows; $i++)
		{
      			$uip  = mysql_result($result,$i,"ip");
      			$uattempt = mysql_result($result,$i,"attempts");
	  		$ulogin = mysql_result($result,$i,"lastlogin");
	  
      			echo "Your failed attempts: $uattempt from $uip at $ulogin";
   		}
   	}
};

$database = new MySQLDB;
include "lbs/close-db-connection.php";

?>
