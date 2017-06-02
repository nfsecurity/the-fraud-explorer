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
 * Description: Code for login page
 */

?>

<html>
    <head>
        <title>Login &raquo; Analytics</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="icon" type="image/x-icon" href="images/favicon.png?v=2" sizes="32x32">
        <link rel="stylesheet" type="text/css" href="css/index.css" media="screen" />
    </head>
    <body>

        <?php
        
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if(!isset($_SESSION['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) throw new RuntimeException('CSRF');
        }

        $key = sha1(microtime());
        $_SESSION['csrf'] = $key;

        include("lbs/login/session.php");
        $_SESSION['welcome'] = "enable";
        
        ?>

        <div align="center">
            <table>
                <th>Please enter the following data<br></th>
                <tbody>
                    <tr>
                        <td class="login-container">
                            <form id="formLogin" name="formLogin" method="post" action="login">
                                <input type="hidden" name="csrf" value="<?php echo $key; ?>" />
                                <center><br>
                                    <table class="sub-container">
                                        <tr>
                                            <td>
                                                Login
                                            </td>
                                            <td>
                                                <input type="text" name="user" id="user" autofocus="autofocus" autocomplete="off" tabindex=1 placeholder=":enter your username" class="input-login" maxlength="30" value="<?php echo $form->value("user"); ?>">
                                            </td>
                                            <td rowspan="3" style="border-top:0px solid #e0e0e0; border-right:0px solid #e0e0e0;">
                                                <center><img src="captcha"/></center><br>
                                                &nbsp;&nbsp;<input type="submit" name="loginok" value="Sign In Now" class="sign-in-button">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Password&nbsp;&nbsp;
                                            </td>
                                            <td>
                                                <input type="password" name="pass" id="pass" tabindex=2 placeholder=":enter your password" class="input-login" maxlength="60" value="<?php echo $form->value("pass"); ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Captcha&nbsp;&nbsp;
                                            </td>
                                            <td>
                                                <input type="captcha" name="captcha" id="captcha" autocomplete="off" tabindex=3 placeholder=":enter captcha value" class="input-login" maxlength="10" value="<?php echo $form->value("captcha"); ?>"> 
                                            </td>
                                            <input type="hidden" name="sublogin" value="1">
                                        </tr>
                                    </table><br>
                                </center>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>

            <?php
            
            if($form->num_errors > 0) echo "<div class=\"failed-logins\">".$form->error("access")." ".$form->error("attempt")."</div>";
            
            ?>

            <div class="failed-attempts">
                <p><?php $database->displayAttempts($session->ip);?></p>
            </div>
        </div>
    </body>
</html>