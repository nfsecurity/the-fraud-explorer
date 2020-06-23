<?php

/*
 * The Fraud Explorer 
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-07
 * Revision: v1.4.6-aim
 *
 * Description: Code for login
 */

include "lbs/login/session.php";

define("LOCATION_INDEX", "Location: index");

class Process
{
    function __construct()
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
            header(LOCATION_INDEX);
        }
    }

    function procLogin()
    {
        global $session, $form;
        $retval = $session->login($_POST['user'], $_POST['pass']);

        if($retval)
        {
            header("Location: ".$session->referrer);
        }
        else
        {
            $_SESSION['value_array'] = $_POST;
            $_SESSION['error_array'] = $form->getErrorArray();
            header(LOCATION_INDEX);
        }
    }

    function procLogout()
    {
        global $session;
        $retval = $session->logout();
        header(LOCATION_INDEX);
    }
}

$process = new Process;

?>
