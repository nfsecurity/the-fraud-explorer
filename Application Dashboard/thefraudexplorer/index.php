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
 * Date: 2020-05
 * Revision: v1.4.4-aim
 *
 * Description: Code for login page
 */

?>

<html>
    <head>
        <title>Login &raquo; Analytics</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="icon" type="image/x-icon" href="images/favicon.png?v=2" sizes="32x32">
        <link rel="stylesheet" type="text/css" href="css/index.css?<?php echo filemtime('css/index.css') ?>" media="screen" />
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
            <table class="login-table">
            <th>Let's get login</th>
                <tbody>
                    <tr>
                        <td class="login-container">
                            <form id="formLogin" name="formLogin" method="post" action="login">
                                <input type="hidden" name="csrf" value="<?php echo $key; ?>" />
                                <br>
                                    <table class="sub-container">
                                        <tr>
                                            <td>
                                                Username
                                            </td>
                                            <td>
                                                <input type="text" name="user" id="user" autofocus="autofocus" autocomplete="off" tabindex=1 placeholder=":enter your company username" class="input-login" maxlength="30" value="<?php echo $form->value("user"); ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Passphrase
                                            </td>
                                            <td>
                                                <input type="password" name="pass" id="pass" tabindex=2 placeholder=":enter your assigned password" class="input-login" maxlength="60" value="<?php echo $form->value("pass"); ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <input type="submit" name="loginok" value="Secure login to the platform" class="sign-in-button">
                                            </td>
                                            <input type="hidden" name="sublogin" value="1">
                                        </tr>
                                    </table>
                            </form>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="table-footer">Realtime Analytics Platform System with Artificial Intelligence</td>
                    </tr>
                </tfoot>
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
