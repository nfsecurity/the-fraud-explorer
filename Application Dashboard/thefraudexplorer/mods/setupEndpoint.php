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
 * Description: Code for setup endpoint
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
include "../lbs/openDBconn.php";
include "../lbs/cryptography.php";

$endpointEnc = filter($_GET['nt']);
$endpointDec = decRijndael($endpointEnc);

?>

<style>

    .title
    {
        font-family: 'FFont', sans-serif; font-size:12px;
    }

    .input-value-text
    {
        width:100%; 
        height: 30px; 
        padding: 5px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }

    .window-footer
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container
    {
        margin: 20px;
    }

    .select-ruleset-styled, .select-gender-styled
    {
        margin-right: 0px;
        min-height: 30px !important;
        max-height: 30px !important;
        padding: 8px 0px 8px 10px;
        line-height: 11.6px;
        border: 1px solid #ccc;
        color: #757575;
    }

    .select-ruleset-styled .list, .select-gender-styled .list
    {
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        overflow-y: scroll;
        max-height: 200px !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Endpoint setup</h4>
</div>

<?php

/* SQL Queries */

$queryName = "SELECT name FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, heartbeat FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";
$queryRule = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, heartbeat FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";
$queryGender = "SELECT gender FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, gender, heartbeat FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";

?>

<div class="div-container">
    <form id="formSetup" name="formSetup" method="post" action="<?php echo 'mods/setupEndpointParameters?nt='.$endpointEnc; ?>">
        <p class="title">Endpoint alias</p><br>
        <input type="text" name="alias" id="alias" autocomplete="off" placeholder=":alias here <?php $aliasquery = mysqli_query($connection, sprintf($queryName, $endpointDec)); $alias = mysqli_fetch_array($aliasquery); if ($alias[0] == NULL) echo '(current value: Not alias yet)'; else echo '(current value: '.$alias[0].')'; ?>" class="input-value-text">
        <br><br><p class="title">Ruleset or Dictionary</p><br>

        <select class="select-ruleset-styled wide" name="ruleset" id="ruleset">
            <option selected="selected"><?php $rulesetquery = mysqli_query($connection, sprintf($queryRule, $endpointDec)); $ruleset = mysqli_fetch_array($rulesetquery); $selectedRuleset = $ruleset[0]; if ($selectedRuleset == NULL) echo 'BASELINE'; else echo $selectedRuleset; ?></option>

            <?php

            $configFile = parse_ini_file("../config.ini");
            $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
            $GLOBALS['listRuleset'] = null;

            foreach ($jsonFT['dictionary'] as $ruleset => $value)
            {
                if ($selectedRuleset != $ruleset) echo '<option value="'.$ruleset.'">'.$ruleset.'</option>';
            }

            ?>
        </select> 

        <br><br><br><p class="title">Endpoint gender</p><br>

        <select class="select-gender-styled wide" name="gender" id="gender">
            <option selected="selected"><?php $genderquery = mysqli_query($connection, sprintf($queryGender, $endpointDec)); $gender = mysqli_fetch_array($genderquery); $selectedGender = $gender[0]; if ($selectedGender == NULL) { echo 'Male'; $selectedGender = "male"; } else echo ucfirst($selectedGender); ?></option>
            <?php if ($selectedGender != "male") echo '<option value="male">Male</option>'; ?>
            <?php if ($selectedGender != "female") echo '<option value="female">Female</option>'; ?>
        </select>

        <br><br><br><br>
        <div class="modal-footer window-footer">
            <br><button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
            <input type="submit" class="btn btn-danger setup" value="Set values" style="outline: 0 !important;">
        </div>
    </form>
</div>

<?php include "../lbs/closeDBconn.php"; ?>

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>
