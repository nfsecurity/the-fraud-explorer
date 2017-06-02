<?php

/*
 * The Fraud Explorer 
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-06
 * Revision: v1.0.1-beta
 *
 * Description: Code for login
 */

include "database.php";
include "form.php";

class Session
{
    var $username;
    var $userid;  
    var $userdomain = array();
    var $domain;
    var $userlevel;
    var $time;     
    var $logged_in;
    var $userinfo = array(); 
    var $url;                
    var $referrer;           
    var $ip;                   

    function Session()
    {
        $this->ip = $_SERVER["REMOTE_ADDR"];
        $this->time = time();
        $this->startSession();
    }

    function startSession()
    {
        global $database;  
        session_start();   

        $this->logged_in = $this->checkLogin();

        if(isset($_SESSION['url']))
        {
            $this->referrer = $_SESSION['url'];
        }
        else
        {
            $this->referrer = "/";
        }

        $this->url = $_SESSION['url'] = "dashBoard";
    }

    function checkLogin()
    {
        global $database; 

        if(isset($_SESSION['username']))
        {
            if($database->confirmUserName($_SESSION['username']) != 0)
            {
                unset($_SESSION['username']);
                return false;
            }

            $this->userinfo  = $database->getUserInfo($_SESSION['username']);
            $this->username  = $this->userinfo['user'];
            $this->userdomain  = $database->getUserDomain($_SESSION['username']);        
            $this->domain  = $this->userdomain['domain'];

            return true;
        }
        else
        {
            return false;
        }
    }

    function login($subuser, $subpass, $subcaptcha)
    {
        global $database, $form;  
        $result = $database->confirmIPAddress($this->ip);

        if($result == 1)
        {
            $error_type = "access";
            $form->setError($error_type, "Access denied for ".TIME_PERIOD." minutes");
        } 

        if($form->num_errors > 0)
        {
            return false;
        }

        $error_type = "attempt";

        if(!$subuser || !$subpass || !$subcaptcha || strlen($subuser = trim($subuser)) == 0)
        {
            $form->setError($error_type, "Username, password or captcha not entered");
        }

        if($form->num_errors > 0)
        {
            return false;
        }

        $subuser = stripslashes($subuser);
        $result = $database->confirmUserPass($subuser, $subpass, $subcaptcha);

        if($result == 1)
        {
            $form->setError($error_type, "Invalid username, password or captcha");
            $database->addLoginAttempt($this->ip);
        }

        if($form->num_errors > 0)
        {
            return false;
        }

        $this->userinfo  = $database->getUserInfo($subuser);
        $this->username  = $_SESSION['username'] = $this->userinfo['user'];
        $database->clearLoginAttempts($this->ip);

        return true;
    }

    function logout()
    {
        global $database;  
        unset($_SESSION['username']);
        $this->logged_in = false;
    }
};

$session = new Session;
$form = new Form;

?>

