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
 * Description: Code for Fraud Tree
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

/* Prevent direct access to this URL */ 

if(!isset($_SERVER['HTTP_REFERER']))
{
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403);
    exit;
}

include "../lbs/globalVars.php";

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .window-footer-config
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container-tree
    {
        margin: 20px;
    }
    
    .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .font-icon-gray 
    { 
        color: #B4BCC2;
    }
    
    .fa-padding 
    { 
        padding-right: 5px; 
    }

    .btn-success, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
        border: 1px solid #4B906F !important;
    }

    .btn-success:hover
    {
        background-color: #57a881 !important;
        border: 1px solid #57a881 !important;
    }

    .fraud-tree-table td
    {
        border: 1px solid white;
        vertical-align: middle;
        text-align: center;
        width: 50%;
        padding: 10px 0px 10px 0px;
    }

    .fraud-tree-table th
    {
        vertical-align: middle;
        text-align: center;
        padding: 20px;
        border: 1px solid white;
        min-width: 186px;
        font-size: 11px;
    }

    .corruption
    {
        background-color: #F2F2F2;
    }

    .financial
    {
        background-color: #F2F2F2;
    }

    .assets
    {
        background-color: #E8E8E8;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Corporate fraud we detect</h4>
</div>

<div class="div-container-tree">

    <table class="fraud-tree-table">
    <thead>
        <tr>
            <th colspan="2" class="corruption" style="border-radius: 8px 0px 0px 0px;">Corruption</th>
            <th colspan="2" class="assets">Asset Misappropriation</th>
            <th colspan="2" class="financial" style="border-radius: 0px 8px 0px 0px;">Financial Fraud</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="corruption">Conflicts of interest</td>
            <td class="corruption">Bribery</td>
            <td class="assets">Cash</td>
            <td class="assets">Inventory<br>Other Assets</td>
            <td class="financial">Income<br>Overstate</td>
            <td class="financial">Income<br>Understate</td>
        <tr>
        <tr>
            <td class="corruption">Illegal<br>Gratuities</td>
            <td class="corruption">Economic Extortion</td>
            <td class="assets">Theft of Cash<br>on Hand</td>
            <td class="assets">Theft of Cash Receipts</td>
            <td class="financial">Timing<br>Differences</td>
            <td class="financial">Fictitious<br>Revenues</td>
        </tr>
        <tr>
            <td class="corruption">Purchasing Schemes</td>
            <td class="corruption">Sales<br>Schemes</td>
            <td class="assets">Fraudulent<br>Disbursements</td>
            <td class="assets">Skimming</td>
            <td class="financial">Concealed<br>Liabilities</td>
            <td class="financial">Improper Asset Valuations</td>
        </tr>
        <tr>
            <td class="corruption">Invoice Kickbacks</td>
            <td class="corruption">Bid<br>Rigging</td>
            <td class="assets">Misuse</td>
            <td class="assets">Larceny</td>
            <td class="financial">Improper Disclosures</td>
            <td class="financial">Understated Revenues</td>
        </tr>
        <tr>
            <td class="assets"><b>Cash<br>Larceny</b></td>
            <td class="assets"><b>Billing<br>Schemes</b></td>
            <td class="assets"><b>Payroll<br>Schemes</b></td>
            <td class="assets"><b>Expense<br>Reimburs</b></td>
            <td class="assets"><b>Check<br>Tampering</b></td>
            <td class="assets"><b>Register Disburs</b></td>
        </tr>
        <tr>
            <td class="assets">Asset<br>Requisitions</td>
            <td class="assets">False<br>Sales</td>
            <td class="assets">Purchasing<br>and Receiving</td>
            <td class="assets">Unconcealed<br>Larceny</td>
            <td class="assets">Sales</td>
            <td class="assets">Receivables</td>
        </tr>
        <tr>
            <td class="assets">Refunds and Other</td>
            <td class="assets">Unrecorded</td>
            <td class="assets">Understated</td>
            <td class="assets">Write-Off<br>Schemes</td>
            <td class="assets">Lapping<br>Schemes</td>
            <td class="assets">Unconcealed</td>
        </tr>
        <tr>
            <td class="assets">Shell<br>Company</td>
            <td class="assets">Ghost<br>Employee</td>
            <td class="assets">Mischaracter<br>Expenses</td>
            <td class="assets">Forged<br>Maker</td>
            <td class="assets">False<br>Voids</td>
            <td class="assets">NonAccomplice Vendor</td>
        </tr>
        <tr>
            <td class="assets">Falsified<br>Wages</td>
            <td class="assets">Overstated Expenses</td>
            <td class="assets">Forged<br>Endorsement</td>
            <td class="assets">False<br>Refunds</td>
            <td class="assets">Personal<br>Purchases</td>
            <td class="assets">Commission Schemes</td>
        </tr>
        <tr>
            <td class="assets" style="border-radius: 0px 0px 0px 8px;">Fictitious Expenses</td>
            <td class="assets">Altered<br>Payee</td>
            <td class="assets">Multiple<br>Reimburs</td>
            <td class="assets">Authorized Maker</td>
            <td class="assets">Information leakage</td>
            <td class="assets" style="border-radius: 0px 0px 8px 0px;">Illegal access</td>
        </tr>
    </tbody>
    </table>
    
</div>
