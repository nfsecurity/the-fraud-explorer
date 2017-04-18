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
 * Date: 2017-04
 * Revision: v0.9.67-beta
 *
 * Description: Code for login
 */

include "lbs/login/session.php";

class Process
{
    function Process()
    {
        global $session;

        if(isset($_POST['sublogin']))
        {
            $this->procLogin();
        }
        else if($session->logged_in)
        {
            $this->procLogout();
        }
        else
        {
            header("Location: index");
        }
    }

    function procLogin()
    {
        global $session, $form;
        $retval = $session->login($_POST['user'], $_POST['pass'], $_POST["captcha"]);

        if($retval)
        {
            header("Location: ".$session->referrer);
        }
        else
        {
            $_SESSION['value_array'] = $_POST;
            $_SESSION['error_array'] = $form->getErrorArray();
            header("Location: index");
        }
    }

    function procLogout()
    {
        global $session;
        $retval = $session->logout();
        header("Location: index");
    }
};

$process = new Process;

?>