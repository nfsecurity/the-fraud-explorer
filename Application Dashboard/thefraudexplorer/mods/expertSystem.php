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
 * Date: 2020-02
 * Revision: v1.4.2-aim
 *
 * Description: Code for artificial intelligence
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";
include "../lbs/endpointMethods.php";
require '../vendor/autoload.php';
include "../lbs/elasticsearch.php";

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

    .div-container
    {
        margin: 20px;
    }

    .table-expert
    {
        font-family: 'FFont', sans-serif; font-size:10px;
        border: 0px solid gray;
        width: 100%;
        border-spacing: 0px;
        border-collapse: collapse;
        border-radius: 5px;
    }

    .table-thead-expert
    {
        display: block;
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        width: 100%;
        height: 45px;
    }

    .table-th-expert
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: calc(555px / 6);
        width: calc(555px / 6);
        text-align: center;
        padding: 0px 0px 0px 0px;
        height: 45px;
    }

    .table-th-expert-last
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: calc(555px / 6);
        width: calc(555px / 6);
        text-align: center;
        padding: 0px 8px 0px 0px;
        height: 45px;
    }

    .table-tbody-expert
    {
        display: block;
        border: 1px solid #e8e9e8;
        width: 100%;
        height: auto !important; 
        max-height: 124px !important;
        overflow-y: scroll;
        border-radius: 5px;
    }

    .table-tr-expert
    {
        border: 0px solid gray;
        height: 30px;
        min-height: 30px;
        background: white;
    }

    .table-tbody-expert tr:nth-child(odd)
    {
        background-color: #EDEDED !important;
    }

    .table-td-expert
    {
        border: 0px solid gray;
        width: calc(555px / 6);
        min-width: calc(555px / 6);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 2px solid white;
    }

     .table-td-expert-app
    {
        border: 0px solid gray;
        width: calc(555px / 6);
        min-width: calc(555px / 6);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 10px;
        text-align: left;
        border-right: 2px solid white;
        white-space: nowrap; 
        text-overflow: ellipsis;
    }
    
    .table-td-expert-why
    {
        border: 0px solid gray;
        width: calc(555px / 6);
        min-width: calc(555px / 6);
        height: 30px;
        min-height: 30px;
        background: #e8e9e8; 
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 2px solid white;
    }
    
    .table-td-expert-view
    {
        border: 0px solid gray;
        width: calc(555px / 6);
        min-width: calc(555px / 6 - 7);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 0px solid white;
    }
    
    .table-td-expert-endpoint
    {
        border: 0px solid gray;
        width: calc(555px / 6);
        min-width: calc(555px / 6);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: center;
        border-right: 0px solid white;
        font-family: 'FFont', sans-serif; font-size: 10px;
    }

    .not-inferences
    {
        text-align: justify;
        font-family: 'FFont', sans-serif; font-size: 12px;
    }

    .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .footer-statistics
    {
        background-color: #e8e9e8;
        border-radius: 5px 5px 5px 5px;
        padding: 8px 8px 8px 8px;
        margin: 0px 0px 15px 0px;
    }
    
    .font-icon-gray 
    { 
        color: #B4BCC2;; 
    }
    
    .fa-padding 
    { 
        padding-right: 5px; 
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">A.I Fraud expert deductions</h4>
</div>

<?php

    /* Elasticsearch variables */

    $client = Elasticsearch\ClientBuilder::create()->build();
    $configFile = parse_ini_file("../config.ini");
    $ESAlerterIndex = $configFile['es_alerter_index'];

    /* Expert System Inference Presentation */

    $fraudTriangleHeight = ['pressure' => 50, 
        'opportunity' => 20, 
        'rationalization' => 30];

    $fraudProbability = ['almost' => $fraudTriangleHeight['pressure'] + $fraudTriangleHeight['opportunity'] + $fraudTriangleHeight['rationalization'], 
        'very' => $fraudTriangleHeight['pressure'] + $fraudTriangleHeight['rationalization'], 
        'maybe' => $fraudTriangleHeight['pressure'] + $fraudTriangleHeight['opportunity'], 
        'less' => $fraudTriangleHeight['opportunity'] + $fraudTriangleHeight['rationalization']];

    /* Rules count */

    $fraudTriangleTerms = array('0'=>'pressure','1'=>'opportunity','2'=>'rationalization');
    $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
    $dictionaryCount = array();
    $phrasesCount = 0;

    foreach ($jsonFT['dictionary'] as $ruleset => $value)
    {
        foreach($fraudTriangleTerms as $term)
        {
            foreach ($jsonFT['dictionary'][$ruleset][$term] as $field => $termPhrase)
            {
                @$dictionaryCount[$ruleset][$term]++;
                $phrasesCount++;
            }
        }      
    }

    /* SQL queries */

    if ($_SESSION['rulesetScope'] == "ALL")
    {
        $queryDeductions = "SELECT * from t_inferences";        
        $queryDeductions_wOSampler = "SELECT * from t_inferences WHERE domain NOT LIKE 'thefraudexplorer.com'";
        $queryDeductionsDomain = "SELECT * from t_inferences WHERE domain = '".$session->domain."' OR domain = 'thefraudexplorer.com'";
        $queryDeductionsDomain_wOSampler = "SELECT * from t_inferences WHERE domain = '".$session->domain."'";
    }
    else
    {
        $queryDeductions = "SELECT * from t_inferences WHERE ruleset = '".$_SESSION['rulesetScope']."'";        
        $queryDeductions_wOSampler = "SELECT * from t_inferences WHERE domain NOT LIKE 'thefraudexplorer.com' AND ruleset = '".$_SESSION['rulesetScope']."'";
        $queryDeductionsDomain = "SELECT * from t_inferences WHERE domain = '".$session->domain."' OR domain = 'thefraudexplorer.com' AND ruleset = '".$_SESSION['rulesetScope']."'";
        $queryDeductionsDomain_wOSampler = "SELECT * from t_inferences WHERE domain = '".$session->domain."' AND ruleset = '".$_SESSION['rulesetScope']."'";
    }

    if ($session->domain == "all")
    {
        if (samplerStatus($session->domain) == "enabled")
        {
            $result_a = mysqli_query($connection, $queryDeductions);
        }
        else
        {
            $result_a = mysqli_query($connection, $queryDeductions_wOSampler);
        }
    }
    else
    {
        if (samplerStatus($session->domain) == "enabled")
        {
            $result_a = mysqli_query($connection, $queryDeductionsDomain);
        }       
        else
        {
            $result_a = mysqli_query($connection, $queryDeductionsDomain_wOSampler);
        }
    }
    
    if(mysqli_num_rows($result_a) == 0)
    {
        echo '<div class="div-container">';
        echo '<p class="not-inferences">There is no deductions or inferences at the moment. When we have one we will show it here, please come back later and please note that the artificial intelligence engine (through the expert system) runs every hour or every time your administrator scheduled it.</p><br>';
        echo '<div class="footer-statistics"><span class="fa fa-cogs font-aw-color fa-padding"></span>There are '.$phrasesCount.' acts in the knowledge base and '.count($fraudProbability).' rules in the Inference Engine</div>';
        echo '<div class="modal-footer window-footer-config">';
        echo '<br>';
        echo '<button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">I will return later</button>';
        echo '<a href="https://github.com/nfsecurity/the-fraud-explorer/issues" target="_blank" class="btn btn-danger" style="outline: 0 !important;">I think AI is failing</a>';
        echo '</div>';
        echo '</div>';

        exit();
    }

?>

<div class="div-container">
    <table class="table-expert">
        <thead class="table-thead-expert">
            <th class="table-th-expert" style="text-align: left;">&ensp;ENDPOINT</th>
            <th class="table-th-expert">PROBABLE</th>
            <th class="table-th-expert">WHEN</th>
            <th class="table-th-expert">APP</th>
            <th class="table-th-expert">WHY</th>
            <th class="table-th-expert-last">VIEW</th>
        </thead>
        <tbody class="table-tbody-expert">

            <?php

            if ($row_a = mysqli_fetch_array($result_a))
            {
                do
                {
                    $application = (strlen($row_a['application']) > 12) ? substr($row_a['application'], 0, 7) . " ..." : $endpointsFraud['agent'] ;
                    $timeDate = substr($row_a['date'], 0, 10);
                    $endpoint = (strlen($row_a['endpoint']) > 12) ? substr($row_a['endpoint'], 0, 12) . " ..." : $row_a['endpoint'];

                    echo '<tr class="table-tr-expert">';
                    echo '<td class="table-td-expert-endpoint" style="text-align: left; border-right: 2px solid white;"><span class="fa fa-user-circle font-icon-color-green fa-padding"></span>'.$endpoint.'</td>';
                    echo '<td class="table-td-expert"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>'.$row_a['deduction'].' %</td>';
                    echo '<td class="table-td-expert"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>'.$timeDate.'</td>';
                    echo '<td class="table-td-expert-app"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>'.$application.'</td>';
                    echo '<td class="table-td-expert-why">'.$row_a['reason'].'</td>';
                    echo '<td class="table-td-expert-view"><a id="viewAlert" href="#" onclick="showAlert(this.id, \''.$row_a['alertid'].'\')"><span class="fa fa-diamond fa-lg font-icon-color-green"></span></a></td>';
                    echo '</tr>';
                }
                while ($row_a = mysqli_fetch_array($result_a));
            }    
            
            ?>

        </tbody>
    </table>

    <div id="alertPanel"></div>

    <?php
    
    echo '<br><div class="footer-statistics"><span class="fa fa-cogs font-aw-color fa-padding"></span>There are '.$phrasesCount.' acts in the knowledge base and '.count($fraudProbability).' rules in the Inference Engine</div>';
    
    ?>

    <div class="modal-footer window-footer-config">
        <br>
        <button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">Good inferences</button>
        <a href="https://github.com/nfsecurity/the-fraud-explorer/issues" target="_blank" class="btn btn-danger" style="outline: 0 !important; color: white;">I think AI is failing</a>
    </div>
</div> 

<!-- Script for alert view -->

<script>

function showAlert(clicked_id, alertid)
{
    if (clicked_id == "viewAlert")
    {
        $("#alertPanel").load("mods/alertAIPhrases.php?alertID=" + alertid);
    }
 }

</script>