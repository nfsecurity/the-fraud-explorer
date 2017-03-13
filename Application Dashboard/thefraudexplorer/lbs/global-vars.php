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
 * Date: 2017-04 15:12:41 -0500 (Wed, 30 Jun 2016)
 * Revision: v0.9.9-beta
 *
 * Description: Code for global vars
 */

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
$serverURL = $configFile['php_server_url'];
$documentRoot = $configFile['php_document_root'];

?>
